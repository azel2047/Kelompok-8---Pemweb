<div class="space-y-2">
    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Upload Banner (HTML Manual Bypass)</label>
    
    <input type="file" 
           id="file_picker_native"
           accept="image/*"
           class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100"
           onchange="compressAndUpload(this)">

    <p class="text-xs text-gray-400 mt-1">*Dilengkapi kompresor otomatis agar state data tidak korup di server.</p>
</div>

<script>
function compressAndUpload(inputElement) {
    const file = inputElement.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.readAsDataURL(file);
    
    reader.onload = function(event) {
        const img = new Image();
        img.src = event.target.result;
        
        img.onload = function() {
            // Setup canvas buat kompresi resolusi gambar
            const canvas = document.createElement('canvas');
            
            // Set max width banner biar proporsional dan enteng
            const MAX_WIDTH = 800;
            let width = img.width;
            let height = img.height;
            
            if (width > MAX_WIDTH) {
                height *= MAX_WIDTH / width;
                width = MAX_WIDTH;
            }
            
            canvas.width = width;
            canvas.height = height;
            
            // Gambar ulang ke canvas
            const ctx2d = canvas.getContext('2d');
            ctx2d.drawImage(img, 0, 0, width, height);
            
            // Output berupa Base64 JPEG dengan kualitas 60% (Super Ringan & Gak bakal kepotong)
            const compressedBase64 = canvas.toDataURL('image/jpeg', 0.6);
            
            // Tembak langsung ke Livewire Filament
            @this.set('data.deskripsi_mapel_opsional', compressedBase64);
        }
    }
}
</script>
