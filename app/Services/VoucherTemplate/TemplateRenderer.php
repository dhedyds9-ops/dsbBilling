<?php

declare(strict_types=1);

namespace App\Services\VoucherTemplate;

use Closure;
use InvalidArgumentException;
use RuntimeException;

/**
 * Sandboxed Voucher Template Renderer — Custom Recursive-Descend Parser.
 *
 * Mendukung:
 *   - Variable interpolation: {{voucher.code}}, {$vs.code}, {$vs['code']}
 *   - Filters: |currency, |number_format, |date('d/m/Y'), |upper, |lower,
 *              |escape, |raw, |trim, |nl2br
 *   - Control structures: {if}, {elseif}, {else}, {/if},
 *                         {foreach $arr as $item}, {foreach $arr as $k=>$v},
 *                         {assign var=X value=Y}, {include file="X"},
 *                         {literal}...{/literal}, {strip}...{/strip}
 *   - Condition operators: == eq, != ne, ===, !==, < lt, > gt, <= le, >= ge,
 *                          && and, || or, ! not, in, empty()
 *   - Whitelisted helper functions: qr(), barcode(), asset(),
 *                                   format_currency(), format_number(),
 *                                   date_format()
 *   - XSS protection via default htmlspecialchars (kecuali |raw)
 *   - Expression evaluator SENDIRI — TANPA eval()
 *
 * SAMPLE TESTS (PHPUnit-compatible expected output — jalan manual di tinker):
 *
 * Sample 1 (Simple interpolation + filter):
 *   $r = new TemplateRenderer();
 *   $res = $r->render(
 *       "Kode: {{voucher.code}}, Harga: {{package.price|currency}}",
 *       ['voucher'=>['code'=>'DEMO01'],'package'=>['price'=>5000],
 *        'currency'=>['symbol'=>'Rp','thousand_separator'=>'.',
 *                     'decimal_separator'=>',','decimals'=>0]]
 *   );
 *   // Expected $res['html'] = 'Kode: DEMO01, Harga: Rp5.000'
 *
 * Sample 2 (Conditional):
 *   $r = new TemplateRenderer();
 *   $tpl = "{if \$voucher.price == 5000}Murah{elseif \$voucher.price == 25000}Sedang{else}Mahal{/if}";
 *   $res1 = $r->render($tpl, ['voucher'=>['price'=>5000]]);   // 'Murah'
 *   $res2 = $r->render($tpl, ['voucher'=>['price'=>25000]]);  // 'Sedang'
 *   $res3 = $r->render($tpl, ['voucher'=>['price'=>50000]]);  // 'Mahal'
 *
 * Sample 3 (Foreach batch):
 *   $r = new TemplateRenderer();
 *   $tpl = "{foreach \$vouchers as \$v}#{\$v@iteration}: {\$v.code} @ {\$v.price}\n{/foreach}";
 *   $ctx = ['vouchers'=>[
 *       ['code'=>'A','price'=>5000],
 *       ['code'=>'B','price'=>25000],
 *       ['code'=>'C','price'=>10000],
 *   ]];
 *   // Expected output:
 *   // "#1: A @ 5000\n#2: B @ 25000\n#3: C @ 10000\n"
 */
class TemplateRenderer
{
    public const TOKEN_LITERAL = 'LITERAL';
    public const TOKEN_BRACE_OPEN = 'BRACE_OPEN';
    public const TOKEN_BRACE_CLOSE = 'BRACE_CLOSE';
    public const TOKEN_CURLY_OPEN = 'CURLY_OPEN';
    public const TOKEN_CURLY_CLOSE = 'CURLY_CLOSE';
    public const TOKEN_EOF = 'EOF';

    private const WHITELIST_HELPERS = [
        'qr', 'barcode', 'asset', 'format_currency', 'format_number', 'date_format',
    ];

    private const ALLOWED_FILTERS = [
        'currency', 'number_format', 'date', 'upper', 'lower',
        'escape', 'raw', 'trim', 'nl2br',
    ];

    /** @var array<string,string> warnings selama render */
    private array $warnings = [];

    /** @var array<string,string> errors FATAL selama render */
    private array $errors = [];

    /** @var array<string> dot-path variable yang benar-benar dipakai */
    private array $usedVariables = [];

    /** @var Closure(string $text, int $size=150): string */
    private Closure $qrHelper;

    /** @var Closure(string $text, int $w=300, int $h=60): string */
    private Closure $barcodeHelper;

    /** @var Closure(string $path): string */
    private Closure $assetHelper;

    /** @var Closure(mixed $val, ?array $currency=null): string */
    private Closure $formatCurrencyHelper;

    /** @var Closure(mixed $val, int $decimals=0, ?array $currency=null): string */
    private Closure $formatNumberHelper;

    /** @var Closure(mixed $val, string $format='Y-m-d H:i:s'): string */
    private Closure $dateFormatHelper;

    /** @var bool apakah {literal} block aktif? */
    private bool $literalMode = false;

    public function __construct()
    {
        $this->initHelpers();
    }

    private function initHelpers(): void
    {
        $this->qrHelper = function (string $text, int $size = 150): string {
            static $svc = null;
            if ($svc === null) {
                $svc = class_exists(QRCodeService::class) ? new QRCodeService() : null;
            }
            if ($svc instanceof QRCodeService) {
                return $svc->generateDataUri($text, $size);
            }
            $safe = htmlspecialchars(substr($text, 0, 32), ENT_QUOTES, 'UTF-8');
            return "data:image/svg+xml;base64," . base64_encode(
                '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '"><rect width="100%" height="100%" fill="#fff"/><text x="50%" y="50%" font-size="10">QR:' . $safe . '</text></svg>'
            );
        };

        $this->barcodeHelper = function (string $text, int $width = 300, int $height = 60): string {
            static $svc = null;
            if ($svc === null) {
                $svc = class_exists(BarcodeService::class) ? new BarcodeService() : null;
            }
            if ($svc instanceof BarcodeService) {
                return $svc->generateCode128DataUri($text, $width, $height);
            }
            $safe = htmlspecialchars(substr($text, 0, 24), ENT_QUOTES, 'UTF-8');
            return "data:image/svg+xml;base64," . base64_encode(
                '<svg xmlns="http://www.w3.org/2000/svg" width="' . $width . '" height="' . $height . '"><rect width="100%" height="100%" fill="#fff"/><text x="50%" y="50%" font-size="10">BC:' . $safe . '</text></svg>'
            );
        };

        $this->assetHelper = function (string $path): string {
            $path = ltrim($path, '/');
            return '/storage/' . $path;
        };

        $this->formatCurrencyHelper = function (mixed $val, ?array $currency = null): string {
            $amount = is_numeric($val) ? (float) $val : 0.0;
            $sym = $currency['symbol'] ?? 'Rp';
            $ts = $currency['thousand_separator'] ?? '.';
            $ds = $currency['decimal_separator'] ?? ',';
            $dec = (int) ($currency['decimals'] ?? 0);
            return $sym . number_format($amount, $dec, $ds, $ts);
        };

        $this->formatNumberHelper = function (mixed $val, int $decimals = 0, ?array $currency = null): string {
            $amount = is_numeric($val) ? (float) $val : 0.0;
            $ts = $currency['thousand_separator'] ?? '.';
            $ds = $currency['decimal_separator'] ?? ',';
            return number_format($amount, $decimals, $ds, $ts);
        };

        $this->dateFormatHelper = function (mixed $val, string $format = 'Y-m-d H:i:s'): string {
            if ($val === null || $val === '') {
                return '';
            }
            if ($val instanceof \DateTimeInterface) {
                return $val->format($format);
            }
            $ts = is_numeric($val) ? (int) $val : strtotime((string) $val);
            if ($ts === false || $ts <= 0) {
                return (string) $val;
            }
            return date($format, $ts);
        };
    }

    /**
     * Render satu template voucher dengan context.
     *
     * @param string               $templateCode Kode template (HTML + syntax template)
     * @param array<string, mixed> $context      Nested array data (voucher, package, dll)
     * @param array<string, mixed> $settings     Pengaturan (paper_size, grid_preset, margins dll)
     * @param string|null          $cssCode      Custom CSS tambahan
     * @param string|null          $jsCode       Custom JS tambahan
     *
     * @return array{
     *     html: string,
     *     full_html: string,
     *     warnings: array<int, string>,
     *     errors: array<int, string>,
     *     used_variables: array<int, string>,
     * }
     *
     * @throws InvalidArgumentException
     */
    public function render(
        string $templateCode,
        array $context,
        array $settings = [],
        ?string $cssCode = null,
        ?string $jsCode = null,
    ): array {
        $this->resetState();

        $mergedCss = $this->injectPresetCss($settings, (string) $cssCode);
        $tokens = $this->tokenize($templateCode);
        $ast = $this->parseTokens($tokens);

        if ($this->hasFatalErrors()) {
            return $this->buildResult('', $mergedCss, (string) $jsCode, $settings);
        }

        $html = $this->renderAst($ast, $context);
        return $this->buildResult($html, $mergedCss, (string) $jsCode, $settings);
    }

