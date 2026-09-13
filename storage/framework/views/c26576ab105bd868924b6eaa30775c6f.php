<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo e(config('app.name', 'dsBilling')); ?> - Customer Portal</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        heading: ['Hanken Grotesk', 'sans-serif'],
                    },
                    colors: {
                        slate: {
                            850: '#151e2e',
                            900: '#0f172a',
                        },
                        blue: {
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body { 
            background-color: #0f172a;
            background-image: 
                radial-gradient(at 100% 100%, hsla(253,16%,7%,1) 0, transparent 50%), 
                radial-gradient(at 0% 100%, hsla(225,39%,30%,0.2) 0, transparent 50%), 
                radial-gradient(at 50% 50%, hsla(339,49%,30%,0.05) 0, transparent 50%);
            background-attachment: fixed;
        }
    </style>
</head>
<body class="font-sans text-slate-300 antialiased min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md relative z-10">
        
        <!-- Login Card -->
        <div class="bg-slate-800/80 backdrop-blur-xl border border-slate-700/50 rounded-3xl shadow-2xl p-8 relative overflow-hidden">
            <!-- Glow effect -->
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-64 h-32 bg-blue-500/20 rounded-full blur-3xl pointer-events-none"></div>
            
            <div class="text-center mb-8">
                <a href="<?php echo e(route('home')); ?>" class="inline-block text-3xl font-heading font-black tracking-tight text-white mb-2">
                    dsBilling
                </a>
                <h1 class="text-xl font-heading font-semibold text-blue-400">Customer Portal</h1>
                <p class="text-slate-400 mt-2 text-sm">Manage your billing and services</p>
            </div>

            <!-- Session Status -->
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('status')): ?>
                <div class="mb-4 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm text-center">
                    <?php echo e(session('status')); ?>

                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <form method="POST" action="<?php echo e(route('login')); ?>" x-data="{ showPassword: false }">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="login_type" value="customer">

                <!-- Identity / Login -->
                <div class="mb-5">
                    <label for="login" class="block text-sm font-medium text-slate-300 mb-2">Email, Username, or Phone</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <span class="material-symbols-outlined text-slate-500 dark:text-slate-400">account_circle</span>
                        </div>
                        <input id="login" type="text" name="login" value="<?php echo e(old('login')); ?>" required autofocus autocomplete="username"
                            class="block w-full pl-12 pr-4 py-3.5 bg-slate-900/60 border border-slate-700 rounded-2xl text-white placeholder-slate-500 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors sm:text-sm dark:bg-slate-900 dark:text-slate-100"
                            placeholder="Your credential">
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['login'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="mt-2 text-sm text-red-400 pl-2"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <!-- Password -->
                <div class="mb-6">
                    <div class="flex justify-between items-center mb-2">
                        <label for="password" class="block text-sm font-medium text-slate-300">Password</label>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(Route::has('password.request')): ?>
                            <a href="<?php echo e(route('password.request')); ?>" class="text-xs font-medium text-blue-400 hover:text-blue-300 transition-colors">
                                Forgot password?
                            </a>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <span class="material-symbols-outlined text-slate-500 dark:text-slate-400">key</span>
                        </div>
                        <input id="password" x-bind:type="showPassword ? 'text' : 'password'" name="password" required autocomplete="current-password"
                            class="block w-full pl-12 pr-12 py-3.5 bg-slate-900/60 border border-slate-700 rounded-2xl text-white placeholder-slate-500 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors sm:text-sm dark:bg-slate-900 dark:text-slate-100"
                            placeholder="••••••••">
                        <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-500 dark:text-slate-400 hover:text-slate-300 transition-colors focus:outline-none">
                            <span class="material-symbols-outlined" x-text="showPassword ? 'visibility_off' : 'visibility'">visibility</span>
                        </button>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="mt-2 text-sm text-red-400 pl-2"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center">
                        <input id="remember_me" type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-600 bg-slate-900/60 text-blue-500 focus:ring-blue-500 focus:ring-offset-slate-800 dark:bg-slate-900 dark:text-slate-100">
                        <label for="remember_me" class="ml-2 block text-sm text-slate-400 cursor-pointer">
                            Keep me signed in
                        </label>
                    </div>
                </div>

                <button type="submit" class="w-full flex justify-center items-center py-3.5 px-4 border border-transparent rounded-2xl shadow-lg text-sm font-bold text-white bg-blue-600 hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-slate-800 focus:ring-blue-500 transition-all">
                    Access Portal
                    <span class="material-symbols-outlined ml-2 text-lg">arrow_forward</span>
                </button>
            </form>
        </div>

        <div class="mt-8 text-center flex items-center justify-center space-x-4">
            <span class="text-sm text-slate-500 dark:text-slate-400">Staff member?</span>
            <a href="<?php echo e(route('admin.login')); ?>" class="inline-flex items-center px-3 py-1.5 border border-slate-700 rounded-lg text-xs font-medium text-slate-300 hover:bg-slate-800 hover:text-white transition-colors">
                <span class="material-symbols-outlined text-sm mr-1.5">admin_panel_settings</span>
                Admin Login
            </a>
        </div>
    </div>

</body>
</html>
<?php /**PATH D:\dsBilling\resources\views\auth\login-customer.blade.php ENDPATH**/ ?>