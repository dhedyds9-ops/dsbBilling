<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

Schema::create('activations', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->unique();
    $table->string('customer_name');
    $table->text('notes')->nullable();
    $table->date('activation_date')->nullable();
    $table->string('service_package')->nullable();
    $table->string('status')->default('pending');
    $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
    $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
    $table->softDeletes();
    $table->timestamps();
});

echo "Tabel activations berhasil dibuat!\n";
