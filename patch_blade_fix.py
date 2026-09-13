file_create = 'D:/dsBilling/resources/views/livewire/admin/employee/create.blade.php'
with open(file_create, 'r', encoding='utf-8') as f:
    content = f.read()
content = content.replace("@foreach( as )", "@foreach($users as $user)")
content = content.replace("{{ ->id }}", "{{ $user->id }}")
content = content.replace("{{ ->name }}", "{{ $user->name }}")
content = content.replace("{{ ->email }}", "{{ $user->email }}")
with open(file_create, 'w', encoding='utf-8') as f:
    f.write(content)

file_edit = 'D:/dsBilling/resources/views/livewire/admin/employee/edit.blade.php'
with open(file_edit, 'r', encoding='utf-8') as f:
    content = f.read()
content = content.replace("@foreach( as )", "@foreach($users as $user)")
content = content.replace("{{ ->id }}", "{{ $user->id }}")
content = content.replace("{{ ->name }}", "{{ $user->name }}")
content = content.replace("{{ ->email }}", "{{ $user->email }}")
with open(file_edit, 'w', encoding='utf-8') as f:
    f.write(content)

print("Fixed blade variables!")
