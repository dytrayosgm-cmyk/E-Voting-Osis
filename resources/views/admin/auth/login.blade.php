<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Control Center — E-Voting OSIS SMK An-Nurmaniyah</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo-smk.jpg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#f8fafc] text-slate-900 font-sans min-h-screen flex items-center justify-center px-4 py-8">
    <div class="w-full max-w-sm space-y-6">
        <div class="text-center">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-white p-1.5 shadow-xs border border-slate-200/90 flex items-center justify-center">
                <img src="{{ asset('images/logo-smk.jpg') }}" alt="Logo SMK An-Nurmaniyah" class="w-full h-full object-contain">
            </div>
            <div class="mt-4 inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-100">
                SISTEM E-VOTING DIGITAL
            </div>
            <h1 class="mt-2 text-2xl font-black text-slate-900 tracking-tight">Control Center Admin</h1>
            <p class="mt-1 text-xs text-slate-500 font-medium">Pemilihan Ketua &amp; Wakil OSIS SMK An-Nurmaniyah</p>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 p-6 sm:p-8">
            @if (session('status'))
                <div class="mb-5 rounded-2xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-xs font-semibold text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-5 rounded-2xl bg-red-50 border border-red-200 px-4 py-3 text-xs font-semibold text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Username Admin</label>
                    <input type="text" name="username" value="{{ old('username') }}" autofocus
                           class="ui-input w-full text-slate-900 font-semibold" placeholder="Masukkan username">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Password</label>
                    <input type="password" name="password"
                           class="ui-input w-full text-slate-900 font-semibold" placeholder="••••••••">
                </div>
                <div class="space-y-2.5 pt-1 text-xs">
                    <div class="flex items-center justify-between">
                        <label class="inline-flex items-center gap-2 text-slate-600 font-medium cursor-pointer">
                            <input type="checkbox" name="remember" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            Ingat saya
                        </label>
                    </div>

                    <div class="p-2.5 rounded-xl bg-amber-50/80 border border-amber-200/90 text-amber-950">
                        <label class="inline-flex items-start gap-2 cursor-pointer">
                            <input type="checkbox" name="force_login" value="1" class="mt-0.5 rounded border-amber-400 text-amber-600 focus:ring-amber-500">
                            <span class="text-[11px] leading-tight">
                                <strong class="font-bold block text-amber-900">Paksa Masuk &amp; Cabut Sesi Lain</strong>
                                <span class="text-amber-700 font-medium">Centang ini jika sebelumnya tab/browser ditutup tanpa logout agar tidak terhalang deteksi sesi aktif.</span>
                            </span>
                        </label>
                    </div>
                </div>
                <button type="submit" class="ui-button w-full bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs py-3 rounded-xl shadow-xs transition">
                    Masuk ke Control Center
                </button>
            </form>
        </div>

        <p class="text-center text-xs text-slate-400">
            © 2026 E-Voting OSIS • Aplikasi Pemilihan Ketua &amp; Wakil OSIS
        </p>
    </div>
</body>
</html>
