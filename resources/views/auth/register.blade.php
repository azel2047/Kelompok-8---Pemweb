<!DOCTYPE html>
<html lang="id" class="h-full bg-gradient-to-br from-blue-900 via-indigo-950 to-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Akun - SI-ABSEN-QR</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full flex items-start justify-center p-4 pt-8 pb-12 font-sans antialiased text-slate-200">
    <div class="w-full max-w-md">

        <!-- Brand Header -->
        <div class="text-center mb-6 space-y-2">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-indigo-500/20 border border-indigo-400/30 text-indigo-400 mb-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-white">Daftar Akun Siswa</h1>
            <p class="text-sm text-slate-300">Buat akun untuk mengakses Portal Presensi</p>
        </div>

        <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl shadow-2xl p-8 space-y-5">

            <!-- Validation Errors -->
            @if($errors->any())
                <div class="bg-red-500/20 border border-red-500/30 text-red-200 text-sm p-4 rounded-xl">
                    <p class="font-semibold mb-1">Terdapat kesalahan input:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Form -->
            <form action="{{ route('register.post') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Nama Lengkap -->
                <div class="space-y-1.5">
                    <label for="name" class="text-sm font-medium text-slate-200 block">
                        Nama Lengkap
                    </label>
                    <input type="text" name="name" id="name" required value="{{ old('name') }}"
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition {{ $errors->has('name') ? 'border-red-500/50 ring-1 ring-red-500/50' : '' }}"
                        placeholder="Nama sesuai rapor">
                </div>

                <!-- Email -->
                <div class="space-y-1.5">
                    <label for="email" class="text-sm font-medium text-slate-200 block">
                        Email
                    </label>
                    <input type="email" name="email" id="email" required value="{{ old('email') }}"
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition {{ $errors->has('email') ? 'border-red-500/50 ring-1 ring-red-500/50' : '' }}"
                        placeholder="nama@email.com">
                </div>

                <!-- NISN -->
                <div class="space-y-1.5">
                    <label for="nisn" class="text-sm font-medium text-slate-200 block">
                        NISN <span class="text-slate-400 text-xs font-normal">(Nomor Induk Siswa Nasional)</span>
                    </label>
                    <input type="text" name="nisn" id="nisn" required value="{{ old('nisn') }}"
                        maxlength="20" inputmode="numeric"
                        class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition font-mono {{ $errors->has('nisn') ? 'border-red-500/50 ring-1 ring-red-500/50' : '' }}"
                        placeholder="0012345678">
                </div>

                <!-- Kelas -->
                <div class="space-y-1.5">
                    <label for="kelas_id" class="text-sm font-medium text-slate-200 block">
                        Kelas
                    </label>
                    <select name="kelas_id" id="kelas_id" required
                        class="w-full bg-slate-800 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition {{ $errors->has('kelas_id') ? 'border-red-500/50 ring-1 ring-red-500/50' : '' }}">
                        <option value="" class="text-slate-400">-- Pilih Kelas --</option>
                        @foreach($kelas as $k)
                            <option value="{{ $k->id }}" {{ old('kelas_id') == $k->id ? 'selected' : '' }}>
                                {{ $k->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Password (Grid 2-col) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label for="password" class="text-sm font-medium text-slate-200 block">
                            Password
                        </label>
                        <input type="password" name="password" id="password" required
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition {{ $errors->has('password') ? 'border-red-500/50 ring-1 ring-red-500/50' : '' }}"
                            placeholder="Min. 6 karakter">
                    </div>
                    <div class="space-y-1.5">
                        <label for="password_confirmation" class="text-sm font-medium text-slate-200 block">
                            Konfirmasi Password
                        </label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required
                            class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                            placeholder="Ulangi password">
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-3 px-4 rounded-xl shadow-lg hover:shadow-indigo-500/20 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-slate-900 flex items-center justify-center space-x-2 mt-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z" />
                    </svg>
                    <span>Daftar Sekarang</span>
                </button>
            </form>

            <!-- Divider -->
            <div class="relative my-2">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-white/10"></div>
                </div>
                <div class="relative flex justify-center text-xs text-slate-400">
                    <span class="bg-transparent px-2">Sudah punya akun?</span>
                </div>
            </div>

            <!-- Login Link -->
            <a href="{{ route('login') }}"
                class="w-full block text-center bg-white/5 hover:bg-white/10 border border-white/10 text-slate-200 font-semibold py-3 px-4 rounded-xl transition-all duration-200">
                Masuk ke Portal
            </a>

        </div>

        <p class="text-center text-xs text-slate-500 mt-4">
            Hanya untuk siswa yang terdaftar. Data kelas harus sesuai dengan data sekolah.
        </p>
    </div>
</body>
</html>
