@php
    $state = $getState();
    $statePath = $getStatePath();
@endphp

<div class="space-y-2" x-data="{
    imageUrl: @js($state),
    handleFile(event) {
        const file = event.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = (e) => {
            const img = new Image();
            img.src = e.target.result;
            img.onload = () => {
                const canvas = document.createElement('canvas');
                const MAX_WIDTH = 800;
                let width = img.width;
                let height = img.height;
                
                if (width > MAX_WIDTH) {
                    height *= MAX_WIDTH / width;
                    width = MAX_WIDTH;
                }
                
                canvas.width = width;
                canvas.height = height;
                
                const ctx2d = canvas.getContext('2d');
                ctx2d.drawImage(img, 0, 0, width, height);
                
                const compressedBase64 = canvas.toDataURL('image/jpeg', 0.6);
                this.imageUrl = compressedBase64;
                $wire.set('{{ $statePath }}', compressedBase64);
            };
        };
    }
}">
    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Banner Mata Pelajaran</label>

    <!-- Upload Box Area -->
    <div 
        class="relative border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-xl p-4 flex flex-col items-center justify-center bg-gray-50 dark:bg-gray-900/50 hover:bg-gray-100 dark:hover:bg-gray-900 transition cursor-pointer min-h-[180px] group"
        @click="$refs.fileInput.click()"
    >
        <!-- Image Preview if Available -->
        <template x-if="imageUrl">
            <div class="w-full h-full flex flex-col items-center justify-center space-y-3">
                <img :src="imageUrl" class="max-h-[140px] rounded-lg object-cover shadow-md border dark:border-gray-800" alt="Banner Preview">
                <span class="text-xs font-semibold text-primary-600 dark:text-primary-400 group-hover:underline">Klik untuk mengganti gambar</span>
            </div>
        </template>

        <!-- Placeholder if No Image -->
        <template x-if="!imageUrl">
            <div class="flex flex-col items-center justify-center space-y-2 text-center">
                <!-- Icon Placeholder -->
                <svg class="w-10 h-10 text-gray-400 dark:text-gray-600 group-hover:text-gray-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <div class="text-sm font-medium text-gray-600 dark:text-gray-400">
                    Pilih Berkas Banner
                </div>
                <div class="text-xs text-gray-400">
                    PNG, JPG, JPEG (Maks. 800px lebar, terkompresi otomatis)
                </div>
            </div>
        </template>
    </div>

    <!-- Hidden Input File -->
    <input 
        type="file" 
        x-ref="fileInput"
        accept="image/*"
        class="hidden"
        @change="handleFile($event)"
    >

    <p class="text-[11px] text-gray-400 dark:text-gray-500">*Sistem secara otomatis mengompresi gambar untuk menghemat ruang penyimpanan server.</p>
</div>
