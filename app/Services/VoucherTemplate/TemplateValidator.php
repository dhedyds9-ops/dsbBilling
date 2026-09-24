<?php

declare(strict_types=1);

namespace App\Services\VoucherTemplate;

use Illuminate\Support\Facades\Storage;

/**
 * Validator template voucher untuk memastikan kualitas template SEBELUM
 * disimpan maupun SEBELUM dijadikan default template.
 *
 * Mengecek berbagai aspek: struktur block syntax ({if}/{foreach}), variabel,
 * filter/helpers, assets, HTML, legacy syntax, dan size constraints.
 * Semua hasil validasi dikumpulkan di result array (tidak throw exception)
 * agar UI dapat menampilkan seluruh isu sekaligus.
 *
 * Contoh penggunaan:
 * ```
 * $validator = app(TemplateValidator::class);
 * $result = $validator->validate($templateCode, context: [], cssCode: $css);
 * if (!$result['is_valid']) { ... }
 * ```
 *
 * Sample test result (1) — Template perfect:
 * ```
 * $result = [
 *   'is_valid'   => true,
 *   'can_default'=> true,
 *   'errors'     => [],
 *   'warnings'   => [],
 *   'info'       => [],
 *   'stats'      => [
 *     'lines'            => 42,
 *     'variables_used'   => ['voucher.code','voucher.price','package.name','company.name'],
 *     'helpers_used'     => ['qr','format_currency'],
 *     'estimated_render_ms' => 25,
 *     'has_legacy_syntax'=> false,
 *   ],
 *   'suggestions'=> [],
 * ];
 * ```
 *
 * Sample test result (2) — Unmatched {if} + 1 unknown variable:
 * ```
 * $result = [
 *   'is_valid'   => false,
 *   'can_default'=> false,
 *   'errors'     => [
 *     ['severity'=>'error','code'=>'ERR_UNMATCHED_IF','message'=>'Kurang penutup {/if} — 1 buka vs 0 tutup','line'=>5,'context'=>'{if voucher.status == \"available\"}'],
 *   ],
 *   'warnings'   => [
 *     ['severity'=>'warning','code'=>'WARN_UNKNOWN_VARIABLE','message'=>'Variabel \"voucher.prcie\" tidak dikenali. Apakah maksud \"voucher.price\"?','line'=>7,'context'=>'{{voucher.prcie}}'],
 *   ],
 *   'info'       => [],
 *   'stats'      => ['lines'=>12,'variables_used'=>['voucher.status','voucher.prcie'],'helpers_used'=>[],'estimated_render_ms'=>10,'has_legacy_syntax'=>false],
 *   'suggestions'=> [['level'=>'info','title'=>'Perbaiki typo variable','from'=>'voucher.prcie','to'=>'voucher.price']],
 * ];
 * ```
 *
 * Sample test result (3) — Legacy Mikhmon dengan 3 unknown_tokens:
 * ```
 * $result = [
 *   'is_valid'   => true,
 *   'can_default'=> false,
 *   'errors'     => [],
 *   'warnings'   => [
 *     ['severity'=>'warning','code'=>'WARN_LEGACY_SYNTAX','message'=>'Template terdeteksi gaya Mikhmon. Disarankan translate agar dapat fitur dsBilling penuh.','line'=>1,'context'=>$vs['kode']],
 *     ['severity'=>'warning','code'=>'WARN_LEGACY_UNKNOWN_TOKENS','message'=>'Hasil translate mempunyai 3 token yang tidak dapat dikonversi otomatis.','line'=>null,'context'=>'unknown_tokens: _TOKEN_X, _TOKEN_Y, _TOKEN_Z'],
 *   ],
 *   'info'       => [],
 *   'stats'      => ['lines'=>30,'variables_used'=>['voucher.code','voucher.username'],'helpers_used'=>[],'estimated_render_ms'=>18,'has_legacy_syntax'=>true],
 *   'suggestions'=> [['level'=>'warning','title'=>'Translate template','from'=>'Legacy Mikhmon','to'=>'dsBilling native']],
 * ];
 * ```
 *
 * Sample test result (4) — Script src external:
 * ```
 * $result = [
 *   'is_valid'   => false,
 *   'can_default'=> false,
 *   'errors'     => [
 *     ['severity'=>'error','code'=>'ERR_EXTERNAL_SCRIPT','message'=>'<script src=\"https://cdn.example.com/x.js\"> tidak diizinkan — hanya inline script yang didukung.','line'=>15,'context'=>'<script src=\"https://cdn.example.com/x.js\">'],
 *   ],
 *   'warnings'   => [],
 *   'info'       => [],
 *   'stats'      => ['lines'=>50,'variables_used'=>['voucher.code'],'helpers_used'=>[],'estimated_render_ms'=>20,'has_legacy_syntax'=>false],
 *   'suggestions'=> [['level'=>'error','title'=>'Hapus script external','from'=>'<script src=..>','to'=>'inline <script> tanpa src']],
 * ];
 * ```
 *
 * Sample test result (5) — Unclosed </div> + external asset http image:
 * ```
 * $result = [
 *   'is_valid'   => false,
 *   'can_default'=> false,
 *   'errors'     => [],
 *   'warnings'   => [
 *     ['severity'=>'warning','code'=>'WARN_UNCLOSED_HTML_TAG','message'=>'Tag <div> dibuka di baris 3 tapi tidak pernah ditutup.','line'=>3,'context'=>'<div class=\"card\">'],
 *     ['severity'=>'warning','code'=>'WARN_EXTERNAL_ASSET','message'=>'Asset eksternal: http://cdn.othersite.com/logo.png. Akan di-render via external, pastikan tersedia saat cetak PDF.','line'=>8,'context'=>'<img src=\"http://cdn.othersite.com/logo.png\">'],
 *   ],
 *   'info'       => [
 *     ['severity'=>'info','code'=>'INFO_EXTERNAL_ASSET_NOTE','message'=>'Asset eksternal HTTP(S) bergantung pada koneksi saat render PDF.','line'=>8,'context'=>'http://cdn.othersite.com/logo.png'],
 *   ],
 *   'stats'      => ['lines'=>60,'variables_used'=>['company.name'],'helpers_used'=>[],'estimated_render_ms'=>30,'has_legacy_syntax'=>false],
 *   'suggestions'=> [['level'=>'warning','title'=>'Upload asset ke storage public','from'=>'http://...','to'=>'asset(\"path/to/logo.png\")']],
 * ];
 * ```
 */
