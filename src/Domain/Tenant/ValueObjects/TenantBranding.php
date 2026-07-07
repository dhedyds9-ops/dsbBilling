<?php

namespace Src\Domain\Tenant\ValueObjects;

class TenantBranding
{
    public function __construct(
        public readonly ?string $logoUrl = null,
        public readonly ?string $faviconUrl = null,
        public readonly ?string $primaryColor = null,
        public readonly ?string $secondaryColor = null,
        public readonly ?string $accentColor = null,
        public readonly ?string $darkModeLogoUrl = null,
        public readonly ?string $emailHeaderUrl = null,
        public readonly ?string $emailFooterText = null,
        public readonly ?string $customCss = null
    ) {}

    public function getColorScheme(): array
    {
        return [
            'primary' => $this->primaryColor ?? '#0ea5e9',
            'secondary' => $this->secondaryColor ?? '#a855f7',
            'accent' => $this->accentColor ?? '#22c55e',
        ];
    }

    public function toArray(): array
    {
        return [
            'logo_url' => $this->logoUrl,
            'favicon_url' => $this->faviconUrl,
            'primary_color' => $this->primaryColor,
            'secondary_color' => $this->secondaryColor,
            'accent_color' => $this->accentColor,
            'dark_mode_logo_url' => $this->darkModeLogoUrl,
            'email_header_url' => $this->emailHeaderUrl,
            'email_footer_text' => $this->emailFooterText,
            'custom_css' => $this->customCss,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            logoUrl: $data['logo_url'] ?? null,
            faviconUrl: $data['favicon_url'] ?? null,
            primaryColor: $data['primary_color'] ?? null,
            secondaryColor: $data['secondary_color'] ?? null,
            accentColor: $data['accent_color'] ?? null,
            darkModeLogoUrl: $data['dark_mode_logo_url'] ?? null,
            emailHeaderUrl: $data['email_header_url'] ?? null,
            emailFooterText: $data['email_footer_text'] ?? null,
            customCss: $data['custom_css'] ?? null
        );
    }
}
