<?php

use App\Controllers\HomeController;
use App\Controllers\CarController;
use App\Controllers\ClientController;
use App\Controllers\ProductController;
use App\Controllers\ScheduleController;
use App\Controllers\ServiceComponentController;
use App\Controllers\ServiceController;
use App\Controllers\UserController;

return [
	['GET', '/api/', [HomeController::class, 'index']],
	['GET', '/api/about', [HomeController::class, 'about']],

	/*	User Controller	*/
	['GET', '/api/users', [UserController::class, 'getUsers']], //with search
	//['POST', '/api/users', [UserController::class, 'postUsers']],
	['GET', '/api/users/{id:\d+}', [UserController::class, 'getUser']],
	//['PUT', '/api/users/{id:\d+}', [UserController::class, 'putUsers']],
	//['DELETE', '/api/users/{id:\d+}', [UserController::class, 'deleteUsers']],
	
	/*	Car Controller	*/
	['GET', '/api/cars', [CarController::class, 'getCars']], //with search
	['POST', '/api/cars', [CarController::class, 'postCars']],
	['GET', '/api/cars/{id:\d+}', [CarController::class, 'getCar']],
	['PUT', '/api/cars/{id:\d+}', [CarController::class, 'putCar']],
	['DELETE', '/api/cars/{id:\d+}', [CarController::class, 'deleteCar']],

	['GET', '/api/models', [CarController::class, 'getModels']], //with search
	['POST', '/api/models', [CarController::class, 'postModels']],
	['GET', '/api/models/{id:\d+}', [CarController::class, 'getModel']],
	['PUT', '/api/models/{id:\d+}', [CarController::class, 'putModel']],
	['DELETE', '/api/models/{id:\d+}', [CarController::class, 'deleteModel']],

	['GET', '/api/makes', [CarController::class, 'getMakes']], //with search
	['POST', '/api/makes', [CarController::class, 'postMakes']],
	['GET', '/api/makes/{id:\d+}', [CarController::class, 'getMake']],
	['PUT', '/api/makes/{id:\d+}', [CarController::class, 'putMake']],
	['DELETE', '/api/makes/{id:\d+}', [CarController::class, 'deleteMake']],

	/*	Product Controller	*/
	['GET', '/api/product_types', [ProductController::class, 'getProductTypes']], //with search
	['POST', '/api/product_types', [ProductController::class, 'postProductTypes']],
	['GET', '/api/product_types/{id:\d+}', [ProductController::class, 'getProductType']],
	['PUT', '/api/product_types/{id:\d+}', [ProductController::class, 'putProductType']],
	['DELETE', '/api/product_types/{id:\d+}', [ProductController::class, 'deleteProductType']],

	['GET', '/api/products', [ProductController::class, 'getProducts']], //with search
	['POST', '/api/products', [ProductController::class, 'postProducts']],
	['GET', '/api/products/{id:\d+}', [ProductController::class, 'getProduct']],
	['PUT', '/api/products/{id:\d+}', [ProductController::class, 'putProduct']],
	['DELETE', '/api/products/{id:\d+}', [ProductController::class, 'deleteProduct']],

	/*	Client Controller	*/
	['GET', '/api/clients', [ClientController::class, 'getClients']], //with search
	['POST', '/api/clients', [ClientController::class, 'postClients']],
	['GET', '/api/clients/{id:\d+}', [ClientController::class, 'getClient']],
	['PUT', '/api/clients/{id:\d+}', [ClientController::class, 'putClient']],
	['DELETE', '/api/clients/{id:\d+}', [ClientController::class, 'deleteClient']],

	/*	Schedule Controller	*/
	['GET', '/api/schedules', [ScheduleController::class, 'getSchedules']], //with search
	['POST', '/api/schedules', [ScheduleController::class, 'postSchedules']],
	['GET', '/api/schedules/{id:\d+}', [ScheduleController::class, 'getSchedule']],
	['PUT', '/api/schedules/{id:\d+}', [ScheduleController::class, 'putSchedule']],
	['DELETE', '/api/schedules/{id:\d+}', [ScheduleController::class, 'deleteSchedule']],
	['POST', '/api/schedules/{id:\d+}/create_service', [ScheduleController::class, 'postCreateServiceFromSchedule']],


	/*	Service Controller	*/
	['GET', '/api/services', [ServiceController::class, 'getServices']], //with search
	['POST', '/api/services', [ServiceController::class, 'postServices']],
	['GET', '/api/services/{id:\d+}', [ServiceController::class, 'getService']],
	['PUT', '/api/services/{id:\d+}', [ServiceController::class, 'putService']],
	['DELETE', '/api/services/{id:\d+}', [ServiceController::class, 'deleteService']],

	/*	Service Component Controller	*/
	['GET', '/api/services/{s_id:\d+}/user_times', [ServiceComponentController::class, 'getSUTs']], //with search
	['POST', '/api/services/{s_id:\d+}/user_times', [ServiceComponentController::class, 'postSUTs']], //with search
	['GET', '/api/services/{s_id:\d+}/user_times/{id:\d+}', [ServiceComponentController::class, 'getSUT']],
	['PUT', '/api/services/{s_id:\d+}/user_times/{id:\d+}', [ServiceComponentController::class, 'putSUT']],
	['DELETE', '/api/services/{s_id:\d+}/user_times/{id:\d+}', [ServiceComponentController::class, 'deleteSUT']],


	['GET', '/api/services/{s_id:\d+}/applied_products', [ServiceComponentController::class, 'getAppliedProducts']], //with search
	['POST', '/api/services/{s_id:\d+}/applied_products', [ServiceComponentController::class, 'postAppliedProducts']], //with search
	['GET', '/api/services/{s_id:\d+}/applied_products/{id:\d+}', [ServiceComponentController::class, 'getAppliedProduct']],
	['PUT', '/api/services/{s_id:\d+}/applied_products/{id:\d+}', [ServiceComponentController::class, 'putAppliedProduct']],
	['DELETE', '/api/services/{s_id:\d+}/applied_products/{id:\d+}', [ServiceComponentController::class, 'deleteAppliedProduct']],


	['GET', '/api/services_applied_products', [ServiceComponentController::class, 'getServiceAppliedProducts']], //with search
	['GET', '/api/services_user_times', [ServiceComponentController::class, 'getServiceUserTimes']], //with search


];
