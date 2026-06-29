<?php


use Illuminate\Support\Facades\Schedule;

// Schedule::command('loans:sync-fines')->dailyAt('00:10');
Schedule::command('loans:sync-fines')->everyMinute();
