file = 'D:/dsBilling/app/Models/CRM/Customer.php'
with open(file, 'r', encoding='utf-8') as f:
    content = f.read()

replacement = """        static::deleted(function ($customer) {
            // Deactivate associated login user
            if ($customer->user_id) {
                \\App\\Models\\User::where('id', $customer->user_id)->update(['is_active' => false]);
            }

            // Cascade soft delete to customer services"""

content = content.replace("        static::deleted(function ($customer) {\n            // Cascade soft delete to customer services", replacement)

replacement2 = """        static::restoring(function ($customer) {
            // Reactivate associated login user
            if ($customer->user_id) {
                \\App\\Models\\User::where('id', $customer->user_id)->update(['is_active' => true]);
            }

            // Cascade restore to customer services"""
            
content = content.replace("        static::restoring(function ($customer) {\n            // Cascade restore to customer services", replacement2)

with open(file, 'w', encoding='utf-8') as f:
    f.write(content)
print("Updated Customer events")
