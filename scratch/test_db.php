<?php
include 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$first = DB::table('contents')->where('file_path', 'like', '%.pdf')->first();
print_r($first);
