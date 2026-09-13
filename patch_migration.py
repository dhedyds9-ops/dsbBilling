file_php = 'D:/dsBilling/database/migrations/2026_09_13_195223_add_period_dates_to_payrolls_table.php'
with open(file_php, 'r', encoding='utf-8') as f:
    content = f.read()

import re

search = """    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            //
        });
    }"""
    
replace = """    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->date('period_start')->nullable()->after('period_year');
            $table->date('period_end')->nullable()->after('period_start');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn(['period_start', 'period_end']);
        });
    }"""

if search in content:
    content = content.replace(search, replace)
    with open(file_php, 'w', encoding='utf-8') as f:
        f.write(content)
    print("Migration modified")
else:
    print("Search block not found.")