class TemplateValidator
{
    private const ALLOWED_FILTERS = [
        'currency', 'number_format', 'date', 'upper', 'lower',
        'escape', 'raw', 'trim', 'nl2br',
    ];

    private const ALLOWED_HELPERS = [
        'qr'               => ['min' => 1, 'max' => 4],
        'barcode'          => ['min' => 1, 'max' => 4],
        'asset'            => ['min' => 1, 'max' => 2],
        'format_currency'  => ['min' => 1, 'max' => 2],
        'format_number'    => ['min' => 1, 'max' => 3],
        'date_format'      => ['min' => 1, 'max' => 2],
    ];

    private const SELF_CLOSING_TAGS = ['img', 'br', 'hr', 'input', 'meta', 'link'];

    private const CONTAINER_TAGS = [
        'div', 'span', 'p', 'a', 'b', 'i', 'u', 'table', 'tr', 'td', 'th',
        'ul', 'ol', 'li', 'style', 'script', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'section', 'main', 'header', 'footer', 'article',
    ];

    /** @var array<int,array{severity:string,code:string,message:string,line:?int,context:string}> */
    private array $errors = [];
    /** @var array<int,array{severity:string,code:string,message:string,line:?int,context:string}> */
    private array $warnings = [];
    /** @var array<int,array{severity:string,code:string,message:string,line:?int,context:string}> */
    private array $info = [];
    /** @var array<int,array{level:string,title:string,from:string,to:string}> */
    private array $suggestions = [];

    /** @var array<string, true> */
    private array $variablesUsed = [];
    /** @var array<string, true> */
    private array $helpersUsed = [];

    /** @var array<int, string> */
    private array $lines = [];
    private int $totalLines = 0;

    /**
     * Menjalankan seluruh validasi template voucher.
     *
     * @param string               $templateCode Source template (wajib)
     * @param array<string, mixed> $context      Context tambahan (mis. variable dari luar)
     * @param string|null          $cssCode      Kode CSS opsional
     * @param string|null          $jsCode       Kode JS opsional
     * @param int|null             $templateId   ID template existing (untuk keperluan debug/audit)
     * @param bool                 $strictMode   Jika false, beberapa ERROR turun jadi WARNING
     *
     * @return array{
     *     is_valid: bool,
     *     can_default: bool,
     *     errors: array<int, array{severity:string,code:string,message:string,line:?int,context:string}>,
     *     warnings: array<int, array{severity:string,code:string,message:string,line:?int,context:string}>,
     *     info: array<int, array{severity:string,code:string,message:string,line:?int,context:string}>,
     *     stats: array{
     *         lines: int,
     *         variables_used: array<int, string>,
     *         helpers_used: array<int, string>,
     *         estimated_render_ms: int,
     *         has_legacy_syntax: bool,
     *     },
     *     suggestions: array<int, array{level:string,title:string,from:string,to:string}>,
     * }
     */
    public function validate(
        string $templateCode,
        array $context = [],
        ?string $cssCode = null,
        ?string $jsCode = null,
        ?int $templateId = null,
        bool $strictMode = true,
    ): array {
        $this->resetState();
        $this->lines = preg_split("/\r\n|\n|\r/", $templateCode) ?: [];
        $this->totalLines = count($this->lines);

        $hasLegacySyntax = false;
        $unknownTokensCount = 0;

        $legacyClass = 'App\\Services\\VoucherTemplate\\LegacyCompatibilityLayer';
        if (class_exists($legacyClass)) {
            $legacy = app($legacyClass);
            if (method_exists($legacy, 'detectLegacySyntax') && $legacy->detectLegacySyntax($templateCode)) {
                $hasLegacySyntax = true;
                $this->addWarning(
                    'WARN_LEGACY_SYNTAX',
                    'Template terdeteksi gaya Mikhmon. Disarankan translate agar dapat fitur dsBilling penuh.',
                    1,
                    substr($templateCode, 0, 80)
                );
                $this->addSuggestion('warning', 'Translate template', 'Legacy Mikhmon', 'dsBilling native');

                if (method_exists($legacy, 'translate')) {
                    try {
                        $translated = $legacy->translate($templateCode);
                        if (is_array($translated) && isset($translated['unknown_tokens']) && is_array($translated['unknown_tokens'])) {
                            $unknownTokensCount = count($translated['unknown_tokens']);
                            if ($unknownTokensCount > 0) {
                                $sample = implode(', ', array_slice($translated['unknown_tokens'], 0, 5));
                                $this->addWarning(
                                    'WARN_LEGACY_UNKNOWN_TOKENS',
                                    sprintf('Hasil translate mempunyai %d token yang tidak dapat dikonversi otomatis.', $unknownTokensCount),
                                    null,
                                    'unknown_tokens: ' . $sample
                                );
                            }
                        }
                    } catch (\Throwable) {
                    }
                }
            }
        }

        $this->checkSizeConstraints($templateCode, $cssCode ?? '', $jsCode ?? '');
        $this->checkBlockStructure($templateCode, $strictMode);
        $this->checkBracesBalance($templateCode);
        $this->checkControlFlowValidity($templateCode, $strictMode);
        $this->checkVariables($templateCode, $context);
        $this->checkFiltersAndHelpers($templateCode, $strictMode);
        $this->checkAssets($templateCode);
        $this->checkHtmlAndPrint($templateCode, $strictMode);

        $loopCount = $this->countForeachLoops($templateCode);
        $charCount = strlen($templateCode) + strlen($cssCode ?? '') + strlen($jsCode ?? '');
        $estimatedMs = (int) min(500, max(5, (int) ($charCount / 2000) + $loopCount * 10));

        $variablesList = array_keys($this->variablesUsed);
        sort($variablesList);
        $helpersList = array_keys($this->helpersUsed);
        sort($helpersList);

        return [
            'is_valid'    => count($this->errors) === 0,
            'can_default' => count($this->errors) === 0 && count($this->warnings) === 0,
            'errors'      => $this->errors,
            'warnings'    => $this->warnings,
            'info'        => $this->info,
            'stats'       => [
                'lines'               => $this->totalLines,
                'variables_used'      => $variablesList,
                'helpers_used'        => $helpersList,
                'estimated_render_ms' => $estimatedMs,
                'has_legacy_syntax'   => $hasLegacySyntax,
            ],
            'suggestions' => $this->suggestions,
        ];
    }

