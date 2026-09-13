<?php
$file = 'D:/dsBilling/app/Http/Requests/Auth/LoginRequest.php';
$content = file_get_contents($file);

$oldFunc = <<<PHP
    private function resolveIdentity(string \$identity): ?\App\Models\User
    {
        \$query = \App\Models\User::query();
        \$lower = mb_strtolower(\$identity);
        
        // Bersihkan nomor HP dari spasi, strip, atau plus
        \$cleanPhone = preg_replace('/[^0-9]/', '', \$identity);
        
        \$phone0 = \$cleanPhone;
        \$phone62 = \$cleanPhone;
        
        if (str_starts_with(\$cleanPhone, '62')) {
            \$phone0 = '0' . substr(\$cleanPhone, 2);
        } elseif (str_starts_with(\$cleanPhone, '0')) {
            \$phone62 = '62' . substr(\$cleanPhone, 1);
        }

        if (ctype_digit(\$cleanPhone) && strlen(\$cleanPhone) >= 10 && strlen(\$cleanPhone) <= 15) {
            return \$query->where(function(\$q) use (\$lower, \$phone0, \$phone62) {
                \$q->whereRaw('LOWER(whatsapp) IN (?, ?)', [\$phone0, \$phone62])
                  ->orWhereRaw('LOWER(pppoe_username) = ?', [\$lower])
                  ->orWhereRaw('LOWER(customer_code) = ? OR LOWER(username) = ? OR LOWER(email) = ?', [\$lower, \$lower, \$lower])
                  ->orWhereHas('customer', function(\$subQ) use (\$phone0, \$phone62) {
                      \$subQ->whereRaw('LOWER(phone) IN (?, ?)', [\$phone0, \$phone62]);
                  });
            })->first();
        }

        if (str_contains(\$identity, '@')) {
            return \$query->whereRaw('LOWER(email) = ?', [\$lower])->first()
                ?? \$query->whereRaw('LOWER(pppoe_username) = ? OR LOWER(customer_code) = ? OR LOWER(username) = ?', [\$lower, \$lower, \$lower])->first();
        }

        return \$query->where(function (\$q) use (\$lower, \$phone0, \$phone62) {
            \$q->whereRaw('LOWER(email) = ?', [\$lower])
                ->orWhereRaw('LOWER(username) = ?', [\$lower])
                ->orWhereRaw('LOWER(customer_code) = ?', [\$lower])
                ->orWhereRaw('LOWER(pppoe_username) = ?', [\$lower])
                ->orWhereRaw('LOWER(whatsapp) IN (?, ?)', [\$phone0, \$phone62])
                ->orWhereHas('customer', function(\$subQ) use (\$phone0, \$phone62) {
                    \$subQ->whereRaw('LOWER(phone) IN (?, ?)', [\$phone0, \$phone62]);
                });
        })->first();
    }
PHP;

$newFunc = <<<PHP
    private function resolveIdentity(string \$identity): ?\App\Models\User
    {
        \$query = \App\Models\User::query();
        \$lower = mb_strtolower(trim(\$identity));
        
        // Bersihkan nomor HP dari spasi, strip, atau plus
        \$cleanPhone = preg_replace('/[^0-9]/', '', \$identity);
        
        \$phone0 = null;
        \$phone62 = null;
        
        if (!empty(\$cleanPhone)) {
            \$phone0 = \$cleanPhone;
            \$phone62 = \$cleanPhone;
            if (str_starts_with(\$cleanPhone, '62')) {
                \$phone0 = '0' . substr(\$cleanPhone, 2);
            } elseif (str_starts_with(\$cleanPhone, '0')) {
                \$phone62 = '62' . substr(\$cleanPhone, 1);
            }
        }

        if (ctype_digit(\$cleanPhone) && strlen(\$cleanPhone) >= 10 && strlen(\$cleanPhone) <= 15) {
            return \$query->where(function(\$q) use (\$lower, \$phone0, \$phone62) {
                if (\$phone0 && \$phone62) {
                    \$q->whereRaw('LOWER(whatsapp) IN (?, ?)', [\$phone0, \$phone62]);
                }
                \$q->orWhereRaw('LOWER(pppoe_username) = ?', [\$lower])
                  ->orWhereRaw('LOWER(customer_code) = ? OR LOWER(username) = ? OR LOWER(email) = ?', [\$lower, \$lower, \$lower]);
                  
                if (\$phone0 && \$phone62) {
                    \$q->orWhereHas('customer', function(\$subQ) use (\$phone0, \$phone62) {
                        \$subQ->whereRaw('LOWER(phone) IN (?, ?)', [\$phone0, \$phone62]);
                    });
                }
            })->first();
        }

        if (str_contains(\$identity, '@')) {
            return \$query->whereRaw('LOWER(email) = ?', [\$lower])->first()
                ?? \$query->whereRaw('LOWER(pppoe_username) = ? OR LOWER(customer_code) = ? OR LOWER(username) = ?', [\$lower, \$lower, \$lower])->first();
        }

        return \$query->where(function (\$q) use (\$lower, \$phone0, \$phone62) {
            \$q->whereRaw('LOWER(email) = ?', [\$lower])
                ->orWhereRaw('LOWER(username) = ?', [\$lower])
                ->orWhereRaw('LOWER(customer_code) = ?', [\$lower])
                ->orWhereRaw('LOWER(pppoe_username) = ?', [\$lower]);
                
            if (\$phone0 && \$phone62) {
                \$q->orWhereRaw('LOWER(whatsapp) IN (?, ?)', [\$phone0, \$phone62])
                  ->orWhereHas('customer', function(\$subQ) use (\$phone0, \$phone62) {
                      \$subQ->whereRaw('LOWER(phone) IN (?, ?)', [\$phone0, \$phone62]);
                  });
            }
        })->first();
    }
PHP;

if (strpos($content, "private function resolveIdentity") !== false) {
    // Regex replace to handle slight formatting differences
    $content = preg_replace('/private function resolveIdentity.*?\}\s*\}/s', $newFunc . "\n}", $content);
    file_put_contents($file, $content);
    echo "Fixed resolveIdentity bug.\n";
} else {
    echo "Could not find resolveIdentity\n";
}
?>
