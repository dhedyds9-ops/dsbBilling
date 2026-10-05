<?php
$dir = __DIR__."/app";
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
foreach($it as $f) {
    if($f->getExtension() == "php") {
        $path = $f->getPathname();
        $c = file_get_contents($path);
        
        $replaced = preg_replace_callback("/catch\s*\(([^)]+)\)\s*{\s*}/m", function($m) {
            $varNameMatches = [];
            preg_match("/\\$([a-zA-Z0-9_]+)/", $m[1], $varNameMatches);
            $varName = isset($varNameMatches[0]) ? $varNameMatches[0] : "\$e";
            return "catch (" . $m[1] . ") {\n            \Illuminate\Support\Facades\Log::error(\"Swallowed exception caught: \" . " . $varName . "->getMessage());\n        }";
        }, $c);
        
        if ($c !== $replaced) {
            file_put_contents($path, $replaced);
            echo "Fixed empty catch in " . $f->getFilename() . "\n";
        }
    }
}