    /**
     * Cek apakah hasil validasi bisa dijadikan default template.
     *
     * Default hanya boleh jika TIDAK ADA ERROR dan TIDAK ADA WARNING.
     *
     * @param array<string, mixed> $validationResult Hasil dari method validate()
     */
    public function canBeDefault(array $validationResult): bool
    {
        $errors = $validationResult['errors'] ?? [];
        $warnings = $validationResult['warnings'] ?? [];
        return is_array($errors) && count($errors) === 0
            && is_array($warnings) && count($warnings) === 0;
    }

    /**
     * Buat string ringkasan singkat dari hasil validasi.
     *
     * Contoh output:
     *   "Valid. 0 error, 2 warning, 1 info. Tidak bisa jadi default karena ada warning."
     *   "Tidak valid. 3 error, 0 warning, 0 info. Tidak bisa jadi default karena ada error."
     *
     * @param array<string, mixed> $validationResult Hasil dari method validate()
     */
    public function summary(array $validationResult): string
    {
        $errorCount = is_countable($validationResult['errors'] ?? []) ? count($validationResult['errors']) : 0;
        $warnCount  = is_countable($validationResult['warnings'] ?? []) ? count($validationResult['warnings']) : 0;
        $infoCount  = is_countable($validationResult['info'] ?? []) ? count($validationResult['info']) : 0;

        $status = ($errorCount === 0) ? 'Valid' : 'Tidak valid';

        if ($errorCount > 0 && $warnCount > 0) {
            $reason = 'karena ada error dan warning';
        } elseif ($errorCount > 0) {
            $reason = 'karena ada error';
        } elseif ($warnCount > 0) {
            $reason = 'karena ada warning';
        } else {
            $reason = 'BISA';
        }

        $defaultPart = ($reason === 'BISA')
            ? 'Bisa dijadikan default template.'
            : sprintf('Tidak bisa jadi default %s.', $reason);

        return sprintf(
            '%s. %d error, %d warning, %d info. %s',
            $status,
            $errorCount,
            $warnCount,
            $infoCount,
            $defaultPart
        );
    }

    /** --- Internal: state management --- */

    private function resetState(): void
    {
        $this->errors = [];
        $this->warnings = [];
        $this->info = [];
        $this->suggestions = [];
        $this->variablesUsed = [];
        $this->helpersUsed = [];
        $this->lines = [];
        $this->totalLines = 0;
    }

    private function addError(string $code, string $message, ?int $line, string $context): void
    {
        $this->errors[] = [
            'severity' => 'error',
            'code'     => $code,
            'message'  => $message,
            'line'     => $line,
            'context'  => $context,
        ];
    }

    private function addWarning(string $code, string $message, ?int $line, string $context): void
    {
        $this->warnings[] = [
            'severity' => 'warning',
            'code'     => $code,
            'message'  => $message,
            'line'     => $line,
            'context'  => $context,
        ];
    }

    private function addInfo(string $code, string $message, ?int $line, string $context): void
    {
        $this->info[] = [
            'severity' => 'info',
            'code'     => $code,
            'message'  => $message,
            'line'     => $line,
            'context'  => $context,
        ];
    }

    private function addSuggestion(string $level, string $title, string $from, string $to): void
    {
        $this->suggestions[] = [
            'level' => $level,
            'title' => $title,
            'from'  => $from,
            'to'    => $to,
        ];
    }

    private function findLineByOffset(string $source, int $offset): ?int
    {
        if ($offset < 0) {
            return null;
        }
        $sub = substr($source, 0, $offset);
        return substr_count($sub, "\n") + 1;
    }

    private function getSnippet(string $source, int $offset, int $length = 60): string
    {
        $snippet = substr($source, max(0, $offset), $length);
        return trim($snippet);
    }

    /** --- B8: SIZE CONSTRAINTS --- */

    private function checkSizeConstraints(string $templateCode, string $cssCode, string $jsCode): void
    {
        $tplLen = strlen($templateCode);
        $cssLen = strlen($cssCode);
        $jsLen  = strlen($jsCode);
        $totalBytes = $tplLen + $cssLen + $jsLen;

        if ($tplLen > 500000) {
            $this->addError(
                'ERR_TEMPLATE_TOO_LARGE',
                sprintf('template_code terlalu besar (%d karakter) — maksimal 500.000.', $tplLen),
                null,
                sprintf('%d chars', $tplLen)
            );
        }
        if ($cssLen > 500000) {
            $this->addError(
                'ERR_CSS_TOO_LARGE',
                sprintf('css_code terlalu besar (%d karakter) — maksimal 500.000.', $cssLen),
                null,
                sprintf('%d chars', $cssLen)
            );
        }
        if ($totalBytes > 750 * 1024) {
            $this->addWarning(
                'WARN_TOTAL_SIZE_LARGE',
                sprintf(
                    'Total ukuran (template+css+js) %d KB melebihi 750 KB — dapat mempengaruhi performa render PDF.',
                    (int) ceil($totalBytes / 1024)
                ),
                null,
                sprintf('total ~%d KB', (int) ceil($totalBytes / 1024))
            );
        }
    }

    /** --- B1: BLOCK STRUCTURE --- */