    /**
     * Render batch voucher dalam satu halaman.
     *
     * $batchContext berisi `vouchers` (list of context per voucher) ditambah
     * variabel global (package, company, currency, dll).
     *
     * @param string               $templateCode Kode template per kartu
     * @param array<string, mixed> $batchContext Context batch ['vouchers'=>[...], ...globals]
     * @param array<string, mixed> $settings     Pengaturan (paper_size, grid_preset diperlukan)
     * @param string|null          $css          Custom CSS
     * @param string|null          $js           Custom JS
     *
     * @return array{
     *     html: string,
     *     full_html: string,
     *     warnings: array<int, string>,
     *     errors: array<int, string>,
     *     used_variables: array<int, string>,
     * }
     *
     * @throws InvalidArgumentException
     */
    public function renderBatch(
        string $templateCode,
        array $batchContext,
        array $settings = [],
        ?string $css = null,
        ?string $js = null,
    ): array {
        $this->resetState();

        $vouchers = $batchContext['vouchers'] ?? [];
        if (!is_array($vouchers)) {
            $vouchers = [];
        }
        $globals = $batchContext;
        unset($globals['vouchers']);

        $mergedCss = $this->injectPresetCss($settings, (string) $css);
        $tokens = $this->tokenize($templateCode);
        $ast = $this->parseTokens($tokens);

        if ($this->hasFatalErrors()) {
            return $this->buildResult('', $mergedCss, (string) $js, $settings);
        }

        $cards = [];
        foreach ($vouchers as $idx => $perVoucherCtx) {
            if (!is_array($perVoucherCtx)) {
                continue;
            }
            $ctx = array_replace_recursive($globals, $perVoucherCtx);
            $cardInner = $this->renderAst($ast, $ctx);
            $cards[] = '<div class="voucher-card"><div class="voucher-card-inner">' . $cardInner . '</div></div>';
        }

        $gridHtml = '<div class="voucher-grid">' . implode('', $cards) . '</div>';
        return $this->buildResult($gridHtml, $mergedCss, (string) $js, $settings);
    }

    /**
     * Mengambil daftar warning dari render terakhir.
     *
     * @return array<int, string>
     */
    public function getWarnings(): array
    {
        return $this->warnings;
    }

    /**
     * Mengambil daftar error FATAL dari render terakhir.
     *
     * @return array<int, string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /* ──────────────────────────────────────────────────────────────
     *  INTERNAL: State management
     * ────────────────────────────────────────────────────────────── */

    private function resetState(): void
    {
        $this->warnings = [];
        $this->errors = [];
        $this->usedVariables = [];
        $this->literalMode = false;
    }

    private function hasFatalErrors(): bool
    {
        return count($this->errors) > 0;
    }

    private function addWarning(string $msg): void
    {
        $this->warnings[] = $msg;
    }

    private function addError(string $msg): void
    {
        $this->errors[] = $msg;
    }

    private function recordUsedVariable(string $dotPath): void
    {
        if (!in_array($dotPath, $this->usedVariables, true)) {
            $this->usedVariables[] = $dotPath;
        }
    }

    /* ──────────────────────────────────────────────────────────────
     *  INTERNAL: CSS preset injection
     * ────────────────────────────────────────────────────────────── */

    /**
     * @param array<string, mixed> $settings
     */
    private function injectPresetCss(array $settings, string $userCss): string
    {
        $paper = (string) ($settings['paper_size'] ?? '');
        $grid = isset($settings['grid_preset']) && $settings['grid_preset'] !== ''
            ? (string) $settings['grid_preset']
            : null;
        $margins = [];
        foreach (['page_top', 'page_right', 'page_bottom', 'page_left',
                     'card_top', 'card_right', 'card_bottom', 'card_left'] as $mk) {
            if (isset($settings[$mk]) && is_numeric($settings[$mk])) {
                $margins[$mk] = (int) $settings[$mk];
            }
        }

        $presetCss = '';
        if ($paper !== '' && class_exists(PrintLayoutPresets::class)) {
            try {
                $presetCss = PrintLayoutPresets::getCssForPreset($paper, $grid, $margins);
            } catch (InvalidArgumentException $e) {
                $this->addWarning('CSS preset gagal dimuat: ' . $e->getMessage());
            }
        }

        return trim($presetCss . "\n" . $userCss);
    }

    /* ──────────────────────────────────────────────────────────────
     *  INTERNAL: Build output HTML (full document + sandbox iframe style)
     * ────────────────────────────────────────────────────────────── */

    /**
     * @param array<string, mixed> $settings
     *
     * @return array{
     *     html: string,
     *     full_html: string,
     *     warnings: array<int, string>,
     *     errors: array<int, string>,
     *     used_variables: array<int, string>,
     * }
     */
    private function buildResult(string $bodyHtml, string $css, string $js, array $settings): array
    {
        $fullHtml = $this->renderFullHtml($bodyHtml, $css, $js);
        $used = $this->usedVariables;
        sort($used);

        return [
            'html' => $bodyHtml,
            'full_html' => $fullHtml,
            'css' => $css,
            'js' => $js,
            'warnings' => $this->warnings,
            'errors' => $this->errors,
            'used_variables' => $used,
        ];
    }

    /**
     * Membangun full HTML DOCTYPE lengkap dengan inline style/script,
     * siap dirender di dalam iframe (sandbox — tanpa akses parent window).
     */
    private function renderFullHtml(string $body, string $css, string $js): string
    {
        $safeCss = $css !== '' ? ("<style>\n" . $css . "\n</style>") : '';
        $safeJs = $js !== '' ? ("<script>\n//<![CDATA[\n" . $js . "\n//]]>\n</script>") : '';

        return '<!DOCTYPE html>' . "\n"
            . '<html lang="id">' . "\n"
            . '<head>' . "\n"
            . '<meta charset="UTF-8"/>' . "\n"
            . '<meta name="viewport" content="width=device-width, initial-scale=1.0"/>' . "\n"
            . '<title>Voucher Preview</title>' . "\n"
            . $safeCss
            . '</head>' . "\n"
            . '<body>' . "\n"
            . $body . "\n"
            . $safeJs
            . '</body>' . "\n"
            . '</html>';
    }

    /* ──────────────────────────────────────────────────────────────
     *  STEP 1: TOKENIZER
     *  Memecah string template menjadi token:
     *    - LITERAL (text mentah di luar tag)
     *    - {{ ... }}   (braces interpolation)
     *    - { ... }     (curly control structures — perhatikan escape)
     * ────────────────────────────────────────────────────────────── */

    /**
     * @return array<int, array{type:string, value:string, line:int}>
     */
    private function tokenize(string $template): array
    {
        $tokens = [];
        $line = 1;
        $length = strlen($template);
        $i = 0;
        $buffer = '';

        while ($i < $length) {
            if ($this->literalMode) {
                $close = strpos($template, '{/literal}', $i);
                if ($close === false) {
                    $buffer .= substr($template, $i);
                    $line += substr_count(substr($template, $i), "\n");
                    $i = $length;
                } else {
                    $buffer .= substr($template, $i, $close - $i);
                    $line += substr_count(substr($template, $i, $close - $i), "\n");
                    $i = $close + strlen('{/literal}');
                    $this->literalMode = false;
                }
                continue;
            }

            $ch = $template[$i];
            $next = $i + 1 < $length ? $template[$i + 1] : '';

            if ($ch === '{' && $next === '{') {
                if ($buffer !== '') {
                    $tokens[] = [self::TOKEN_LITERAL, $buffer, $line];
                    $line += substr_count($buffer, "\n");
                    $buffer = '';
                }
                $end = strpos($template, '}}', $i + 2);
                if ($end === false) {
                    $this->addError(sprintf('Unclosed {{ ... }} di sekitar baris %d', $line));
                    return $tokens;
                }
                $inner = substr($template, $i + 2, $end - $i - 2);
                $tokens[] = [self::TOKEN_BRACE_OPEN, trim($inner), $line];
                $line += substr_count($inner, "\n");
                $i = $end + 2;
                continue;
            }

            if ($ch === '{') {
                if ($this->startsWith($template, '{literal}', $i)) {
                    if ($buffer !== '') {
                        $tokens[] = [self::TOKEN_LITERAL, $buffer, $line];
                        $line += substr_count($buffer, "\n");
                        $buffer = '';
                    }
                    $this->literalMode = true;
                    $i += strlen('{literal}');
                    continue;
                }

                if ($buffer !== '') {
                    $tokens[] = [self::TOKEN_LITERAL, $buffer, $line];
                    $line += substr_count($buffer, "\n");
                    $buffer = '';
                }

                $closeBrace = $this->findMatchingBrace($template, $i);
                if ($closeBrace === false) {
                    $this->addError(sprintf('Unclosed { ... } di sekitar baris %d', $line));
                    return $tokens;
                }
                $inner = substr($template, $i + 1, $closeBrace - $i - 1);
                $tokens[] = [self::TOKEN_CURLY_OPEN, trim($inner), $line];
                $line += substr_count($inner, "\n");
                $i = $closeBrace + 1;
                continue;
            }

            $buffer .= $ch;
            if ($ch === "\n") {
                $line++;
            }
            $i++;
        }

        if ($buffer !== '') {
            $tokens[] = [self::TOKEN_LITERAL, $buffer, $line];
        }

        $tokens[] = [self::TOKEN_EOF, '', $line];
        return $tokens;
    }

