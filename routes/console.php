<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(\Illuminate\Foundation\Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('billing:run-automation')
    ->dailyAt('08:00')
    ->timezone('Asia/Jakarta');

Schedule::command('billing:run-automation')
    ->dailyAt('18:00')
    ->timezone('Asia/Jakarta');
