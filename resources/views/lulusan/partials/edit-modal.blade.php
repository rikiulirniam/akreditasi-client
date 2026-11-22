<!-- ========================================== -->
<!-- MODAL: Update Lulusan -->
<!-- ========================================== -->
<div 
    id="editLulusanModal" 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/45 opacity-0 pointer-events-none transition-all duration-200 select-none"
    role="dialog"
    aria-modal="true"
    aria-labelledby="editLulusanModalTitle"
>
    <div class="lulusan-modal-card relative bg-white rounded-[28px] p-8 max-w-[460px] w-full shadow-2xl text-left transform scale-95 transition-all duration-200 max-h-[92vh] overflow-y-auto">
        <h3 id="editLulusanModalTitle" class="text-xl font-bold text-gray-900 tracking-tight text-center mb-6">
            Update Mahasiswa
        </h3>

        <form id="editLulusanForm" action="" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <!-- Field: Nama Mahasiswa -->
            <div>
                <label for="editNamaInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Nama Mahasiswa
                </label>
                <input 
                    type="text" 
                    name="nama_mahasiswa" 
                    id="editNamaInput" 
                    placeholder="Isi nama" 
                    required 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none placeholder:text-gray-500"
                >
            </div>

            <!-- Field: NIM -->
            <div>
                <label for="editNimInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    NIM
                </label>
                <input 
                    type="text" 
                    name="nim" 
                    id="editNimInput" 
                    placeholder="Masukkan NIM" 
                    required 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none placeholder:text-gray-500"
                >
            </div>

            <!-- Field: Tahun Masuk -->
            <div>
                <label for="editTahunInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Tahun Masuk
                </label>
                <div class="relative">
                    <select 
                        name="tahun_masuk" 
                        id="editTahunInput" 
                        required 
                        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none appearance-none cursor-pointer"
                    >
                        <option value="" disabled>Pilih Tahun Masuk</option>
                        @php
                            $tahunOpts = $angkatanOptions ?? range((int)date('Y'), (int)date('Y') - 8);
                        @endphp
                        @foreach($tahunOpts as $th)
                            <option value="{{ $th }}">{{ $th }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-600">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Field: Jenis Pendaftaran -->
            <div>
                <label for="editJenisInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Jenis Pendaftaran
                </label>
                <div class="relative">
                    <select 
                        name="jenis_pendaftaran" 
                        id="editJenisInput" 
                        class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none appearance-none cursor-pointer"
                    >
                        <option value="SNBP">SNBP</option>
                        <option value="SNBT">SNBT</option>
                        <option value="Mandiri">Mandiri</option>
                        <option value="baru">Baru</option>
                        <option value="transfer">Transfer</option>
                        <option value="asing">Asing</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-600">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Field: Tanggal Kelulusan -->
            <div>
                <label for="editKelulusanInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Tanggal Kelulusan
                </label>
                <input 
                    type="date" 
                    name="tanggal_kelulusan" 
                    id="editKelulusanInput" 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none placeholder:text-gray-500 cursor-pointer"
                >
            </div>

            <!-- Field: Masa Studi -->
            <div>
                <label for="editMasaStudiInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Masa Studi (Bulan)
                </label>
                <input 
                    type="number" 
                    name="masa_studi_bulan" 
                    id="editMasaStudiInput" 
                    placeholder="Isi Masa Studi" 
                    min="1"
                    max="120"
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none placeholder:text-gray-500"
                >
            </div>

            <!-- Field: Masa Tunggu -->
            <div>
                <label for="editMasaTungguInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Masa Tunggu (Bulan)
                </label>
                <input 
                    type="number" 
                    name="masa_tunggu_bulan" 
                    id="editMasaTungguInput" 
                    placeholder="Isi Masa Tunggu Kerja (opsional)" 
                    min="0"
                    max="120"
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none placeholder:text-gray-500"
                >
            </div>

            <!-- Field: Tempat Kerja -->
            <div>
                <label for="editTempatKerjaInput" class="block text-xs font-bold text-gray-900 mb-1.5">
                    Tempat Kerja
                </label>
                <input 
                    type="text" 
                    name="tempat_kerja" 
                    id="editTempatKerjaInput" 
                    placeholder="Contoh: PT Bank Mandiri (opsional)" 
                    class="w-full px-4 py-2.5 rounded-[10px] bg-white-form border-none text-sm font-medium text-gray-900 focus:ring-2 focus:ring-additional/30 focus:outline-none placeholder:text-gray-500"
                >
            </div>

            <!-- Modal Action Buttons (Batal & Simpan) -->
            <div class="grid grid-cols-2 gap-4 pt-4">
                <button 
                    type="button" 
                    class="close-lulusan-modal w-full py-3 px-6 rounded-xl bg-white-form hover:bg-gray-300 text-black font-bold text-sm transition cursor-pointer text-center"
                >
                    Batal
                </button>
                <button 
                    type="submit" 
                    class="w-full py-3 px-6 rounded-xl bg-additional hover:bg-[#01426c] text-white font-bold text-sm shadow-sm transition cursor-pointer text-center"
                >
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>