    private function startsWith(string $haystack, string $needle, int $offset): bool
    {
        return substr($haystack, $offset, strlen($needle)) === $needle;
    }

    /**
     * Mencari } yang cocok, mengabaikan yang ada di dalam string kutip.
     *
     * @return int|false
     */
    private function findMatchingBrace(string $template, int $openPos): int|false
    {
        $len = strlen($template);
        $i = $openPos + 1;
        $inStr = null;
        $escaped = false;

        while ($i < $len) {
            $c = $template[$i];
            if ($escaped) {
                $escaped = false;
                $i++;
                continue;
            }
            if ($c === '\\') {
                $escaped = true;
                $i++;
                continue;
            }
            if ($inStr !== null) {
                if ($c === $inStr) {
                    $inStr = null;
                }
                $i++;
                continue;
            }
            if ($c === '"' || $c === "'") {
                $inStr = $c;
                $i++;
                continue;
            }
            if ($c === '}') {
                return $i;
            }
            $i++;
        }
        return false;
    }

    /* ──────────────────────────────────────────────────────────────
     *  STEP 2: PARSER — recursive-descend membangun AST sederhana
     *  AST node types:
     *    ['literal', value]
     *    ['interpolate_braces', expression]   // {{ expr }}
     *    ['if', [cond, thenAst], [elseif...], elseAst]
     *    ['foreach', arrayExpr, itemVar, keyVar|null, bodyAst]
     *    ['assign', varName, valueExpr]
     *    ['include', fileExpr]
     *    ['strip', bodyAst]
     *    ['block', children]
     * ────────────────────────────────────────────────────────────── */

    /**
     * @param array<int, array{type:string, value:string, line:int}> $tokens
     * @return array<mixed> AST node (block root)
     */
    private function parseTokens(array $tokens): array
    {
        $ast = $this->parseBlock($tokens, 0, count($tokens), $ifStack, $foreachStack);
        foreach ($ifStack ?? [] as $_unclosedLine) {
            $this->addError(sprintf('Unclosed {if} block (dibuka baris %d)', $_unclosedLine));
        }
        foreach ($foreachStack ?? [] as $_unclosedLine) {
            $this->addError(sprintf('Unclosed {foreach} block (dibuka baris %d)', $_unclosedLine));
        }
        return $ast;
    }

    /**
     * @param array<int, array{type:string, value:string, line:int}> $tokens
     * @param int $startIdx
     * @param int $endIdx exclusive
     * @param array<int,int>|null $ifStack   (out) tracking {if} belum ditutup
     * @param array<int,int>|null $foreachStack (out) tracking {foreach} belum ditutup
     * @return array<mixed>
     */
    private function parseBlock(
        array $tokens,
        int $startIdx,
        int $endIdx,
        ?array &$ifStack = [],
        ?array &$foreachStack = [],
    ): array {
        if ($ifStack === null) {
            $ifStack = [];
        }
        if ($foreachStack === null) {
            $foreachStack = [];
        }

        $children = [];
        $i = $startIdx;

        while ($i < $endIdx) {
            [$type, $value, $line] = $tokens[$i];

            if ($type === self::TOKEN_EOF) {
                break;
            }

            if ($type === self::TOKEN_LITERAL) {
                $children[] = ['literal', $value];
                $i++;
                continue;
            }

            if ($type === self::TOKEN_BRACE_OPEN) {
                $children[] = ['interpolate_braces', $value, $line];
                $i++;
                continue;
            }

            if ($type === self::TOKEN_CURLY_OPEN) {
                $parsed = $this->parseCurlyTag($value, $line, $tokens, $i, $endIdx, $ifStack, $foreachStack);
                if ($parsed !== null) {
                    $children[] = $parsed;
                }
                $i++;
                continue;
            }

            $i++;
        }

        return ['block', $children];
    }

    /**
     * Parse tag { ... } curly. Untuk control structure yang membutuhkan
     * body (if, foreach, strip), akan consume token sampai closing tag.
     *
     * @param array<int, array{type:string, value:string, line:int}> $tokens
     * @param array<int,int> &$ifStack
     * @param array<int,int> &$foreachStack
     * @return array<mixed>|null
     */
    private function parseCurlyTag(
        string $tagContent,
        int $line,
        array $tokens,
        int &$i,
        int $endIdx,
        array &$ifStack,
        array &$foreachStack,
    ): ?array {
        $content = trim($tagContent);
        if ($content === '') {
            return null;
        }

        if ($content === '/literal') {
            $this->literalMode = false;
            return null;
        }
        if ($content === 'literal') {
            $this->addWarning(sprintf('{literal} tag seharusnya di-tokenize (baris %d)', $line));
            return null;
        }

        if ($this->startsWith($content, 'strip', 0) && trim($content) === 'strip') {
            return $this->parseUntilTag($tokens, $i, $endIdx, '/strip', 'strip', $bodyEndIdx, $innerAst, $foreachStack)
                ? ['strip', $innerAst]
                : null;
        }

        if (preg_match('#^if\s+(.+)$#s', $content, $m)) {
            $condExpr = trim($m[1]);
            $ifStack[] = $line;
            return $this->parseIfChain($condExpr, $tokens, $i, $endIdx, $ifStack, $foreachStack);
        }

        if ($content === '/if') {
            if (count($ifStack) > 0) {
                array_pop($ifStack);
            } else {
                $this->addError(sprintf('Tidak ada {if} yang terbuka untuk {/if} di baris %d', $line));
            }
            return ['__CLOSE_IF__'];
        }

        if (preg_match('#^elseif\s+(.+)$#s', $content, $m)) {
            return ['__ELSEIF__', trim($m[1]), $line];
        }

        if ($content === 'else') {
            return ['__ELSE__', $line];
        }

        if (preg_match('#^foreach\s+(.+)$#s', $content, $m)) {
            $foreachDef = trim($m[1]);
            $parsed = $this->parseForeachDefinition($foreachDef);
            if ($parsed === null) {
                $this->addError(sprintf('Syntax {foreach} tidak valid di baris %d: %s', $line, $foreachDef));
                return null;
            }
            [$arrayExpr, $itemVar, $keyVar] = $parsed;
            $foreachStack[] = $line;
            $bodyAst = $this->parseForeachBody($tokens, $i, $endIdx, $foreachStack);
            return ['foreach', $arrayExpr, $itemVar, $keyVar, $bodyAst, $line];
        }

        if ($content === '/foreach') {
            if (count($foreachStack) > 0) {
                array_pop($foreachStack);
            } else {
                $this->addError(sprintf('Tidak ada {foreach} yang terbuka untuk {/foreach} di baris %d', $line));
            }
            return ['__CLOSE_FOREACH__'];
        }

        if (preg_match('#^assign\s+(.+)$#s', $content, $m)) {
            return $this->parseAssignTag(trim($m[1]), $line);
        }

        if (preg_match('#^include\s+(.+)$#s', $content, $m)) {
            return $this->parseIncludeTag(trim($m[1]), $line);
        }

        return ['interpolate_curly', $content, $line];
    }

    /**
     * Parse body {foreach ...} ... {/foreach} (atau sampai EOF).
     *
     * @param array<int, array{type:string, value:string, line:int}> $tokens
     * @param array<int,int> &$foreachStack
     * @return array<mixed> body AST block
     */
    private function parseForeachBody(array $tokens, int &$i, int $endIdx, array &$foreachStack): array
    {
        $depth = 1;
        $startIdx = $i + 1;
        $bodyEnd = $endIdx;
        $j = $startIdx;
        while ($j < $endIdx) {
            [$t, $v] = $tokens[$j];
            if ($t === self::TOKEN_CURLY_OPEN) {
                $vTrim = trim($v);
                if (preg_match('#^foreach\s+#', $vTrim)) {
                    $depth++;
                } elseif ($vTrim === '/foreach') {
                    $depth--;
                    if ($depth === 0) {
                        $bodyEnd = $j;
                        break;
                    }
                }
            }
            $j++;
        }
        $bodyAst = $this->parseBlockInternal($tokens, $startIdx, $bodyEnd, $ifStack, $foreachStack);
        $i = $j;
        if ($depth === 0) {
            array_pop($foreachStack);
        }
        return $bodyAst;
    }

