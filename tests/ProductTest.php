<?php declare(strict_types = 1);
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/TestDatabase.php';
require_once __DIR__ . '/../database/product.php';

final class ProductTest extends TestCase{

	public function testGetAll(): void
	{
		$db = TestDatabase::create();
		$result = Product::getProducts($db);	
		$expected = array(
			new Product(1,"Filtro Ar", "PA7553",3), 
			new Product(2,"Filtro Oleo", "FT6086",3),
			new Product(3,"Anticongelante Rosa", "ACR",1)
		);
		$this->assertEquals($expected, $result);
	}

	public function testGetOne(): void
	{
		$db = TestDatabase::create();
		$result = Product::getProductById($db,1);	
		$expected = new Product(1,"Filtro Ar", "PA7553", 3);
		$this->assertEquals($expected, $result );
	}

	public function testSave(): void
	{
		$db = TestDatabase::create();
		$product= Product::getProductById($db,1);	
		$product_type = ProductType::getProductTypeById($db,1);
		$product->designation= "teste";
		$product->reference= "tst";
		$product->setProductType($product_type);
		$product->save($db);
		$product = Product::getProductById($db,1);	
		$this->assertEquals("teste", $product->designation);
		$this->assertEquals("tst", $product->reference);
		$this->assertEquals($product_type ,$product->getProductType($db));
	}

	public function testGetInvalidException(): void
	{
		$db = TestDatabase::create();
		$this->expectException(Exception::class);
		Product::getProductById($db,10);
	}
}
?>

