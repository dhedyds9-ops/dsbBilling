file = 'D:/dsBilling/resources/views/livewire/admin/employee/create.blade.php'
with open(file, 'r', encoding='utf-8') as f:
    content = f.read()

debug_html = """
    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
            <ul>
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form wire:submit="save">
"""
content = content.replace('<form wire:submit="save">', debug_html)
with open(file, 'w', encoding='utf-8') as f:
    f.write(content)
print("Added error debug to Create blade")
