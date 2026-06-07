<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Akun - EduAttend</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex items-center justify-center p-4 font-sans antialiased text-slate-800">

    <!-- Centered Card Container -->
    <div class="w-full max-w-[440px] bg-white rounded-[32px] border border-slate-100 shadow-xl shadow-slate-100/50 p-8 space-y-5 animate-[fadeIn_0.2s_ease-out]">
        
        <!-- Brand Logo Header -->
        <div class="text-center space-y-2">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-[20px] bg-gradient-to-tr from-indigo-500 to-indigo-700 text-white font-extrabold text-xl shadow-md shadow-indigo-100">
                E
            </div>
            <h1 class="text-xl font-black text-slate-800 tracking-tight">Daftar Akun Siswa</h1>
            <p class="text-[11px] text-slate-400 font-bold">Buat akun untuk mengakses Portal Presensi</p>
        </div>

        <!-- Error Alerts -->
        @if($errors->any())
            <div class="bg-rose-50 border border-rose-100 text-rose-700 text-xs p-3.5 rounded-[20px] shadow-sm">
                <ul class="list-disc list-inside space-y-0.5 font-medium">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Form -->
        <form action="{{ route('register.post') }}" method="POST" class="space-y-3.5">
            @csrf
            
            <!-- Nama Lengkap -->
            <div class="space-y-1">
                <label for="name" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Nama Lengkap</label>
                <input type="text" name="name" id="name" required value="{{ old('name') }}"
                    class="w-full bg-white border border-slate-200/80 rounded-2xl px-4 py-2.5 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-transparent transition shadow-xs"
                    placeholder="Nama sesuai rapor">
            </div>

            <!-- Email -->
            <div class="space-y-1">
                <label for="email" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Email</label>
                <input type="email" name="email" id="email" required value="{{ old('email') }}"
                    class="w-full bg-white border border-slate-200/80 rounded-2xl px-4 py-2.5 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-transparent transition shadow-xs"
                    placeholder="nama@email.com">
            </div>

            <!-- NISN -->
            <div class="space-y-1">
                <label for="nisn" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">NISN</label>
                <input type="text" name="nisn" id="nisn" required value="{{ old('nisn') }}"
                    maxlength="20" inputmode="numeric"
                    class="w-full bg-white border border-slate-200/80 rounded-2xl px-4 py-2.5 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-transparent transition font-mono shadow-xs"
                    placeholder="0012345678">
            </div>

            <!-- Kelas -->
            <div class="space-y-1">
                <label for="kelas_id" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Kelas</label>
                <div class="relative">
                    <select name="kelas_id" id="kelas_id" required
                        class="w-full bg-white border border-slate-200/80 rounded-2xl px-4 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-transparent transition appearance-none shadow-xs">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($kelas as $k)
                            <option value="{{ $k->id }}" {{ old('kelas_id') == $k->id ? 'selected' : '' }}>
                                {{ $k->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                    <!-- custom chevron arrow -->
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                    </div>
                </div>
            </div>

            <!-- Password Fields Grid -->
            <div class="grid grid-cols-2 gap-3">
                <div class="space-y-1">
                    <label for="password" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Password</label>
                    <input type="password" name="password" id="password" required
                        class="w-full bg-white border border-slate-200/80 rounded-2xl px-3 py-2.5 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-transparent transition shadow-xs"
                        placeholder="Min 6 karakter">
                </div>
                <div class="space-y-1">
                    <label for="password_confirmation" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Konfirmasi</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required
                        class="w-full bg-white border border-slate-200/80 rounded-2xl px-3 py-2.5 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-transparent transition shadow-xs"
                        placeholder="Ulangi password">
                </div>
            </div>

            <button type="submit"
                class="w-full bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-500 hover:to-blue-500 text-white font-bold py-3 px-4 rounded-2xl shadow-md hover:shadow-indigo-500/20 transition-all duration-200 focus:outline-none text-xs flex items-center justify-center space-x-1.5 mt-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z" />
                </svg>
                <span>Daftar Sekarang</span>
            </button>
        </form>

        <!-- Back to Login Link -->
        <div class="text-center space-y-4 pt-1">
            <div class="relative">
                <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-200/60"></div></div>
                <div class="relative flex justify-center text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                    <span class="bg-white px-2.5">Sudah punya akun?</span>
                </div>
            </div>

            <a href="{{ route('login') }}"
                class="block w-full text-center bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 font-bold py-3 px-4 rounded-2xl transition-all duration-200 text-xs shadow-2xs">
                Masuk ke Portal
            </a>
        </div>

        <p class="text-center text-[9px] font-semibold text-slate-400 leading-normal">
            Hanya untuk siswa yang terdaftar. Data kelas harus sesuai dengan data sekolah.
        </p>
    </div>

</body>
</html>
