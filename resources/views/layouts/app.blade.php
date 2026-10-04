<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'E-Voting OSIS — SMK An-Nurmaniyah' }}</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo-smk.jpg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-slate-50 text-slate-900 antialiased min-h-screen">
    {{ $slot }}
    @livewireScripts
</body>
</html>
