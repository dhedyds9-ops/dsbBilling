<?php

declare(strict_types=1);

namespace App\Services\ISP\Radius\ValueObjects;

use App\Models\Customer\CustomerService;
use App\Models\ISP\HotspotUser;
use App\Models\ISP\PPPoEUser;
use App\Models\ISP\RadiusNas;
use App\Models\ISP\ServiceProfile;
use App\Models\ISP\Voucher;

/**
 * SSOT: Immutable DTO context yang dikirim ke setiap Policy Rule.
 *
 * Policy Engine TIDAK BOLEH mengambil data sendiri dari DB.
 * Semua dependency yang dibutuhkan policy harus sudah diinject via Context ini.
 */
final readonly class RadiusAccessContext
{
    public function __construct(
        public string          $username,
        public string          $password,
        public ?RadiusNas      $nas = null,
        public ?string         $nasIp = null,
        public ?string         $callingStationId = null,
        public ?string         $calledStationId = null,
        public ?string         $framedIp = null,
        public ?PPPoEUser      $pppoeUser = null,
        public ?HotspotUser    $hotspotUser = null,
        public ?Voucher        $voucher = null,
        public ?CustomerService $customerService = null,
        public ?ServiceProfile $serviceProfile = null,
        public string          $protocol = 'pppoe', // pppoe / hotspot
        public array           $extras = [],
    ) {}
}
