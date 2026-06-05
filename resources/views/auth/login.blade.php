<!DOCTYPE html>
<html lang="id" class="h-full bg-gradient-to-br from-blue-900 via-indigo-950 to-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Siswa - SI-ABSEN-QR</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex items-center justify-center p-4 font-sans antialiased text-slate-200">
    <div class="w-full max-w-md bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl shadow-2xl p-8 space-y-6">
        <!-- Brand Header -->
        <div class="text-center space-y-2">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-indigo-500/20 border border-indigo-400/30 text-indigo-400 mb-2">
                <!-- QR Code Icon -->
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5c-.621 0-1.125-.504-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5c-.621 0-1.125-.504-1.125-1.125v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5c-.621 0-1.125-.504-1.125-1.125v-4.5zM13.5 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5c-.621 0-1.125-.504-1.125-1.125v-4.5z" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-white">SI-ABSEN-QR</h1>
            <p class="text-sm text-slate-300">Portal Presensi Siswa</p>
        </div>

        @if($errors->any())
            <div class="bg-red-500/20 border border-red-500/30 text-red-200 text-sm p-4 rounded-xl">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('success'))
            <div class="bg-emerald-500/20 border border-emerald-500/30 text-emerald-200 text-sm p-4 rounded-xl">
                {{ session('success') }}
            </div>
        @endif

        <!-- Form -->
        <form action="{{ route('login.post') }}" method="POST" class="space-y-5">
            @csrf
            <div class="space-y-2">
                <label for="email" class="text-sm font-medium text-slate-200 block">Email / Username</label>
                <input type="email" name="email" id="email" required value="{{ old('email') }}"
                    class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                    placeholder="nama@siswa.com">
            </div>

            <div class="space-y-2">
                <div class="flex justify-between items-center">
                    <label for="password" class="text-sm font-medium text-slate-200 block">Password</label>
                </div>
                <input type="password" name="password" id="password" required
                    class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                    placeholder="••••••••">
            </div>

            <button type="submit"
                class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-3 px-4 rounded-xl shadow-lg hover:shadow-indigo-500/20 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-slate-900">
                Masuk ke Portal
            </button>
        </form>

        <div class="text-center space-y-3">
            <span class="text-xs text-slate-400">Gunakan akun yang telah didaftarkan oleh Admin.</span>
            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-white/10"></div>
                </div>
                <div class="relative flex justify-center text-xs text-slate-400">
                    <span class="bg-transparent px-2">Belum punya akun?</span>
                </div>
            </div>
            <a href="{{ route('register') }}"
                class="block w-full text-center bg-white/5 hover:bg-white/10 border border-white/10 text-slate-200 font-semibold py-2.5 px-4 rounded-xl transition-all duration-200 text-sm">
                Daftar Akun Baru
            </a>
        </div>

    </div>
</body>
</html>
