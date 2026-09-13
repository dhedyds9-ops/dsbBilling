{{--
/**
 * Page Card Component - Enterprise Design System
 * Wrapper card khusus untuk page content (dengan border, bg, shadow yang standard)
 */
--}}
<div {{ $attributes->merge(['class' => 'bg-ds-surface border border-slate-200 dark:border-slate-700 rounded-2xl shadow-soft-sm p-4 sm:p-6']) }}>
    {{ $slot }}
</div>






