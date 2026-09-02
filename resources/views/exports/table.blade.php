<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #111; }
        h2 { margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        th { background: #f4f4f5; }
        tr:nth-child(even) { background: #fafafa; }
    </style>
</head>
<body>
<h2>{{ $title }} — {{ now()->format('Y-m-d H:i') }}</h2>
<table>
    <thead>
    <tr>
        @foreach ($columns as $heading)
            <th>{{ $heading }}</th>
        @endforeach
    </tr>
    </thead>
    <tbody>
    @foreach ($rows as $row)
        <tr>
            @foreach (array_keys($columns) as $column)
                <td>{{ data_get($row, $column) }}</td>
            @endforeach
        </tr>
    @endforeach
    </tbody>
</table>

@if (!empty($autoPrint))
    <script>window.onload = function () { window.print(); };</script>
@endif
</body>
</html>