    private function checkBlockStructure(string $source, bool $strictMode): void
    {
        // Strip HTML comments to avoid matching tags in comments (keep newlines for line counting)
        $source = preg_replace_callback('/<!--.*?-->/s', function ($m) {
            return preg_replace('/[^\r\n]/', ' ', $m[0]);
        }, $source);

        // Strip Smarty-style comments as well (keep newlines for line counting)
        $source = preg_replace_callback('/\{\*.*?\*\}/s', function ($m) {
            return preg_replace('/[^\r\n]/', ' ', $m[0]);
        }, $source);

        $ifOpen = 0;
        $ifClose = 0;
        $foreachOpen = 0;
        $foreachClose = 0;
        $literalOpen = 0;
        $literalClose = 0;
        $stripOpen = 0;
        $stripClose = 0;
        $maxDepth = 0;
        $currentDepth = 0;

        $ifStack = [];

        preg_match_all('/\{(\/?)(if|foreach|literal|strip|elseif|else)([\s\}])/', $source, $matches, PREG_OFFSET_CAPTURE);

        foreach ($matches[0] as $idx => $fullMatch) {
            $isClose    = ($matches[1][$idx][0] === '/');
            $tagName    = $matches[2][$idx][0];
            $offset     = (int) $fullMatch[1];
            $line       = $this->findLineByOffset($source, $offset);
            $snippet    = $this->getSnippet($source, $offset);

            if ($tagName === 'if' && !$isClose) {
                $ifOpen++;
                $currentDepth++;
                $maxDepth = max($maxDepth, $currentDepth);
                $ifStack[] = ['line' => $line, 'offset' => $offset];
            } elseif ($tagName === 'if' && $isClose) {
                $ifClose++;
                $currentDepth = max(0, $currentDepth - 1);
                if (count($ifStack) > 0) {
                    array_pop($ifStack);
                }
            } elseif ($tagName === 'elseif' || $tagName === 'else') {
                if (count($ifStack) === 0 && $strictMode) {
                    $this->addError(
                        'ERR_ELSE_WITHOUT_IF',
                        sprintf('{elseif/else} tanpa {if} yang sesuai.', $tagName),
                        $line,
                        $snippet
                    );
                } elseif (count($ifStack) === 0 && !$strictMode) {
                    $this->addWarning(
                        'WARN_ELSE_WITHOUT_IF',
                        sprintf('{elseif/else} tanpa {if} yang sesuai.', $tagName),
                        $line,
                        $snippet
                    );
                }
            } elseif ($tagName === 'foreach' && !$isClose) {
                $foreachOpen++;
                $currentDepth++;
                $maxDepth = max($maxDepth, $currentDepth);
            } elseif ($tagName === 'foreach' && $isClose) {
                $foreachClose++;
                $currentDepth = max(0, $currentDepth - 1);
            } elseif ($tagName === 'literal' && !$isClose) {
                $literalOpen++;
            } elseif ($tagName === 'literal' && $isClose) {
                $literalClose++;
            } elseif ($tagName === 'strip' && !$isClose) {
                $stripOpen++;
            } elseif ($tagName === 'strip' && $isClose) {
                $stripClose++;
            }
        }

        if ($ifOpen !== $ifClose) {
            $sample = '';
            if (count($ifStack) > 0) {
                $last = $ifStack[0];
                $sample = $this->getSnippet($source, (int) $last['offset']);
            }
            $severityFn = $strictMode ? 'addError' : 'addWarning';
            $codePrefix = $strictMode ? 'ERR' : 'WARN';
            $this->{$severityFn}(
                $codePrefix . '_UNMATCHED_IF',
                sprintf('Unmatched {if}: %d buka vs %d tutup.', $ifOpen, $ifClose),
                count($ifStack) > 0 ? $ifStack[0]['line'] : null,
                $sample ?: sprintf('if_open=%d if_close=%d', $ifOpen, $ifClose)
            );
        }

        if ($foreachOpen !== $foreachClose) {
            $severityFn = $strictMode ? 'addError' : 'addWarning';
            $codePrefix = $strictMode ? 'ERR' : 'WARN';
            $this->{$severityFn}(
                $codePrefix . '_UNMATCHED_FOREACH',
                sprintf('Unmatched {foreach}: %d buka vs %d tutup.', $foreachOpen, $foreachClose),
                null,
                sprintf('foreach_open=%d foreach_close=%d', $foreachOpen, $foreachClose)
            );
        }

        if ($literalOpen !== $literalClose) {
            $this->addError(
                'ERR_UNMATCHED_LITERAL',
                sprintf('Unmatched {literal}: %d buka vs %d tutup.', $literalOpen, $literalClose),
                null,
                sprintf('literal_open=%d literal_close=%d', $literalOpen, $literalClose)
            );
        }

        if ($stripOpen !== $stripClose) {
            $this->addError(
                'ERR_UNMATCHED_STRIP',
                sprintf('Unmatched {strip}: %d buka vs %d tutup.', $stripOpen, $stripClose),
                null,
                sprintf('strip_open=%d strip_close=%d', $stripOpen, $stripClose)
            );
        }

        if ($maxDepth > 10) {
            $this->addError(
                'ERR_NESTED_DEPTH_EXCEEDED',
                sprintf('Nested depth terlalu dalam (%d level) — maksimal 10.', $maxDepth),
                null,
                sprintf('max_nested_depth=%d', $maxDepth)
            );
        }
    }

