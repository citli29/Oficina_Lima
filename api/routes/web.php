<?php

use App\Controllers\HomeController;
use App\Controllers\CarController;

return [
    ['GET', '/api/', [HomeController::class, 'index']],
    ['GET', '/api/about', [HomeController::class, 'about']],
    ['GET', '/api/cars', [CarController::class, 'cars']], //with search
    ['POST', '/api/cars', [CarController::class, 'create']],
    ['GET', '/api/car/{id:\d+}', [CarController::class, 'car']],
    ['PUT', '/api/car/{id:\d+}', [CarController::class, 'update']],
    ['DELETE', '/api/car/{id:\d+}', [CarController::class, 'delete']],
];
