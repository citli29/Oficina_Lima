<?php

use App\Controllers\HomeController;
use App\Controllers\CarController;

return [
    ['GET', '/', [HomeController::class, 'index']],
    ['GET', '/about', [HomeController::class, 'about']],
    ['GET', '/cars', [CarController::class, 'cars']],
    ['POST', '/cars', [CarController::class, 'create']],
    ['GET', '/car/{id:\d+}', [CarController::class, 'car']],
    ['PUT', '/car/{id:\d+}', [CarController::class, 'update']],
    ['DELETE', '/car/{id:\d+}', [CarController::class, 'delete']],
    ['GET', '/cars/{search}', [CarController::class, 'carsSearch']],
];
