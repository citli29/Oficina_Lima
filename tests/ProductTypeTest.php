<?php declare(strict_types = 1);
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/TestDatabase.php';
require_once __DIR__ . '/../database/product_type.php';

final class ProductTypeTest extends TestCase{

	public function testGetAll(): void
	{
		$db = TestDatabase::create();
		$result = ProductType::getProductTypes($db);	
		$expected = array(
			new ProductType(1,"Consumiveis"),
			new ProductType(2,"Mao de Obra"),
			new ProductType(3,"Itens")
		);
		$this->assertEquals($expected, $result);
	}

	public function testGetOne(): void
	{
		$db = TestDatabase::create();
		$result = ProductType::getProductTypeById($db,2);	
		$expected = new ProductType(2,"Mao de Obra");
		$this->assertEquals($expected, $result);
	}

	public function testSave(): void
	{
		$db = TestDatabase::create();
		$product_type = ProductType::getProductTypeById($db,1);	
		$product_type->designation = "Teste";
		$product_type->save($db);
		$product_type = ProductType::getProductTypeById($db,1);	
		$this->assertEquals("Teste", $product_type->designation);
	}

	public function testDelete(): void
	{
		$db = TestDatabase::create();
		$product_type = ProductType::getProductTypeById($db,1);	
		$product_type->delete($db);
		$this->expectException(Exception::class);
		ProductType::getProductTypeById($db,1);
	}

	public function testGetInvalidException(): void
	{
		$db = TestDatabase::create();
		$this->expectException(Exception::class);
		ProductType::getProductTypeById($db,10);
	}
}
?>

