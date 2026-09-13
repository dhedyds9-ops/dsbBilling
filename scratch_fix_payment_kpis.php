<?php
$file = 'app/Livewire/Billing/Payment/Index.php';
$content = file_get_contents($file);

$oldRender = <<<PHP
    public function render()
    {
        \$query = Payment::with(['customer'])
            ->when(auth()->user()->hasRole('reseller'), function(\$q) {
                \$q->whereHas('customer', function(\$cq) {
                    \$cq->where(function(\$qq) {
                        \$qq->where('reseller_id', auth()->id())
                           ->orWhere('created_by', auth()->id());
                    });
                });
            });

        if (\$this->search) {
            \$query->where(function(\$q) {
                \$q->where('reference_number', 'like', '%' . \$this->search . '%')
                  ->orWhereHas('customer', function(\$q) {
                      \$q->where('name', 'like', '%' . \$this->search . '%');
                  });
            });
        }

        if (\$this->filters['status']) {
            \$query->where('status', \$this->filters['status']);
        }

        if (\$this->filters['method']) {
            \$query->where('method', \$this->filters['method']);
        }

        \$payments = \$query->orderBy(\$this->sortField, \$this->sortDirection)
                       ->paginate(\$this->perPage);

        \$baseStatsQuery = Payment::query()->when(auth()->user()->hasRole('reseller'), function(\$q) {
            \$q->whereHas('customer', function(\$cq) {
                \$cq->where(function(\$qq) {
                    \$qq->where('reseller_id', auth()->id())
                       ->orWhere('created_by', auth()->id());
                });
            });
        });

        \$stats = [
            'total' => (clone \$baseStatsQuery)->count(),
            'total_amount' => (clone \$baseStatsQuery)->sum('amount'),
            'success' => (clone \$baseStatsQuery)->where('status', 'success')->count(),
            'pending' => (clone \$baseStatsQuery)->where('status', 'pending')->count(),
            'failed' => (clone \$baseStatsQuery)->where('status', 'failed')->count(),
        ];
PHP;

$newRender = <<<PHP
    protected function buildScopedQuery(\$ignoreStatus = false)
    {
        \$query = Payment::with(['customer'])
            ->when(auth()->user()->hasRole('reseller'), function(\$q) {
                \$q->whereHas('customer', function(\$cq) {
                    \$cq->where(function(\$qq) {
                        \$qq->where('reseller_id', auth()->id())
                           ->orWhere('created_by', auth()->id());
                    });
                });
            });

        if (\$this->search) {
            \$query->where(function(\$q) {
                \$q->where('reference_number', 'like', '%' . \$this->search . '%')
                  ->orWhereHas('customer', function(\$q) {
                      \$q->where('name', 'like', '%' . \$this->search . '%');
                  });
            });
        }

        if (!\$ignoreStatus && !empty(\$this->filters['status'])) {
            \$query->where('status', \$this->filters['status']);
        }

        if (!empty(\$this->filters['method'])) {
            \$query->where('method', \$this->filters['method']);
        }

        return \$query;
    }

    public function render()
    {
        \$query = \$this->buildScopedQuery();

        \$payments = (clone \$query)->orderBy(\$this->sortField, \$this->sortDirection)
                       ->paginate(\$this->perPage);

        \$baseStatsQuery = \$this->buildScopedQuery(true);

        \$stats = [
            'total' => (clone \$baseStatsQuery)->count(),
            'total_amount' => (clone \$baseStatsQuery)->sum('amount'),
            'success' => (clone \$baseStatsQuery)->where('status', 'success')->count(),
            'pending' => (clone \$baseStatsQuery)->where('status', 'pending')->count(),
            'failed' => (clone \$baseStatsQuery)->where('status', 'failed')->count(),
        ];
PHP;

$content = str_replace($oldRender, $newRender, $content);
file_put_contents($file, $content);
echo "Fixed Payment Index KPIs\n";
