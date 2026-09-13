<?php
$file = 'app/Livewire/Pengaturan/Perusahaan/Index.php';
$content = file_get_contents($file);

$oldSuccess = <<<PHP
            \$this->savedStatus = 'success';
            session()->flash('success', 'Data Perusahaan berhasil disimpan.');
PHP;

$newSuccess = <<<PHP
            \$this->savedStatus = 'saved';
            \$this->dispatch('toast', type: 'success', message: 'Data Perusahaan berhasil disimpan.');
PHP;

$oldError = <<<PHP
            \$this->savedStatus = 'error';
            \$this->addError('company', 'Gagal menyimpan: ' . \$e->getMessage());
PHP;

$newError = <<<PHP
            \$this->savedStatus = 'error';
            \$this->dispatch('toast', type: 'error', message: 'Gagal menyimpan: ' . \$e->getMessage());
            \$this->addError('company', 'Gagal menyimpan: ' . \$e->getMessage());
PHP;

$content = str_replace($oldSuccess, $newSuccess, $content);
$content = str_replace($oldError, $newError, $content);

file_put_contents($file, $content);
echo "Updated Index.php notifications\n";
