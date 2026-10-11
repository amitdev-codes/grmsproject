import { Button } from '@/components/ui/button';
import { Link, useForm } from '@inertiajs/react';
import { ArrowLeft, FileAudio } from 'lucide-react';
import { route } from 'ziggy-js';
import IndexLayout from '../../../../../../resources/js/components/index-layout';

interface VoiceGrievance {
    id: number;
    reference_no: string;
    audio_mime: string;
    duration_seconds: number | null;
    language_selected: string | null;
    language_detected: string | null;
    transcript_original: string | null;
    transcript_english: string | null;
    contact_phone: string | null;
    status: string;
    assigned_officer: { id: number; name: string } | null;
    grievance: { id: number; reference_no: string } | null;
    created_at: string;
}

interface Option {
    id: number;
    name: string;
}

interface VoiceGrievanceShowProps {
    voiceGrievance: VoiceGrievance;
    audioUrl: string;
    categories: { id: number; name_en: string }[];
    districts: Option[];
}

interface ReviewForm {
    transcript_original: string;
    transcript_english: string;
    language_detected: string;
    category_id: string;
    district_id: string;
}

export default function VoiceGrievanceShow({
    voiceGrievance,
    audioUrl,
    categories,
    districts,
}: VoiceGrievanceShowProps) {
    const filed = voiceGrievance.grievance !== null;
    const form = useForm<ReviewForm>({
        transcript_original: voiceGrievance.transcript_original ?? '',
        transcript_english: voiceGrievance.transcript_english ?? '',
        language_detected: voiceGrievance.language_detected ?? '',
        category_id: '',
        district_id: '',
    });

    const submit = (action: 'review' | 'file') => {
        const target = route(
            action === 'review'
                ? 'voice-grievances.review'
                : 'voice-grievances.file',
            voiceGrievance.id,
        );
        form.post(target, { preserveScroll: true });
    };

    return (
        <IndexLayout
            title="Review voice grievance"
            breadcrumbs={[
                { label: 'Grievances', href: '/grievances' },
                { label: 'Voice grievance inbox', href: '/voice-grievances' },
                { label: voiceGrievance.reference_no, href: '#' },
            ]}
        >
            <div className="mx-auto w-full max-w-4xl space-y-4">
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h1 className="text-2xl font-semibold">
                            Review voice grievance
                        </h1>
                        <p className="font-mono text-sm text-muted-foreground">
                            {voiceGrievance.reference_no}
                        </p>
                    </div>
                    <Button asChild variant="outline">
                        <Link href={route('voice-grievances.index')}>
                            <ArrowLeft className="mr-2 h-4 w-4" />
                            Back to inbox
                        </Link>
                    </Button>
                </div>

                <section className="space-y-4 rounded-lg border bg-card p-5">
                    <h2 className="flex items-center gap-2 font-semibold">
                        <FileAudio className="h-5 w-5" />
                        Audio recording
                    </h2>
                    <dl className="grid gap-3 text-sm sm:grid-cols-3">
                        <div>
                            <dt className="text-muted-foreground">Language reported</dt>
                            <dd>{voiceGrievance.language_selected ?? 'Unknown'}</dd>
                        </div>
                        <div>
                            <dt className="text-muted-foreground">Received</dt>
                            <dd>{new Date(voiceGrievance.created_at).toLocaleString()}</dd>
                        </div>
                        <div>
                            <dt className="text-muted-foreground">Contact phone</dt>
                            <dd>{voiceGrievance.contact_phone ?? 'Not provided'}</dd>
                        </div>
                    </dl>
                    <audio
                        controls
                        preload="metadata"
                        src={audioUrl}
                        className="w-full"
                        aria-label={`Audio recording ${voiceGrievance.reference_no}`}
                    />
                </section>

                {filed ? (
                    <section className="rounded-lg border bg-card p-5">
                        <h2 className="font-semibold">Already filed</h2>
                        <p className="mt-2 text-sm">
                            Grievance reference:{' '}
                            <span className="font-mono">
                                {voiceGrievance.grievance?.reference_no}
                            </span>
                        </p>
                    </section>
                ) : (
                    <section className="space-y-5 rounded-lg border bg-card p-5">
                        <div>
                            <h2 className="font-semibold">Officer review</h2>
                            <p className="mt-1 text-sm text-muted-foreground">
                                Enter what the caller said, then prepare an English
                                translation. Review it against the audio before filing.
                            </p>
                        </div>

                        <div className="space-y-2">
                            <label htmlFor="language_detected" className="block text-sm font-medium">
                                Language detected by officer
                            </label>
                            <select
                                id="language_detected"
                                value={form.data.language_detected}
                                onChange={(event) =>
                                    form.setData('language_detected', event.target.value)
                                }
                                className="min-h-10 w-full rounded-md border bg-background px-3 text-sm"
                            >
                                <option value="">Select language</option>
                                <option value="st">Sesotho</option>
                                <option value="en">English</option>
                                <option value="zu">isiZulu</option>
                                <option value="xh">isiXhosa</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div className="space-y-2">
                            <label htmlFor="transcript_original" className="block text-sm font-medium">
                                Original-language transcript
                            </label>
                            <textarea
                                id="transcript_original"
                                rows={5}
                                value={form.data.transcript_original}
                                onChange={(event) =>
                                    form.setData('transcript_original', event.target.value)
                                }
                                className="w-full rounded-md border bg-background px-3 py-2 text-sm"
                            />
                            {form.errors.transcript_original && (
                                <p role="alert" className="text-sm text-destructive">
                                    {form.errors.transcript_original}
                                </p>
                            )}
                        </div>

                        <div className="space-y-2">
                            <label htmlFor="transcript_english" className="block text-sm font-medium">
                                English translation
                            </label>
                            <textarea
                                id="transcript_english"
                                rows={5}
                                value={form.data.transcript_english}
                                onChange={(event) =>
                                    form.setData('transcript_english', event.target.value)
                                }
                                className="w-full rounded-md border bg-background px-3 py-2 text-sm"
                            />
                            {form.errors.transcript_english && (
                                <p role="alert" className="text-sm text-destructive">
                                    {form.errors.transcript_english}
                                </p>
                            )}
                        </div>

                        <div className="grid gap-4 sm:grid-cols-2">
                            <div className="space-y-2">
                                <label htmlFor="category_id" className="block text-sm font-medium">
                                    Grievance category
                                </label>
                                <select
                                    id="category_id"
                                    value={form.data.category_id}
                                    onChange={(event) =>
                                        form.setData('category_id', event.target.value)
                                    }
                                    className="min-h-10 w-full rounded-md border bg-background px-3 text-sm"
                                >
                                    <option value="">Select category before filing</option>
                                    {categories.map((category) => (
                                        <option key={category.id} value={category.id}>
                                            {category.name_en}
                                        </option>
                                    ))}
                                </select>
                                {form.errors.category_id && (
                                    <p role="alert" className="text-sm text-destructive">
                                        {form.errors.category_id}
                                    </p>
                                )}
                            </div>
                            <div className="space-y-2">
                                <label htmlFor="district_id" className="block text-sm font-medium">
                                    District (optional)
                                </label>
                                <select
                                    id="district_id"
                                    value={form.data.district_id}
                                    onChange={(event) =>
                                        form.setData('district_id', event.target.value)
                                    }
                                    className="min-h-10 w-full rounded-md border bg-background px-3 text-sm"
                                >
                                    <option value="">Select district</option>
                                    {districts.map((district) => (
                                        <option key={district.id} value={district.id}>
                                            {district.name}
                                        </option>
                                    ))}
                                </select>
                                {form.errors.language_detected && (
                                    <p role="alert" className="text-sm text-destructive">
                                        {form.errors.language_detected}
                                    </p>
                                )}
                            </div>
                        </div>

                        <div className="flex flex-wrap justify-end gap-3 border-t pt-4">
                            <Button
                                type="button"
                                variant="outline"
                                disabled={form.processing}
                                onClick={() => submit('review')}
                            >
                                Save transcript
                            </Button>
                            <Button
                                type="button"
                                disabled={form.processing}
                                onClick={() => submit('file')}
                            >
                                File regular grievance
                            </Button>
                        </div>
                        {form.errors && (
                            <p aria-live="polite" className="text-sm text-destructive">
                                {form.errors.category_id}
                            </p>
                        )}
                    </section>
                )}
            </div>
        </IndexLayout>
    );
}
