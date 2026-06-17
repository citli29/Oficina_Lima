<?php

use App\Controllers\HomeController;
use App\Controllers\CarController;

return [
    ['GET', '/', [HomeController::class, 'index']],
    ['GET', '/about', [HomeController::class, 'about']],
    ['GET', '/cars', [CarController::class, 'cars']],
    ['GET', '/car/{id:\d+}', [CarController::class, 'car']],
    ['GET', '/cars/{search}', [CarController::class, 'carsSearch']],
];
