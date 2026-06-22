<?php

use App\Controllers\HomeController;
use App\Controllers\CarController;
use App\Controllers\ClientController;
use App\Controllers\ProductController;

return [
	['GET', '/api/', [HomeController::class, 'index']],
	['GET', '/api/about', [HomeController::class, 'about']],

	/*	Car Controller	*/
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

	/*	Product Controller	*/
	['GET', '/api/product_types', [ProductController::class, 'getProductTypes']], //with search
	['POST', '/api/product_types', [ProductController::class, 'postProductTypes']],
	['GET', '/api/product_type/{id:\d+}', [ProductController::class, 'getProductType']],
	['PUT', '/api/product_type/{id:\d+}', [ProductController::class, 'putProductType']],
	['DELETE', '/api/product_type/{id:\d+}', [ProductController::class, 'deleteProductType']],

	['GET', '/api/products', [ProductController::class, 'getProducts']], //with search
	['POST', '/api/products', [ProductController::class, 'postProducts']],
	['GET', '/api/product/{id:\d+}', [ProductController::class, 'getProduct']],
	['PUT', '/api/product/{id:\d+}', [ProductController::class, 'putProduct']],
	['DELETE', '/api/product/{id:\d+}', [ProductController::class, 'deleteProduct']],

	/*	Client Controller	*/
	['GET', '/api/clients', [ClientController::class, 'getClients']], //with search
	['POST', '/api/clients', [ClientController::class, 'postClients']],
	['GET', '/api/client/{id:\d+}', [ClientController::class, 'getClient']],
	['PUT', '/api/client/{id:\d+}', [ClientController::class, 'putClient']],
	['DELETE', '/api/client/{id:\d+}', [ClientController::class, 'deleteClient']],
];
