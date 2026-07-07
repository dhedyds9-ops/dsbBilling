<?php

namespace Src\Domain\Tenant\Services;

use Src\Domain\Tenant\ValueObjects\TenantBranding;

class BrandingService
{
    public function generateCSS(TenantBranding $branding, bool $darkMode = false): string
    {
        $colors = $branding->getColorScheme();

        $css = ":root {\n";
        $css .= "  --brand-primary: {$colors['primary']};\n";
        $css .= "  --brand-secondary: {$colors['secondary']};\n";
        $css .= "  --brand-accent: {$colors['accent']};\n";
        $css .= "}\n";

        if ($branding->customCss) {
            $css .= "\n" . $branding->customCss . "\n";
        }

        return $css;
    }

    public function generateEmailTemplate(TenantBranding $branding, string $content): string
    {
        $header = $branding->emailHeaderUrl
            ? "<img src='{$branding->emailHeaderUrl}' alt='Header' style='max-width: 600px;'>"
            : '';

        $footer = $branding->emailFooterText ?? '';

        return "
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
                    .header { margin-bottom: 20px; }
                    .content { margin-bottom: 30px; }
                    .footer { color: #666; font-size: 12px; border-top: 1px solid #eee; padding-top: 20px; }
                </style>
            </head>
            <body>
                <div class='header'>{$header}</div>
                <div class='content'>{$content}</div>
                <div class='footer'>{$footer}</div>
            </body>
            </html>
        ";
    }

    public function getFaviconDataUrl(?string $faviconUrl): ?string
    {
        if (!$faviconUrl) {
            return null;
        }

        return "data:image/x-icon;base64," . base64_encode(file_get_contents($faviconUrl));
    }

    public function validateBrandingColors(array $colors): bool
    {
        foreach ($colors as $color) {
            if (!$this->isValidHexColor($color)) {
                return false;
            }
        }
        return true;
    }

    private function isValidHexColor(string $color): bool
    {
        return preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color) === 1;
    }
}
