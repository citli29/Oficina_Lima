<?php declare(strict_types = 1);
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/TestDatabase.php';
require_once __DIR__ . '/../database/service_applied_product.php';

final class ServiceAppliedProductTest extends TestCase{

	public function testGetAll(): void
	{
		$db = TestDatabase::create();
		echo 'Gang 1';
		print_r(ServiceAppliedProduct::getAppliedProducts($db));
		echo 'Gang 2';
		print_r($product = Product::getProductById($db,1));
		echo 'Gang 3';
		print_r($service = Service::getServiceById($db,1));
		echo 'Gang 4';
		print_r(ServiceAppliedProduct::getAppliedProductsByProduct($db,$product));
		echo 'Gang 5';
		print_r(ServiceAppliedProduct::getAppliedProductsByService($db,$service));
	}
}
