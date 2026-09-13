file_php = 'D:/dsBilling/app/Models/User.php'
with open(file_php, 'r', encoding='utf-8') as f:
    content = f.read()

import re

search = """    public function roles(): BelongsToMany
    {"""

replace = """    public function employee()
    {
        return $this->hasOne(\\App\\Models\\Employee::class);
    }

    public function roles(): BelongsToMany
    {"""

if search in content:
    content = content.replace(search, replace)
    with open(file_php, 'w', encoding='utf-8') as f:
        f.write(content)
    print("Added employee relationship to User model")
else:
    print("Search block not found.")
