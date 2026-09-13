<?php
$files = [
    'app/Livewire/ISP/PPPoEUser/Create.php' => [
        "return redirect()->route('isp.pppoe-users.index');",
        "return redirect()->route('billing.invoices.show', \$result['invoice']->id);"
    ],
    'app/Livewire/ISP/HotspotUser/Create.php' => [
        "return redirect()->route('isp.hotspot-users.index');",
        "return redirect()->route('billing.invoices.show', \$result['invoice']->id);"
    ]
];

foreach ($files as $file => $replacements) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $content = str_replace($replacements[0], $replacements[1], $content);
        file_put_contents($file, $content);
        echo "Updated $file\n";
    }
}
