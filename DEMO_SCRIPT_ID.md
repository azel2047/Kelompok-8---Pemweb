# EduAttend (SI-ABSEN-QR) - Naskah Demo Singkat (Bahasa Indonesia)

Naskah presentasi ringkas berdurasi 3 menit yang fokus pada fitur utama dan keunggulan teknis.

---

### Bagian 1: Pendahuluan & Solusi (30 detik)
* **Visual:** Dashboard dengan judul utama: **EduAttend (SI-ABSEN-QR)**.
* **Naskah:**
> "Halo semua. Hari ini saya akan mendemonstrasikan **EduAttend**, sistem absensi cerdas untuk mengeliminasi kecurangan titip absen atau penyebaran screenshot QR.
> 
> EduAttend menggunakan verifikasi ganda:
> 1. **QR Code Dinamis** berbasis tanda tangan HMAC waktu (Time-based HMAC) yang kedaluwarsa tiap 15 detik.
> 2. **Verifikasi Wajah (Face ID)** real-time langsung di browser menggunakan **face-api.js**."

---

### Bagian 2: Sisi Guru - QR Code Dinamis (45 detik)
* **Visual:** Panel Admin Filament v3 $\rightarrow$ Masuk ke Jadwal Pelajaran $\rightarrow$ Tampilkan layar QR Code. Tunjukkan QR yang berubah secara otomatis.
* **Naskah:**
> "Ini adalah Dashboard Guru yang dibangun dengan **Filament v3**.
> 
> Saat kelas dimulai, guru menampilkan QR Code ini. Perhatikan bahwa pola QR Code ini berganti secara otomatis setiap 15 detik.
> 
> Karena adanya verifikasi tanda tangan HMAC dinamis ini, siswa tidak bisa curang dengan cara mengirim screenshot QR ke temannya di luar kelas."

---

### Bagian 3: Sisi Siswa - Face ID & Scan (60 detik)
* **Visual:** Portal Siswa (tampilan HP) $\rightarrow$ Tampilkan registrasi wajah (kamera aktif dengan kotak pelacak hijau) $\rightarrow$ Lakukan Scan QR $\rightarrow$ Verifikasi wajah otomatis $\rightarrow$ Status berubah menjadi Hadir.
* **Naskah:**
> "Berikutnya adalah Portal Siswa, dibangun dengan Livewire dan Alpine.js.
> 
> Pertama, siswa mendaftarkan wajah. Pustaka **face-api.js** mendeteksi koordinat wajah di browser dan mengubahnya menjadi **128-dimensional embedding vector** untuk disimpan di database.
> 
> Saat siswa memindai QR Code kelas, kamera depan akan langsung aktif untuk mencocokkan wajah.
> 
> Sistem menghitung jarak **Euclidean Distance** antara wajah pindaian dan wajah terdaftar. Jika hasilnya $\le 0.6$, siswa langsung dicatat 'Hadir' secara instan tanpa perlu reload halaman."

---

### Bagian 4: Sistem Database & Penutup (45 detik)
* **Visual:** Tampilan menu Audit Log di Panel Admin.
* **Naskah:**
> "Di sisi backend, kami menggunakan MySQL **Stored Procedure** (`sp_catat_absen_qr`) agar proses pencatatan absen berlangsung sangat cepat.
> 
> Kami juga menanamkan database **Trigger** (`tr_after_insert_absensi`) yang otomatis mencatat riwayat aktivitas ke tabel `audit_logs` demi keamanan data.
> 
> Dengan kombinasi teknologi ini, EduAttend menjamin sistem absensi yang aman, cepat, dan bebas manipulasi. Terima kasih!"
