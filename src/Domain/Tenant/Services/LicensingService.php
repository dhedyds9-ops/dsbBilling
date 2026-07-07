<?php

namespace Src\Domain\Tenant\Services;

use Src\Domain\Tenant\Aggregates\License;
use Src\Domain\Tenant\Enums\LicenseStatus;
use Src\Domain\Tenant\Enums\LicenseType;
use Src\Domain\Tenant\Repositories\LicenseRepositoryInterface;
use Src\Domain\Tenant\Events\LicenseActivatedEvent;
use Src\Domain\Tenant\Events\LicenseExpiringEvent;
use Src\Domain\Tenant\Events\LicenseExpiredEvent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Src\Domain\SharedKernel\ValueObjects\Uuid;

class LicensingService
{
    public function __construct(
        private readonly LicenseRepositoryInterface $licenseRepository
    ) {}

    public function createSubscription(
        Uuid $tenantId,
        \DateTimeImmutable $expiresAt,
        ?string $licenseKey = null
    ): License {
        Log::info("LicensingService: Creating subscription for tenant {$tenantId}");

        $license = License::createSubscription(
            $tenantId,
            $expiresAt,
            $licenseKey ? new \Src\Domain\Tenant\ValueObjects\LicenseKey($licenseKey, '') : null
        );

        $this->licenseRepository->save($license);

        return $license;
    }

    public function createTrial(Uuid $tenantId, int $trialDays = 14): License
    {
        Log::info("LicensingService: Creating trial for tenant {$tenantId}");

        $license = License::createTrial($tenantId, $trialDays);
        $this->licenseRepository->save($license);

        return $license;
    }

    public function activateLicense(string $licenseId): bool
    {
        $license = $this->licenseRepository->findById(
            Uuid::fromString($licenseId)
        );

        if (!$license) {
            throw new \InvalidArgumentException("License not found: {$licenseId}");
        }

        if ($license->isActive()) {
            return false;
        }

        if ($license->getMaxActivations()) {
            if ($license->getActivationCount() >= $license->getMaxActivations()) {
                Log::warning("LicensingService: Max activations reached for license {$licenseId}");
                return false;
            }
        }

        $license->activate();
        $this->licenseRepository->save($license);

        Event::dispatch(new LicenseActivatedEvent(
            $license->getId()->toString(),
            $license->getTenantId()->toString(),
            $license->getType()->value
        ));

        return true;
    }

    public function validateLicense(string $tenantId): array
    {
        $license = $this->licenseRepository->findByTenantId(
            Uuid::fromString($tenantId)
        );

        if (!$license) {
            return [
                'valid' => false,
                'reason' => 'no_license',
                'message' => 'No license found for this tenant',
            ];
        }

        if ($license->getStatus() === LicenseStatus::SUSPENDED) {
            return [
                'valid' => false,
                'reason' => 'suspended',
                'message' => 'License has been suspended',
            ];
        }

        if ($license->getStatus() === LicenseStatus::CANCELLED) {
            return [
                'valid' => false,
                'reason' => 'cancelled',
                'message' => 'License has been cancelled',
            ];
        }

        if ($license->isExpired()) {
            Event::dispatch(new LicenseExpiredEvent(
                $license->getId()->toString(),
                $license->getTenantId()->toString()
            ));

            return [
                'valid' => false,
                'reason' => 'expired',
                'message' => 'License has expired',
                'expired_at' => $license->getExpiresAt()->format('Y-m-d H:i:s'),
            ];
        }

        $remainingDays = $license->getRemainingDays();
        if ($remainingDays <= 7) {
            Event::dispatch(new LicenseExpiringEvent(
                $license->getId()->toString(),
                $license->getTenantId()->toString(),
                $remainingDays
            ));
        }

        return [
            'valid' => true,
            'license_id' => $license->getId()->toString(),
            'type' => $license->getType()->value,
            'expires_at' => $license->getExpiresAt()->format('Y-m-d H:i:s'),
            'remaining_days' => $remainingDays,
        ];
    }

    public function renewLicense(string $licenseId, \DateTimeImmutable $newExpiresAt): void
    {
        $license = $this->licenseRepository->findById(
            Uuid::fromString($licenseId)
        );

        if (!$license) {
            throw new \InvalidArgumentException("License not found: {$licenseId}");
        }

        $license->renew($newExpiresAt);
        $this->licenseRepository->save($license);
    }

    public function checkExpiringLicenses(int $days = 7): array
    {
        return $this->licenseRepository->findExpiringLicenses($days);
    }
}
