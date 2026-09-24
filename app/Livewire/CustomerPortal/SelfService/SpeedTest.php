<?php

namespace App\Livewire\CustomerPortal\SelfService;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.customer-app')]
class SpeedTest extends Component
{
    public $publicIp;
    public $ispName;
    public $city;
    public $localIp;

    public function mount()
    {
        $this->localIp = request()->ip();
        $this->detectPublicIp();
    }

    protected function detectPublicIp()
    {
        $ipForwarded = request()->header('X-Forwarded-For')
            ?? request()->header('X-Real-IP')
            ?? request()->header('CF-Connecting-IP')
            ?? request()->ip();

        if (is_string($ipForwarded) && str_contains($ipForwarded, ',')) {
            $ipForwarded = trim(explode(',', $ipForwarded)[0]);
        }

        $this->publicIp = $ipForwarded;
        $this->ispName = '-';
        $this->city = '-';

        $apiUrls = [
            'https://ipapi.co/json/',
            'https://ipwho.is/' . urlencode($this->publicIp),
            'https://api.ipbase.com/v2/info?ip=' . urlencode($this->publicIp),
        ];

        foreach ($apiUrls as $url) {
            try {
                $ctx = stream_context_create([
                    'http' => [
                        'timeout' => 3,
                        'ignore_errors' => true,
                        'header' => "User-Agent: dsBilling/1.0\r\n",
                    ],
                ]);
                $response = @file_get_contents($url, false, $ctx);
                if ($response) {
                    $data = @json_decode($response, true);
                    if ($data) {
                        if (!empty($data['ip'])) {
                            $this->publicIp = $data['ip'];
                        }
                        if (!empty($data['org']) || !empty($data['connection']['isp']) || !empty($data['data']['connection']['isp'])) {
                            $this->ispName = $data['org']
                                ?? $data['connection']['isp']
                                ?? $data['data']['connection']['isp']
                                ?? '-';
                        }
                        if (!empty($data['city']) || !empty($data['data']['location']['city']['name'])) {
                            $this->city = $data['city']
                                ?? $data['data']['location']['city']['name']
                                ?? '-';
                        }
                        if (empty($this->ispName) && !empty($data['isp'])) {
                            $this->ispName = $data['isp'];
                        }
                        break;
                    }
                }
            } catch (\Throwable $e) {
                continue;
            }
        }
    }

    public function render()
    {
        return view('livewire.customer-portal.self-service.speed-test');
    }
}
