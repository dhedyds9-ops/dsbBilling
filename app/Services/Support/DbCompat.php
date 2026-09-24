<?php

namespace App\Services\Support;

use Illuminate\Support\Facades\DB;

class DbCompat
{
    public static function fieldOrder(string $column, array $values): string
    {
        $driver = DB::getDriverName();
        $quotedColumn = $column;

        if ($driver === 'sqlite') {
            $caseParts = [];
            foreach (array_values($values) as $idx => $val) {
                $order = $idx + 1;
                $escaped = str_replace("'", "''", (string)$val);
                $caseParts[] = "WHEN '{$escaped}' THEN {$order}";
            }
            $caseStr = implode(' ', $caseParts);
            return "CASE {$quotedColumn} {$caseStr} ELSE " . (count($values) + 1) . " END";
        }

        $escapedValues = array_map(function ($v) {
            return "'" . str_replace("'", "''", (string)$v) . "'";
        }, $values);
        $valuesStr = implode(', ', $escapedValues);
        return "FIELD({$quotedColumn}, {$valuesStr})";
    }

    public static function concat(array $parts): string
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            $escaped = array_map(function ($part) {
                if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*(\.[a-zA-Z_][a-zA-Z0-9_]*)?$/', $part)) {
                    return "CAST({$part} AS TEXT)";
                }
                $escaped = str_replace("'", "''", (string)$part);
                return "'{$escaped}'";
            }, $parts);
            return '(' . implode(' || ', $escaped) . ')';
        }

        $escaped = array_map(function ($part) {
            if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*(\.[a-zA-Z_][a-zA-Z0-9_]*)?$/', $part)) {
                return $part;
            }
            $escaped = str_replace("'", "''", (string)$part);
            return "'{$escaped}'";
        }, $parts);
        return 'CONCAT(' . implode(', ', $escaped) . ')';
    }

    public static function concatId(string $prefix, string $idColumn = 'id'): string
    {
        $driver = DB::getDriverName();
        $p = "'" . str_replace("'", "''", $prefix) . "'";
        if ($driver === 'sqlite') {
            return "({$p} || CAST({$idColumn} AS TEXT))";
        }
        return "CONCAT({$p}, {$idColumn})";
    }

    public static function dateBucket(string $column, string $period): string
    {
        $driver = DB::getDriverName();
        $minuteFmt = $driver === 'sqlite' ? '%M' : '%i';

        $format = match($period) {
            '15m'  => "%Y-%m-%d %H:{$minuteFmt}",
            '1h'   => "%Y-%m-%d %H:{$minuteFmt}",
            '6h'   => '%Y-%m-%d %H:00',
            '24h'  => '%Y-%m-%d %H:00',
            default => "%Y-%m-%d %H:{$minuteFmt}",
        };

        if ($driver === 'sqlite') {
            return "strftime('{$format}', {$column})";
        }

        return "DATE_FORMAT({$column}, '{$format}')";
    }

    public static function dateDiffDays(string $date1Expr, string $date2Expr): string
    {
        $driver = DB::getDriverName();
        if ($driver === 'sqlite') {
            return "CAST((julianday({$date1Expr}) - julianday({$date2Expr})) AS INTEGER)";
        }
        return "DATEDIFF({$date1Expr}, {$date2Expr})";
    }

    public static function currentDate(): string
    {
        $driver = DB::getDriverName();
        if ($driver === 'sqlite') {
            return "date('now')";
        }
        return 'CURDATE()';
    }

    public static function year(string $column): string
    {
        $driver = DB::getDriverName();
        if ($driver === 'sqlite') {
            return "CAST(strftime('%Y', {$column}) AS INTEGER)";
        }
        return "YEAR({$column})";
    }

    public static function month(string $column): string
    {
        $driver = DB::getDriverName();
        if ($driver === 'sqlite') {
            return "CAST(strftime('%m', {$column}) AS INTEGER)";
        }
        return "MONTH({$column})";
    }

    public static function day(string $column): string
    {
        $driver = DB::getDriverName();
        if ($driver === 'sqlite') {
            return "CAST(strftime('%d', {$column}) AS INTEGER)";
        }
        return "DAY({$column})";
    }
}
