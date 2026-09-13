filepath = 'D:/dsBilling/routes/web.php'
with open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

content = content.replace("//Route::get", "Route::get")

with open(filepath, 'w', encoding='utf-8') as f:
    f.write(content)