    /**
     * @param array<int, array{type:string, value:string, line:int}> $tokens
     * @param array<int,int> &$ifStack
     * @param array<int,int> &$foreachStack
     * @return array<mixed>
     */
    private function parseBlockInternal(array $tokens, int $s, int $e, ?array &$ifStack, ?array &$foreachStack): array
    {
        $children = [];
        $skip = [];
        for ($idx = $s; $idx < $e; $idx++) {
            if (isset($skip[$idx])) {
                continue;
            }
            [$type, $value, $line] = $tokens[$idx];
            if ($type === self::TOKEN_EOF) {
                break;
            }
            if ($type === self::TOKEN_LITERAL) {
                $children[] = ['literal', $value];
                continue;
            }
            if ($type === self::TOKEN_BRACE_OPEN) {
                $children[] = ['interpolate_braces', $value, $line];
                continue;
            }
            if ($type === self::TOKEN_CURLY_OPEN) {
                $vTrim = trim($value);
                if ($vTrim === '/foreach' || $vTrim === '/if') {
                    continue;
                }
                if (preg_match('#^strip$#', $vTrim)) {
                    $parsed = $this->parseInnerUntil($tokens, $idx, $e, '/strip', $bodyStart, $bodyEnd2, $inner);
                    if ($parsed) {
                        for ($k = $idx; $k <= $bodyEnd2; $k++) {
                            $skip[$k] = true;
                        }
                        $children[] = ['strip', $inner];
                        $idx = $bodyEnd2;
                        continue;
                    }
                }
                if (preg_match('#^if\s+(.+)$#s', $vTrim, $mm)) {
                    $condExpr = trim($mm[1]);
                    $ifStack[] = $line;
                    $node = $this->parseInnerIfChain($condExpr, $tokens, $idx, $e, $ifStack, $foreachStack, $consumed);
                    for ($k = $idx; $k <= $consumed; $k++) {
                        $skip[$k] = true;
                    }
                    $children[] = $node;
                    $idx = $consumed;
                    continue;
                }
                if (preg_match('#^foreach\s+(.+)$#s', $vTrim, $mm)) {
                    $def = trim($mm[1]);
                    $parsedDef = $this->parseForeachDefinition($def);
                    if ($parsedDef === null) {
                        $this->addError(sprintf('Syntax {foreach} tidak valid di baris %d', $line));
                        continue;
                    }
                    [$arrayExpr, $itemVar, $keyVar] = $parsedDef;
                    $foreachStack[] = $line;
                    $depthF = 1;
                    $bStart = $idx + 1;
                    $bEnd = $e;
                    for ($k = $bStart; $k < $e; $k++) {
                        [$tt, $vv] = $tokens[$k];
                        if ($tt === self::TOKEN_CURLY_OPEN) {
                            $vvt = trim($vv);
                            if (preg_match('#^foreach\s+#', $vvt)) {
                                $depthF++;
                            } elseif ($vvt === '/foreach') {
                                $depthF--;
                                if ($depthF === 0) {
                                    $bEnd = $k;
                                    break;
                                }
                            }
                        }
                    }
                    $bodyAst = $this->parseBlockInternal($tokens, $bStart, $bEnd, $ifStack, $foreachStack);
                    for ($k = $idx; $k <= $bEnd; $k++) {
                        $skip[$k] = true;
                    }
                    $idx = $bEnd;
                    if ($depthF === 0) {
                        array_pop($foreachStack);
                    }
                    $children[] = ['foreach', $arrayExpr, $itemVar, $keyVar, $bodyAst, $line];
                    continue;
                }
                if (preg_match('#^assign\s+(.+)$#s', $vTrim, $mm)) {
                    $children[] = $this->parseAssignTag(trim($mm[1]), $line);
                    continue;
                }
                if (preg_match('#^include\s+(.+)$#s', $vTrim, $mm)) {
                    $children[] = $this->parseIncludeTag(trim($mm[1]), $line);
                    continue;
                }
                $children[] = ['interpolate_curly', $value, $line];
            }
        }
        return ['block', $children];
    }

    private function parseInnerIfChain(
        string $firstCond,
        array $tokens,
        int $startIdx,
        int $endIdx,
        array &$ifStack,
        array &$foreachStack,
        ?int &$consumed,
    ): array {
        $branches = [];
        $currentCond = $firstCond;
        $currentStart = $startIdx + 1;
        $closeIdx = $endIdx - 1;
        $depth = 1;

        $j = $currentStart;
        while ($j < $endIdx) {
            [$type, $value] = $tokens[$j];
            if ($type !== self::TOKEN_CURLY_OPEN) {
                $j++;
                continue;
            }
            $vTrim = trim($value);
            if (preg_match('#^if\s+#', $vTrim)) {
                $depth++;
                $j++;
                continue;
            }
            if ($vTrim === '/if') {
                $depth--;
                if ($depth === 0) {
                    $bodyAst = $this->parseBlockInternal($tokens, $currentStart, $j, $ifStack, $foreachStack);
                    $branches[] = [$currentCond, $bodyAst];
                    $closeIdx = $j;
                    break;
                }
                $j++;
                continue;
            }
            if ($depth === 1) {
                if (preg_match('#^elseif\s+(.+)$#s', $vTrim, $m)) {
                    $bodyAst = $this->parseBlockInternal($tokens, $currentStart, $j, $ifStack, $foreachStack);
                    $branches[] = [$currentCond, $bodyAst];
                    $currentCond = trim($m[1]);
                    $currentStart = $j + 1;
                    $j++;
                    continue;
                }
                if ($vTrim === 'else') {
                    $bodyAst = $this->parseBlockInternal($tokens, $currentStart, $j, $ifStack, $foreachStack);
                    $branches[] = [$currentCond, $bodyAst];
                    $currentCond = '__ELSE__';
                    $currentStart = $j + 1;
                    $j++;
                    continue;
                }
            }
            $j++;
        }

        if (count($ifStack) > 0) {
            array_pop($ifStack);
        }

        $consumed = $closeIdx;
        return ['if', $branches, $startIdx];
    }

    /**
     * @param array<int, array{type:string, value:string, line:int}> $tokens
     * @return array<mixed>|null
     */
    private function parseIfChain(
        string $firstCond,
        array $tokens,
        int &$startIdx,
        int $endIdx,
        array &$ifStack,
        array &$foreachStack,
    ): array {
        $consumed = $startIdx;
        $ast = $this->parseInnerIfChain($firstCond, $tokens, $startIdx, $endIdx, $ifStack, $foreachStack, $consumed);
        $startIdx = $consumed;
        return $ast;
    }

    /**
     * @param array<int, array{type:string, value:string, line:int}> $tokens
     * @param int &$bodyEndIdx (out)
     * @param array<mixed> &$innerAst (out)
     * @param array<int,int> &$foreachStack
     */
    private function parseUntilTag(
        array $tokens,
        int $i,
        int $endIdx,
        string $closeTag,
        string $openTagName,
        ?int &$bodyEndIdx,
        ?array &$innerAst,
        array &$foreachStack,
    ): bool {
        $startIdx = $i + 1;
        $j = $startIdx;
        $bodyEndIdx = $endIdx - 1;
        while ($j < $endIdx) {
            [$type, $value] = $tokens[$j];
            if ($type === self::TOKEN_CURLY_OPEN && trim($value) === $closeTag) {
                $bodyEndIdx = $j;
                break;
            }
            $j++;
        }
        $ifStackInt = [];
        $fsInt = [];
        $innerAst = $this->parseBlockInternal($tokens, $startIdx, $bodyEndIdx, $ifStackInt, $fsInt);
        return true;
    }

    /**
     * @param array<int, array{type:string, value:string, line:int}> $tokens
     * @param int &$bodyStart out
     * @param int &$bodyEnd   out
     * @param array<mixed> &$inner out
     */
    private function parseInnerUntil(array $tokens, int $startIdx, int $endIdx, string $closeTag, ?int &$bodyStart, ?int &$bodyEnd, ?array &$inner): bool
    {
        $bodyStart = $startIdx + 1;
        $j = $bodyStart;
        $bodyEnd = $endIdx - 1;
        while ($j < $endIdx) {
            [$type, $value] = $tokens[$j];
            if ($type === self::TOKEN_CURLY_OPEN && trim($value) === $closeTag) {
                $bodyEnd = $j;
                break;
            }
            $j++;
        }
        $ifStackInt = [];
        $fsInt = [];
        $inner = $this->parseBlockInternal($tokens, $bodyStart, $bodyEnd, $ifStackInt, $fsInt);
        return true;
    }

    /**
     * Parse definisi foreach: "$arr as $item" atau "$arr as $k => $v"
     *
     * @return array{0:string, 1:string, 2:string|null}|null
     */
    private function parseForeachDefinition(string $def): ?array
    {
        $def = trim($def);
        if (preg_match('#^\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\s+as\s+\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\s*$#', $def, $m)) {
            return [$m[1], $m[2], null];
        }
        if (preg_match('#^\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\s+as\s+\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\s*=>\s*\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\s*$#', $def, $m)) {
            return [$m[1], $m[3], $m[2]];
        }
        return null;
    }

    /**
     * @return array{0:string, 1:mixed, 2:int}|null  ['assign', varName, valueExpr, line]
     */
    private function parseAssignTag(string $content, int $line): ?array
    {
        $attrs = $this->parseAttrs($content);
        $var = $attrs['var'] ?? null;
        $value = $attrs['value'] ?? null;
        if ($var === null) {
            $this->addWarning(sprintf('{assign} tanpa var= di baris %d', $line));
            return null;
        }
        return ['assign', (string) $var, $value, $line];
    }

    /**
     * @return array{0:string, 1:mixed, 2:int}  ['include', fileExpr, line]
     */
    private function parseIncludeTag(string $content, int $line): array
    {
        $attrs = $this->parseAttrs($content);
        $file = $attrs['file'] ?? '';
        $this->addWarning(sprintf('Template include tidak didukung, skip: "%s" (baris %d)', (string) $file, $line));
        return ['include', (string) $file, $line];
    }

