@props(['title'- 'description' => null- 'actions' => null])

<div class="sm:flex sm:items-center sm:justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100">{{ $title }}</h1>
        @if($description)
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $description }}</p>
        @endif
    </div>
    @if($actions)
        <div class="mt-4 sm:mt-0 flex gap-3">
            {{ $actions }}
        </div>
    @endif
</div>






