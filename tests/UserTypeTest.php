<?php declare(strict_types = 1);
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/TestDatabase.php';
require_once __DIR__ . '/../database/user_type.php';

final class UserTypeTest extends TestCase{

	public function testGetAll(): void
	{
		$db = TestDatabase::create();
		$result = UserType::getUserTypes($db);	
		$expected = array(
			new UserType(1,"Escritorio"),
			new UserType(2,"Oficina")
		);
		$this->assertEquals($expected, $result);
	}

	public function testGetOne(): void
	{
		$db = TestDatabase::create();
		$result = UserType::getUserTypeById($db,2);	
		$expected = new UserType(2,"Oficina");
		$this->assertEquals($expected, $result);
	}

	public function testSave(): void
	{
		$db = TestDatabase::create();
		$user_type = UserType::getUserTypeById($db,1);	
		$user_type->designation = "Teste";
		$user_type->save($db);
		$user_type = UserType::getUserTypeById($db,1);	
		$this->assertEquals("Teste", $user_type->designation);
	}

	public function testDelete(): void
	{
		$db = TestDatabase::create();
		$user_type = UserType::getUserTypeById($db,1);	
		$user_type->delete($db);
		$this->expectException(Exception::class);
		UserType::getUserTypeById($db,1);
	}

	public function testGetInvalidException(): void
	{
		$db = TestDatabase::create();
		$this->expectException(Exception::class);
		UserType::getUserTypeById($db,3);
	}
}
?>