    /**
     * Parse key=value attr list (value bisa quoted string atau $variable).
     *
     * @return array<string, mixed>
     */
    private function parseAttrs(string $content): array
    {
        $attrs = [];
        $len = strlen($content);
        $i = 0;
        while ($i < $len) {
            while ($i < $len && ctype_space($content[$i])) {
                $i++;
            }
            if ($i >= $len) {
                break;
            }
            $keyStart = $i;
            while ($i < $len && !ctype_space($content[$i]) && $content[$i] !== '=') {
                $i++;
            }
            $key = substr($content, $keyStart, $i - $keyStart);
            while ($i < $len && ctype_space($content[$i])) {
                $i++;
            }
            if ($i >= $len || $content[$i] !== '=') {
                $attrs[$key] = true;
                continue;
            }
            $i++;
            while ($i < $len && ctype_space($content[$i])) {
                $i++;
            }
            if ($i >= $len) {
                $attrs[$key] = '';
                break;
            }
            $val = '';
            if ($content[$i] === '"' || $content[$i] === "'") {
                $quote = $content[$i];
                $i++;
                while ($i < $len && $content[$i] !== $quote) {
                    if ($content[$i] === '\\' && $i + 1 < $len) {
                        $val .= $content[$i + 1];
                        $i += 2;
                    } else {
                        $val .= $content[$i];
                        $i++;
                    }
                }
                if ($i < $len) {
                    $i++;
                }
                $attrs[$key] = $val;
            } elseif ($content[$i] === '$') {
                $start = $i;
                $i++;
                while ($i < $len && (ctype_alnum($content[$i]) || $content[$i] === '_' || $content[$i] === '.' || $content[$i] === '[' || $content[$i] === ']' || $content[$i] === "'" || $content[$i] === '"' || $content[$i] === '@')) {
                    $i++;
                }
                $attrs[$key] = ['__VAR__' => substr($content, $start, $i - $start)];
            } else {
                $start = $i;
                while ($i < $len && !ctype_space($content[$i])) {
                    $i++;
                }
                $raw = substr($content, $start, $i - $start);
                if (is_numeric($raw)) {
                    $attrs[$key] = str_contains($raw, '.') ? (float) $raw : (int) $raw;
                } else {
                    $attrs[$key] = $raw;
                }
            }
        }
        return $attrs;
    }

    /* ──────────────────────────────────────────────────────────────
     *  STEP 3: EXPRESSION EVALUATOR (SANDBOXED — TANPA eval())
     *
     *  Grammar:
     *    Expr        → OrExpr
     *    OrExpr      → AndExpr ( ('||'|'or') AndExpr )*
     *    AndExpr     → EqExpr ( ('&&'|'and') EqExpr )*
     *    EqExpr      → RelExpr ( ('=='|'eq'|'!='|'ne'|'==='|'!==') RelExpr )*
     *    RelExpr     → InExpr ( ('<'|'lt'|'>'|'gt'|'<='|'le'|'>='|'ge') InExpr )*
     *    InExpr      → Unary ( 'in' Unary )*
     *    Unary       → ('!'|'not') Unary | Primary
     *    Primary     → STRING | NUMBER | BOOL | NULL
     *                 | '$' VarPath ( '@' property )? | empty '(' Expr ')'
     *                 | IDENT '(' ArgList ')' | '(' Expr ')'
     * ────────────────────────────────────────────────────────────── */

    /**
     * @param array<string, mixed> $context
     */
    private function evaluateExpression(string $expr, array $context, array $assignments = []): mixed
    {
        $tokens = $this->exprTokenize($expr);
        $pos = 0;
        $result = $this->exprParseOr($tokens, $pos, $context, $assignments);
        return $result;
    }

    /**
     * @return array<int, array{0:string, 1:string|int|float|bool|null}>
     */
    private function exprTokenize(string $expr): array
    {
        $tokens = [];
        $len = strlen($expr);
        $i = 0;
        while ($i < $len) {
            while ($i < $len && ctype_space($expr[$i])) {
                $i++;
            }
            if ($i >= $len) {
                break;
            }
            $c = $expr[$i];

            if ($c === '"' || $c === "'") {
                $quote = $c;
                $i++;
                $val = '';
                while ($i < $len && $expr[$i] !== $quote) {
                    if ($expr[$i] === '\\' && $i + 1 < $len) {
                        $n = $expr[$i + 1];
                        $val .= match ($n) {
                            'n' => "\n", 't' => "\t", 'r' => "\r",
                            '"' => '"', "'" => "'", '\\' => '\\',
                            default => $n,
                        };
                        $i += 2;
                    } else {
                        $val .= $expr[$i];
                        $i++;
                    }
                }
                if ($i < $len) {
                    $i++;
                }
                $tokens[] = ['STR', $val];
                continue;
            }

            if (ctype_digit($c) || ($c === '.' && $i + 1 < $len && ctype_digit($expr[$i + 1]))
                || ($c === '-' && $i + 1 < $len && ctype_digit($expr[$i + 1]))) {
                $start = $i;
                if ($c === '-') {
                    $i++;
                }
                $hasDot = false;
                while ($i < $len && (ctype_digit($expr[$i]) || $expr[$i] === '.')) {
                    if ($expr[$i] === '.') {
                        if ($hasDot) {
                            break;
                        }
                        $hasDot = true;
                    }
                    $i++;
                }
                $numStr = substr($expr, $start, $i - $start);
                $tokens[] = $hasDot ? ['NUM', (float) $numStr] : ['NUM', (int) $numStr];
                continue;
            }

            if ($c === '$') {
                $start = $i;
                $i++;
                while ($i < $len) {
                    if (ctype_alnum($expr[$i]) || $expr[$i] === '_' || $expr[$i] === '.') {
                        $i++;
                    } elseif ($expr[$i] === '-' && $i + 1 < $len && $expr[$i + 1] === '>') {
                        $i += 2;
                    } else {
                        break;
                    }
                }
                if ($i < $len && $expr[$i] === '[') {
                    $braceDepth = 1;
                    $i++;
                    while ($i < $len && $braceDepth > 0) {
                        if ($expr[$i] === '[') {
                            $braceDepth++;
                        }
                        if ($expr[$i] === ']') {
                            $braceDepth--;
                        }
                        $i++;
                    }
                }
                if ($i < $len && $expr[$i] === '@') {
                    $i++;
                    while ($i < $len && (ctype_alnum($expr[$i]) || $expr[$i] === '_')) {
                        $i++;
                    }
                }
                $tokens[] = ['VAR', substr($expr, $start, $i - $start)];
                continue;
            }

            if (ctype_alpha($c) || $c === '_') {
                $start = $i;
                while ($i < $len && (ctype_alnum($expr[$i]) || $expr[$i] === '_')) {
                    $i++;
                }
                $word = strtolower(substr($expr, $start, $i - $start));
                $map = [
                    'eq' => 'OP_EQ', 'ne' => 'OP_NE', 'lt' => 'OP_LT', 'gt' => 'OP_GT',
                    'le' => 'OP_LE', 'ge' => 'OP_GE', 'and' => 'OP_AND', 'or' => 'OP_OR',
                    'not' => 'OP_NOT', 'in' => 'OP_IN',
                    'true' => ['BOOL', true], 'false' => ['BOOL', false],
                    'null' => ['NULL', null], 'empty' => 'EMPTY',
                ];
                if (isset($map[$word])) {
                    if (is_array($map[$word])) {
                        $tokens[] = $map[$word];
                    } else {
                        $tokens[] = [$map[$word], $word];
                    }
                    continue;
                }
                $tokens[] = ['IDENT', $word];
                continue;
            }

            $two = substr($expr, $i, 2);
            if ($two === '===') {
                $tokens[] = ['OP_IDENT', '==='];
                $i += 3;
                continue;
            }
            if ($two === '!==') {
                $tokens[] = ['OP_NIDENT', '!=='];
                $i += 3;
                continue;
            }
            if ($two === '==') {
                $tokens[] = ['OP_EQ', '=='];
                $i += 2;
                continue;
            }
            if ($two === '!=') {
                $tokens[] = ['OP_NE', '!='];
                $i += 2;
                continue;
            }
            if ($two === '<=') {
                $tokens[] = ['OP_LE', '<='];
                $i += 2;
                continue;
            }
            if ($two === '>=') {
                $tokens[] = ['OP_GE', '>='];
                $i += 2;
                continue;
            }
            if ($two === '&&') {
                $tokens[] = ['OP_AND', '&&'];
                $i += 2;
                continue;
            }
            if ($two === '||') {
                $tokens[] = ['OP_OR', '||'];
                $i += 2;
                continue;
            }
            if ($c === '(') { $tokens[] = ['LP', '(']; $i++; continue; }
            if ($c === ')') { $tokens[] = ['RP', ')']; $i++; continue; }
            if ($c === ',') { $tokens[] = ['COMMA', ',']; $i++; continue; }
            if ($c === '!') { $tokens[] = ['OP_NOT', '!']; $i++; continue; }
            if ($c === '<') { $tokens[] = ['OP_LT', '<']; $i++; continue; }
            if ($c === '>') { $tokens[] = ['OP_GT', '>']; $i++; continue; }

            $this->addWarning(sprintf('Unknown character in expression: "%s" (pos %d)', $c, $i));
            $i++;
        }
        $tokens[] = ['EOF', null];
        return $tokens;
    }

