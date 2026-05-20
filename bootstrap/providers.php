<?php

use App\Providers\AppServiceProvider;
use App\Providers\FortifyServiceProvider;
use App\Providers\SnsServiceProvider;

return [
    AppServiceProvider::class,
    FortifyServiceProvider::class,
    SnsServiceProvider::class,
];
