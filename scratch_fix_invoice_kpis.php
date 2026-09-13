<?php
$file = 'app/Livewire/Billing/Invoice/Index.php';
$content = file_get_contents($file);

$oldCode = <<<PHP
    protected function buildScopedQuery()
    {
        \$query = Invoice::query()->with(['customer.customerServices.serviceProfile', 'items', 'customer.reseller'])
            ->whereNotExists(function (\$sub) {
                \$sub->select('id')
                    ->from('voucher_orders')
                    ->whereColumn('voucher_orders.invoice_id', 'invoices.id');
            })
            ->when(auth()->user()->hasRole('reseller'), function(\$q) {
                \$q->whereHas('customer', function(\$cq) {
                    \$cq->where(function(\$qq) {
                        \$qq->where('reseller_id', auth()->id())
                           ->orWhere('created_by', auth()->id());
                    });
                });
            });

        if (\$this->activeTab === 'unpaid') {
PHP;

$newCode = <<<PHP
    protected function buildScopedQuery(\$ignoreStatus = false)
    {
        \$query = Invoice::query()->with(['customer.customerServices.serviceProfile', 'items', 'customer.reseller'])
            ->whereNotExists(function (\$sub) {
                \$sub->select('id')
                    ->from('voucher_orders')
                    ->whereColumn('voucher_orders.invoice_id', 'invoices.id');
            })
            ->when(auth()->user()->hasRole('reseller'), function(\$q) {
                \$q->whereHas('customer', function(\$cq) {
                    \$cq->where(function(\$qq) {
                        \$qq->where('reseller_id', auth()->id())
                           ->orWhere('created_by', auth()->id());
                    });
                });
            });

        if (!\$ignoreStatus) {
            if (\$this->activeTab === 'unpaid') {
                \$query->whereIn('status', ['pending', 'unpaid', 'partial', 'draft']);
            } elseif (\$this->activeTab === 'paid') {
                \$query->where('status', 'paid');
            } elseif (\$this->activeTab === 'overdue') {
                \$today = \Illuminate\Support\Carbon::today()->toDateString();
                \$query->where(function (\$q) use (\$today) {
                    \$q->whereDate('due_date', '<', \$today)
                      ->whereIn('status', ['pending', 'unpaid', 'partial', 'draft']);
                });
            }
        }
PHP;

$content = str_replace($oldCode, $newCode, $content);

$oldRender = <<<PHP
    public function render()
    {
        \$query = \$this->buildScopedQuery();

        \$invoices = (clone \$query)
            ->orderBy(\$this->sortField, \$this->sortDirection)
            ->paginate(\$this->perPage);

        \$today = Carbon::today()->toDateString();
        \$statsQuery = clone \$query;
        \$overdueQuery = clone \$query;
PHP;

$newRender = <<<PHP
    public function render()
    {
        // sync activeTab from filters
        if (isset(\$this->filters['status'])) {
            \$this->activeTab = empty(\$this->filters['status']) ? 'all' : \$this->filters['status'];
        }

        \$query = \$this->buildScopedQuery();

        \$invoices = (clone \$query)
            ->orderBy(\$this->sortField, \$this->sortDirection)
            ->paginate(\$this->perPage);

        \$today = Carbon::today()->toDateString();
        // Base query ignoring status for global KPIs
        \$statsQuery = \$this->buildScopedQuery(true);
        \$overdueQuery = \$this->buildScopedQuery(true);
PHP;

$content = str_replace($oldRender, $newRender, $content);
file_put_contents($file, $content);
echo "Fixed buildScopedQuery and statsQuery\n";
