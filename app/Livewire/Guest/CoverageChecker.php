<?php

namespace App\Livewire\Guest;

use App\Models\CRM\Lead;
use App\Models\ISP\Odp;
use Illuminate\Support\Str;
use Livewire\Component;

class CoverageChecker extends Component
{
    public $searchQuery = '';
    public $status = null; // null, 'available', 'unavailable', 'submitted'

    // Form Pengajuan
    public $formName = '';
    public $formPhone = '';
    public $formAddress = '';

    public function checkCoverage()
    {
        $this->validate([
            'searchQuery' => 'required|string|min:3',
        ]);

        $query = trim($this->searchQuery);
        
        $exists = Odp::where('status', 'active')
            ->where(function ($q) use ($query) {
                $q->where('address', 'like', "%{$query}%")
                  ->orWhere('village', 'like', "%{$query}%")
                  ->orWhere('district', 'like', "%{$query}%")
                  ->orWhere('regency', 'like', "%{$query}%")
                  ->orWhere('name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            })->exists();

        if ($exists) {
            $this->status = 'available';
        } else {
            $this->status = 'unavailable';
            $this->formAddress = $this->searchQuery;
        }
    }

    public function submitLead()
    {
        $this->validate([
            'formName' => 'required|string|max:100',
            'formPhone' => 'required|string|max:20',
            'formAddress' => 'required|string|max:500',
        ]);

        $lead = Lead::create([
            'uuid' => (string) Str::uuid(),
            'name' => $this->formName,
            'phone' => $this->formPhone,
            'address' => $this->formAddress,
            'source' => 'website_coverage_check',
            'status' => 'new',
            'notes' => 'Pengajuan dari form Cek Coverage. Area: ' . $this->searchQuery,
        ]);

        try {
            // Coba ambil nomor WA Admin dari tabel users, atau setting company.phone
            $adminPhone = \App\Models\User::whereHas('roles', function($q) {
                $q->where('name', 'admin');
            })->whereNotNull('whatsapp')->first()?->whatsapp 
            ?? \App\Models\Setting::getValue('company.phone', '081234567890');

            if ($adminPhone) {
                $waService = app(\App\Services\Notifications\WhatsApp\WhatsAppNotificationService::class);
                $messageText = "*[CALON USER BARU - PENDAFTARAN DARI WEBSITE]*\n\n"
                    . "Ada pendaftaran calon pelanggan baru dari menu Cek Coverage di landing:\n\n"
                    . "Nama: {$this->formName}\n"
                    . "WA: {$this->formPhone}\n"
                    . "Area Pencarian: {$this->searchQuery}\n"
                    . "Alamat Lengkap: {$this->formAddress}\n\n"
                    . "Silakan tindak lanjuti di menu: List Pelanggan > Calon User pada panel Admin.";

                $msg = new \App\Services\Notifications\WhatsApp\ValueObjects\WaOutgoingMessage(
                    toPhone: $adminPhone,
                    type: 'text',
                    text: $messageText,
                    category: 'info',
                    priorityHigh: true
                );

                $waService->sendImmediate($msg);
            }
        } catch (\Throwable $th) {
            // Abaikan jika gagal mengirim WA agar form tetap sukses disubmit
            \Illuminate\Support\Facades\Log::error('Gagal kirim notif WA Lead Baru: ' . $th->getMessage());
        }

        $this->status = 'submitted';
    }

    public function render()
    {
        return view('livewire.guest.coverage-checker');
    }
}
