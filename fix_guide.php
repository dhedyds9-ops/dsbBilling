<?php
$file = 'docs/GENIEACS_GUIDE.md';
$content = file_get_contents($file);

$search = "### C. Mengaktifkan Nginx\n```bash\nsudo ln -s /etc/nginx/sites-available/acs /etc/nginx/sites-enabled/\nsudo ln -s /etc/nginx/sites-available/cwmp /etc/nginx/sites-enabled/\nsudo nginx -t\nsudo systemctl restart nginx\n```";

$replace = $search . "\n\n### D. Keamanan SSL / HTTPS (Let's Encrypt)\nUntuk mengamankan password login admin Anda, **sangat disarankan** memasang SSL/HTTPS untuk Web UI. Namun, **biarkan domain CWMP tetap menggunakan HTTP biasa**.\n\nKenapa CWMP dibiarkan HTTP? Karena banyak modem (ONT) lawas atau *firmware* bawaan yang memiliki masalah kompatibilitas sertifikat SSL. Jika Anda memaksa HTTPS di CWMP, jutaan modem jadul berisiko terputus dari server.\n\nUntuk memasang SSL di Web UI menggunakan Certbot:\n```bash\nsudo apt install certbot python3-certbot-nginx\nsudo certbot --nginx -d acs.domainanda.com\n```\nIkuti instruksi di layar, dan web UI Anda akan otomatis memiliki gembok hijau (HTTPS).";

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "GENIEACS_GUIDE.md updated.\n";
