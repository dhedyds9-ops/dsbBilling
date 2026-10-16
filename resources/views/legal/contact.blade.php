<x-layouts.public-legal title="Hubungi Kami & Layanan Pelanggan">
    <div class="space-y-8">
        <section>
            <p class="text-base text-slate-300">
                Punya pertanyaan seputar paket internet, butuh bantuan instalasi baru, atau mengalami kendala teknis? Tim dukungan pelanggan <strong>{{ $companyName }}</strong> siap membantu Anda.
            </p>
        </section>

        <!-- Contact Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 not-prose">
            <!-- Office Address Card -->
            @if(!empty($companyAddress))
            <div class="glass-card p-6 rounded-2xl border border-white/10 flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center text-cyan-400 shrink-0">
                    <span class="material-symbols-outlined text-2xl">location_on</span>
                </div>
                <div>
                    <h3 class="text-white font-bold text-base mb-1">Alamat Kantor Operasional</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        {{ $companyAddress }}<br>
                        @if(!empty($companyCity) || !empty($companyProvince))
                            {{ $companyCity }}@if(!empty($companyCity) && !empty($companyProvince)), @endif{{ $companyProvince }} {{ $companyPostal }}<br>
                        @endif
                        Indonesia
                    </p>
                </div>
            </div>
            @endif

            <!-- WhatsApp Card -->
            @if(!empty($companyPhone))
            <div class="glass-card p-6 rounded-2xl border border-white/10 flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 shrink-0">
                    <span class="material-symbols-outlined text-2xl">chat</span>
                </div>
                <div>
                    <h3 class="text-white font-bold text-base mb-1">WhatsApp Customer Service</h3>
                    <p class="text-slate-400 text-sm mb-2">Konsultasi paket & bantuan cepat:</p>
                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $companyPhone) }}" target="_blank" class="inline-flex items-center gap-1.5 text-sm font-bold text-emerald-400 hover:text-emerald-300 transition-colors">
                        {{ $companyPhone }} <span class="material-symbols-outlined text-[16px]">open_in_new</span>
                    </a>
                </div>
            </div>
            @endif

            <!-- Email Card -->
            <div class="glass-card p-6 rounded-2xl border border-white/10 flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400 shrink-0">
                    <span class="material-symbols-outlined text-2xl">mail</span>
                </div>
                <div>
                    <h3 class="text-white font-bold text-base mb-1">Email Resmi</h3>
                    <p class="text-slate-400 text-sm mb-2">Pertanyaan umum, penagihan & partnership:</p>
                    <a href="mailto:{{ $companyEmail }}" class="inline-flex items-center gap-1.5 text-sm font-bold text-blue-400 hover:text-blue-300 transition-colors">
                        {{ $companyEmail }}
                    </a>
                </div>
            </div>

            <!-- Hours Card -->
            <div class="glass-card p-6 rounded-2xl border border-white/10 flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400 shrink-0">
                    <span class="material-symbols-outlined text-2xl">schedule</span>
                </div>
                <div>
                    <h3 class="text-white font-bold text-base mb-1">Jam Operasional</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        {{ $companyHours }}
                    </p>
                    <span class="inline-block mt-2 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-950 text-emerald-400 border border-emerald-800">
                        NOC Monitoring 24 Jam
                    </span>
                </div>
            </div>
        </div>

        <section class="mt-8 pt-6 border-t border-white/10">
            <h2 class="text-xl font-bold text-white mb-2">Ingin Mendaftar Langganan Baru?</h2>
            <p class="text-sm text-slate-400 mb-4">
                Silakan pilih paket yang sesuai dengan kebutuhan Anda di halaman utama atau klik tombol di bawah ini:
            </p>
            <a href="{{ route('home') }}#paket" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-bold bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white shadow-lg shadow-cyan-500/25 transition-all">
                <span class="material-symbols-outlined text-[18px]">rocket_launch</span> Lihat Pilihan Paket
            </a>
        </section>
    </div>
</x-layouts.public-legal>
