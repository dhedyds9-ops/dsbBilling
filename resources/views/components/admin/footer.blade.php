{{--
/**
 * Admin Footer Component
 */
--}}

<footer class="py-4 px-6 border-t border-slate-200 bg-white">
    <div class="flex flex-col md:flex-row items-center justify-between gap-4 text-sm text-slate-500">
        <div class="flex items-center gap-2">
            <span>&copy; {{ date('Y') }} WiFinan</span>
            <span class="hidden md:inline">•</span>
            <span>Version 1.0.0</span>
        </div>

        <div class="flex items-center gap-4">
            <a href="/docs" class="hover:text-slate-700 transition-colors">Documentation</a>
            <a href="/support" class="hover:text-slate-700 transition-colors">Support</a>
            <a href="/terms" class="hover:text-slate-700 transition-colors">Terms</a>
            <a href="/privacy" class="hover:text-slate-700 transition-colors">Privacy</a>
        </div>
    </div>
</footer>