    /**
     * @param array<int, array{0:string, 1:mixed}> $tokens
     * @param array<string, mixed> $context
     * @param array<string, mixed> $assignments
     */
    private function exprParseOr(array $tokens, int &$pos, array $context, array $assignments): mixed
    {
        $left = $this->exprParseAnd($tokens, $pos, $context, $assignments);
        while ($pos < count($tokens) && $tokens[$pos][0] === 'OP_OR') {
            $pos++;
            $right = $this->exprParseAnd($tokens, $pos, $context, $assignments);
            $left = $this->isTruthy($left) || $this->isTruthy($right);
        }
        return $left;
    }

    /**
     * @param array<int, array{0:string, 1:mixed}> $tokens
     * @param array<string, mixed> $context
     * @param array<string, mixed> $assignments
     */
    private function exprParseAnd(array $tokens, int &$pos, array $context, array $assignments): mixed
    {
        $left = $this->exprParseEq($tokens, $pos, $context, $assignments);
        while ($pos < count($tokens) && $tokens[$pos][0] === 'OP_AND') {
            $pos++;
            $right = $this->exprParseEq($tokens, $pos, $context, $assignments);
            $left = $this->isTruthy($left) && $this->isTruthy($right);
        }
        return $left;
    }

    /**
     * @param array<int, array{0:string, 1:mixed}> $tokens
     * @param array<string, mixed> $context
     * @param array<string, mixed> $assignments
     */
    private function exprParseEq(array $tokens, int &$pos, array $context, array $assignments): mixed
    {
        $left = $this->exprParseRel($tokens, $pos, $context, $assignments);
        while (true) {
            $type = $tokens[$pos][0] ?? 'EOF';
            if ($type === 'OP_EQ') {
                $pos++;
                $right = $this->exprParseRel($tokens, $pos, $context, $assignments);
                $left = $left == $right;
            } elseif ($type === 'OP_NE') {
                $pos++;
                $right = $this->exprParseRel($tokens, $pos, $context, $assignments);
                $left = $left != $right;
            } elseif ($type === 'OP_IDENT') {
                $pos++;
                $right = $this->exprParseRel($tokens, $pos, $context, $assignments);
                $left = $left === $right;
            } elseif ($type === 'OP_NIDENT') {
                $pos++;
                $right = $this->exprParseRel($tokens, $pos, $context, $assignments);
                $left = $left !== $right;
            } else {
                break;
            }
        }
        return $left;
    }

    /**
     * @param array<int, array{0:string, 1:mixed}> $tokens
     * @param array<string, mixed> $context
     * @param array<string, mixed> $assignments
     */
    private function exprParseRel(array $tokens, int &$pos, array $context, array $assignments): mixed
    {
        $left = $this->exprParseIn($tokens, $pos, $context, $assignments);
        while (true) {
            $type = $tokens[$pos][0] ?? 'EOF';
            if ($type === 'OP_LT') {
                $pos++;
                $right = $this->exprParseIn($tokens, $pos, $context, $assignments);
                $left = $left < $right;
            } elseif ($type === 'OP_GT') {
                $pos++;
                $right = $this->exprParseIn($tokens, $pos, $context, $assignments);
                $left = $left > $right;
            } elseif ($type === 'OP_LE') {
                $pos++;
                $right = $this->exprParseIn($tokens, $pos, $context, $assignments);
                $left = $left <= $right;
            } elseif ($type === 'OP_GE') {
                $pos++;
                $right = $this->exprParseIn($tokens, $pos, $context, $assignments);
                $left = $left >= $right;
            } else {
                break;
            }
        }
        return $left;
    }

    /**
     * @param array<int, array{0:string, 1:mixed}> $tokens
     * @param array<string, mixed> $context
     * @param array<string, mixed> $assignments
     */
    private function exprParseIn(array $tokens, int &$pos, array $context, array $assignments): mixed
    {
        $left = $this->exprParseUnary($tokens, $pos, $context, $assignments);
        while ($pos < count($tokens) && $tokens[$pos][0] === 'OP_IN') {
            $pos++;
            $right = $this->exprParseUnary($tokens, $pos, $context, $assignments);
            $left = is_array($right) && in_array($left, $right, true);
        }
        return $left;
    }

    /**
     * @param array<int, array{0:string, 1:mixed}> $tokens
     * @param array<string, mixed> $context
     * @param array<string, mixed> $assignments
     */
    private function exprParseUnary(array $tokens, int &$pos, array $context, array $assignments): mixed
    {
        $type = $tokens[$pos][0] ?? 'EOF';
        if ($type === 'OP_NOT') {
            $pos++;
            $val = $this->exprParseUnary($tokens, $pos, $context, $assignments);
            return !$this->isTruthy($val);
        }
        return $this->exprParsePrimary($tokens, $pos, $context, $assignments);
    }

    /**
     * @param array<int, array{0:string, 1:mixed}> $tokens
     * @param array<string, mixed> $context
     * @param array<string, mixed> $assignments
     */
    private function exprParsePrimary(array $tokens, int &$pos, array $context, array $assignments): mixed
    {
        $type = $tokens[$pos][0] ?? 'EOF';
        $value = $tokens[$pos][1] ?? null;

        if ($type === 'NUM' || $type === 'STR' || $type === 'BOOL' || $type === 'NULL') {
            $pos++;
            return $value;
        }

        if ($type === 'LP') {
            $pos++;
            $val = $this->exprParseOr($tokens, $pos, $context, $assignments);
            if (($tokens[$pos][0] ?? 'EOF') === 'RP') {
                $pos++;
            }
            return $val;
        }

        if ($type === 'EMPTY') {
            $pos++;
            if (($tokens[$pos][0] ?? 'EOF') === 'LP') {
                $pos++;
                $val = $this->exprParseOr($tokens, $pos, $context, $assignments);
                if (($tokens[$pos][0] ?? 'EOF') === 'RP') {
                    $pos++;
                }
                return $this->isEmpty($val);
            }
            return true;
        }

        if ($type === 'VAR') {
            $pos++;
            return $this->resolveVariable((string) $value, $context, $assignments);
        }

        if ($type === 'IDENT') {
            $funcName = strtolower((string) $value);
            $pos++;
            if (!in_array($funcName, self::WHITELIST_HELPERS, true)) {
                $this->addWarning(sprintf('Function "%s" tidak ada di whitelist, hasilkan string kosong', $funcName));
                if ($pos < count($tokens) && $tokens[$pos][0] === 'LP') {
                    $this->skipParens($tokens, $pos);
                }
                return '';
            }
            if (($tokens[$pos][0] ?? 'EOF') !== 'LP') {
                return '';
            }
            $pos++;
            $args = [];
            if (($tokens[$pos][0] ?? 'EOF') !== 'RP') {
                $args[] = $this->exprParseOr($tokens, $pos, $context, $assignments);
                while (($tokens[$pos][0] ?? 'EOF') === 'COMMA') {
                    $pos++;
                    $args[] = $this->exprParseOr($tokens, $pos, $context, $assignments);
                }
            }
            if (($tokens[$pos][0] ?? 'EOF') === 'RP') {
                $pos++;
            }
            return $this->callHelper($funcName, $args, $context);
        }

        $pos++;
        return null;
    }

    /**
     * @param array<int, array{0:string, 1:mixed}> $tokens
     */
    private function skipParens(array $tokens, int &$pos): void
    {
        $depth = 1;
        $pos++;
        while ($pos < count($tokens) && $depth > 0) {
            if ($tokens[$pos][0] === 'LP') {
                $depth++;
            }
            if ($tokens[$pos][0] === 'RP') {
                $depth--;
            }
            $pos++;
        }
    }

    private function isTruthy(mixed $val): bool
    {
        if (is_bool($val)) {
            return $val;
        }
        if ($val === null) {
            return false;
        }
        if (is_scalar($val)) {
            if (is_string($val)) {
                return $val !== '' && $val !== '0';
            }
            if (is_numeric($val)) {
                return (float) $val !== 0.0;
            }
            return (bool) $val;
        }
        if (is_array($val)) {
            return count($val) > 0;
        }
        return true;
    }

    private function isEmpty(mixed $val): bool
    {
        if ($val === null) {
            return true;
        }
        if (is_string($val)) {
            return trim($val) === '';
        }
        if (is_array($val)) {
            return count($val) === 0;
        }
        if (is_bool($val)) {
            return !$val;
        }
        if (is_numeric($val)) {
            return (float) $val === 0.0;
        }
        return empty($val);
    }

