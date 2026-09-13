file_blade = 'D:/dsBilling/resources/views/layouts/enterprise.blade.php'
with open(file_blade, 'r', encoding='utf-8') as f:
    content = f.read()

import re

search = """    <!-- Sidebar -->
    <x-admin.sidebar />

    <!-- Main Content Area -->
    <div
        class="lg:pl-64 transition-all duration-300 relative flex flex-col min-h-screen"
        :class="sidebarCollapsed ? 'lg:pl-20' : 'lg:pl-64'"
    >
        <!-- Topbar -->
        <x-admin.topbar :breadcrumbs="$breadcrumbs ?? []" :user="auth()->user()" />

        <!-- Page Content -->
        <main class="flex-1 p-6">"""

replace = """    <!-- Sidebar -->
    <div class="print:hidden">
        <x-admin.sidebar />
    </div>

    <!-- Main Content Area -->
    <div
        class="lg:pl-64 print:pl-0 transition-all duration-300 relative flex flex-col min-h-screen"
        :class="sidebarCollapsed ? 'lg:pl-20 print:pl-0' : 'lg:pl-64 print:pl-0'"
    >
        <!-- Topbar -->
        <div class="print:hidden">
            <x-admin.topbar :breadcrumbs="$breadcrumbs ?? []" :user="auth()->user()" />
        </div>

        <!-- Page Content -->
        <main class="flex-1 p-6 print:p-0 print:m-0">"""

if search in content:
    content = content.replace(search, replace)
    with open(file_blade, 'w', encoding='utf-8') as f:
        f.write(content)
    print("Added print:hidden to sidebar and topbar in enterprise layout")
else:
    print("Search block not found.")
