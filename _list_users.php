<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

foreach (App\Models\User::orderBy('id')->get() as $u) {
    echo $u->id.' | '.$u->name.' | '.$u->email.' | role_col:'.($u->role ?? '-').' | spatie:'.$u->getRoleNames()->implode(',')."\n";
}
