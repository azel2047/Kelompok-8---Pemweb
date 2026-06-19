<div class="space-y-2">
    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Upload Banner Pengumuman</label>
    
    <input type="file" 
           accept="image/*"
           class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100"
           onchange="prosesBannerPengumuman(this)">

    <p class="text-xs text-gray-400 mt-1">*Gambar otomatis dikompresi ke teks ringan agar kebal eror server.</p>
</div>

<script>
function prosesBannerPengumuman(inputElement) {
    const file = inputElement.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.readAsDataURL(file);
    
    reader.onload = function(event) {
        const img = new Image();
        img.src = event.target.result;
        
        img.onload = function() {
            const canvas = document.createElement('canvas');
            const MAX_WIDTH = 400; 
            let width = img.width;
            let height = img.height;
            
            if (width > MAX_WIDTH) {
                height *= MAX_WIDTH / width;
                width = MAX_WIDTH;
            }
            
            canvas.width = width;
            canvas.height = height;
            
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0, width, height);
            
            const compressedBase64 = canvas.toDataURL('image/jpeg', 0.3);
            
            // 🔥 TRIK TEMBAK JANGKAR: Cari elemen input hidden milik Filament
            // Filament biasanya menaruh name="data.banner_base64" atau id yang mirip
            const inputHidden = document.querySelector('input[name="banner_base64"]') || 
                                document.querySelector('input[id$="banner_base64"]');
            
            if (inputHidden) {
                // Isi nilainya secara fisik
                inputHidden.value = compressedBase64;
                // Paksa trigger event 'input' agar Livewire mendeteksi perubahan
                inputHidden.dispatchEvent(new Event('input', { bubbles: true }));
                console.log('Berhasil mengunci data ke input hidden Filament.');
            }
            
            // Fallback cadangan tetap jalankan set defer murni
            @this.set('data.banner_base64', compressedBase64);
        }
    }
}
</script>