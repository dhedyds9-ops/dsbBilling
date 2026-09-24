{{--
/**
 * Admin Footer Component
 */
--}}

<footer class="py-4 px-6 border-t border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
    <div class="flex flex-col md:flex-row items-center justify-between gap-4 text-sm text-slate-500 dark:text-slate-400">
        <div class="flex items-center gap-2">
            <span>&copy; {{ date('Y') }} WiFinan</span>
            <span class="hidden md:inline">•</span>
            <span>Version 1.0.0</span>
        </div>

        <div class="flex items-center gap-4">
            <a href="/docs" class="hover:text-slate-700 dark:text-slate-300 transition-colors">Documentation</a>
            <a href="/support" class="hover:text-slate-700 dark:text-slate-300 transition-colors">Support</a>
            <a href="/terms" class="hover:text-slate-700 dark:text-slate-300 transition-colors">Terms</a>
            <a href="/privacy" class="hover:text-slate-700 dark:text-slate-300 transition-colors">Privacy</a>
        </div>
    </div>
</footer>