    /**
     * @param array<string, mixed> $context
     * @param array<string, mixed> $assignments
     */
    private function resolveVariable(string $rawVar, array $context, array $assignments): mixed
    {
        $rawVar = ltrim($rawVar, '$');
        $atProp = null;
        $atPos = strpos($rawVar, '@');
        if ($atPos !== false) {
            $atProp = substr($rawVar, $atPos + 1);
            $rawVar = substr($rawVar, 0, $atPos);
        }

        $path = $this->varPathToDot($rawVar);

        if ($atProp !== null) {
            $loopKey = $path . '@' . $atProp;
            if (isset($assignments[$loopKey])) {
                return $assignments[$loopKey];
            }
            if (isset($assignments['__loopdata__']) && is_array($assignments['__loopdata__'])) {
                return $assignments['__loopdata__'][$atProp] ?? null;
            }
            return null;
        }

        if (isset($assignments[$path])) {
            return $assignments[$path];
        }

        $firstDot = strpos($path, '.');
        if ($firstDot !== false) {
            $topKey = substr($path, 0, $firstDot);
            $restPath = substr($path, $firstDot + 1);
            if (isset($assignments[$topKey])) {
                $topVal = $assignments[$topKey];
                if (is_array($topVal)) {
                    $r = $this->getFromContext($restPath, $topVal);
                    if ($r !== null || array_key_exists($restPath, $topVal)) {
                        return $r;
                    }
                }
            }
        }

        if (str_starts_with($rawVar, "vs['") || str_starts_with($rawVar, 'vs["') || str_starts_with($rawVar, 'vs.')) {
            $this->addWarning(sprintf('Legacy variable $vs.* tanpa compatibility layer — gunakan {{voucher.code}}: "%s"', $rawVar));
        }

        $this->recordUsedVariable($path);
        if (!VariableRegistry::exists($path)) {
            $firstPart = explode('.', $path)[0];
            if (!in_array($firstPart, ['voucher', 'package', 'router', 'company', 'currency', 'hotspot', 'system', 'vouchers', 'vs', 'price', 'username', 'password', 'code', 'timelimit', 'validity', 'duration', 'quota'], true)) {
                $this->addWarning(sprintf('Variable "%s" tidak terdaftar di registry', $path));
            }
        }

        return $this->getFromContext($path, $context);
    }

    /**
     * Ubah "voucher.code" / "vs['code']" / "package[0]" menjadi dot-path standar.
     */
    private function varPathToDot(string $raw): string
    {
        $orig = $raw;
        $raw = str_replace('->', '.', $raw);
        $raw = preg_replace('#\[["\']([^\]]*?)["\']\]#', '.$1', $raw);
        $raw = preg_replace('#\[(\d+)\]#', '.$1', $raw);
        $raw = trim((string) $raw, '.');

        if (str_starts_with($raw, 'vs.') || $raw === 'vs') {
            $rest = substr($raw, 3);
            $map = [
                'code' => 'voucher.code',
                'username' => 'voucher.username',
                'password' => 'voucher.password',
                'price' => 'voucher.price',
                'status' => 'voucher.status',
                'type' => 'voucher.type',
                'timelimit' => 'voucher.timelimit',
                'validity' => 'voucher.validity',
                'created_at' => 'voucher.created_at',
                'expired_at' => 'voucher.expired_at',
                'login_url' => 'voucher.login_url',
            ];
            if ($rest === '') {
                return 'voucher';
            }
            return $map[$rest] ?? 'voucher.' . $rest;
        }

        return $raw;
    }

    /**
     * @param array<string, mixed> $context
     */
    private function getFromContext(string $dotPath, array $context): mixed
    {
        $keys = explode('.', $dotPath);
        $current = $context;
        foreach ($keys as $k) {
            if (is_array($current) && array_key_exists($k, $current)) {
                $current = $current[$k];
            } else {
                return null;
            }
        }
        return $current;
    }

    private function extractAtProperty(mixed $loopData, string $prop): mixed
    {
        if (!is_array($loopData)) {
            return null;
        }
        return $loopData[$prop] ?? null;
    }

    /**
     * @param array<int, mixed> $args
     * @param array<string, mixed> $context
     */
    private function callHelper(string $name, array $args, array $context): mixed
    {
        $currency = $context['currency'] ?? null;
        return match ($name) {
            'qr' => ($this->qrHelper)(
                isset($args[0]) ? (string) $args[0] : '',
                isset($args[1]) ? (int) $args[1] : 150,
            ),
            'barcode' => ($this->barcodeHelper)(
                isset($args[0]) ? (string) $args[0] : '',
                isset($args[1]) ? (int) $args[1] : 300,
                isset($args[2]) ? (int) $args[2] : 60,
            ),
            'asset' => ($this->assetHelper)(isset($args[0]) ? (string) $args[0] : ''),
            'format_currency' => ($this->formatCurrencyHelper)($args[0] ?? 0, $currency),
            'format_number' => ($this->formatNumberHelper)($args[0] ?? 0, isset($args[1]) ? (int) $args[1] : 0, $currency),
            'date_format' => ($this->dateFormatHelper)($args[0] ?? '', isset($args[1]) ? (string) $args[1] : 'Y-m-d H:i:s'),
            default => '',
        };
    }

    /* ──────────────────────────────────────────────────────────────
     *  STEP 4: FILTERS
     * ────────────────────────────────────────────────────────────── */

    /**
     * @param array<string, mixed> $context
     */
    private function applyFilters(mixed $value, string $filterChain, array $context): mixed
    {
        $parts = $this->splitFilters($filterChain);
        $currency = $context['currency'] ?? null;
        foreach ($parts as $f) {
            [$fname, $fargs] = $this->parseOneFilter($f);
            $fnameLower = strtolower($fname);
            if (!in_array($fnameLower, self::ALLOWED_FILTERS, true)) {
                $this->addWarning(sprintf('Filter "%s" tidak dikenal, skip', $fname));
                continue;
            }
            $value = $this->applyOneFilter($value, $fnameLower, $fargs, $currency);
        }
        return $value;
    }

    /**
     * @return array<int, string>
     */
    private function splitFilters(string $chain): array
    {
        $parts = [];
        $len = strlen($chain);
        $buffer = '';
        $i = 0;
        $inStr = null;
        while ($i < $len) {
            $c = $chain[$i];
            if ($inStr !== null) {
                $buffer .= $c;
                if ($c === '\\' && $i + 1 < $len) {
                    $buffer .= $chain[$i + 1];
                    $i += 2;
                    continue;
                }
                if ($c === $inStr) {
                    $inStr = null;
                }
                $i++;
                continue;
            }
            if ($c === '"' || $c === "'") {
                $inStr = $c;
                $buffer .= $c;
                $i++;
                continue;
            }
            if ($c === '|') {
                if (trim($buffer) !== '') {
                    $parts[] = trim($buffer);
                }
                $buffer = '';
                $i++;
                continue;
            }
            $buffer .= $c;
            $i++;
        }
        if (trim($buffer) !== '') {
            $parts[] = trim($buffer);
        }
        return $parts;
    }

    /**
     * @return array{0:string, 1:array<int, mixed>}
     */
    private function parseOneFilter(string $f): array
    {
        $lp = strpos($f, '(');
        if ($lp === false) {
            return [$f, []];
        }
        $name = trim(substr($f, 0, $lp));
        $rest = substr($f, $lp);
        $args = [];
        $expr = trim($rest, '() ');
        if ($expr === '') {
            return [$name, []];
        }
        $tokens = $this->exprTokenize($expr);
        $pos = 0;
        $ctx = [];
        $args[] = $this->exprParseOr($tokens, $pos, $ctx, []);
        while ($pos < count($tokens) && $tokens[$pos][0] === 'COMMA') {
            $pos++;
            $args[] = $this->exprParseOr($tokens, $pos, $ctx, []);
        }
        return [$name, $args];
    }

    private function applyOneFilter(mixed $value, string $fname, array $fargs, mixed $currency): mixed
    {
        return match ($fname) {
            'raw' => $value,
            'escape' => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'),
            'upper' => function_exists('mb_strtoupper') ? mb_strtoupper((string) $value, 'UTF-8') : strtoupper((string) $value),
            'lower' => function_exists('mb_strtolower') ? mb_strtolower((string) $value, 'UTF-8') : strtolower((string) $value),
            'trim' => trim((string) $value),
            'nl2br' => nl2br((string) $value, false),
            'number_format' => $this->applyFilterNumberFormat($value, $fargs, $currency),
            'currency' => ($this->formatCurrencyHelper)($value, is_array($currency) ? $currency : null),
            'date' => ($this->dateFormatHelper)($value, isset($fargs[0]) ? (string) $fargs[0] : 'Y-m-d H:i:s'),
            default => $value,
        };
    }

    private function applyFilterNumberFormat(mixed $value, array $fargs, mixed $currency): string
    {
        $decimals = isset($fargs[0]) ? (int) $fargs[0] : 0;
        $ts = is_array($currency) ? ($currency['thousand_separator'] ?? ',') : ',';
        $ds = is_array($currency) ? ($currency['decimal_separator'] ?? '.') : '.';
        if (isset($fargs[1])) {
            $ds = (string) $fargs[1];
        }
        if (isset($fargs[2])) {
            $ts = (string) $fargs[2];
        }
        $amount = is_numeric($value) ? (float) $value : 0.0;
        return number_format($amount, $decimals, $ds, $ts);
    }

