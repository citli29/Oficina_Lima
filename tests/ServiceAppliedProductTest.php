<?php declare(strict_types = 1);
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/TestDatabase.php';
require_once __DIR__ . '/../database/service_applied_product.php';

final class ServiceAppliedProductTest extends TestCase{

	public function testGetAll(): void
	{
		$db = TestDatabase::create();
		$result = ServiceAppliedProduct::getAppliedProducts($db);
		$expected = [
			new ServiceAppliedProduct(1,1,1,0,false),
			new ServiceAppliedProduct(2,1,3,0,false),
			new ServiceAppliedProduct(3,2,2,0,false),
			new ServiceAppliedProduct(4,2,1,0,true)
		];
		$this->assertEquals($expected, $result);
	}

	public function testGetByService(): void
	{
		$db = TestDatabase::create();
		$service1 = Service::getServiceById($db,1);
		$service2 = Service::getServiceById($db,2);
		$result1 = ServiceAppliedProduct::getAppliedProductsByService($db,$service1);
		$result2 = ServiceAppliedProduct::getAppliedProductsByService($db,$service2);
		$expected1 = [
			new ServiceAppliedProduct(1,1, 1,0,false),
			new ServiceAppliedProduct(2,1, 3,0,false),
		];
		$expected2 = [
			new ServiceAppliedProduct(3,2, 2,0,false),
			new ServiceAppliedProduct(4,2, 1,0,true)
		];
		$this->assertEquals($expected1, $result1);
		$this->assertEquals($expected2, $result2);
	}
	public function testGetByProduct(): void
	{
		$db = TestDatabase::create();
		$product1 = Product::getProductById($db,1);
		$product2 = Product::getProductById($db,2);
		$product3 = Product::getProductById($db,3);
		$result1 = ServiceAppliedProduct::getAppliedProductsByProduct($db,$product1);
		$result2 = ServiceAppliedProduct::getAppliedProductsByProduct($db,$product2);
		$result3 = ServiceAppliedProduct::getAppliedProductsByProduct($db,$product3);
		$expected1 = [
			new ServiceAppliedProduct(1,1, 1,0,false),
			new ServiceAppliedProduct(4,2, 1,0,true)
		];
		$expected2 = [
			new ServiceAppliedProduct(3,2, 2,0,false),
		];
		$expected3 = [
			new ServiceAppliedProduct(2,1, 3,0,false),
		];
		$this->assertEquals($expected1, $result1);
		$this->assertEquals($expected2, $result2);
		$this->assertEquals($expected3, $result3);
	}
	public function testGetOne(): void
	{
		$db = TestDatabase::create();
		$result = ServiceAppliedProduct::getServiceAppliedProductById($db,1);	
		$expected = new ServiceAppliedProduct(1,1,1,0,false);
		$this->assertEquals($expected, $result);
	}
	public function testSave(): void
	{
		$db = TestDatabase::create();
		$sap = ServiceAppliedProduct::getServiceAppliedProductById($db,1);
		$product = Product::getProductById($db,2);
		$service = Service::getServiceById($db,2);
		$sap->setProduct($product);
		$sap->setService($service);
		$sap->quantity = 3;
		$sap->is_applied = true;
		$sap->save($db);
		$sap = ServiceAppliedProduct::getServiceAppliedProductById($db,1);
		$expected = new ServiceAppliedProduct(1, 2,2,3,true);
		$this->assertEquals($expected, $sap);
	}
}
