<?php
$file = 'app/Livewire/Pengaturan/Perusahaan/Index.php';
$content = file_get_contents($file);

$messages = <<<'PHP'
    public function messages(): array
    {
        return [
            'logoFile.image' => 'Logo utama harus berupa file gambar (JPG, PNG, GIF, WebP).',
            'logoFile.max' => 'Ukuran file logo utama tidak boleh lebih dari 2MB.',
            'logoFile.uploaded' => 'Sistem server menolak file logo utama. Kemungkinan karena setelan web server (Nginx client_max_body_size) atau PHP (upload_max_filesize) terlalu kecil, atau folder temporary penuh.',
            
            'stampFile.image' => 'Cap/Stempel harus berupa file gambar (JPG, PNG, GIF, WebP).',
            'stampFile.max' => 'Ukuran file Cap/Stempel tidak boleh lebih dari 2MB.',
            'stampFile.uploaded' => 'Sistem server menolak file Cap/Stempel. Periksa kapasitas atau izin folder temporary server.',
            
            'partnerLogoFile.image' => 'Logo mitra harus berupa file gambar (JPG, PNG, GIF, WebP).',
            'partnerLogoFile.max' => 'Ukuran file logo mitra tidak boleh lebih dari 2MB.',
            'partnerLogoFile.uploaded' => 'Sistem server menolak file logo mitra. Periksa kapasitas atau izin folder temporary server.',
        ];
    }

    public function save(): void
PHP;

$content = str_replace('public function save(): void', $messages, $content);
file_put_contents($file, $content);
echo "Custom validation messages added.\n";
