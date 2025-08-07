<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use App\Mail\DailyPendingReportMail;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    Mail::to('nimal.admin@blogapp.com')->send(new DailyPendingReportMail());
    info('Daily report test email sent.');
})->everyMinute();
