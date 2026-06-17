<div class="min-h-screen bg-slate-50 flex flex-col md:flex-row text-slate-800"
     x-data="{
         activeTab: 'dashboard',
         selectedDay: '{{ $defaultSelectedDay }}',
         showScannerModal: @entangle('showScannerModal'),
         showRegisterFaceModal: @entangle('showRegisterFaceModal'),
         locationStatus: 'Area Sekolah (Radius Aman)',
         timeStr: '',
         dateStr: '',
         
         // Profile Modals
         showPersonalInfoModal: false,
         showNotificationModal: false,
         showHelpModal: false,
         showFaceIdManageModal: false,
         showLogoutModal: false,
         
         // Calendar History state
         selectedHistoryDay: null,

         initTime() {
             const update = () => {
                 const now = new Date();
                 let hours = now.getHours();
                 let minutes = now.getMinutes();
                 let ampm = hours >= 12 ? 'PM' : 'AM';
                 hours = hours % 12;
                 hours = hours ? hours : 12;
                 minutes = minutes < 10 ? '0' + minutes : minutes;
                 this.timeStr = (hours < 10 ? '0' + hours : hours) + ':' + minutes + ' ' + ampm;
                 
                 const options = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
                 this.dateStr = now.toLocaleDateString('id-ID', options);
             };
             update();
             setInterval(update, 1000);
         }
     }"
     x-init="initTime()">
    
    <style>
        .scrollbar-none::-webkit-scrollbar {
            display: none;
        }
        .scrollbar-none {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        @keyframes scanLaser {
            0% { top: 0%; }
            50% { top: 100%; }
            100% { top: 0%; }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes zoomIn {
            from { opacity: 0; transform: scale(0.92); }
            to { opacity: 1; transform: scale(1); }
        }
        #webcam-portal-reader video {
            object-fit: cover !important;
            border-radius: 1.5rem;
            width: 100% !important;
            height: 100% !important;
        }
        .face-video-container {
            position: relative;
            width: 100%;
            max-width: 280px;
            aspect-ratio: 1 / 1;
            background: #020617;
            border-radius: 1rem;
            overflow: hidden;
            border: 1px solid #1e293b;
        }
        .face-video-container video,
        .face-video-container canvas {
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
        }
        [x-cloak] { display: none !important; }
    </style>

    @push('scripts')
        <script src="https://unpkg.com/html5-qrcode"></script>
        <script>
            // =========================================================================
            // PERTANYAAN DOSEN/PENGUJI: "Bagaimana cara kerja deteksi & verifikasi Face ID di frontend?"
            // 
            // 1. LIBRARY YANG DIGUNAKAN:
            //    Menggunakan Vladmandic Face-API (porting modern dari face-api.js) yang memproses model AI di browser siswa (client-side).
            // 
            // 2. MODEL AI YANG DIMUAT (PRELOAD):
            //    - `tinyFaceDetector`: Model deteksi keberadaan wajah yang sangat ringan dan cepat.
            //    - `faceLandmark68TinyNet`: Mendeteksi 68 titik wajah (mata, alis, hidung, mulut) untuk alignment wajah.
            //    - `faceRecognitionNet`: Membuat representasi matematis berupa array 128 elemen (Face Descriptor / Face Embedding).
            // 
            // 3. PROSES DETEKSI REAL-TIME (`detectFaceLoop` & `detectFaceVerifyLoop`):
            //    - Sistem menangkap gambar dari webcam/kamera lewat HTML5 `<video>` tag.
            //    - Secara berkala (setiap 300 milidetik), script memanggil `faceapi.detectSingleFace(...)` untuk mencari wajah.
            //    - Jika wajah terdeteksi dengan tingkat keyakinan (scoreThreshold) >= 50% (0.5), maka descriptor wajahnya diambil.
            // 
            // 4. ALUR REGISTRASI (PENDAFTARAN WAJAH):
            //    - Setelah wajah terdeteksi, data descriptor diubah menjadi array standar (`Array.from(detection.descriptor)`).
            //    - Array ini dikirim ke backend Livewire melalui method `$wire.simpanFaceEmbedding(JSON.stringify(this.embedding))` untuk disimpan ke database.
            // =========================================================================
            window.__faceModelsLoaded = false;
            window.__faceApiReady = false;
            window.__loadFaceApi = async function() {
                if (window.__faceApiReady) return;
                if (typeof faceapi === 'undefined') {
                    await new Promise((resolve, reject) => {
                        const s = document.createElement('script');
                        s.src = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.js';
                        s.onload = resolve;
                        s.onerror = reject;
                        document.head.appendChild(s);
                    });
                }
                window.__faceApiReady = true;
            };
            window.__loadFaceModels = async function() {
                if (window.__faceModelsLoaded) return;
                await window.__loadFaceApi();
                const CDN_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model/';
                await Promise.all([
                    faceapi.nets.tinyFaceDetector.loadFromUri(CDN_URL),
                    faceapi.nets.faceLandmark68TinyNet.loadFromUri(CDN_URL),
                    faceapi.nets.faceRecognitionNet.loadFromUri(CDN_URL)
                ]);
                window.__faceModelsLoaded = true;
            };

            window.addEventListener('DOMContentLoaded', () => {
                setTimeout(() => {
                    window.__loadFaceModels().then(() => {
                        console.log('Face models preloaded successfully in background.');
                    }).catch(err => {
                        console.error('Failed to preload face models:', err);
                    });
                }, 2000);
            });
        </script>
    @endpush

    <aside class="hidden md:flex md:w-64 md:flex-col md:h-screen md:sticky md:top-0 bg-white border-r border-slate-100 shrink-0 text-slate-700 z-30 select-none p-5 justify-between">
        <div class="space-y-6">
            <div class="flex items-center space-x-2.5">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-500 to-indigo-700 flex items-center justify-center text-white font-extrabold text-base shadow-md shadow-indigo-100">
                    E
                </div>
                <span class="text-lg font-black text-slate-800 tracking-tight">EduAttend</span>
            </div>

            <div class="bg-slate-50/70 border border-slate-100 p-4 rounded-2xl flex items-center space-x-3">
                @if($siswa->foto_profil)
                    <img src="{{ asset('storage/' . $siswa->foto_profil) }}" alt="{{ $siswa->user->name }}" class="w-10 h-10 rounded-full object-cover">
                @else
                    <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-indigo-500 to-blue-600 flex items-center justify-center text-white font-black text-sm">
                        {{ strtoupper(substr($siswa->user->name, 0, 2)) }}
                    </div>
                @endif
                <div class="space-y-0.5 overflow-hidden">
                    <h4 class="text-xs font-black text-slate-800 truncate leading-tight">{{ $siswa->user->name }}</h4>
                    <p class="text-[10px] text-slate-400 font-semibold truncate">{{ $siswa->kelas->nama_kelas }}</p>
                    <span class="inline-flex items-center text-[8px] font-bold text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded-full mt-1 border border-emerald-100">
                        <span class="w-1 h-1 rounded-full bg-emerald-500 mr-1 animate-pulse"></span>Active
                    </span>
                </div>
            </div>

            <nav class="space-y-1 text-xs font-bold text-slate-600">
                <button @click="activeTab = 'dashboard'" class="w-full flex items-center space-x-2.5 px-3 py-2.5 rounded-xl transition hover:bg-slate-50"
                        :class="activeTab === 'dashboard' ? 'text-indigo-600 bg-indigo-50/60' : ''">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z" />
                    </svg>
                    <span>Dashboard</span>
                </button>
                <button @click="activeTab = 'history'" class="w-full flex items-center space-x-2.5 px-3 py-2.5 rounded-xl transition hover:bg-slate-50"
                        :class="activeTab === 'history' ? 'text-indigo-600 bg-indigo-50/60' : ''">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Riwayat Kehadiran</span>
                </button>
                <button @click="activeTab = 'schedule'" class="w-full flex items-center space-x-2.5 px-3 py-2.5 rounded-xl transition hover:bg-slate-50"
                        :class="activeTab === 'schedule' ? 'text-indigo-600 bg-indigo-50/60' : ''">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>Jadwal Pelajaran</span>
                </button>
                <button @click="activeTab = 'profile'" class="w-full flex items-center space-x-2.5 px-3 py-2.5 rounded-xl transition hover:bg-slate-50"
                        :class="activeTab === 'profile' ? 'text-indigo-600 bg-indigo-50/60' : ''">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span>Profil Saya</span>
                </button>
            </nav>
        </div>

        <button @click="showLogoutModal = true" class="w-full flex items-center space-x-2.5 px-3 py-2.5 rounded-xl transition hover:bg-rose-50 text-rose-600 text-xs font-bold">
            <svg class="w-4.5 h-4.5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            <span>Keluar Akun</span>
        </button>
    </aside>

    <div class="flex-grow flex flex-col min-h-screen relative overflow-hidden">
        
        <header class="bg-white px-5 py-3.5 flex md:hidden justify-between items-center border-b border-slate-100 sticky top-0 z-20 shrink-0">
            <div class="flex items-center space-x-2">
                <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-indigo-500 to-indigo-700 flex items-center justify-center text-white font-bold text-sm shadow-md shadow-indigo-200">
                    E
                </div>
                <span class="text-base font-bold text-slate-800 tracking-tight">EduAttend</span>
            </div>
            <button class="w-8 h-8 rounded-full bg-slate-50 hover:bg-slate-100 flex items-center justify-center text-slate-600 relative transition">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4.5 h-4.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                </svg>
                <span class="absolute top-2.5 right-2.5 w-1.5 h-1.5 bg-rose-500 rounded-full"></span>
            </button>
        </header>

        @if($successMessage || $errorMessage)
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4" 
                 x-data="{ open: true }" 
                 x-show="open" 
                 x-init="$watch('$wire.successMessage', value => { if(value) open = true }); $watch('$wire.errorMessage', value => { if(value) open = true })">
                <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-md transition-opacity duration-300" @click="open = false; $wire.set('successMessage', ''); $wire.set('errorMessage', '')"></div>

                <div class="bg-white rounded-[32px] max-w-sm w-full p-6 md:p-8 text-center shadow-2xl border border-slate-100 z-10 transform transition-all select-none animate-[zoomIn_0.25s_ease-out]">
                    @if($successMessage)
                        <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-emerald-50 border-4 border-emerald-100 text-emerald-600 mb-6 animate-[bounce_1s_infinite]">
                            <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <h3 class="text-xl md:text-2xl font-black text-slate-800 tracking-tight mb-2">Berhasil!</h3>
                        <p class="text-xs md:text-sm text-slate-500 font-semibold px-2 leading-relaxed mb-6">{{ $successMessage }}</p>
                        
                        <button wire:click="$set('successMessage', '')" @click="open = false" 
                                class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-sm py-4 px-6 rounded-2xl transition shadow-md shadow-emerald-100">
                            Selesai & Tutup
                        </button>
                    @endif

                    @if($errorMessage)
                        <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-rose-50 border-4 border-rose-100 text-rose-600 mb-6 animate-[pulse_1.5s_infinite]">
                            <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <h3 class="text-xl md:text-2xl font-black text-slate-800 tracking-tight mb-2">Pemberitahuan</h3>
                        <p class="text-xs md:text-sm text-slate-500 font-semibold px-2 leading-relaxed mb-6">{{ $errorMessage }}</p>
                        
                        <button wire:click="$set('errorMessage', '')" @click="open = false" 
                                class="w-full bg-indigo-950 hover:bg-indigo-900 text-white font-extrabold text-sm py-4 px-6 rounded-2xl transition shadow-md shadow-indigo-900/10">
                            Mengerti
                        </button>
                    @endif
                </div>
            </div>
        @endif

        <div class="flex-grow overflow-y-auto pb-24 md:pb-8 px-4 py-5 md:px-8 md:py-6 space-y-6 scrollbar-none">

            <div x-show="activeTab === 'dashboard'" class="space-y-6 animate-[fadeIn_0.2s_ease-out]">
                <div class="flex justify-between items-center bg-white p-5 md:p-6 rounded-[28px] border border-slate-100 shadow-xs">
                    <div>
                        <h2 class="text-base md:text-xl font-extrabold text-slate-800 tracking-tight">Selamat Datang, {{ $siswa->user->name }}!</h2>
                        <p class="text-xs text-slate-400 font-medium">Student ID: <span class="font-mono text-slate-500 font-semibold">{{ $siswa->nisn }}</span></p>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-100 shadow-sm shrink-0">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                        Status: Active
                    </span>
                </div>

                @php
                    $dirPath = base_path('../public_html/images/');
                    if (!is_dir($dirPath)) { $dirPath = $_SERVER['DOCUMENT_ROOT'] . '/images/'; }
                    $namaFileTersken = 'banner3.png';
                    if (is_dir($dirPath)) {
                        $files = scandir($dirPath);
                        foreach ($files as $file) {
                            if (str_contains($file, 'LOGO_OSCAR') && !str_contains($file, '.tmp')) {
                                $namaFileTersken = $file;
                                break;
                            }
                        }
                    }
                @endphp
                <div class="overflow-hidden rounded-[28px] bg-white p-5 md:p-6 shadow-xs border border-slate-100">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-5">
                        <div class="space-y-1 text-center sm:text-left">
                            <h3 class="text-base font-black text-slate-800 tracking-tight">📌 Informasi & Pengumuman Sekolah</h3>
                            <p class="text-xs text-slate-400 font-semibold leading-relaxed">
                                @if(str_contains($namaFileTersken, 'LOGO_OSCAR'))
                                    Selamat! Logo kustomisasi absensi sekolah berhasil terunggah dan aktif secara real-time di sistem utama.
                                @else
                                    Pastikan pencahayaan cukup dan wajah terdeteksi jelas di depan kamera saat verifikasi Face ID presensi kelas.
                                @endif
                            </p>
                        </div>
                        <img src="{{ asset('images/' . $namaFileTersken) }}" alt="Banner Pengumuman" class="h-20 w-auto rounded-xl object-cover border border-slate-100/70 shrink-0 shadow-xs">
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    <div class="lg:col-span-5 space-y-6">
                        <div class="bg-white p-5 md:p-6 rounded-[28px] shadow-xs border border-slate-100/80 flex flex-col items-center text-center space-y-3.5">
                            <div class="flex items-center space-x-1.5 bg-emerald-50/50 px-3.5 py-1.5 rounded-full border border-emerald-100/50 text-[10px] text-emerald-700 font-bold">
                                <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-ping"></span>
                                <span x-text="locationStatus">Lokasi Anda: Area Sekolah (Radius Aman)</span>
                            </div>
                            <div class="space-y-1">
                                <h2 class="text-3xl md:text-4xl font-black text-slate-800 tracking-tight" x-text="timeStr">05:27 PM</h2>
                                <p class="text-[11px] font-semibold text-slate-400" x-text="dateStr">Senin, 14 Agustus 2024</p>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Quick Verification</h3>
                            <div class="grid grid-cols-2 gap-3.5">
                                <button wire:click="toggleScannerModal" class="w-full bg-indigo-950 hover:bg-indigo-900 text-white py-5 px-4 rounded-[24px] shadow-md transition flex flex-col items-center justify-center space-y-2 border border-indigo-900 group">
                                    <div class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center text-indigo-200 group-hover:scale-105 transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 12v1.5m0 0v1.5m0-1.5h1.5m-1.5 0h-1.5M12 18.75h1.5M16.5 21h1.5m-3-2.25h.008v.008H15v-.008z" />
                                        </svg>
                                    </div>
                                    <span class="text-xs font-bold">Scan QR Code</span>
                                </button>

                                @if($siswa->face_embedding)
                                    <button wire:click="toggleScannerModal" class="w-full bg-white hover:bg-slate-50 text-indigo-950 py-5 px-4 rounded-[24px] shadow-sm border border-slate-200 transition flex flex-col items-center justify-center space-y-2 group">
                                        <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-indigo-600 group-hover:scale-105 transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.182 15.182a4.5 4.5 0 01-6.364 0M21 12a9 9 0 11-18 0 9 9 0 0118 0zM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75zm-.375 0h.008v.015h-.008V9.75zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75zm-.375 0h.008v.015h-.008V9.75z" />
                                            </svg>
                                        </div>
                                        <span class="text-xs font-bold text-slate-800">Verify Face ID</span>
                                    </button>
                                @else
                                    <button wire:click="$set('showRegisterFaceModal', true)" class="w-full bg-white hover:bg-slate-50 text-indigo-950 py-5 px-4 rounded-[24px] shadow-sm border border-slate-200 transition flex flex-col items-center justify-center space-y-2 group">
                                        <div class="w-10 h-10 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600 group-hover:scale-105 transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                            </svg>
                                        </div>
                                        <span class="text-xs font-bold text-indigo-600">Daftar Face ID</span>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-7">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-indigo-950 text-white p-5 rounded-[28px] shadow-sm flex flex-col justify-between min-h-[120px] relative overflow-hidden">
                                <p class="text-[11px] font-bold text-indigo-200 uppercase tracking-wider">Hadir Bulan Ini</p>
                                <h3 class="text-3xl font-black tracking-tight mt-auto">{{ $hadirBulanIniCount }} <span class="text-sm font-semibold text-indigo-300">/20</span></h3>
                            </div>
                            <div class="bg-emerald-600 text-white p-5 rounded-[28px] shadow-sm flex flex-col justify-between min-h-[120px] relative overflow-hidden">
                                <p class="text-[11px] font-bold text-emerald-100 uppercase tracking-wider">Tepat Waktu</p>
                                <h3 class="text-3xl font-black tracking-tight mt-auto">{{ $tepatWaktuCount }}</h3>
                            </div>
                            <div class="bg-amber-500 text-white p-5 rounded-[28px] shadow-sm flex flex-col justify-between min-h-[120px] relative overflow-hidden">
                                <p class="text-[11px] font-bold text-amber-100 uppercase tracking-wider">Izin / Sakit</p>
                                <h3 class="text-3xl font-black tracking-tight mt-auto">{{ $izinSakitCount }}</h3>
                            </div>
                            <div class="bg-rose-500 text-white p-5 rounded-[28px] shadow-sm flex flex-col justify-between min-h-[120px] relative overflow-hidden">
                                <p class="text-[11px] font-bold text-rose-100 uppercase tracking-wider">Terlambat</p>
                                <h3 class="text-3xl font-black tracking-tight mt-auto">{{ $terlambatCount }}</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-5 md:p-6 rounded-[28px] border border-slate-100 shadow-xs space-y-5">
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4.5 h-4.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-6.75a1.125 1.125 0 00-1.125 1.125v3.375m9 0h-9M9 10.5h.008v.008H9V10.5zm3 0h.008v.008H12V10.5zm3 0h.008v.008H15V10.5z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-extrabold text-slate-800 tracking-tight">Manajemen Kegiatan Ekstrakurikuler</h3>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Ekstrakurikuler yang Diikuti</h4>
                                <span class="text-[10px] font-bold text-indigo-600 bg-indigo-50 px-2.5 py-0.5 rounded-full border border-indigo-100/50">
                                    {{ $ekstrakurikulerSiswa->count() }} Terdaftar
                                </span>
                            </div>
                            <div class="space-y-2.5">
                                @forelse($ekstrakurikulerSiswa as $ekskul)
                                    <div class="bg-slate-50 border border-slate-100/50 p-4 rounded-2xl flex items-center justify-between group animate-[fadeIn_0.15s_ease-out]">
                                        <div class="space-y-0.5 overflow-hidden pr-2">
                                            <h5 class="text-xs font-black text-slate-800 truncate">{{ $ekskul->nama_ekskul }}</h5>
                                            <p class="text-[10px] text-slate-400 font-semibold truncate">Pembina: {{ $ekskul->pembina }}</p>
                                        </div>
                                        <button wire:click="keluarEkstrakurikuler({{ $ekskul->id }})" 
                                                class="px-2.5 py-1.5 rounded-xl text-[10px] font-bold text-rose-600 bg-rose-50 border border-rose-100 hover:bg-rose-100/60 transition shrink-0">
                                            Keluar
                                        </button>
                                    </div>
                                @empty
                                    <div class="py-6 text-center text-slate-400 border border-dashed border-slate-200 rounded-2xl flex flex-col items-center justify-center space-y-1 bg-slate-50/20">
                                        <p class="text-xs font-bold text-slate-500">Belum mengikuti ekskul</p>
                                        <p class="text-[10px] text-slate-400">Silakan daftar pada daftar ekskul tersedia di sebelah kanan.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Ekstrakurikuler yang Tersedia</h4>
                                <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-100/50">
                                    {{ $ekstrakurikulerTersedia->count() }} Tersedia
                                </span>
                            </div>
                            <div class="space-y-2.5">
                                @forelse($ekstrakurikulerTersedia as $ekskul)
                                    <div class="bg-white border border-slate-100 p-4 rounded-2xl flex items-center justify-between group animate-[fadeIn_0.15s_ease-out]">
                                        <div class="space-y-0.5 overflow-hidden pr-2">
                                            <h5 class="text-xs font-black text-slate-800 truncate group-hover:text-indigo-600 transition">{{ $ekskul->nama_ekskul }}</h5>
                                            <p class="text-[10px] text-slate-400 font-semibold truncate">Pembina: {{ $ekskul->pembina }}</p>
                                        </div>
                                        <button wire:click="ikutiEkstrakurikuler({{ $ekskul->id }})" 
                                                class="px-2.5 py-1.5 rounded-xl text-[10px] font-bold text-white bg-indigo-950 hover:bg-indigo-900 transition shrink-0 shadow-sm shadow-indigo-100">
                                            Daftar / Ikuti
                                        </button>
                                    </div>
                                @empty
                                    <div class="py-6 text-center text-slate-400 border border-dashed border-slate-200 rounded-2xl flex flex-col items-center justify-center space-y-1 bg-slate-50/20">
                                        <p class="text-xs font-bold text-slate-500">Semua Ekskul Diikuti</p>
                                        <p class="text-[10px] text-slate-400">Hebat! Anda telah terdaftar di semua ekstrakurikuler sekolah.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div x-show="activeTab === 'history'" x-cloak class="space-y-6 animate-[fadeIn_0.2s_ease-out]">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-extrabold text-slate-800 tracking-tight">Riwayat Absensi</h2>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                    <div class="lg:col-span-5 bg-white p-5 rounded-[28px] shadow-xs border border-slate-100">
                        <div class="flex justify-between items-center mb-4">
                            <span class="text-sm font-bold text-slate-800">{{ now()->translatedFormat('F Y') }}</span>
                            <div class="flex space-x-2 text-slate-400">
                                <button @click="selectedHistoryDay = null" class="text-[10px] font-bold text-indigo-600 bg-indigo-50 px-2.5 py-1.5 rounded-xl">Reset Filter</button>
                            </div>
                        </div>
                        <div class="grid grid-cols-7 gap-1 text-center text-[10px] font-bold text-slate-400 mb-2">
                            <span>Min</span><span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span><span>Jum</span><span>Sab</span>
                        </div>
                        <div class="grid grid-cols-7 gap-1.5 text-center text-xs font-bold text-slate-700 select-none">
                            @php
                                $startDayOfWeek = now()->startOfMonth()->dayOfWeek;
                                $daysInMonth = now()->daysInMonth;
                                $todayDayNum = now()->day;
                            @endphp
                            
                            @for($i = 0; $i < $startDayOfWeek; $i++)
                                <span class="py-1 opacity-0">.</span>
                            @endfor

                            @for($day = 1; $day <= $daysInMonth; $day++)
                                @php
                                    $isToday = ($day === $todayDayNum);
                                @endphp
                                <div @click="selectedHistoryDay = (selectedHistoryDay === {{ $day }} ? null : {{ $day }})"
                                     class="py-1 flex items-center justify-center cursor-pointer group">
                                    <span class="w-8 h-8 flex items-center justify-center rounded-full transition font-bold"
                                          :class="selectedHistoryDay === {{ $day }} ? 'bg-indigo-600 text-white shadow-md shadow-indigo-100' : '{{ $isToday ? 'bg-indigo-950 text-white shadow-md' : 'group-hover:bg-slate-100 text-slate-700' }}'">
                                        {{ $day }}
                                    </span>
                                </div>
                            @endfor
                        </div>
                    </div>

                    <div class="lg:col-span-7 space-y-3">
                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Aktivitas Terbaru</h3>
                        
                        <div class="space-y-2.5">
                            @forelse($riwayatAbsensiLengkap as $absen)
                                @php
                                    $isLate = false;
                                    if ($absen->status === 'Hadir' && $absen->jadwalPelajaran) {
                                        $checkinTime = $absen->created_at->format('H:i:s');
                                        $startTimePlus15 = date('H:i:s', strtotime($absen->jadwalPelajaran->jam_mulai . ' +15 minutes'));
                                        if ($checkinTime > $startTimePlus15) {
                                            $isLate = true;
                                        }
                                    }
                                @endphp
                                <div x-show="selectedHistoryDay === null || selectedHistoryDay === {{ $absen->created_at->day }}"
                                     class="bg-white p-3.5 rounded-[24px] shadow-xs border border-slate-100 flex items-center justify-between animate-[fadeIn_0.15s_ease-out]">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 {{ $isLate || $absen->status === 'Alfa' ? 'bg-rose-50 text-rose-500' : ($absen->status === 'Hadir' ? 'bg-emerald-50 text-emerald-500' : 'bg-amber-50 text-amber-500') }}">
                                            @if($isLate || $absen->status === 'Alfa')
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                            @elseif($absen->status === 'Hadir')
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            @else
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            @endif
                                        </div>
                                        <div class="space-y-0.5">
                                            <h4 class="text-xs font-extrabold text-slate-800 leading-tight">
                                                {{ $absen->jadwalPelajaran ? $absen->jadwalPelajaran->mataPelajaran->nama_mapel : 'Absensi Umum' }}
                                            </h4>
                                            <p class="text-[10px] text-slate-400 font-semibold">
                                                {{ $absen->created_at->translatedFormat('D, d F Y') }} &bull; {{ $absen->created_at->format('H:i') }} WIB
                                                @if($isLate)
                                                    <span class="text-rose-500 font-bold">(Terlambat)</span>
                                                @elseif($absen->status === 'Hadir')
                                                    <span class="text-emerald-500 font-bold">(Tepat Waktu)</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    <span class="px-2.5 py-1 rounded-lg text-[9px] font-extrabold border {{ $isLate || $absen->status === 'Alfa' ? 'bg-rose-50 text-rose-600 border-rose-100' : ($absen->status === 'Hadir' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-amber-50 text-amber-600 border-amber-100') }}">
                                        {{ $absen->status }}
                                    </span>
                                </div>
                            @empty
                                <p class="text-xs text-center text-slate-400 py-6 bg-white rounded-[24px] border border-slate-100 w-full">Belum ada riwayat kehadiran.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div x-show="activeTab === 'schedule'" x-cloak class="space-y-6 animate-[fadeIn_0.2s_ease-out] flex flex-col flex-grow min-h-[480px]">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-extrabold text-slate-800 tracking-tight">Jadwal Pelajaran</h2>
                </div>

                @php
                    $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                    $dayShort = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                @endphp
                <div class="flex space-x-3 overflow-x-auto pb-2 select-none scrollbar-none shrink-0 md:grid md:grid-cols-6 md:gap-3 md:space-x-0">
                    @foreach($days as $idx => $d)
                        <div @click="selectedDay = '{{ $d }}'"
                             class="flex-shrink-0 w-12 md:w-auto py-3 rounded-2xl flex flex-col items-center justify-center cursor-pointer transition border border-slate-100"
                             :class="selectedDay === '{{ $d }}' ? 'bg-indigo-950 text-white shadow-md border-indigo-950' : 'bg-white text-slate-500 hover:bg-slate-50'">
                             <span class="text-[9px] font-bold uppercase tracking-wider opacity-85" :class="selectedDay === '{{ $d }}' ? 'text-indigo-200' : 'text-slate-400'">{{ $dayShort[$idx] }}</span>
                             <span class="text-xs font-black mt-1">{{ 14 + $idx }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="w-full flex-grow flex flex-col">
                    @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $dName)
                        @php
                            $schedulesForDay = $jadwalSeminggu->where('hari', $dName);
                        @endphp
                        
                        <div x-show="selectedDay === '{{ $dName }}'" class="w-full flex-grow flex flex-col">
                            @if($schedulesForDay->count() > 0)
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @foreach($schedulesForDay as $j)
                                        @php
                                            $currentTime = date('H:i:s');
                                            $isToday = ($j->hari === $hariIni);
                                            
                                            if (!$isToday) {
                                                $status = 'Mendatang';
                                            } elseif ($currentTime > $j->jam_selesai) {
                                                $status = 'Selesai';
                                            } elseif ($currentTime >= $j->jam_mulai && $currentTime <= $j->jam_selesai) {
                                                $status = 'Sedang Berlangsung';
                                            } else {
                                                $status = 'Mendatang';
                                            }
                                        @endphp
                                        <div class="rounded-[28px] p-5 shadow-xs border transition duration-150 relative overflow-hidden flex flex-col justify-between min-h-[140px]
                                             {{ $status === 'Sedang Berlangsung' ? 'bg-indigo-950 text-white border-indigo-950 shadow-md' : ($status === 'Selesai' ? 'bg-slate-50/70 text-slate-400 border-slate-200/50' : 'bg-white text-slate-700 border-slate-100') }}">
                                             
                                             <div class="flex justify-between items-start space-x-2">
                                                 <div class="space-y-0.5">
                                                     <p class="text-[10px] font-bold tracking-wide {{ $status === 'Sedang Berlangsung' ? 'text-indigo-200' : 'text-slate-400' }}">
                                                         {{ substr($j->jam_mulai, 0, 5) }} - {{ substr($j->jam_selesai, 0, 5) }} WIB
                                                     </p>
                                                     <h3 class="text-sm font-black tracking-tight leading-snug">
                                                         {{ $j->mataPelajaran->nama_mapel }}
                                                     </h3>
                                                 </div>
                                                 <span class="px-2.5 py-1 rounded-full text-[9px] font-extrabold border shrink-0
                                                     {{ $status === 'Sedang Berlangsung' ? 'bg-white/10 text-white border-white/20' : ($status === 'Selesai' ? 'bg-slate-200/50 text-slate-500 border-slate-300/30' : 'bg-emerald-50 text-emerald-600 border-emerald-100') }}">
                                                     {{ $status }}
                                                 </span>
                                             </div>
                                        
                                             <div class="mt-4 flex items-center justify-between text-[10px] font-medium border-t pt-3.5 {{ $status === 'Sedang Berlangsung' ? 'border-white/10 text-indigo-200' : 'border-slate-100 text-slate-500' }}">
                                                 <span class="flex items-center">
                                                     <svg class="w-3.5 h-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                                     Guru Pengampu
                                                 </span>
                                                 <span class="flex items-center">
                                                     <svg class="w-3.5 h-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                                     R. Kelas
                                                 </span>
                                             </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="bg-white p-8 rounded-[28px] border border-slate-100/80 text-center text-slate-400 w-full flex-grow flex flex-col items-center justify-center min-h-[320px] shadow-xs">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 mx-auto mb-2 text-slate-300">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                                    </svg>
                                    <p class="text-xs font-bold text-slate-500">Tidak ada jadwal pelajaran</p>
                                    <p class="text-[10px] text-slate-400 mt-0.5">Nikmati waktu istirahat Anda!</p>
                                </div>
                            @endif
                        </div>
                    @endforeach
                    
                    <div x-show="selectedDay === 'Minggu'" class="bg-white p-8 rounded-[28px] border border-slate-100/80 text-center text-slate-400 w-full flex-grow flex flex-col items-center justify-center min-h-[320px] shadow-xs">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 mx-auto mb-2 text-slate-300">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.182 16.318A4.486 4.486 0 0012.016 15a4.486 4.486 0 00-3.198 1.318M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-xs font-bold text-slate-500">Hari Minggu libur</p>
                        <p class="text-[10px] text-slate-400 mt-0.5">Waktunya bersantai di rumah!</p>
                    </div>
                </div>
            </div>

            <div x-show="activeTab === 'profile'" x-cloak class="space-y-6 animate-[fadeIn_0.2s_ease-out]">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                    <div class="lg:col-span-5 space-y-6">
                        <div class="bg-white p-6 rounded-[28px] shadow-xs border border-slate-100 flex flex-col items-center space-y-4">
                            <div class="relative">
                                @if($siswa->foto_profil)
                                    <img src="{{ asset('storage/' . $siswa->foto_profil) }}" alt="{{ $siswa->user->name }}" class="w-24 h-24 rounded-full object-cover border-4 border-white shadow-md">
                                @else
                                    <div class="w-24 h-24 rounded-full bg-gradient-to-tr from-indigo-500 to-blue-600 flex items-center justify-center border-4 border-white shadow-md text-white font-black text-3xl">
                                        {{ strtoupper(substr($siswa->user->name, 0, 2)) }}
                                    </div>
                                @endif
                                <button class="absolute bottom-0 right-0 w-8 h-8 bg-indigo-600 rounded-full border-2 border-white flex items-center justify-center text-white shadow-sm hover:bg-indigo-500 transition">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                </button>
                            </div>
                            <div class="text-center space-y-1">
                                <h3 class="text-lg font-extrabold text-slate-800 leading-tight">{{ $siswa->user->name }}</h3>
                                <p class="text-xs text-slate-400 font-semibold">SMA Negeri SI-ABSEN &bull; {{ $siswa->kelas->nama_kelas }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-indigo-950 text-white p-5 rounded-[24px] shadow-sm flex flex-col justify-between h-28">
                                <p class="text-[10px] font-bold text-indigo-200 uppercase tracking-wider">Total Kehadiran</p>
                                <h3 class="text-2xl font-black tracking-tight mt-auto">{{ $kehadiranPercentage }}%</h3>
                            </div>
                            <div class="bg-white text-slate-700 p-5 rounded-[24px] border border-slate-100 shadow-xs flex flex-col justify-between h-28">
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Poin Perilaku</p>
                                <h3 class="text-2xl font-black text-emerald-600 tracking-tight mt-auto">150</h3>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-7 space-y-3">
                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Pengaturan Akun</h3>
                        
                        <div class="bg-white rounded-[28px] border border-slate-100 shadow-xs divide-y divide-slate-100 overflow-hidden text-sm font-semibold text-slate-700">
                            <div @click="showPersonalInfoModal = true" class="p-4 flex items-center justify-between hover:bg-slate-50 cursor-pointer transition">
                                <span class="flex items-center"><svg class="w-4 h-4 mr-2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>Informasi Pribadi</span>
                                <svg class="w-4 h-4 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                            </div>
                            <div @click="showNotificationModal = true" class="p-4 flex items-center justify-between hover:bg-slate-50 cursor-pointer transition">
                                <span class="flex items-center"><svg class="w-4 h-4 mr-2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>Pengaturan Notifikasi</span>
                                <svg class="w-4 h-4 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                            </div>
                            <div @click="showFaceIdManageModal = true" class="p-4 flex items-center justify-between hover:bg-slate-50 cursor-pointer transition">
                                <span class="flex items-center"><svg class="w-4 h-4 mr-2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 009 14a13.92 13.92 0 01-2-7.3m3.44 14.44a13.947 13.947 0 005.003-9.571M19.04 12.04l.054-.09a13.916 13.916 0 00-1.094-11.23M19.04 12.04c0 1.29-.166 2.542-.48 3.738M19.04 12.04a13.96 13.96 0 01-3.003-9.571m-2 13.571a13.9 13.9 0 01-6-2.29M12 9a3 3 0 100-6 3 3 0 000 6z" /></svg>Keamanan & Face ID</span>
                                <div class="flex items-center space-x-1">
                                    <span class="text-[10px] text-indigo-600 font-bold mr-1">{{ $siswa->face_embedding ? 'Aktif' : 'Nonaktif' }}</span>
                                    <svg class="w-4 h-4 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                                </div>
                            </div>
                            <div @click="showHelpModal = true" class="p-4 flex items-center justify-between hover:bg-slate-50 cursor-pointer transition">
                                <span class="flex items-center"><svg class="w-4 h-4 mr-2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" /></svg>Pusat Bantuan</span>
                                <svg class="w-4 h-4 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                            </div>
                            
                            <div @click="showLogoutModal = true" class="p-4 flex items-center justify-between hover:bg-rose-50 text-rose-600 cursor-pointer transition font-semibold">
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                                    Keluar
                                </span>
                                <svg class="w-4 h-4 text-rose-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center pt-2 select-none">
                    <p class="text-[9px] font-bold text-slate-300">Versi 2.4.0 (Build 82)</p>
                </div>
            </div>
            
        </div>

        <nav class="md:hidden absolute bottom-0 left-0 right-0 bg-white border-t border-slate-100 px-6 py-3 flex justify-between items-center z-30 shrink-0 select-none">
            <button @click="activeTab = 'dashboard'" class="flex flex-col items-center space-y-1 group">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 transition" :class="activeTab === 'dashboard' ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-600'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z" />
                </svg>
                <span class="text-[10px] font-extrabold tracking-wide transition" :class="activeTab === 'dashboard' ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-500'">Dashboard</span>
            </button>
            
            <button @click="activeTab = 'history'" class="flex flex-col items-center space-y-1 group">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 transition" :class="activeTab === 'history' ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-600'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-[10px] font-extrabold tracking-wide transition" :class="activeTab === 'history' ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-500'">History</span>
            </button>

            <button @click="activeTab = 'schedule'" class="flex flex-col items-center space-y-1 group">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 transition" :class="activeTab === 'schedule' ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-600'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span class="text-[10px] font-extrabold tracking-wide transition" :class="activeTab === 'schedule' ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-500'">Schedule</span>
            </button>

            <button @click="activeTab = 'profile'" class="flex flex-col items-center space-y-1 group">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 transition" :class="activeTab === 'profile' ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-600'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span class="text-[10px] font-extrabold tracking-wide transition" :class="activeTab === 'profile' ? 'text-indigo-600' : 'text-slate-400 group-hover:text-slate-500'">Profile</span>
            </button>
        </nav>

        @if($showScannerModal)
            <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true"
                 x-data="{
                     html5QrCode: null,
                     hasFaceEmbedding: {{ $siswa->face_embedding ? 'true' : 'false' }},
                     isFaceVerificationMode: false,
                     statusText: 'Pindai QR Code kelas yang ditampilkan guru',
                     faceStream: null,
                     isFaceDetected: false,
                     faceEmbedding: null,
                     qrPayload: '',
                     
                     initScanner() {
                         this.html5QrCode = new Html5Qrcode('webcam-portal-reader');
                         this.html5QrCode.start(
                             { facingMode: 'environment' },
                             { 
                                 fps: 15, 
                                 qrbox: { width: 220, height: 220 },
                                 aspectRatio: 1.0
                             },
                             (decodedText, decodedResult) => {
                                 this.playBeep();
                                 this.qrPayload = decodedText;

                                 if (this.hasFaceEmbedding) {
                                     this.html5QrCode.stop().then(() => {
                                         this.isFaceVerificationMode = true;
                                         this.statusText = 'QR Valid! Harap hadapkan kamera ke wajah Anda.';
                                         this.initFaceVerification();
                                     }).catch(err => {
                                         console.error(err);
                                         this.isFaceVerificationMode = true;
                                         this.statusText = 'QR Valid! Harap hadapkan kamera ke wajah Anda.';
                                         this.initFaceVerification();
                                     });
                                 } else {
                                     this.html5QrCode.stop().then(() => {
                                         $wire.prosesScanQrKelas(decodedText);
                                     }).catch(err => {
                                         console.error('Stop error:', err);
                                         $wire.prosesScanQrKelas(decodedText);
                                     });
                                 }
                             },
                             (errorMessage) => {}
                         ).catch(err => {
                             console.error('Camera startup failed:', err);
                             $wire.set('errorMessage', 'Gagal mengakses kamera. Pastikan browser diizinkan menggunakan kamera dan pastikan Anda menggunakan HTTPS.');
                             $wire.set('showScannerModal', false);
                         });
                     },
                     async initFaceVerification() {
                         try {
                             await window.__loadFaceModels();

                             this.faceStream = await navigator.mediaDevices.getUserMedia({ 
                                 video: { facingMode: 'user', width: 640, height: 480 } 
                             });

                             const video = this.$refs.faceVerifyVideo;
                             video.srcObject = this.faceStream;
                             await video.play();

                             this.detectFaceVerifyLoop();
                         } catch (err) {
                             console.error(err);
                             $wire.set('errorMessage', 'Gagal memuat kamera Face ID: ' + err.message);
                             this.closeScanner();
                         }
                     },

                     async detectFaceVerifyLoop() {
                         const video = this.$refs.faceVerifyVideo;
                         const canvas = this.$refs.faceVerifyCanvas;

                         if (!video || !this.faceStream) return;

                         const displaySize = { width: video.videoWidth || 320, height: video.videoHeight || 240 };
                         faceapi.matchDimensions(canvas, displaySize);

                         const interval = setInterval(async () => {
                             if (!this.faceStream || !video) {
                                 clearInterval(interval);
                                 return;
                             }

                             const detection = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.5 }))
                                 .withFaceLandmarks(true)
                                 .withFaceDescriptor();

                             const ctx = canvas.getContext('2d');
                             ctx.clearRect(0, 0, canvas.width, canvas.height);

                             if (detection) {
                                 this.isFaceDetected = true;
                                 this.statusText = 'Wajah terdeteksi! Memverifikasi...';
                                 this.faceEmbedding = Array.from(detection.descriptor);

                                 const resizedDetections = faceapi.resizeResults(detection, displaySize);
                                 faceapi.draw.drawDetections(canvas, resizedDetections);

                                 clearInterval(interval);
                                 this.submitAttendance();
                             } else {
                                 this.isFaceDetected = false;
                                 this.statusText = 'Posisikan wajah Anda di depan kamera depan';
                             }
                         }, 300);
                     },
                     submitAttendance() {
                         if (this.faceStream) {
                             this.faceStream.getTracks().forEach(track => track.stop());
                             this.faceStream = null;
                         }
                         $wire.prosesScanQrKelas(this.qrPayload, JSON.stringify(this.faceEmbedding));
                     },
                     playBeep() {
                         try {
                             const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                             const oscillator = audioCtx.createOscillator();
                             const gainNode = audioCtx.createGain();
                             oscillator.connect(gainNode);
                             gainNode.connect(audioCtx.destination);
                             oscillator.type = 'sine';
                             oscillator.frequency.value = 880;
                             gainNode.gain.setValueAtTime(0.3, audioCtx.currentTime);
                             oscillator.start();
                             gainNode.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.15);
                             oscillator.stop(audioCtx.currentTime + 0.15);
                         } catch(e) {
                             console.error(e);
                         }
                     },
                     closeScanner() {
                         if (this.faceStream) {
                             this.faceStream.getTracks().forEach(track => track.stop());
                             this.faceStream = null;
                         }
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
                
                <div class="flex items-center justify-center min-h-screen p-4 text-center">
                    <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-xs" @click="closeScanner()" aria-hidden="true"></div>
                    
                    <div class="relative z-50 inline-block align-middle bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all w-full max-w-[360px] border border-slate-100 animate-[fadeIn_0.2s_ease-out]">
                        <div class="bg-white px-5 pt-5 pb-4">
                            <div class="flex justify-between items-center pb-3.5 border-b border-slate-100">
                                <h3 class="text-sm font-bold text-slate-800" id="modal-title">
                                    Pindai QR Code & Face ID
                                </h3>
                                <button @click="closeScanner()" class="text-slate-400 hover:text-slate-600 focus:outline-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <div class="mt-4 flex flex-col items-center justify-center space-y-3">
                                <div x-show="!isFaceVerificationMode" class="relative w-full max-w-[280px] bg-slate-950 rounded-2xl overflow-hidden aspect-square border border-slate-800 flex flex-col items-center justify-center p-3">
                                    <div class="absolute inset-0 pointer-events-none border-2 border-indigo-500/20 rounded-2xl z-10">
                                        <div class="w-full h-0.5 bg-gradient-to-r from-transparent via-indigo-500 to-transparent shadow-[0_0_12px_rgba(99,102,241,0.8)] animate-[scanLaser_2s_infinite] absolute top-0"></div>
                                    </div>
                                    <div id="webcam-portal-reader" class="w-full h-full" style="border: none !important;"></div>
                                </div>

                                <div x-show="isFaceVerificationMode" class="face-video-container mx-auto">
                                    <video x-ref="faceVerifyVideo" id="face-verify-video-el" class="scale-x-[-1]" playsinline muted></video>
                                    <canvas x-ref="faceVerifyCanvas" class="scale-x-[-1]"></canvas>
                                </div>
                                
                                <div class="text-center space-y-0.5">
                                    <p class="text-[11px] font-semibold text-indigo-600 animate-pulse" x-text="statusText"></p>
                                    <p class="text-[9px] text-slate-400" x-show="!isFaceVerificationMode">Scan QR Code kelas yang ditampilkan guru</p>
                                    <p class="text-[9px] text-slate-400" x-show="isFaceVerificationMode">Dekatkan wajah Anda ke kamera depan</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-slate-50 px-5 py-3 flex justify-end">
                            <button @click="closeScanner()" type="button" 
                                class="bg-white hover:bg-slate-100 border border-slate-200 text-slate-700 font-semibold px-4 py-2 rounded-xl text-xs transition focus:outline-none">
                                Batal
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if($showRegisterFaceModal)
            <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-face-reg" role="dialog" aria-modal="true"
                 x-data="{
                     stream: null,
                     isLoading: true,
                     statusText: 'Memuat modul Face ID...',
                     isFaceDetected: false,
                     embedding: null,
                     async initFaceReg() {
                         try {
                             this.statusText = 'Memuat modul pengenalan wajah...';
                             await window.__loadFaceModels();

                             this.statusText = 'Membuka kamera depan...';
                             
                             this.stream = await navigator.mediaDevices.getUserMedia({ 
                                 video: { facingMode: 'user', width: 640, height: 480 } 
                             });

                             const video = this.$refs.faceRegVideo;
                             video.srcObject = this.stream;
                             await video.play();

                             this.isLoading = false;
                             this.statusText = 'Posisikan wajah Anda di tengah layar';
                             this.detectFaceLoop();
                         } catch (err) {
                             console.error(err);
                             $wire.set('errorMessage', 'Gagal memulai Face ID: ' + err.message);
                             this.closeModal();
                         }
                     },
                     loadScript(src) {
                         return new Promise((resolve, reject) => {
                             const s = document.createElement('script');
                             s.src = src;
                             s.onload = resolve;
                             s.onerror = reject;
                             document.head.appendChild(s);
                         });
                     },
                     async detectFaceLoop() {
                         const video = this.$refs.faceRegVideo;
                         const canvas = this.$refs.faceRegCanvas;
                         
                         if (!video || !this.stream) return;

                         const displaySize = { width: video.videoWidth || 320, height: video.videoHeight || 240 };
                         faceapi.matchDimensions(canvas, displaySize);

                         const interval = setInterval(async () => {
                             if (!this.stream || !video) {
                                 clearInterval(interval);
                                 return;
                             }

                             const detection = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.5 }))
                                 .withFaceLandmarks(true)
                                 .withFaceDescriptor();

                             const ctx = canvas.getContext('2d');
                             ctx.clearRect(0, 0, canvas.width, canvas.height);

                             if (detection) {
                                 this.isFaceDetected = true;
                                 this.statusText = 'Wajah terdeteksi! Silakan klik Simpan Wajah.';
                                 this.embedding = Array.from(detection.descriptor);

                                 const resizedDetections = faceapi.resizeResults(detection, displaySize);
                                 faceapi.draw.drawDetections(canvas, resizedDetections);
                             } else {
                                 this.isFaceDetected = false;
                                 this.statusText = 'Posisikan wajah Anda dengan jelas di depan kamera';
                             }
                         }, 300);
                     },
                     saveFace() {
                         if (this.embedding) {
                             $wire.simpanFaceEmbedding(JSON.stringify(this.embedding));
                             this.closeModal();
                         }
                     },
                     closeModal() {
                         if (this.stream) {
                             this.stream.getTracks().forEach(track => track.stop());
                             this.stream = null;
                         }
                         $wire.set('showRegisterFaceModal', false);
                     }
                 }"
                 x-init="setTimeout(() => initFaceReg(), 300)">
                
                <div class="flex items-center justify-center min-h-screen p-4 text-center">
                    <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-xs" @click="closeModal()" aria-hidden="true"></div>

                    <div class="relative z-50 inline-block align-middle bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all w-full max-w-[360px] border border-slate-100 animate-[fadeIn_0.2s_ease-out]">
                        <div class="bg-white px-5 pt-5 pb-4">
                            <div class="flex justify-between items-center pb-3.5 border-b border-slate-100">
                                <h3 class="text-sm font-bold text-slate-800">
                                    Registrasi Face ID Anda
                                </h3>
                                <button @click="closeModal()" class="text-slate-400 hover:text-slate-600 focus:outline-none">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <div class="mt-3 p-2.5 rounded-2xl text-[10px] font-bold text-center"
                                 :class="isFaceDetected ? 'bg-emerald-50 text-emerald-800 border border-emerald-100' : 'bg-slate-100 text-slate-700'">
                                <span x-text="statusText"></span>
                            </div>

                            <div class="mt-4 flex flex-col items-center justify-center">
                                <div class="face-video-container mx-auto">
                                    <video x-ref="faceRegVideo" id="face-reg-video-el" class="scale-x-[-1]" playsinline muted></video>
                                    <canvas x-ref="faceRegCanvas" class="scale-x-[-1]"></canvas>

                                    <div x-show="isLoading" class="absolute inset-0 flex flex-col items-center justify-center bg-slate-950/80 text-white space-y-2 z-10">
                                        <svg class="animate-spin h-6 w-6 text-indigo-500" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span class="text-[9px] text-slate-400">Menyiapkan Kamera...</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-slate-50 px-5 py-3.5 flex justify-end space-x-2.5">
                            <button @click="closeModal()" type="button" 
                                class="bg-white hover:bg-slate-100 border border-slate-200 text-slate-700 font-semibold px-4 py-2 rounded-xl text-xs transition focus:outline-none">
                                Batal
                            </button>
                            <button @click="saveFace()" type="button" :disabled="!isFaceDetected"
                                class="bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-500 hover:to-blue-500 text-white disabled:opacity-50 disabled:cursor-not-allowed font-semibold px-5 py-2 rounded-xl text-xs transition focus:outline-none shadow-md">
                                Simpan Wajah
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div x-show="showPersonalInfoModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 animate-[fadeIn_0.15s_ease-out]" x-cloak>
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs" @click="showPersonalInfoModal = false"></div>
            <div class="bg-white rounded-3xl p-6 w-full max-w-[320px] relative z-10 border border-slate-100 shadow-xl space-y-4 text-left">
                <h3 class="text-xs font-bold text-slate-800 border-b pb-1.5 uppercase tracking-wider">Informasi Pribadi</h3>
                <div class="space-y-3 text-[11px] text-slate-600">
                    <div>
                        <p class="font-bold text-slate-400 uppercase text-[8px]">Nama Lengkap</p>
                        <p class="font-semibold text-slate-700">{{ $siswa->user->name }}</p>
                    </div>
                    <div>
                        <p class="font-bold text-slate-400 uppercase text-[8px]">NISN</p>
                        <p class="font-mono font-semibold text-slate-700">{{ $siswa->nisn }}</p>
                    </div>
                    <div>
                        <p class="font-bold text-slate-400 uppercase text-[8px]">Kelas</p>
                        <p class="font-semibold text-slate-700">{{ $siswa->kelas->nama_kelas }}</p>
                    </div>
                    <div>
                        <p class="font-bold text-slate-400 uppercase text-[8px]">Email</p>
                        <p class="font-semibold text-slate-700">{{ $siswa->user->email }}</p>
                    </div>
                </div>
                <button @click="showPersonalInfoModal = false" class="w-full bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-2 rounded-xl text-[11px] transition">Tutup</button>
            </div>
        </div>

        <div x-show="showNotificationModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 animate-[fadeIn_0.15s_ease-out]" x-cloak>
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs" @click="showNotificationModal = false"></div>
            <div class="bg-white rounded-3xl p-6 w-full max-w-[320px] relative z-10 border border-slate-100 shadow-xl space-y-4 text-center">
                <div class="w-9 h-9 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto">
                    <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                </div>
                <h3 class="text-xs font-bold text-slate-800">Notifikasi Sistem</h3>
                <p class="text-[10px] text-slate-500 leading-normal">Notifikasi absensi dan jadwal telah diaktifkan otomatis di perangkat Anda.</p>
                <button @click="showNotificationModal = false" class="w-full bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-2 rounded-xl text-[11px] transition">Baik</button>
            </div>
        </div>

        <div x-show="showHelpModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 animate-[fadeIn_0.15s_ease-out]" x-cloak>
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs" @click="showHelpModal = false"></div>
            <div class="bg-white rounded-3xl p-6 w-full max-w-[320px] relative z-10 border border-slate-100 shadow-xl space-y-4 text-center">
                <div class="w-9 h-9 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto">
                    <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                </div>
                <h3 class="text-xs font-bold text-slate-800">Pusat Bantuan</h3>
                <p class="text-[10px] text-slate-500 leading-normal">Ada kendala presensi atau Face ID? Silakan hubungi operator tata usaha sekolah Anda.</p>
                <button @click="showHelpModal = false" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-2 rounded-xl text-[11px] transition">Hubungi Operator</button>
            </div>
        </div>

        <div x-show="showFaceIdManageModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 animate-[fadeIn_0.15s_ease-out]" x-cloak>
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs" @click="showFaceIdManageModal = false"></div>
            <div class="bg-white rounded-3xl p-6 w-full max-w-[320px] relative z-10 border border-slate-100 shadow-xl space-y-4 text-center">
                <div class="w-9 h-9 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto">
                    <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 009 14a13.92 13.92 0 01-2-7.3m3.44 14.44a13.947 13.947 0 005.003-9.571M19.04 12.04l.054-.09a13.916 13.916 0 00-1.094-11.23M19.04 12.04c0 1.29-.166 2.542-.48 3.738M19.04 12.04a13.96 13.96 0 01-3.003-9.571m-2 13.571a13.9 13.9 0 01-6-2.29M12 9a3 3 0 100-6 3 3 0 000 6z" /></svg>
                </div>
                <h3 class="text-xs font-bold text-slate-800">Manajemen Face ID</h3>
                @if($siswa->face_embedding)
                    <p class="text-[10px] text-slate-500 leading-normal">Face ID Anda telah aktif. Anda dapat menyetel ulang data wajah Anda dengan menghapusnya terlebih dahulu.</p>
                    <div class="space-y-2 pt-1.5">
                        <button wire:click="hapusFaceEmbedding" @click="showFaceIdManageModal = false" class="w-full bg-rose-600 hover:bg-rose-500 text-white font-bold py-2 rounded-xl text-[11px] transition">Hapus Face ID</button>
                        <button @click="showFaceIdManageModal = false" class="w-full bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-2 rounded-xl text-[11px] transition">Batal</button>
                    </div>
                @else
                    <p class="text-[10px] text-slate-500 leading-normal">Anda belum mendaftarkan wajah Anda. Silakan daftarkan wajah Anda untuk melakukan absensi lebih cepat.</p>
                    <div class="space-y-2 pt-1.5">
                        <button @click="showFaceIdManageModal = false; showRegisterFaceModal = true" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-2 rounded-xl text-[11px] transition">Daftarkan Sekarang</button>
                        <button @click="showFaceIdManageModal = false" class="w-full bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-2 rounded-xl text-[11px] transition">Batal</button>
                    </div>
                @endif
            </div>
        </div>

        <div x-show="showLogoutModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 animate-[fadeIn_0.15s_ease-out]" x-cloak>
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs" @click="showLogoutModal = false"></div>
            <div class="bg-white rounded-3xl p-6 w-full max-w-[320px] relative z-10 border border-slate-100 shadow-xl space-y-4 text-center animate-[scaleIn_0.2s_ease-out]">
                <div class="w-9 h-9 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center mx-auto">
                    <svg class="w-5 h-5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </div>
                <div class="space-y-0.5">
                    <h3 class="text-xs font-bold text-slate-800">Konfirmasi Keluar</h3>
                    <p class="text-[10px] text-slate-500 leading-normal">Apakah Anda yakin ingin keluar dari akun Anda?</p>
                </div>
                <div class="grid grid-cols-2 gap-2 pt-1.5">
                    <button @click="showLogoutModal = false" type="button" class="w-full bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-2 rounded-xl text-[11px] transition">Batal</button>
                    <form action="{{ route('logout') }}" method="POST" id="logout-form-profile" class="w-full">
                        @csrf
                        <button type="submit" class="w-full bg-rose-600 hover:bg-rose-500 text-white font-bold py-2 rounded-xl text-[11px] transition shadow-sm">Ya, Keluar</button>
                    </form>
                </div>
            </div>
        </div>

    </div>

</div>