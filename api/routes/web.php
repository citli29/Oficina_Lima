<?php

use App\Controllers\HomeController;
use App\Controllers\CarController;

return [
    ['GET', '/api/', [HomeController::class, 'index']],
    ['GET', '/api/about', [HomeController::class, 'about']],

    ['GET', '/api/cars', [CarController::class, 'getCars']], //with search
    ['POST', '/api/cars', [CarController::class, 'postCars']],
    ['GET', '/api/car/{id:\d+}', [CarController::class, 'getCar']],
    ['PUT', '/api/car/{id:\d+}', [CarController::class, 'putCar']],
    ['DELETE', '/api/car/{id:\d+}', [CarController::class, 'deleteCar']],

    ['GET', '/api/models', [CarController::class, 'getModels']], //with search
    ['POST', '/api/models', [CarController::class, 'postModels']],
    ['GET', '/api/model/{id:\d+}', [CarController::class, 'getModel']],
    ['PUT', '/api/model/{id:\d+}', [CarController::class, 'putModel']],
    ['DELETE', '/api/model/{id:\d+}', [CarController::class, 'deleteModel']],

    ['GET', '/api/makes', [CarController::class, 'getMakes']], //with search
    ['POST', '/api/makes', [CarController::class, 'postMakes']],
    ['GET', '/api/make/{id:\d+}', [CarController::class, 'getMake']],
    ['PUT', '/api/make/{id:\d+}', [CarController::class, 'putMake']],
    ['DELETE', '/api/make/{id:\d+}', [CarController::class, 'deleteMake']],
];
