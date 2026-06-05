<div class="min-h-screen bg-slate-50 pb-12">
    <!-- html5-qrcode library -->
    <script src="https://unpkg.com/html5-qrcode"></script>

    <!-- Navbar / Header -->
    <header class="bg-gradient-to-r from-blue-600 via-indigo-600 to-indigo-800 text-white shadow-lg sticky top-0 z-40">
        <div class="max-w-4xl mx-auto px-4 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <div class="bg-white/20 p-2 rounded-xl backdrop-blur-md">
                    <!-- School Icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.62 48.62 0 0112 20.904a48.62 48.62 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 017.918 5.838 50.57 50.57 0 00-2.658.813M4.26 10.147a49.117 49.117 0 0115.48 0" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-lg font-bold tracking-wide">SI-ABSEN-QR</h1>
                    <p class="text-xs text-blue-100">Portal Presensi Siswa</p>
                </div>
            </div>

            <!-- Logout Form -->
            <form action="{{ route('logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="bg-red-500/20 hover:bg-red-500/40 border border-red-500/30 text-red-200 text-xs px-3 py-2 rounded-xl font-semibold transition duration-150 flex items-center space-x-1">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                    </svg>
                    <span>Keluar</span>
                </button>
            </form>
        </div>
    </header>

    <!-- Main Container -->
    <div class="max-w-4xl mx-auto px-4 mt-6 space-y-6">
        <!-- Floating Success & Error Alert Toast Notifications -->
        @if($successMessage || $errorMessage)
            <div class="fixed top-6 left-4 right-4 z-50 max-w-md mx-auto animate-[slideInDown_0.3s_ease-out]">
                @if($successMessage)
                    <div class="bg-emerald-600 text-white rounded-2xl shadow-xl p-4 flex items-start space-x-3 border border-emerald-500">
                        <svg class="w-6 h-6 shrink-0 text-emerald-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="flex-grow space-y-0.5">
                            <p class="font-bold text-sm">Absensi Berhasil</p>
                            <p class="text-xs text-emerald-100">{{ $successMessage }}</p>
                        </div>
                        <button wire:click="$set('successMessage', '')" class="text-white/80 hover:text-white focus:outline-none">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                @endif

                @if($errorMessage)
                    <div class="bg-red-600 text-white rounded-2xl shadow-xl p-4 flex items-start space-x-3 border border-red-500 mt-2">
                        <svg class="w-6 h-6 shrink-0 text-red-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div class="flex-grow space-y-0.5">
                            <p class="font-bold text-sm">Pemberitahuan</p>
                            <p class="text-xs text-red-100">{{ $errorMessage }}</p>
                        </div>
                        <button wire:click="$set('errorMessage', '')" class="text-white/80 hover:text-white focus:outline-none">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                @endif
            </div>
        @endif

        <!-- Siswa Info Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex flex-col md:flex-row items-center justify-between gap-6 transition hover:shadow-md duration-200">
            <div class="flex flex-col md:flex-row items-center gap-6 text-center md:text-left">
                <!-- Profile Image / Avatar -->
                <div class="relative">
                    @if($siswa->foto_profil)
                        <img src="{{ asset('storage/' . $siswa->foto_profil) }}" alt="{{ $siswa->user->name }}" class="w-24 h-24 rounded-full object-cover border-4 border-indigo-100 shadow-inner">
                    @else
                        <div class="w-24 h-24 rounded-full bg-gradient-to-tr from-indigo-500 to-blue-600 flex items-center justify-center border-4 border-indigo-100 shadow-lg text-white font-bold text-3xl">
                            {{ strtoupper(substr($siswa->user->name, 0, 2)) }}
                        </div>
                    @endif
                    <div class="absolute bottom-0 right-0 w-6 h-6 bg-emerald-500 rounded-full border-4 border-white" title="Online"></div>
                </div>

                <!-- Profile Details -->
                <div class="space-y-1">
                    <h2 class="text-xl font-bold text-slate-800">{{ $siswa->user->name }}</h2>
                    <p class="text-sm font-semibold text-indigo-600">{{ $siswa->kelas->nama_kelas }}</p>
                    <p class="text-xs text-slate-400">NISN: <span class="font-mono text-slate-600 font-medium">{{ $siswa->nisn }}</span></p>
                </div>
            </div>

            <!-- Action Button: Scan QR Kelas (Webcam Scan) -->
            <button wire:click="toggleScannerModal" 
                class="w-full md:w-auto bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-500 hover:to-blue-500 text-white font-semibold px-6 py-3.5 rounded-xl shadow-md hover:shadow-indigo-500/20 transform hover:-translate-y-0.5 transition duration-150 flex items-center justify-center space-x-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" />
                </svg>
                <span>Pindai QR Absen</span>
            </button>
        </div>

        <!-- Layout Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Left Side: Jadwal Hari Ini (Span 2) -->
            <div class="md:col-span-2 space-y-4">
                <div class="flex justify-between items-center">
                    <h3 class="text-md font-bold text-slate-800 flex items-center space-x-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-indigo-600">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                        </svg>
                        <span>Jadwal Hari Ini ({{ $hariIni }})</span>
                    </h3>
                </div>

                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm divide-y divide-slate-100 overflow-hidden">
                    @forelse($jadwalHariIni as $j)
                        <div class="p-5 flex items-center justify-between hover:bg-slate-50/50 transition">
                            <div class="flex items-start space-x-4">
                                <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                                    </svg>
                                </div>
                                <div class="space-y-0.5">
                                    <h4 class="font-bold text-slate-800 text-sm md:text-base">{{ $j->mataPelajaran->nama_mapel }}</h4>
                                    <p class="text-xs text-slate-500 font-medium">Kode: {{ $j->mataPelajaran->kode_mapel }}</p>
                                    <div class="flex items-center space-x-1 text-xs text-slate-400 mt-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>{{ substr($j->jam_mulai, 0, 5) }} - {{ substr($j->jam_selesai, 0, 5) }} WIB</span>
                                    </div>
                                </div>
                            </div>

                            <div>
                                @if(in_array($j->id, $todayAbsensi))
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/50">
                                        <svg class="w-3.5 h-3.5 mr-1 text-emerald-600 animate-[bounce_1s_infinite]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span>Sudah Hadir</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                        Aktif
                                    </span>
                                @endif
                            </div>

                        </div>
                    @empty
                        <div class="p-8 text-center text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 mx-auto mb-2 text-slate-300">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.182 16.318A4.486 4.486 0 0012.016 15a4.486 4.486 0 00-3.198 1.318M21 12a9 9 0 11-18 0 9 9 0 0118 0zM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75zm-.375 0h.008v.015h-.008V9.75zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75zm-.375 0h.008v.015h-.008V9.75z" />
                            </svg>
                            <p class="text-sm font-medium">Tidak ada jadwal pelajaran hari ini.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Right Side: Riwayat Absensi -->
            <div class="space-y-4">
                <h3 class="text-md font-bold text-slate-800 flex items-center space-x-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-indigo-600">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.03 0 1.9.693 2.166 1.638m-7.377 2.24a.75.75 0 011.077-.191l3.47 2.445a.75.75 0 01.224.814l-.005.015a.75.75 0 01-1.078.191l-3.47-2.445a.75.75 0 01-.224-.814z" />
                    </svg>
                    <span>Kehadiran Terakhir</span>
                </h3>

                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 space-y-3">
                    @forelse($riwayatAbsensi as $a)
                        <div class="p-3 bg-slate-50/50 rounded-xl flex items-center justify-between border border-slate-100">
                            <div class="space-y-0.5 min-w-0">
                                <h4 class="font-bold text-slate-800 text-xs truncate">{{ $a->jadwalPelajaran->mataPelajaran->nama_mapel }}</h4>
                                <p class="text-[10px] text-slate-400">{{ $a->created_at->diffForHumans() }}</p>
                            </div>
                            <div>
                                @php
                                    $badgeColor = match($a->status) {
                                        'Hadir' => 'bg-emerald-50 text-emerald-700 border-emerald-200/50',
                                        'Sakit' => 'bg-blue-50 text-blue-700 border-blue-200/50',
                                        'Izin' => 'bg-amber-50 text-amber-700 border-amber-200/50',
                                        'Alfa' => 'bg-red-50 text-red-700 border-red-200/50',
                                        default => 'bg-slate-50 text-slate-700 border-slate-200/50'
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-bold border {{ $badgeColor }}">
                                    {{ $a->status }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-center text-slate-400 py-6">Belum ada riwayat kehadiran.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- QR Code Scanner Modal (Student Side Real-Time Webcam) -->
    @if($showScannerModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true"
             x-data="{
                 html5QrCode: null,
                 initScanner() {
                     // Instantiate scanning engine on target div
                     this.html5QrCode = new Html5Qrcode('webcam-portal-reader');

                     // Start the camera directly without extra buttons/controls
                     this.html5QrCode.start(
                         { facingMode: 'environment' }, // Default to back camera
                         { 
                             fps: 15, 
                             qrbox: { width: 220, height: 220 },
                             aspectRatio: 1.0
                         },
                         (decodedText, decodedResult) => {
                             // Success callback
                             this.playBeep();
                             
                             // Call Livewire and clear camera stream
                             this.html5QrCode.stop().then(() => {
                                 $wire.prosesScanQrKelas(decodedText);
                             }).catch(err => {
                                 console.error('Stop error:', err);
                                 $wire.prosesScanQrKelas(decodedText);
                             });
                         },
                         (errorMessage) => {
                             // Skip scan failures to keep console clean
                         }
                     ).catch(err => {
                         console.error('Camera startup failed:', err);
                         $wire.set('errorMessage', 'Gagal mengakses kamera. Pastikan browser diizinkan menggunakan kamera dan pastikan Anda menggunakan HTTPS (misal: Ngrok).');
                         $wire.set('showScannerModal', false);
                     });
                 },
                 playBeep() {
                     try {
                         const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                         const oscillator = audioCtx.createOscillator();
                         const gainNode = audioCtx.createGain();
                         oscillator.connect(gainNode);
                         gainNode.connect(audioCtx.destination);
                         oscillator.type = 'sine';
                         oscillator.frequency.value = 880; // A5 pitch
                         gainNode.gain.setValueAtTime(0.3, audioCtx.currentTime);
                         oscillator.start();
                         gainNode.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.15);
                         oscillator.stop(audioCtx.currentTime + 0.15);
                     } catch(e) {
                         console.error(e);
                     }
                 },
                 closeScanner() {
                     if (this.html5QrCode && this.html5QrCode.isScanning) {
                         this.html5QrCode.stop().then(() => {
                             $wire.toggleScannerModal();
                         }).catch(err => {
                             console.error(err);
                             $wire.toggleScannerModal();
                         });
                     } else {
                         $wire.toggleScannerModal();
                     }
                 }
             }"
             x-init="setTimeout(() => initScanner(), 300)">
            <!-- Backdrop -->
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-sm" @click="closeScanner()" aria-hidden="true"></div>

                <!-- Spacer -->
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <!-- Modal Box -->
                <div class="relative z-10 inline-block align-middle bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-100 animate-[fadeIn_0.2s_ease-out]">
                    <div class="bg-white px-6 pt-6 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex justify-between items-center pb-4 border-b border-slate-100">
                            <h3 class="text-lg font-bold text-slate-800" id="modal-title">
                                Pindai QR Code Absensi
                            </h3>
                            <button @click="closeScanner()" class="text-slate-400 hover:text-slate-600 focus:outline-none">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Camera Permission & HTTPS Notice -->
                        <div class="mt-4 p-3.5 bg-amber-50/80 rounded-2xl border border-amber-200/60 flex items-start space-x-2 text-left leading-normal shadow-sm">
                            <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <div class="space-y-0.5 text-amber-900">
                                <p class="text-xs font-bold">Izin Kamera Diperlukan</p>
                                <p class="text-[11px] text-amber-800 font-medium">
                                    Izinkan akses kamera di browser HP Anda. Jika menggunakan HP, pastikan Anda mengakses via URL **HTTPS** aman (seperti menggunakan terowongan <span class="font-mono bg-amber-100 border border-amber-200/50 px-1 py-0.5 rounded text-[10px]">Ngrok</span> atau <span class="font-mono bg-amber-100 border border-amber-200/50 px-1 py-0.5 rounded text-[10px]">Localtunnel</span>) agar sistem keamanan browser mengaktifkan kamera.
                                </p>
                            </div>
                        </div>

                        <!-- Scanner Area -->
                        <div class="mt-6 flex flex-col items-center justify-center space-y-4">
                            <div class="relative w-full max-w-sm bg-slate-950 rounded-2xl overflow-hidden aspect-square border border-slate-800 flex flex-col items-center justify-center p-4">
                                <!-- Scanner Laser Overlay -->
                                <div class="absolute inset-0 pointer-events-none border-2 border-indigo-500/20 rounded-2xl z-10">
                                    <div class="w-full h-0.5 bg-gradient-to-r from-transparent via-indigo-500 to-transparent shadow-[0_0_12px_rgba(99,102,241,0.8)] animate-[scanLaser_2s_infinite] absolute top-0"></div>
                                </div>
                                
                                <div id="webcam-portal-reader" class="w-full h-full" style="border: none !important;"></div>
                            </div>
                            
                            <div class="text-center space-y-1">
                                <p class="text-xs font-semibold text-indigo-600">Scan QR Code kelas yang ditampilkan guru</p>
                                <p class="text-[10px] text-slate-400">Pastikan kamera diizinkan pada browser HP Anda</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-50 px-6 py-4 flex justify-end">
                        <button @click="closeScanner()" type="button" 
                            class="bg-white hover:bg-slate-100 border border-slate-200 text-slate-700 font-semibold px-5 py-2.5 rounded-xl text-sm transition focus:outline-none">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- CSS Animation / Scanner laser overlay -->
    <style>
        @keyframes scanLaser {
            0% { top: 0%; }
            50% { top: 100%; }
            100% { top: 0%; }
        }
        #webcam-portal-reader video {
            object-fit: cover !important;
            border-radius: 1rem;
            width: 100% !important;
            height: 100% !important;
        }
    </style>
</div>
