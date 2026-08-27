<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.users.index') }}" class="p-2 text-slate-500 hover:text-slate-700 rounded-lg hover:bg-slate-100">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Detail User</h1>
            <p class="mt-1 text-sm text-slate-500">{{ $user->name }}</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
        <div class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nama</label>
                    <p class="text-slate-900">{{ $user->name }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                    <p class="text-slate-900">{{ $user->email }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nomor WhatsApp</label>
                    <p class="text-slate-900">{{ $user->whatsapp ?? '-' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Wilayah</label>
                    <p class="text-slate-900">{{ $user->wilayah ?? '-' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Role</label>
                    <div class="flex flex-wrap gap-1">
                        @foreach($user->roles as $role)
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                {{ ucfirst($role->name) }}
                            </span>
                        @endforeach
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Tanggal Dibuat</label>
                    <p class="text-slate-900">{{ $user->created_at?->translatedFormat('d M Y H:i') ?? '-' }}</p>
                </div>
            </div>

            <div class="flex items-center justify-end gap-4 pt-4 border-t border-slate-200">
                <a href="{{ route('admin.users.edit', $user->id) }}" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors">
                    Edit
                </a>
            </div>
        </div>
    </div>
</div>