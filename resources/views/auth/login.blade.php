<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk - EduAttend</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex items-center justify-center p-4 font-sans antialiased text-slate-800">

    <!-- Centered Card Container -->
    <div class="w-full max-w-[400px] bg-white rounded-[32px] border border-slate-100 shadow-xl shadow-slate-100/50 p-8 space-y-6 animate-[fadeIn_0.2s_ease-out]">
        
        <!-- Brand Logo Header -->
        <div class="text-center space-y-3">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-[24px] bg-gradient-to-tr from-indigo-500 to-indigo-700 text-white font-extrabold text-2xl shadow-lg shadow-indigo-100 mb-2">
                E
            </div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tight">EduAttend</h1>
            <p class="text-xs text-slate-400 font-bold">Portal Presensi Siswa</p>
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

        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-100 text-emerald-700 text-xs p-3.5 rounded-[20px] shadow-sm font-medium">
                {{ session('success') }}
            </div>
        @endif

        <!-- Form -->
        <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
            @csrf
            
            <div class="space-y-1.5">
                <label for="email" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Email</label>
                <input type="email" name="email" id="email" required value="{{ old('email') }}"
                    class="w-full bg-white border border-slate-200/80 rounded-2xl px-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-transparent transition shadow-xs"
                    placeholder="nama@siswa.com">
            </div>

            <div class="space-y-1.5">
                <label for="password" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Password</label>
                <input type="password" name="password" id="password" required
                    class="w-full bg-white border border-slate-200/80 rounded-2xl px-4 py-3 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-transparent transition shadow-xs"
                    placeholder="••••••••">
            </div>

            <button type="submit"
                class="w-full bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-500 hover:to-blue-500 text-white font-bold py-3.5 px-4 rounded-2xl shadow-md hover:shadow-indigo-500/20 transition-all duration-200 focus:outline-none text-sm mt-2">
                Masuk ke Portal
            </button>
        </form>

        <!-- Registration Redirect -->
        <div class="text-center space-y-4 pt-2">
            <p class="text-[10px] font-semibold text-slate-400">Gunakan akun yang telah didaftarkan oleh sekolah.</p>
            
            <div class="relative">
                <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-200/60"></div></div>
                <div class="relative flex justify-center text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                    <span class="bg-white px-2.5">Belum punya akun?</span>
                </div>
            </div>

            <a href="{{ route('register') }}"
                class="block w-full text-center bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 font-bold py-3 px-4 rounded-2xl transition-all duration-200 text-xs shadow-2xs">
                Daftar Akun Baru
            </a>
        </div>

    </div>

</body>
</html>
