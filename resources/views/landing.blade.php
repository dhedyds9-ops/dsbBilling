<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Title & Meta Description -->
    <title>dsBilling - Internet Fiber Optic Cepat & Stabil di Indonesia</title>
    <meta name="description" content="Layanan internet fiber optic dsBilling dengan kecepatan hingga 100 Mbps, unlimited, dukungan 24/7, dan pembayaran QRIS. Paket mulai Rp150.000/bulan.">
    <meta name="keywords" content="internet fiber optic, internet rumah, internet cepat, wifi stabil, paket internet, dsBilling, internet murah">
    <meta name="robots" content="index, follow">

    <!-- Open Graph / Social Media -->
    <meta property="og:title" content="dsBilling - Internet Fiber Optic Cepat & Stabil">
    <meta property="og:description" content="Internet berkualitas untuk rumah dan bisnis Anda. Kecepatan stabil, unlimited, support 24/7.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://dsbilling.com">
    <meta property="og:image" content="https://dsbilling.com/images/og-image.jpg">
    <meta property="og:locale" content="id_ID">

    <!-- Favicon -->
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="https://dsbilling.com">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        .gradient-bg { background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); }
        .feature-icon { background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%); }
    </style>
</head>
<body class="font-sans antialiased">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="/" class="text-2xl font-bold text-blue-600">dsBilling</a>
                </div>
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#features" class="text-gray-700 hover:text-blue-600 transition">Fitur</a>
                    <a href="#pricing" class="text-gray-700 hover:text-blue-600 transition">Harga</a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('customer.login') }}" class="text-blue-600 hover:text-blue-800 font-medium transition">Login Pelanggan</a>
                    <a href="{{ route('admin.login') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition font-medium">Login Admin</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="gradient-bg text-white py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div>
                    <h1 class="text-4xl md:text-6xl font-bold mb-6 leading-tight">Internet Cepat & Stabil untuk Rumah Anda</h1>
                    <p class="text-xl text-blue-100 mb-8">Layanan internet fiber optic dengan kecepatan tinggi dan dukungan 24/7 untuk kebutuhan Anda.</p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="#pricing" class="bg-white text-blue-600 px-8 py-3 rounded-xl font-semibold text-lg hover:bg-gray-100 transition text-center">Lihat Paket</a>
                        <a href="{{ route('customer.login') }}" class="border-2 border-white text-white px-8 py-3 rounded-xl font-semibold text-lg hover:bg-white hover:text-blue-600 transition text-center">Login Pelanggan</a>
                    </div>
                </div>
                <div class="hidden md:block">
                    <svg class="w-full h-auto" viewBox="0 0 400 300" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="50" y="150" width="300" height="100" rx="20" fill="#ffffff" fill-opacity="0.1"/>
                        <circle cx="100" cy="200" r="30" fill="#3b82f6"/>
                        <circle cx="200" cy="200" r="40" fill="#10b981"/>
                        <circle cx="300" cy="200" r="30" fill="#f59e0b"/>
                        <path d="M130 200 L160 200" stroke="#ffffff" stroke-width="3"/>
                        <path d="M240 200 L270 200" stroke="#ffffff" stroke-width="3"/>
                        <rect x="80" y="50" width="80" height="60" rx="10" fill="#ffffff" fill-opacity="0.2"/>
                        <rect x="240" y="70" width="100" height="80" rx="10" fill="#ffffff" fill-opacity="0.2"/>
                    </svg>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Mengapa Memilih dsBilling?</h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">Fitur terbaik untuk pengalaman internet yang nyaman dan stabil</p>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white p-8 rounded-2xl shadow-sm hover:shadow-md transition border border-gray-100">
                    <div class="w-16 h-16 rounded-2xl feature-icon flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Kecepatan Stabil</h3>
                    <p class="text-gray-600">Jaringan fiber optic yang stabil tanpa gangguan untuk kebutuhan streaming, gaming, dan kerja dari rumah.</p>
                </div>
                <div class="bg-white p-8 rounded-2xl shadow-sm hover:shadow-md transition border border-gray-100">
                    <div class="w-16 h-16 rounded-2xl feature-icon flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Dukungan 24/7</h3>
                    <p class="text-gray-600">Tim support siap membantu kapan saja jika Anda mengalami masalah dengan layanan internet.</p>
                </div>
                <div class="bg-white p-8 rounded-2xl shadow-sm hover:shadow-md transition border border-gray-100">
                    <div class="w-16 h-16 rounded-2xl feature-icon flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Pembayaran QRIS</h3>
                    <p class="text-gray-600">Bayar tagihan dengan mudah menggunakan QRIS, transfer bank, atau e-wallet pilihan Anda.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Paket Internet Kami</h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">Pilih paket yang sesuai dengan kebutuhan Anda</p>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white border-2 border-gray-200 rounded-2xl p-8 hover:border-blue-200 transition">
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Starter</h3>
                    <div class="mb-6">
                        <span class="text-4xl font-bold text-gray-900">Rp 150k</span>
                        <span class="text-gray-600">/bulan</span>
                    </div>
                    <ul class="space-y-3 mb-8">
                        <li class="flex items-center text-gray-700"><svg class="w-5 h-5 mr-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Kecepatan 10 Mbps</li>
                        <li class="flex items-center text-gray-700"><svg class="w-5 h-5 mr-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Unlimited</li>
                        <li class="flex items-center text-gray-700"><svg class="w-5 h-5 mr-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Dukungan 24/7</li>
                    </ul>
                    <a href="{{ route('customer.login') }}" class="block w-full text-center border-2 border-blue-600 text-blue-600 px-6 py-3 rounded-xl font-semibold hover:bg-blue-50 transition">Pilih Paket</a>
                </div>
                <div class="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-2xl p-8 text-white transform md:scale-105 shadow-xl">
                    <h3 class="text-2xl font-bold mb-2">Professional</h3>
                    <div class="mb-6">
                        <span class="text-4xl font-bold">Rp 300k</span>
                        <span class="text-blue-200">/bulan</span>
                    </div>
                    <ul class="space-y-3 mb-8">
                        <li class="flex items-center"><svg class="w-5 h-5 mr-3 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Kecepatan 30 Mbps</li>
                        <li class="flex items-center"><svg class="w-5 h-5 mr-3 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Unlimited</li>
                        <li class="flex items-center"><svg class="w-5 h-5 mr-3 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Dukungan 24/7</li>
                        <li class="flex items-center"><svg class="w-5 h-5 mr-3 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Free Router</li>
                    </ul>
                    <a href="{{ route('customer.login') }}" class="block w-full text-center bg-white text-blue-600 px-6 py-3 rounded-xl font-semibold hover:bg-gray-100 transition">Pilih Paket</a>
                </div>
                <div class="bg-white border-2 border-gray-200 rounded-2xl p-8 hover:border-blue-200 transition">
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Business</h3>
                    <div class="mb-6">
                        <span class="text-4xl font-bold text-gray-900">Rp 500k</span>
                        <span class="text-gray-600">/bulan</span>
                    </div>
                    <ul class="space-y-3 mb-8">
                        <li class="flex items-center text-gray-700"><svg class="w-5 h-5 mr-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Kecepatan 100 Mbps</li>
                        <li class="flex items-center text-gray-700"><svg class="w-5 h-5 mr-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Unlimited</li>
                        <li class="flex items-center text-gray-700"><svg class="w-5 h-5 mr-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Dukungan 24/7</li>
                        <li class="flex items-center text-gray-700"><svg class="w-5 h-5 mr-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Priority Support</li>
                    </ul>
                    <a href="{{ route('customer.login') }}" class="block w-full text-center border-2 border-blue-600 text-blue-600 px-6 py-3 rounded-xl font-semibold hover:bg-blue-50 transition">Pilih Paket</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-2xl font-bold mb-4">dsBilling</h3>
                    <p class="text-gray-400 mb-4">Layanan internet terpercaya untuk rumah dan bisnis Anda.</p>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Navigasi</h4>
                    <ul class="space-y-2">
                        <li><a href="#features" class="text-gray-400 hover:text-white transition">Fitur</a></li>
                        <li><a href="#pricing" class="text-gray-400 hover:text-white transition">Harga</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Kontak</h4>
                    <p class="text-gray-400">support@dsbilling.com</p>
                    <p class="text-gray-400">+62 812-3456-7890</p>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; {{ date('Y') }} dsBilling. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>
