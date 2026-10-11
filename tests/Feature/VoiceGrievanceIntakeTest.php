<?php

use App\Http\Middleware\HandleInertiaRequests;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Grievance\Models\Grievance;
use Modules\Grievance\Models\VoiceGrievance;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutMiddleware(HandleInertiaRequests::class);
});

it('renders a separate voice grievance entry page', function () {
    $this->get('/lodge-by-voice', ['X-Inertia' => 'true'])
        ->assertOk()
        ->assertJsonPath('component', 'Frontend::voice-grievance');
});

it('stores a public audio submission on the private disk', function () {
    Storage::fake('local');

    $response = $this->post('/lodge-by-voice', [
        'audio' => fakeWaveRecording(),
        'duration_seconds' => 42,
        'language_selected' => 'st',
        'contact_phone' => '+266 5900 1234',
    ]);

    $response->assertSessionHasNoErrors();
    $voiceGrievance = VoiceGrievance::query()->firstOrFail();

    $response->assertRedirect(route('voice-grievance.received', $voiceGrievance->reference_no));
    expect($voiceGrievance->reference_no)->toStartWith('VGR-')
        ->and($voiceGrievance->status)->toBe('received')
        ->and($voiceGrievance->language_selected)->toBe('st')
        ->and($voiceGrievance->duration_seconds)->toBe(42);

    Storage::disk('local')->assertExists($voiceGrievance->audio_path);
});

it('rejects non-audio uploads and unsupported language codes', function () {
    $this->post('/lodge-by-voice', [
        'audio' => UploadedFile::fake()->create('note.txt', 1, 'text/plain'),
        'language_selected' => 'xx',
    ])->assertSessionHasErrors(['audio', 'language_selected']);

    expect(VoiceGrievance::query()->count())->toBe(0);
});

it('serves voice audio only through an authenticated private response', function () {
    Storage::fake('local');
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::findOrCreate('grievances.view', 'web'));
    $this->actingAs($user);

    Storage::disk('local')->put('voice-grievances/private.wav', 'private audio');
    $voiceGrievance = VoiceGrievance::query()->create([
        'reference_no' => 'VGR-'.Str::ulid(),
        'audio_path' => 'voice-grievances/private.wav',
        'audio_mime' => 'audio/wav',
        'language_selected' => 'st',
        'status' => 'received',
    ]);

    $this->get(route('voice-grievances.audio', $voiceGrievance))
        ->assertOk()
        ->assertHeader('Cache-Control', 'no-store, private')
        ->assertStreamedContent('private audio');
});

it('files a reviewed voice recording through the regular grievance registration service', function () {
    Queue::fake();
    $user = User::factory()->create();
    Role::query()->create([
        'name' => 'Director',
        'name_st' => 'Motsamaisi',
        'code' => 'DIRECTOR',
        'status' => true,
        'guard_name' => 'web',
    ]);
    $user->givePermissionTo([
        Permission::findOrCreate('grievances.view', 'web'),
        Permission::findOrCreate('grievances.create', 'web'),
    ]);
    $this->actingAs($user);

    $now = now();
    $categoryId = DB::table('grievance_categories')->insertGetId([
        'ulid' => (string) Str::ulid(),
        'code' => 'VOI',
        'name_en' => 'Voice Category',
        'name_st' => 'Sehlopha sa lentsoe',
        'slug' => 'voice-category',
        'created_at' => $now,
        'updated_at' => $now,
    ]);
    DB::table('grievance_channels')->insert([
        'ulid' => (string) Str::ulid(),
        'code' => 'helpdesk',
        'name' => 'Helpdesk Officer',
        'created_at' => $now,
        'updated_at' => $now,
    ]);
    $voiceGrievance = VoiceGrievance::query()->create([
        'reference_no' => 'VGR-'.Str::ulid(),
        'audio_path' => 'voice-grievances/test.wav',
        'audio_mime' => 'audio/wav',
        'language_selected' => 'st',
        'contact_phone' => '+266 5900 1234',
        'status' => 'received',
    ]);

    $response = $this->post(route('voice-grievances.file', $voiceGrievance), [
        'transcript_original' => 'Ke tlaleha bothata tseleng.',
        'transcript_english' => 'I am reporting a problem on the road near my home.',
        'language_detected' => 'st',
        'category_id' => $categoryId,
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();
    $voiceGrievance->refresh();
    $grievance = Grievance::query()->findOrFail($voiceGrievance->grievance_id);

    $response->assertRedirect(route('grievances.edit', $grievance));
    expect($voiceGrievance->status)->toBe('filed')
        ->and($voiceGrievance->language_detected)->toBe('st')
        ->and($grievance->description)->toBe('I am reporting a problem on the road near my home.')
        ->and($grievance->metadata['voice_grievance_reference'])->toBe($voiceGrievance->reference_no);
});

function fakeWaveRecording(): UploadedFile
{
    $samples = str_repeat("\0", 1600);
    $header = 'RIFF'.pack('V', 36 + strlen($samples))
        .'WAVEfmt '.pack('VvvVVvv', 16, 1, 1, 8000, 16000, 2, 16)
        .'data'.pack('V', strlen($samples));

    return UploadedFile::fake()->createWithContent('complaint.wav', $header.$samples);
}
