<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo e(config('app.name', 'dsBilling')); ?> - Admin Access</title>
    
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
                        cyan: {
                            400: '#22d3ee',
                            500: '#06b6d4',
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
                radial-gradient(at 0% 0%, hsla(253,16%,7%,1) 0, transparent 50%), 
                radial-gradient(at 50% 0%, hsla(225,39%,30%,0.2) 0, transparent 50%), 
                radial-gradient(at 100% 0%, hsla(339,49%,30%,0.1) 0, transparent 50%);
            background-attachment: fixed;
        }
    </style>
</head>
<body class="font-sans text-slate-300 antialiased min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md relative z-10">
        <!-- Brand -->
        <div class="text-center mb-8">
            <a href="<?php echo e(route('home')); ?>" class="inline-block text-4xl font-heading font-black tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-500 hover:from-cyan-300 hover:to-blue-400 transition-all">
                dsBilling
            </a>
            <p class="mt-2 text-sm text-slate-400 font-medium tracking-wide uppercase">Workspace Access</p>
        </div>

        <!-- Login Card -->
        <div class="bg-slate-800/60 backdrop-blur-xl border border-slate-700/50 rounded-2xl shadow-2xl p-8 relative overflow-hidden">
            <!-- Glow effect -->
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>
            
            <div class="text-center mb-8">
                <h1 class="text-2xl font-heading font-bold text-white">Welcome Back</h1>
                <p class="text-slate-400 mt-1 text-sm">Sign in to manage your network</p>
            </div>

            <!-- Session Status -->
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('status')): ?>
                <div class="mb-4 p-4 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm">
                    <?php echo e(session('status')); ?>

                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <form method="POST" action="<?php echo e(route('login')); ?>" x-data="{ showPassword: false }">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="login_type" value="admin">

                <!-- Identity / Login -->
                <div class="mb-5">
                    <label for="login" class="block text-sm font-medium text-slate-300 mb-2">Email, Username, or Phone</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-symbols-outlined text-slate-500 dark:text-slate-400 text-xl">person</span>
                        </div>
                        <input id="login" type="text" name="login" value="<?php echo e(old('login')); ?>" required autofocus autocomplete="username"
                            class="block w-full pl-10 pr-4 py-3 bg-slate-900/50 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 transition-colors sm:text-sm dark:bg-slate-900 dark:text-slate-100"
                            placeholder="Enter your credential">
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['login'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="mt-2 text-sm text-red-400"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <!-- Password -->
                <div class="mb-5">
                    <div class="flex justify-between items-center mb-2">
                        <label for="password" class="block text-sm font-medium text-slate-300">Password</label>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(Route::has('password.request')): ?>
                            <a href="<?php echo e(route('password.request')); ?>" class="text-xs font-medium text-cyan-400 hover:text-cyan-300 transition-colors">
                                Forgot password?
                            </a>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-symbols-outlined text-slate-500 dark:text-slate-400 text-xl">lock</span>
                        </div>
                        <input id="password" x-bind:type="showPassword ? 'text' : 'password'" name="password" required autocomplete="current-password"
                            class="block w-full pl-10 pr-10 py-3 bg-slate-900/50 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 transition-colors sm:text-sm dark:bg-slate-900 dark:text-slate-100"
                            placeholder="••••••••">
                        <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-500 dark:text-slate-400 hover:text-slate-300 transition-colors focus:outline-none">
                            <span class="material-symbols-outlined text-xl" x-text="showPassword ? 'visibility_off' : 'visibility'">visibility</span>
                        </button>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="mt-2 text-sm text-red-400"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center mb-6">
                    <input id="remember_me" type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-600 bg-slate-900/50 text-cyan-500 focus:ring-cyan-500 focus:ring-offset-slate-800 dark:bg-slate-900 dark:text-slate-100">
                    <label for="remember_me" class="ml-2 block text-sm text-slate-400 cursor-pointer">
                        Remember me
                    </label>
                </div>

                <button type="submit" class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-semibold text-white bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-slate-800 focus:ring-cyan-500 transition-all">
                    Sign In
                    <span class="material-symbols-outlined ml-2 text-lg">login</span>
                </button>
            </form>
        </div>

        <div class="mt-8 text-center">
            <p class="text-sm text-slate-500 dark:text-slate-400">
                Are you a customer? 
                <a href="<?php echo e(route('customer.login')); ?>" class="font-medium text-slate-300 hover:text-white transition-colors underline decoration-slate-600 hover:decoration-slate-400 underline-offset-4">
                    Customer Portal
                </a>
            </p>
        </div>
    </div>

</body>
</html>
<?php /**PATH D:\dsBilling\resources\views\auth\login-admin.blade.php ENDPATH**/ ?>