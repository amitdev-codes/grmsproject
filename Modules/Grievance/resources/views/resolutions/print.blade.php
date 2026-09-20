<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Resolution {{ $resolution->id }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #172033; line-height: 1.5; }
        h1 { font-size: 20px; margin-bottom: 4px; }
        .muted { color: #5e6879; font-size: 12px; }
        .content { margin-top: 28px; white-space: pre-wrap; }
    </style>
</head>
<body>
    <h1>{{ ucfirst($resolution->document_type) }} for {{ $resolution->grievance?->reference_no }}</h1>
    <div class="muted">Prepared by {{ $resolution->proposedBy?->name ?? 'Unknown' }} on {{ $resolution->created_at?->format('Y-m-d H:i') }}</div>
    <div class="content">{{ $resolution->resolution_text }}</div>
</body>
</html>
