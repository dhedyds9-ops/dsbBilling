<?php
$file = 'app/Console/Commands/ISP/ReapDeadRadiusSessionsCommand.php';
$content = file_get_contents($file);

$search = <<<PHP
        \$total = RadiusAccounting::query()
            ->whereNull('acct_stop_time')
            ->where(function (\$q) use (\$timeoutSec) {
                \$q->whereNotNull('acct_start_time')
                    ->whereRaw("TIMESTAMPDIFF(SECOND, acct_start_time, NOW()) >= {\$timeoutSec}");
            })
            ->orWhere(function (\$q) use (\$timeoutSec) {
                \$q->whereNull('acct_start_time')
                    ->whereRaw("TIMESTAMPDIFF(SECOND, received_at, NOW()) >= {\$timeoutSec}");
            })
            ->count();
PHP;

$replace = <<<PHP
        \$timeoutDate = now()->subSeconds(\$timeoutSec);
        \$total = RadiusAccounting::query()
            ->whereNull('acct_stop_time')
            ->where(function (\$q) use (\$timeoutDate) {
                \$q->where(function (\$q2) use (\$timeoutDate) {
                    \$q2->whereNotNull('acct_start_time')
                       ->where('acct_start_time', '<=', \$timeoutDate);
                })->orWhere(function (\$q2) use (\$timeoutDate) {
                    \$q2->whereNull('acct_start_time')
                       ->where('created_at', '<=', \$timeoutDate);
                });
            })
            ->count();
PHP;

$content = str_replace($search, $replace, $content);

$search2 = <<<PHP
            ->where(function (\$q) use (\$timeoutSec) {
                \$q->whereNotNull('acct_start_time')
                    ->whereRaw("TIMESTAMPDIFF(SECOND, acct_start_time, NOW()) >= {\$timeoutSec}");
            })
            ->orWhere(function (\$q) use (\$timeoutSec) {
                \$q->whereNull('acct_start_time')
                    ->whereRaw("TIMESTAMPDIFF(SECOND, received_at, NOW()) >= {\$timeoutSec}");
            })
            ->chunkById
PHP;

$replace2 = <<<PHP
            ->where(function (\$q) use (\$timeoutDate) {
                \$q->where(function (\$q2) use (\$timeoutDate) {
                    \$q2->whereNotNull('acct_start_time')
                       ->where('acct_start_time', '<=', \$timeoutDate);
                })->orWhere(function (\$q2) use (\$timeoutDate) {
                    \$q2->whereNull('acct_start_time')
                       ->where('created_at', '<=', \$timeoutDate);
                });
            })
            ->chunkById
PHP;

$content = str_replace($search2, $replace2, $content);

file_put_contents($file, $content);
echo "Fixed SQLite compatibility\n";
