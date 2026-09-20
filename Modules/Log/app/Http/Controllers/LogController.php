<?php

namespace Modules\Log\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Response;

class LogController extends Controller
{
    protected function authorizeLogs(Request $request): void
    {
        $user = $request->user();
        abort_unless($user && ($user->can('logs.view') || $user->hasRole(['Super Admin', 'Developer'])), 403);
    }

    protected function listParams(Request $request): array
    {
        $search = trim((string) $request->input('search', ''));
        $page = max(1, (int) $request->input('page', 1));
        $perPage = min(100, max(10, (int) $request->input('per_page', 15)));

        return [$search, $page, $perPage];
    }

    public function index(Request $request): Response
    {
        $this->authorizeLogs($request);

        return to_route('activity-logs.index');
    }

    protected function activityLogs(string $search, int $page, int $perPage): array
    {
        $query = DB::table('activity_log')->orderByDesc('created_at');

        if ($search !== '') {
            $query->where(function ($query) use ($search): void {
                $query->where('description', 'like', "%{$search}%")
                    ->orWhere('event', 'like', "%{$search}%")
                    ->orWhere('log_name', 'like', "%{$search}%");
            });
        }

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        return [
            'items' => collect($paginator->items())->map(fn ($row): array => [
                'id' => $row->id,
                'log_name' => $row->log_name,
                'description' => $row->description,
                'event' => $row->event,
                'causer' => $this->actorLabel($row->causer_type, $row->causer_id),
                'attribute_changes' => $this->decodeJson($row->attribute_changes),
                'properties' => $this->decodeJson($row->properties),
                'created_at' => $this->toIso($row->created_at),
            ])->all(),
            'meta' => $this->paginatorMeta($paginator),
        ];
    }

    protected function auditLogs(string $search, int $page, int $perPage): array
    {
        $query = DB::table('audits')->orderByDesc('created_at');

        if ($search !== '') {
            $query->where(function ($query) use ($search): void {
                $query->where('event', 'like', "%{$search}%")
                    ->orWhere('url', 'like', "%{$search}%")
                    ->orWhere('tags', 'like', "%{$search}%")
                    ->orWhere('old_values', 'like', "%{$search}%")
                    ->orWhere('new_values', 'like', "%{$search}%");
            });
        }

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        return [
            'items' => collect($paginator->items())->map(fn ($row): array => [
                'id' => $row->id,
                'event' => $row->event,
                'user' => $this->actorLabel($row->user_type, $row->user_id),
                'auditable' => $this->actorLabel($row->auditable_type, $row->auditable_id),
                'old_values' => $this->decodeJson($row->old_values),
                'new_values' => $this->decodeJson($row->new_values),
                'url' => $row->url,
                'ip_address' => $row->ip_address,
                'user_agent' => $row->user_agent,
                'tags' => $row->tags,
                'created_at' => $this->toIso($row->created_at),
            ])->all(),
            'meta' => $this->paginatorMeta($paginator),
        ];
    }

    protected function systemLogs(string $date, string $search, int $page, int $perPage): array
    {
        $path = storage_path('logs/laravel-'.$date.'.log');
        $lines = is_file($path) ? @file($path, FILE_IGNORE_NEW_LINES) : [];
        $entries = [];
        $current = null;

        foreach ($lines ?: [] as $line) {
            if (preg_match('/^\[([^\]]+)\]\s+[^ ]+\.(DEBUG|INFO|NOTICE|WARNING|ERROR|CRITICAL|ALERT|EMERGENCY):\s*(.*)$/', $line, $matches)) {
                if ($current !== null) {
                    $entries[] = $current;
                }

                $current = [
                    'id' => count($entries) + 1,
                    'timestamp' => $matches[1],
                    'level' => $matches[2],
                    'message' => $matches[3],
                ];

                continue;
            }

            if ($current !== null && trim($line) !== '') {
                $current['message'] .= "\n".$line;
            }
        }

        if ($current !== null) {
            $entries[] = $current;
        }

        $entries = array_reverse($entries);

        if ($search !== '') {
            $entries = array_values(array_filter(
                $entries,
                fn (array $entry): bool => str_contains(strtolower($entry['message']), strtolower($search)),
            ));
        }

        $total = count($entries);
        $lastPage = max(1, (int) ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;

        return [
            'items' => array_values(array_slice($entries, $offset, $perPage)),
            'meta' => [
                'current_page' => $page,
                'last_page' => $lastPage,
                'per_page' => $perPage,
                'total' => $total,
                'from' => $total === 0 ? null : $offset + 1,
                'to' => $total === 0 ? null : min($total, $offset + $perPage),
            ],
        ];
    }

    protected function availableSystemLogDates(): array
    {
        $files = glob(storage_path('logs/laravel-*.log')) ?: [];
        $dates = [];

        foreach ($files as $file) {
            $date = basename($file, '.log');
            $date = str_replace('laravel-', '', $date);

            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                $dates[] = $date;
            }
        }

        rsort($dates);

        return array_values(array_unique($dates));
    }

    protected function validatedDate(string $date): string
    {
        $parsed = date_create_from_format('Y-m-d', $date);

        return $parsed && $parsed->format('Y-m-d') === $date
            ? $date
            : now()->toDateString();
    }

    protected function actorLabel(?string $type, int|string|null $id): string
    {
        if ($type === null || $id === null) {
            return 'System';
        }

        return class_basename($type).' #'.$id;
    }

    protected function decodeJson(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        $decoded = json_decode($value, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
    }

    protected function paginatorMeta($paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
        ];
    }

    protected function toIso(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->toIso8601String();
        }

        try {
            return Carbon::parse($value)->toIso8601String();
        } catch (\Throwable) {
            return (string) $value;
        }
    }
}
