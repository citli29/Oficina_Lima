<?php declare(strict_types = 1);
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/TestDatabase.php';
require_once __DIR__ . '/../database/make.php';

final class MakeTest extends TestCase{

	public function testGetAll(): void
	{
		$db = TestDatabase::create();
		$result = Make::getMakes($db);	
		$expected = array(
			new Make(1,"Renault"),
			new Make(2,"Opel")
		);
		$this->assertEquals($expected, $result);
	}

	public function testGetOne(): void
	{
		$db = TestDatabase::create();
		$result = Make::getMakeById($db,1);	
		$expected = new Make(1,"Renault");
		$this->assertEquals($expected, $result);
	}

	public function testSave(): void
	{
		$db = TestDatabase::create();
		$make = Make::getMakeById($db,1);	
		$make->name = "ui";
		$make->save($db);
		$make = Make::getMakeById($db,1);	
		$this->assertEquals("ui", $make->name);
	}

	public function testDelete(): void
	{
		$db = TestDatabase::create();
		$make = Make::getMakeById($db,1);	
		$make->delete($db);
		$this->expectException(Exception::class);
		Make::getMakeById($db,1);
	}

	public function testGetInvalidException(): void
	{
		$db = TestDatabase::create();
		$this->expectException(Exception::class);
		Make::getMakeById($db,10);
	}
}
?>
