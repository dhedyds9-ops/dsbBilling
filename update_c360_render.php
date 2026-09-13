<?php
$file = 'D:/dsBilling/app/Livewire/Crm/Customer/Customer360.php';
$content = file_get_contents($file);

// Add with('tickets')
$content = str_replace("'installations',", "'installations', 'tickets',", $content);

// Update render()
$renderCode = <<<'PHP'
    public function render()
    {
        // 1. Timeline Building
        $timeline = [];
        $timeline[] = ['date' => $this->customer->created_at, 'title' => 'Pendaftaran Akun', 'description' => 'Customer terdaftar di sistem', 'type' => 'create'];
        
        if ($this->customer->installations) {
            foreach ($this->customer->installations as $inst) {
                $timeline[] = ['date' => $inst->completed_at ?? $inst->scheduled_at ?? $inst->created_at, 'title' => 'Instalasi Jaringan', 'description' => $inst->notes ?? 'Instalasi pelanggan dilakukan', 'type' => 'installation'];
            }
        }
        
        if ($this->customer->tickets) {
            foreach ($this->customer->tickets as $tick) {
                $timeline[] = ['date' => $tick->created_at, 'title' => 'Komplain / Tiket', 'description' => $tick->subject ?? $tick->title ?? 'Tiket dibuat', 'type' => 'ticket'];
                if ($tick->resolved_at) {
                    $timeline[] = ['date' => $tick->resolved_at, 'title' => 'Tiket Diselesaikan', 'description' => 'Komplain telah diselesaikan', 'type' => 'ticket_resolved'];
                }
            }
        }
        
        if ($this->customer->invoices) {
            foreach ($this->customer->invoices as $inv) {
                if ($inv->status === 'paid' && $inv->paid_at) {
                    $timeline[] = ['date' => $inv->paid_at, 'title' => 'Pembayaran Tagihan', 'description' => 'Tagihan ' . $inv->invoice_number . ' Lunas', 'type' => 'payment'];
                }
            }
        }
        
        // Sort timeline descending by date
        usort($timeline, function($a, $b) {
            return $b['date'] <=> $a['date'];
        });

        // 2. Tickets
        $tickets = $this->customer->tickets ?? [];

        // 3. Notifications (fetch if user_id exists)
        $notifications = [];
        if ($this->customer->user_id) {
            $notifications = \App\Models\Notification\Notification::where('recipient_id', $this->customer->user_id)->orderBy('created_at', 'desc')->get();
        }

        // 4. Activities (Mock audit trail based on timeline but formatted for activities)
        $activities = [];
        foreach (array_slice($timeline, 0, 10) as $t) {
            $user = 'Sistem';
            $module = 'System';
            if ($t['type'] === 'create') { $user = $this->customer->createdBy->name ?? 'Admin'; $module = 'CRM'; }
            if ($t['type'] === 'installation') { $module = 'Teknisi'; }
            if ($t['type'] === 'ticket') { $module = 'Support'; }
            if ($t['type'] === 'payment') { $module = 'Finance'; }
            
            $activities[] = [
                'user' => $user,
                'action' => $t['title'] . ' - ' . $t['description'],
                'module' => $module,
                'time' => \Carbon\Carbon::parse($t['date'])->diffForHumans()
            ];
        }

        $invoices = $this->customer->invoices;
        $payments = $this->customer->payments;
        $installations = $this->customer->installations;
        
        $devices = [];
        $this->customer->customerServices->each(function ($service) use (&$devices) {
            if ($service->onu) {
                $devices[] = [
                    'id' => $service->onu->id, 
                    'type' => 'ONT', 
                    'brand' => $service->onu->brand ?? 'Unknown', 
                    'model' => $service->onu->model ?? 'Unknown', 
                    'serial' => $service->onu->serial_number ?? 'Unknown', 
                    'status' => $service->onu->status ?? 'active',
                    'wifi_ssid' => $service->onu->wifi_ssid ?? '-',
                    'wifi_password' => $service->onu->wifi_password ?? '-',
                ];
            }
        });
        
        $monthlyBill = 0;
        $this->customer->customerServices->each(function ($service) use (&$monthlyBill) {
            if ($service->status === 'active' && $service->serviceProfile) {
                $monthlyBill += (float) $service->serviceProfile->base_price;
            }
        });

        $unpaidBill = 0;
        $earliestDueDate = null;
        if ($this->customer->invoices) {
            foreach ($this->customer->invoices as $inv) {
                if ($inv->status !== 'paid' && $inv->status !== 'cancelled') {
                    $unpaidBill += (float) $inv->total_amount;
                    if ($inv->due_date) {
                        if (!$earliestDueDate || $inv->due_date->lt($earliestDueDate)) {
                            $earliestDueDate = $inv->due_date;
                        }
                    }
                }
            }
        }

        $monitoring = [];
        $this->customer->customerServices->each(function ($service) use (&$monitoring) {
            $mon = [
                'service' => $service->service->name ?? 'Unknown',
                'status' => $service->status,
                'onu_rx' => $service->onu->rx_power_dbm ?? 'N/A',
                'onu_tx' => $service->onu->tx_power_dbm ?? 'N/A',
                'onu_status' => $service->onu->status ?? 'Unknown',
                'last_seen' => $service->onu->last_seen_at ?? 'Never',
                'ip' => $service->pppoeUser->static_ip ?? $service->hotspotUser->static_ip ?? 'Dynamic',
                'mac' => $service->pppoeUser->mac_address ?? $service->hotspotUser->mac_address ?? $service->onu->mac_address ?? 'N/A',
            ];
            $monitoring[] = $mon;
        });

        return view('livewire.crm.customer.customer360', compact(
            'monthlyBill', 
            'unpaidBill', 
            'earliestDueDate', 
            'timeline', 
            'activities', 
            'invoices', 
            'payments', 
            'tickets', 
            'devices', 
            'installations',
            'notifications',
            'monitoring'
        ));
    }
PHP;

$content = preg_replace('/public function render\(\).*?return view\(\'livewire\.crm\.customer\.customer360\', compact\([\s\S]*?\)\);\s*\}/', $renderCode, $content);
file_put_contents($file, $content);
echo "Updated render method.";
?>
