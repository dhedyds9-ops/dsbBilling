file_blade = 'D:/dsBilling/resources/views/components/display/avatar.blade.php'
with open(file_blade, 'r', encoding='utf-8') as f:
    content = f.read()

search = """    @if ($src)
        <img
            src="{{ $src }}"
            alt="{{ $alt ?? $name }}"
            class="{{ $avatarClasses }} {{ $roundedClass }} object-cover ring-2 ring-white"
        />
    @elseif ($initials)
        <div class="{{ $avatarClasses }} {{ $roundedClass }} {{ $bgColors[$colorIndex] }} flex items-center justify-center text-white font-medium ring-2 ring-white">
            {{ $initials }}
        </div>
    @else
        <div class="{{ $avatarClasses }} {{ $roundedClass }} bg-slate-200 flex items-center justify-center text-slate-500 dark:text-slate-400 ring-2 ring-white">
            <svg class="{{ $sizes[$size]['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
        </div>
    @endif"""

replace = """    @if ($src)
        <img
            src="{{ $src }}"
            alt="{{ $alt ?? $name }}"
            class="{{ $avatarClasses }} {{ $roundedClass }} object-cover border-2 border-white dark:border-slate-800"
        />
    @elseif ($name)
        <img
            src="https://ui-avatars.com/api/?name={{ urlencode($name) }}&background=random&color=fff&bold=true"
            alt="{{ $alt ?? $name }}"
            class="{{ $avatarClasses }} {{ $roundedClass }} object-cover border-2 border-white dark:border-slate-800"
        />
    @else
        <div class="{{ $avatarClasses }} {{ $roundedClass }} bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-slate-500 dark:text-slate-400 border-2 border-white dark:border-slate-800">
            <svg class="{{ $sizes[$size]['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
        </div>
    @endif"""

content = content.replace(search, replace)
# Also fix the ring to border in status
content = content.replace("ring-2 ring-white", "border-2 border-white dark:border-slate-800")
with open(file_blade, 'w', encoding='utf-8') as f:
    f.write(content)
print("Avatar patched")
