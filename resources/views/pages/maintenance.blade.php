<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title }}</title>
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/utility.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/maintenance.css') }}">
</head>
<body class="maintenance-page">
    <main class="maintenance-card">
        @if ($logo)
            <img src="{{ $logo }}" alt="{{ $company_name }}" class="maintenance-logo">
        @elseif (filled($company_name))
            <p class="maintenance-brand">{{ $company_name }}</p>
        @endif

        <h1>{{ $title }}</h1>
        <p>{{ $message }}</p>
    </main>
</body>
</html>
