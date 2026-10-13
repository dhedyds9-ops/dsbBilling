{{-- Alert / Flash Message Component --}}
@if (session()->has('success'))
    <div class="mb-5 flex items-center gap-3 px-4 py-3 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 rounded-xl text-emerald-700 dark:text-emerald-400 text-sm font-medium">
        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">check_circle</span>
        <div>{{ session('success') }}</div>
    </div>
@endif

@if (session()->has('error'))
    <div class="mb-5 flex items-center gap-3 px-4 py-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 rounded-xl text-red-700 dark:text-red-400 text-sm font-medium">
        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">error</span>
        <div>{{ session('error') }}</div>
    </div>
@endif

@if (session()->has('warning'))
    <div class="mb-5 flex items-center gap-3 px-4 py-3 bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-700 rounded-xl text-amber-700 dark:text-amber-400 text-sm font-medium">
        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">warning</span>
        <div>{{ session('warning') }}</div>
    </div>
@endif

@if (session()->has('info'))
    <div class="mb-5 flex items-center gap-3 px-4 py-3 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-700 rounded-xl text-blue-700 dark:text-blue-400 text-sm font-medium">
        <span class="material-symbols-outlined notranslate" translate="no" style="font-size:18px">info</span>
        <div>{{ session('info') }}</div>
    </div>
@endif

@if (isset($errors) && $errors->any())
    <div class="mb-5 flex items-start gap-3 px-4 py-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 rounded-xl text-red-700 dark:text-red-400 text-sm">
        <span class="material-symbols-outlined notranslate mt-0.5" translate="no" style="font-size:18px">error</span>
        <div>
            <p class="font-bold mb-1">Peringatan Validasi:</p>
            <ul class="list-disc pl-4 space-y-0.5 text-red-600 dark:text-red-400 text-xs">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
