<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin — E-Voting OSIS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center px-4 py-8">
    <div class="w-full max-w-sm">
        <div class="text-center mb-6">
            <span class="inline-flex w-12 h-12 rounded-xl bg-indigo-600 items-center justify-center text-white font-bold text-lg">EV</span>
            <h1 class="mt-4 text-2xl font-bold text-gray-900">Control Center Admin</h1>
            <p class="mt-1 text-base text-gray-500">Masuk untuk mengelola sesi E-Voting OSIS</p>
        </div>

        <div class="bg-white rounded-2xl shadow-md p-7 sm:p-8">
            @if ($errors->any())
                <div class="mb-5 rounded-lg bg-red-50 px-4 py-3 text-base text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login') }}" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-base font-semibold text-gray-700">Username</label>
                    <input type="text" name="username" value="{{ old('username') }}" autofocus
                           class="ui-input mt-1.5 w-full">
                </div>
                <div>
                    <label class="block text-base font-semibold text-gray-700">Password</label>
                    <input type="password" name="password"
                           class="ui-input mt-1.5 w-full">
                </div>
                <label class="inline-flex items-center gap-2 text-sm text-gray-600">
                    <input type="checkbox" name="remember" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    Ingat saya
                </label>
                <button type="submit" class="ui-button w-full bg-indigo-600 text-white hover:bg-indigo-500">
                    Masuk
                </button>
            </form>
        </div>
    </div>
</body>
</html>
