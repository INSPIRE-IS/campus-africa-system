<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Beds report download</title>
    <style>
        body {
            background: #f4f6f8;
            color: #263238;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 32px 16px;
        }
        .report-card {
            background: #fff;
            border: 1px solid #dfe3e6;
            border-radius: 6px;
            margin: 32px auto;
            max-width: 560px;
            padding: 28px;
        }
        .report-card h1 {
            font-size: 22px;
            margin: 0 0 20px;
        }
        .report-meta {
            line-height: 1.7;
            overflow-wrap: anywhere;
        }
        .report-status {
            margin: 20px 0;
        }
        .download-button {
            background: #146c43;
            border-radius: 4px;
            color: #fff;
            display: inline-block;
            padding: 11px 18px;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <main class="report-card">
        <h1>Beds report</h1>
        <div class="report-meta">
            <div><strong>File:</strong> {{ $filename }}</div>
            <div><strong>Created:</strong> {{ $createdAt->format('d M Y H:i T') }}</div>
            <div><strong>Link expires:</strong> {{ $expiresAt->format('d M Y H:i T') }}</div>
        </div>

        @if ($expired)
            <p class="report-status" role="status">This download link has expired. Please request a new report.</p>
        @elseif (!$reportExists)
            <p class="report-status" role="status">This report file is no longer available.</p>
        @else
            <p class="report-status">Your report is ready to download.</p>
            <a class="download-button" href="{{ $downloadUrl }}">Download report</a>
        @endif
    </main>
</body>
</html>
