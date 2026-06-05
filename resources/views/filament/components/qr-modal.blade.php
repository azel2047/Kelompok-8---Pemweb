<div class="space-y-6 p-4">
    @php
        $jadwals = \App\Models\JadwalPelajaran::with('kelas')
            ->where('mata_pelajaran_id', $record->id)
            ->get();
    @endphp

    @if($jadwals->isEmpty())
        <div class="text-center py-6 text-slate-400 dark:text-slate-500">
            Belum ada jadwal pelajaran yang terdaftar untuk mata pelajaran ini.
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            @foreach($jadwals as $j)
                <div class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 flex flex-col items-center justify-center space-y-4 text-center">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300">
                        {{ $j->kelas->nama_kelas }}
                    </span>
                    
                    <div class="bg-white p-3 rounded-xl shadow-inner border border-slate-100 flex items-center justify-center min-h-[184px] min-w-[184px]">
                        <div id="qr-container-{{ $j->id }}" data-jadwal-id="{{ $j->id }}">
                            <img src="{{ route('admin.jadwal.qr', $j->id) }}?t={{ time() }}" alt="QR Code" class="w-40 h-40">
                        </div>
                    </div>
                    
                    <div class="space-y-1">
                        <p class="text-xs font-semibold text-slate-600 dark:text-slate-400">{{ $j->hari }}</p>
                        <p class="text-[10px] text-slate-400 dark:text-slate-500">{{ substr($j->jam_mulai, 0, 5) }} - {{ substr($j->jam_selesai, 0, 5) }} WIB</p>
                    </div>
                </div>
            @endforeach
        </div>
        
        <script>
            (function() {
                const containers = document.querySelectorAll('[id^="qr-container-"]');
                containers.forEach(container => {
                    const jadwalId = container.getAttribute('data-jadwal-id');
                    const img = container.querySelector('img');
                    
                    const intervalId = setInterval(() => {
                        if (!document.body.contains(container)) {
                            clearInterval(intervalId);
                            return;
                        }
                        const url = "{{ route('admin.jadwal.qr', ':id') }}".replace(':id', jadwalId);
                        img.src = url + '?t=' + Date.now();
                    }, 5000);
                });
            })();
        </script>
    @endif
</div>

