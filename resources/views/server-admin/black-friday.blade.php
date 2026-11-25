<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Black Friday booking fees | Campus Africa</title>
    @foreach ($entry['css'] ?? [] as $stylesheet)
        <link rel="stylesheet" href="{{ asset('server-admin-build/' . $stylesheet) }}">
    @endforeach
</head>
<body>
    @if ($entry)
        <div id="black-friday" data-status-url="{{ route('server-admin.black-friday.status', [], false) }}"
            data-logo-url="{{ asset('images/logo/Campus_Africa_Logo.png') }}"></div>
        <script type="module" src="{{ asset('server-admin-build/' . $entry['file']) }}"></script>
        <noscript>JavaScript is required to check live booking fees.</noscript>
    @else
        <main>
            <h1>Black Friday booking fees</h1>
            <p>The admin assets have not been built for this release.</p>
        </main>
    @endif
</body>
</html>