    /* ──────────────────────────────────────────────────────────────
     *  STEP 5: RENDER AST → HTML STRING
     * ────────────────────────────────────────────────────────────── */

    /**
     * @param array<mixed> $ast
     * @param array<string, mixed> $context
     * @param array<string, mixed> $assignments
     */
    private function renderAst(array $ast, array $context, array &$assignments = []): string
    {
        if (!isset($ast[0])) {
            return '';
        }
        $nodeType = $ast[0];

        return match ($nodeType) {
            'block' => $this->renderBlock($ast, $context, $assignments),
            'literal' => $ast[1],
            'interpolate_braces' => $this->renderInterpolateBraces($ast[1], $ast[2] ?? 0, $context, $assignments),
            'interpolate_curly' => $this->renderInterpolateCurly($ast[1], $ast[2] ?? 0, $context, $assignments),
            'if' => $this->renderIf($ast, $context, $assignments),
            'foreach' => $this->renderForeach($ast, $context, $assignments),
            'assign' => $this->renderAssign($ast, $context, $assignments),
            'include' => '',
            'strip' => $this->renderStrip($ast, $context, $assignments),
            '__CLOSE_IF__', '__CLOSE_FOREACH__', '__ELSEIF__', '__ELSE__' => '',
            default => '',
        };
    }

    /**
     * @param array<mixed> $ast
     * @param array<string, mixed> $context
     * @param array<string, mixed> $assignments
     */
    private function renderBlock(array $ast, array $context, array &$assignments): string
    {
        $children = $ast[1] ?? [];
        $out = '';
        foreach ($children as $child) {
            if (!is_array($child) || !isset($child[0])) {
                continue;
            }
            if ($child[0] === 'assign') {
                $this->renderAssign($child, $context, $assignments);
                continue;
            }
            $out .= $this->renderAst($child, $context, $assignments);
        }
        return $out;
    }

    /**
     * @param array<string, mixed> $context
     * @param array<string, mixed> $assignments
     */
    private function renderInterpolateBraces(string $content, int $line, array $context, array &$assignments): string
    {
        $content = trim($content);
        if ($content === '') {
            return '';
        }
        $filterSplit = $this->splitExpressionAndFilters($content);
        $exprStr = $filterSplit[0];
        $filterChain = $filterSplit[1];
        try {
            $value = $this->evaluateExpression($exprStr, $context, $assignments);
        } catch (\Throwable $e) {
            $this->addWarning(sprintf('Gagal evaluasi "{{%s}}" di baris %d: %s', $content, $line, $e->getMessage()));
            return '';
        }
        if ($filterChain !== '') {
            $value = $this->applyFilters($value, $filterChain, $context);
        }
        $raw = $this->hasRawFilter($filterChain);
        return $this->scalarToString($value, !$raw);
    }

    /**
     * @param array<string, mixed> $context
     * @param array<string, mixed> $assignments
     */
    private function renderInterpolateCurly(string $content, int $line, array $context, array &$assignments): string
    {
        $content = trim($content);
        if ($content === '') {
            return '';
        }
        if (preg_match('#^([a-zA-Z_][a-zA-Z0-9_]*)\s*\($#', $content)) {
            return '';
        }
        $filterSplit = $this->splitExpressionAndFilters($content);
        $exprStr = $filterSplit[0];
        $filterChain = $filterSplit[1];
        try {
            $value = $this->evaluateExpression($exprStr, $context, $assignments);
        } catch (\Throwable $e) {
            $this->addWarning(sprintf('Gagal evaluasi "{%s}" di baris %d: %s', $content, $line, $e->getMessage()));
            return '';
        }
        if ($filterChain !== '') {
            $value = $this->applyFilters($value, $filterChain, $context);
        }
        $raw = $this->hasRawFilter($filterChain);
        return $this->scalarToString($value, !$raw);
    }

    /**
     * @return array{0:string, 1:string}
     */
    private function splitExpressionAndFilters(string $content): array
    {
        $len = strlen($content);
        $inStr = null;
        $escape = false;
        $firstPipe = -1;
        for ($i = 0; $i < $len; $i++) {
            $c = $content[$i];
            if ($escape) {
                $escape = false;
                continue;
            }
            if ($c === '\\') {
                $escape = true;
                continue;
            }
            if ($inStr !== null) {
                if ($c === $inStr) {
                    $inStr = null;
                }
                continue;
            }
            if ($c === '"' || $c === "'") {
                $inStr = $c;
                continue;
            }
            if ($c === '|') {
                $firstPipe = $i;
                break;
            }
        }
        if ($firstPipe === -1) {
            return [$content, ''];
        }
        return [trim(substr($content, 0, $firstPipe)), trim(substr($content, $firstPipe + 1))];
    }

    private function hasRawFilter(string $filterChain): bool
    {
        $filters = $this->splitFilters($filterChain);
        foreach ($filters as $f) {
            [$fname] = $this->parseOneFilter($f);
            if (strtolower($fname) === 'raw') {
                return true;
            }
        }
        return false;
    }

    private function scalarToString(mixed $value, bool $escapeHtml): string
    {
        if ($value === null) {
            return '';
        }
        if (is_bool($value)) {
            $s = $value ? '1' : '';
        } elseif (is_scalar($value)) {
            $s = (string) $value;
        } elseif (is_array($value)) {
            $s = json_encode($value, JSON_UNESCAPED_UNICODE);
            $s = $s === false ? '' : $s;
        } else {
            $s = '';
        }
        if ($escapeHtml) {
            return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
        }
        return $s;
    }

    /**
     * @param array<mixed> $ast  ['if', [branches...], line]
     * @param array<string, mixed> $context
     * @param array<string, mixed> $assignments
     */
    private function renderIf(array $ast, array $context, array &$assignments): string
    {
        $branches = $ast[1] ?? [];
        foreach ($branches as [$condStr, $bodyAst]) {
            if ($condStr === '__ELSE__') {
                return $this->renderAst($bodyAst, $context, $assignments);
            }
            try {
                $result = $this->evaluateExpression($condStr, $context, $assignments);
            } catch (\Throwable $e) {
                $this->addWarning(sprintf('Gagal evaluasi kondisi IF: %s', $e->getMessage()));
                $result = false;
            }
            if ($this->isTruthy($result)) {
                return $this->renderAst($bodyAst, $context, $assignments);
            }
        }
        return '';
    }

    /**
     * @param array<mixed> $ast
     * @param array<string, mixed> $context
     * @param array<string, mixed> $assignments
     */
    private function renderForeach(array $ast, array $context, array &$assignments): string
    {
        [, $arrayExpr, $itemVar, $keyVar, $bodyAst] = $ast;
        try {
            $array = $this->evaluateExpression('$' . $arrayExpr, $context, $assignments);
        } catch (\Throwable $e) {
            $this->addWarning(sprintf('Gagal evaluasi foreach array: %s', $e->getMessage()));
            return '';
        }
        if (!is_array($array) || count($array) === 0) {
            return '';
        }

        $out = '';
        $total = count($array);
        $iteration = 0;
        foreach ($array as $k => $v) {
            $iteration++;
            $loopAssign = $assignments;
            if (is_array($v)) {
                $loopAssign[$itemVar] = $v;
            } else {
                $loopAssign[$itemVar] = $v;
            }
            if ($keyVar !== null) {
                $loopAssign[$keyVar] = $k;
            }
            $loopAssign['__loopdata__'] = [
                'index' => $iteration - 1,
                'iteration' => $iteration,
                'first' => $iteration === 1,
                'last' => $iteration === $total,
            ];
            $loopAssign[$itemVar . '@index'] = $iteration - 1;
            $loopAssign[$itemVar . '@iteration'] = $iteration;
            $loopAssign[$itemVar . '@first'] = $iteration === 1;
            $loopAssign[$itemVar . '@last'] = $iteration === $total;

            $out .= $this->renderAst($bodyAst, $context, $loopAssign);
        }
        return $out;
    }

    /**
     * @param array<mixed> $ast  ['assign', varName, valueExpr, line]
     * @param array<string, mixed> $context
     * @param array<string, mixed> $assignments (REFERENCE)
     */
    private function renderAssign(array $ast, array $context, array &$assignments): string
    {
        [, $varName, $valueExpr] = $ast;
        if (is_array($valueExpr) && isset($valueExpr['__VAR__'])) {
            $v = $this->evaluateExpression($valueExpr['__VAR__'], $context, $assignments);
        } elseif (is_string($valueExpr) && str_starts_with($valueExpr, '$')) {
            $v = $this->evaluateExpression($valueExpr, $context, $assignments);
        } else {
            $v = $valueExpr;
        }
        $assignments[(string) $varName] = $v;
        return '';
    }

    /**
     * @param array<mixed> $ast
     * @param array<string, mixed> $context
     * @param array<string, mixed> $assignments
     */
    private function renderStrip(array $ast, array $context, array &$assignments): string
    {
        $inner = $this->renderAst($ast[1], $context, $assignments);
        return trim(preg_replace('/\s+/u', ' ', $inner) ?? '');
    }
}
