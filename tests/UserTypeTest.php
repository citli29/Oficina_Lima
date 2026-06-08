<?php declare(strict_types = 1);
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/TestDatabase.php';
require_once __DIR__ . '/../database/user_type.php';

final class UserTypeTest extends TestCase{

	public function testGetAll(): void
	{
		$db = TestDatabase::create();
		$result = UserType::getUserTypes($db);	
		$expected = array(new UserType(0,"Escritorio"), new UserType(1,"Oficina"));
		$this->assertEquals($result, $expected);
	}
	public function testGetOne(): void
	{
		$db = TestDatabase::create();
		$result = UserType::getUserTypeById($db,1);	
		$expected = new UserType(1,"Oficina");
		$this->assertEquals($result, $expected);
	}

	public function testGetInvalidException(): void
	{
		$db = TestDatabase::create();
		$this->expectException(Exception::class);
		UserType::getUserTypeById($db,2);
	}
}
?>