    private function checkBracesBalance(string $source): void
    {
        $openBrace  = 0;
        $closeBrace = 0;
        $dblOpen = 0;
        $dblClose = 0;

        $tagPatterns = [
            '/\{(if|elseif|foreach|literal|strip|else)[\s\}]/',
            '/\{\/(if|foreach|literal|strip)\}/',
        ];

        $stripped = $source;
        foreach ($tagPatterns as $pat) {
            $stripped = preg_replace($pat, '', $stripped) ?? '';
        }

        preg_match_all('/\{\{/', $stripped, $mOpen);
        preg_match_all('/\}\}/', $stripped, $mClose);
        $dblOpen = count($mOpen[0] ?? []);
        $dblClose = count($mClose[0] ?? []);

        if ($dblOpen !== $dblClose) {
            $this->addError(
                'ERR_MALFORMED_BRACES_DOUBLE',
                sprintf('Kurung ganda tidak seimbang: %d kali {{ vs %d kali }}.', $dblOpen, $dblClose),
                null,
                sprintf('{{ x %d, }} x %d', $dblOpen, $dblClose)
            );
        }

        // Remove literal blocks to avoid CSS braces causing false positives
        $sourceWithoutLiteral = preg_replace('/\{literal\}.*?\{\/literal\}/is', '', $source) ?? $source;

        preg_match_all('/(?<!\{)\{(?![\{\/])[A-Za-z]/', $sourceWithoutLiteral, $singleOpen);
        $openCount = count($singleOpen[0] ?? []);
        preg_match_all('/(?<=[A-Za-z0-9_\s])(?<!\})\}(?!\})/', $sourceWithoutLiteral, $singleClose);
        $closeCount = count($singleClose[0] ?? []);

        if (abs($openCount - $closeCount) > 20) {
            $this->addError(
                'ERR_MALFORMED_BRACES_SINGLE',
                'Terdapat kemungkinan kurung tunggal "{" atau "}" yang tidak lengkap (malformed braces).',
                null,
                sprintf('open_suspected=%d, close_suspected=%d', $openCount, $closeCount)
            );
        }
    }

    /** --- B2: CONTROL FLOW --- */

    private function checkControlFlowValidity(string $source, bool $strictMode): void
    {
        preg_match_all('/\{foreach\s+([^}]+)\}/', $source, $matches, PREG_OFFSET_CAPTURE);
        foreach ($matches[1] as $idx => $body) {
            $offset = (int) $matches[0][$idx][1];
            $line = $this->findLineByOffset($source, $offset);
            $bodyStr = trim((string) $body[0]);

            if (!preg_match('/\$?[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*\s+as\s+/', $bodyStr)
                && !preg_match('/(from|in)\s+/', $bodyStr)
                && !preg_match('/item\s*=/', $bodyStr)) {
                $this->addWarning(
                    'WARN_FOREACH_MODIFIER_UNKNOWN',
                    sprintf('Modifier foreach tidak dikenali: "%s". Gunakan pola standar.', $bodyStr),
                    $line,
                    '{foreach ' . $bodyStr . '}'
                );
            }

            if (preg_match('/\b(item|key)\s*=\s*(\w+)/', $bodyStr, $m2)) {
                $modifier = $m2[1];
                $val = $m2[2];
                if (!preg_match('/^[a-z_][a-z0-9_]*$/i', $val)) {
                    $this->addWarning(
                        'WARN_FOREACH_ITEM_KEY_NAME',
                        sprintf('Nama %s foreach "%s" tidak sesuai pattern.', $modifier, $val),
                        $line,
                        '{foreach ' . $bodyStr . '}'
                    );
                }
            }
        }

        preg_match_all('/\{assign\s+var\s*=\s*["\']?([a-zA-Z_][a-zA-Z0-9_]*)["\']?\s+value\s*=\s*([^}]+)\}/', $source, $assignMatches, PREG_OFFSET_CAPTURE);
        foreach ($assignMatches[1] as $idx => $nameMatch) {
            $offset = (int) $assignMatches[0][$idx][1];
            $line = $this->findLineByOffset($source, $offset);
            $varName = (string) $nameMatch[0];

            $registryVars = array_keys(VariableRegistry::all());
            $flatNames = [];
            foreach ($registryVars as $dotPath) {
                $parts = explode('.', $dotPath);
                $flatNames[] = $parts[0];
                $flatNames[] = end($parts);
                $flatNames[] = str_replace('.', '_', $dotPath);
            }
            if (in_array($varName, $flatNames, true)) {
                $this->addWarning(
                    'WARN_ASSIGN_COLLIDES_WITH_REGISTRY',
                    sprintf('Nama variable assign "%s" berpotensi collision dengan registry variable.', $varName),
                    $line,
                    sprintf('{assign var=%s ...}', $varName)
                );
            }
        }
    }

    /** --- B3: VARIABLES --- */

    /**
     * @param array<string, mixed> $context
     */
    private function checkVariables(string $source, array $context): void
    {
        $assignedVars = [];
        preg_match_all('/\{assign\s+var\s*=\s*["\']?([a-zA-Z_][a-zA-Z0-9_]*)["\']?/', $source, $mAssign);
        foreach ($mAssign[1] as $v) {
            $assignedVars[(string) $v] = true;
            if (!preg_match('/^[a-z_][a-z0-9_]*$/', (string) $v)) {
                $line = null;
                if (preg_match('/\{assign\s+var\s*=\s*["\']?' . preg_quote((string) $v, '/') . '["\']?/', $source, $mm, PREG_OFFSET_CAPTURE)) {
                    $line = $this->findLineByOffset($source, (int) $mm[0][1]);
                }
                $this->addWarning(
                    'WARN_VARIABLE_PATTERN',
                    sprintf('Nama variable baru "%s" tidak sesuai pattern [a-z_][a-z0-9_]*.', (string) $v),
                    $line,
                    (string) $v
                );
            }
        }

        $foreachLoopVars = [];
        preg_match_all('/\{foreach\s+([^}]+)\}/', $source, $mForEach);
        foreach ($mForEach[1] as $body) {
            if (preg_match('/item\s*=\s*["\']?(\w+)/', (string) $body, $mm)) {
                $foreachLoopVars[$mm[1]] = true;
            }
            if (preg_match('/key\s*=\s*["\']?(\w+)/', (string) $body, $mm)) {
                $foreachLoopVars[$mm[1]] = true;
            }
            if (preg_match('/\$(\w+)\s+as\s+\$(\w+)(?:\s*=>\s*\$(\w+))?/', (string) $body, $mm2)) {
                if (isset($mm2[3])) {
                    $foreachLoopVars[$mm2[3]] = true;
                    $foreachLoopVars[$mm2[2]] = true;
                } else {
                    $foreachLoopVars[$mm2[2]] = true;
                }
            }
        }

        $contextKeys = [];
        array_walk_recursive($context, function ($_, $k) use (&$contextKeys): void {
            if (is_string($k)) {
                $contextKeys[] = $k;
            }
        });

        $varPattern = '/\{\{\s*([a-zA-Z_][a-zA-Z0-9_\.]*)(\s*\||\s*\()?/';
        preg_match_all($varPattern, $source, $varMatches, PREG_OFFSET_CAPTURE);

        $allRegistryPaths = array_keys(VariableRegistry::all());
        $allRegistryFirstParts = [];
        foreach ($allRegistryPaths as $p) {
            $allRegistryFirstParts[explode('.', $p)[0]] = true;
        }

        foreach ($varMatches[1] as $idx => $varMatch) {
            $rawName = trim((string) $varMatch[0]);
            $offset  = (int) $varMatches[0][$idx][1];
            $line    = $this->findLineByOffset($source, $offset);
            $snippet = $this->getSnippet($source, $offset);

            if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $rawName)
                && isset(self::ALLOWED_HELPERS[$rawName])) {
                continue;
            }

            $this->variablesUsed[$rawName] = true;

            $isKnown = false;
            if (VariableRegistry::exists($rawName)) {
                $isKnown = true;
            }
            $parts = explode('.', $rawName);
            if (count($parts) >= 2 && isset($allRegistryFirstParts[$parts[0]])) {
                $isKnown = true;
            }
            if (isset($assignedVars[$parts[0]])) {
                $isKnown = true;
            }
            if (isset($foreachLoopVars[$parts[0]])) {
                $isKnown = true;
            }
            if (in_array($parts[0], $contextKeys, true)) {
                $isKnown = true;
            }

            if (!$isKnown) {
                $suggestion = $this->findSimilarVariable($rawName, $allRegistryPaths);
                $msg = $suggestion !== null
                    ? sprintf('Variable "%s" tidak dikenali. Apakah maksud "%s"?', $rawName, $suggestion)
                    : sprintf('Variable "%s" tidak dikenali (bukan registry, bukan hasil assign, bukan foreach loop key/value).', $rawName);

                $this->addWarning('WARN_UNKNOWN_VARIABLE', $msg, $line, $snippet);

                if ($suggestion !== null) {
                    $this->addSuggestion('info', 'Perbaiki typo variable', $rawName, $suggestion);
                }
            }
        }
    }

    /**
     * @param array<int, string> $candidates
     */
    private function findSimilarVariable(string $needle, array $candidates): ?string
    {
        $best = null;
        $bestDist = 3;
        foreach ($candidates as $c) {
            $d = levenshtein(strtolower($needle), strtolower($c));
            if ($d <= 2 && $d < $bestDist) {
                $bestDist = $d;
                $best = $c;
            }
        }
        if ($best === null) {
            $last = basename(str_replace('.', '/', $needle));
            foreach ($candidates as $c) {
                $lastC = basename(str_replace('.', '/', $c));
                $d = levenshtein(strtolower($last), strtolower($lastC));
                if ($d <= 2 && $d < $bestDist) {
                    $bestDist = $d;
                    $best = $c;
                }
            }
        }
        return $best;
    }

    /** --- B4: FILTERS & HELPERS --- */

    private function checkFiltersAndHelpers(string $source, bool $strictMode): void
    {
        preg_match_all('/\|\s*([a-zA-Z_][a-zA-Z0-9_]*)\s*(?:\([^)]*\))?/', $source, $filterMatches, PREG_OFFSET_CAPTURE);
        foreach ($filterMatches[1] as $idx => $fName) {
            $name = strtolower(trim((string) $fName[0]));
            $offset = (int) $filterMatches[0][$idx][1];
            $line = $this->findLineByOffset($source, $offset);
            $snippet = $this->getSnippet($source, $offset);
            if (!in_array($name, self::ALLOWED_FILTERS, true)) {
                $severityFn = $strictMode ? 'addError' : 'addWarning';
                $codePrefix = $strictMode ? 'ERR' : 'WARN';
                $this->{$severityFn}(
                    $codePrefix . '_UNKNOWN_FILTER',
                    sprintf('Filter "%s" tidak dikenal (bukan whitelist: %s).', $name, implode(', ', self::ALLOWED_FILTERS)),
                    $line,
                    $snippet
                );
            }
        }

        preg_match_all('/\{\{\s*([a-zA-Z_][a-zA-Z0-9_]*)\s*\(([^}]*)\)\s*\}\}/', $source, $helperMatches, PREG_OFFSET_CAPTURE);
        foreach ($helperMatches[1] as $idx => $hName) {
            $name = strtolower(trim((string) $hName[0]));
            $offset = (int) $helperMatches[0][$idx][1];
            $line = $this->findLineByOffset($source, $offset);
            $snippet = $this->getSnippet($source, $offset);
            $rawArgs = (string) $helperMatches[2][$idx][0];

            $this->helpersUsed[$name] = true;

            if ($name === 'currency') {
                $this->addInfo(
                    'INFO_SUGGEST_CURRENCY_FILTER',
                    'Filter |currency tersedia tapi disarankan menggunakan helper format_currency() untuk konfigurasi otomatis.',
                    $line,
                    $snippet
                );
                $this->addSuggestion('info', 'Gunakan format_currency', '|currency', '{{format_currency(voucher.price)}}');
            }

            if (!isset(self::ALLOWED_HELPERS[$name])) {
                $severityFn = $strictMode ? 'addError' : 'addWarning';
                $codePrefix = $strictMode ? 'ERR' : 'WARN';
                $this->{$severityFn}(
                    $codePrefix . '_UNKNOWN_HELPER',
                    sprintf('Helper "%s" tidak dikenal (bukan whitelist: %s).', $name, implode(', ', array_keys(self::ALLOWED_HELPERS))),
                    $line,
                    $snippet
                );
                continue;
            }

            $argCount = $this->countHelperArgs($rawArgs);
            $limits = self::ALLOWED_HELPERS[$name];
            if ($argCount < $limits['min'] || $argCount > $limits['max']) {
                $this->addWarning(
                    'WARN_HELPER_PARAM_COUNT',
                    sprintf(
                        'Helper %s() butuh %d-%d parameter, diberikan %d.',
                        $name,
                        $limits['min'],
                        $limits['max'],
                        $argCount
                    ),
                    $line,
                    $snippet
                );
            }
        }
    }

    private function countHelperArgs(string $rawArgs): int
    {
        $rawArgs = trim($rawArgs);
        if ($rawArgs === '') {
            return 0;
        }
        $depth = 0;
        $inStr = null;
        $count = 1;
        $len = strlen($rawArgs);
        for ($i = 0; $i < $len; $i++) {
            $c = $rawArgs[$i];
            if ($inStr !== null) {
                if ($c === '\\' && $i + 1 < $len) {
                    $i++;
                    continue;
                }
                if ($c === $inStr) {
                    $inStr = null;
                }
                continue;
            }
            if ($c === '"' || $c === "'") {
                $inStr = $c;
                continue;
            }
            if ($c === '(' || $c === '[') {
                $depth++;
            } elseif ($c === ')' || $c === ']') {
                $depth--;
            } elseif ($c === ',' && $depth === 0) {
                $count++;
            }
        }
        return $count;
    }

    /** --- B5: ASSETS --- */

    private function checkAssets(string $source): void
    {
        $assetPatterns = [
            '/asset\s*\(\s*["\']([^"\']+)["\']/',
            '/<img[^>]+src\s*=\s*["\']([^"\']+)["\']/i',
            '/background(?:-image)?\s*:\s*url\s*\(\s*["\']?([^)"\']+)["\']?\s*\)/i',
        ];

        foreach ($assetPatterns as $pattern) {
            preg_match_all($pattern, $source, $matches, PREG_OFFSET_CAPTURE);
            foreach ($matches[1] as $idx => $m) {
                $path = trim((string) $m[0]);
                $offset = (int) $matches[0][$idx][1];
                $line = $this->findLineByOffset($source, $offset);
                $snippet = $this->getSnippet($source, $offset);

                if (preg_match('#^(https?:|data:)#i', $path)) {
                    $this->addWarning(
                        'WARN_EXTERNAL_ASSET',
                        sprintf('Asset "%s" pakai URL external/data URI. Akan di-render via external, pastikan tersedia saat cetak PDF.', $this->truncate($path, 50)),
                        $line,
                        $snippet
                    );
                    $this->addInfo(
                        'INFO_EXTERNAL_ASSET_NOTE',
                        'Asset eksternal membutuhkan koneksi internet saat render PDF.',
                        $line,
                        $this->truncate($path, 50)
                    );
                    continue;
                }

                $cleanPath = ltrim($path, '/');
                if ($cleanPath !== '' && !preg_match('~^(https?:|data:|#)~i', $cleanPath)) {
                    try {
                        $exists = Storage::disk('public')->exists($cleanPath);
                    } catch (\Throwable) {
                        $exists = false;
                    }
                    if (!$exists) {
                        $this->addWarning(
                            'WARN_ASSET_NOT_FOUND',
                            sprintf('Asset tidak ditemukan di storage public: "%s".', $this->truncate($cleanPath, 60)),
                            $line,
                            $snippet
                        );
                    }
                }
            }
        }
    }

    private function truncate(string $s, int $max): string
    {
        if (strlen($s) <= $max) {
            return $s;
        }
        return substr($s, 0, $max - 3) . '...';
    }

    /** --- B6: HTML & CETAK --- */

    private function checkHtmlAndPrint(string $source, bool $strictMode): void
    {
        $this->checkHtmlTags($source, $strictMode);
        $this->checkScriptAndIframes($source);
        $this->checkInlineStyles($source);
        $this->checkPrintMediaStyle($source);
    }

    private function checkHtmlTags(string $source, bool $strictMode): void
    {
        $tagRegex = '/<\s*(\/?)\s*([a-zA-Z][a-zA-Z0-9]*)([^>]*)>/';
        preg_match_all($tagRegex, $source, $matches, PREG_OFFSET_CAPTURE);

        $stack = [];

        foreach ($matches[0] as $idx => $full) {
            $isClosing = ($matches[1][$idx][0] === '/');
            $tagName = strtolower((string) $matches[2][$idx][0]);
            $attrs = (string) $matches[3][$idx][0];
            $offset = (int) $full[1];
            $line = $this->findLineByOffset($source, $offset);
            $snippet = $this->getSnippet($source, $offset);

            if (!in_array($tagName, self::CONTAINER_TAGS, true)
                && !in_array($tagName, self::SELF_CLOSING_TAGS, true)) {
                continue;
            }

            $isSelfClosing = in_array($tagName, self::SELF_CLOSING_TAGS, true)
                || preg_match('#\/\s*>$#', trim($attrs . $full[0])) !== false;

            if ($isSelfClosing && !$isClosing) {
                continue;
            }

            if (!$isClosing) {
                $stack[] = ['tag' => $tagName, 'line' => $line, 'offset' => $offset, 'snippet' => $snippet];
            } else {
                $found = false;
                for ($j = count($stack) - 1; $j >= 0; $j--) {
                    if ($stack[$j]['tag'] === $tagName) {
                        array_splice($stack, $j, 1);
                        $found = true;
                        break;
                    }
                }
                if (!$found && in_array($tagName, self::CONTAINER_TAGS, true)) {
                    $severityFn = $strictMode ? 'addWarning' : 'addWarning';
                    $this->{$severityFn}(
                        'WARN_CLOSED_TAG_WITHOUT_OPEN',
                        sprintf('Tag </%s> ditutup tanpa pernah dibuka.', $tagName),
                        $line,
                        $snippet
                    );
                }
            }
        }

        foreach ($stack as $unclosed) {
            if (!in_array($unclosed['tag'], self::CONTAINER_TAGS, true)) {
                continue;
            }
            $severityFn = $strictMode ? 'addWarning' : 'addWarning';
            $this->{$severityFn}(
                'WARN_UNCLOSED_HTML_TAG',
                sprintf('Tag <%s> dibuka di baris %s tapi tidak pernah ditutup.', $unclosed['tag'], $unclosed['line'] ?? '?'),
                $unclosed['line'],
                $unclosed['snippet']
            );
        }
    }

    private function checkScriptAndIframes(string $source): void
    {
        preg_match_all('/<script\b[^>]*src\s*=\s*["\']([^"\']+)["\']/i', $source, $scriptMatches, PREG_OFFSET_CAPTURE);
        foreach ($scriptMatches[1] as $idx => $src) {
            $srcStr = trim((string) $src[0]);
            $offset = (int) $scriptMatches[0][$idx][1];
            $line = $this->findLineByOffset($source, $offset);
            $snippet = $this->getSnippet($source, $offset);

            if (preg_match('#^https?://#i', $srcStr)) {
                $this->addError(
                    'ERR_EXTERNAL_SCRIPT',
                    sprintf('<script src="%s"> tidak diizinkan — hanya inline <script> yang didukung.', $this->truncate($srcStr, 60)),
                    $line,
                    $snippet
                );
                $this->addSuggestion('error', 'Hapus script external', '<script src=..>', 'inline <script> tanpa src');
            }
        }

        $dangerousTags = ['iframe', 'frame', 'object', 'embed'];
        foreach ($dangerousTags as $dTag) {
            preg_match_all('/<' . preg_quote($dTag, '/') . '\b[^>]*src\s*=\s*["\']([^"\']+)["\']/i', $source, $tagMatches, PREG_OFFSET_CAPTURE);
            foreach ($tagMatches[1] as $idx => $src) {
                $srcStr = trim((string) $src[0]);
                $offset = (int) $tagMatches[0][$idx][1];
                $line = $this->findLineByOffset($source, $offset);
                $snippet = $this->getSnippet($source, $offset);

                $isAllowed = (stripos($srcStr, 'data:image') === 0);
                if (!$isAllowed && preg_match('#^https?://#i', $srcStr)) {
                    $this->addError(
                        'ERR_EXTERNAL_EMBED',
                        sprintf('<%s src="%s"> tidak diizinkan — hanya data:image atau srcdoc yang diijinkan.', $dTag, $this->truncate($srcStr, 50)),
                        $line,
                        $snippet
                    );
                }
            }
        }

        preg_match_all('/<iframe\b[^>]*srcdoc\s*=\s*["\']([^"\']*)["\']/i', $source, $srcDocMatches, PREG_OFFSET_CAPTURE);
        foreach ($srcDocMatches[1] as $idx => $_) {
            $offset = (int) $srcDocMatches[0][$idx][1];
            $line = $this->findLineByOffset($source, $offset);
            $snippet = $this->getSnippet($source, $offset);
            $this->addInfo(
                'INFO_IFRAME_SRCDOC',
                '<iframe srcdoc="..."> diijinkan tapi dapat menyebabkan issue pada PDF — gunakan dengan hati-hati.',
                $line,
                $snippet
            );
        }
    }

    private function checkInlineStyles(string $source): void
    {
        preg_match_all('/style\s*=\s*["\']([^"\']+)["\']/i', $source, $styleMatches, PREG_OFFSET_CAPTURE);
        foreach ($styleMatches[1] as $idx => $styleBody) {
            $styleStr = strtolower((string) $styleBody[0]);
            $offset = (int) $styleMatches[0][$idx][1];
            $line = $this->findLineByOffset($source, $offset);
            $snippet = $this->getSnippet($source, $offset);

            if (str_contains($styleStr, 'position:fixed') || preg_match('/position\s*:\s*fixed/', $styleStr)) {
                $this->addWarning(
                    'WARN_STYLE_POSITION_FIXED',
                    'Inline style dengan position:fixed dapat menyebabkan layout print rusak (elemen melayang di semua halaman).',
                    $line,
                    $snippet
                );
            }
            if (preg_match('/overflow\s*:\s*hidden/', $styleStr)) {
                $this->addWarning(
                    'WARN_STYLE_OVERFLOW_HIDDEN',
                    'Inline style overflow:hidden berpotensi memotong konten saat cetak PDF.',
                    $line,
                    $snippet
                );
            }
            if (preg_match('/margin\s*:/', $styleStr) && preg_match('/<body\b/i', $snippet)) {
                $this->addWarning(
                    'WARN_BODY_INLINE_MARGIN',
                    'Inline margin pada <body> dapat merusak layout halaman PDF — gunakan @page CSS.',
                    $line,
                    $snippet
                );
            }
        }

        preg_match_all('/body\s*\{[^}]*margin\s*:/i', $source, $bodyMarginMatches, PREG_OFFSET_CAPTURE);
        foreach ($bodyMarginMatches[0] as $idx => $bodyMatch) {
            $offset = (int) $bodyMatch[1];
            $line = $this->findLineByOffset($source, $offset);
            $snippet = $this->getSnippet($source, $offset);
            $this->addWarning(
                'WARN_BODY_MARGIN_CSS',
                'CSS body { margin: ... } dapat merusak layout PDF — gunakan @page untuk margin halaman.',
                $line,
                $snippet
            );
        }
    }

    private function checkPrintMediaStyle(string $source): void
    {
        $hasPrintMedia = preg_match('/@media\s+print\b/i', $source) === 1
            || preg_match('/@page\b/i', $source) === 1;

        if (!$hasPrintMedia) {
            $this->addInfo(
                'INFO_RECOMMEND_PRINT_STYLE',
                'Template tidak memiliki @media print atau @page — disarankan tambahkan agar layout cetak lebih konsisten.',
                null,
                'Tambahkan: <style>@page { margin: 5mm; }</style>'
            );
            $this->addSuggestion(
                'info',
                'Tambahkan @media print style',
                '(tidak ada print style)',
                '<style>@page { margin: 5mm; size: auto; }</style>'
            );
        }
    }

    /** --- Helper for stats --- */

    private function countForeachLoops(string $source): int
    {
        preg_match_all('/\{foreach\b/', $source, $m);
        return count($m[0] ?? []);
    }
}

