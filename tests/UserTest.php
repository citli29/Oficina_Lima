<?php declare(strict_types = 1);
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/TestDatabase.php';
require_once __DIR__ . '/../database/user.php';

final class UserTest extends TestCase{

	public function testGetAll(): void
	{
		$db = TestDatabase::create();
		$result = User::getUsers($db);	
		$expected = array(
			new User(1,"teste", "teste@email.com",1), 
			new User(2,"teste", "teste2@email.com",2)
		);
		$this->assertEquals($expected, $result);
	}

	public function testGetOne(): void
	{
		$db = TestDatabase::create();
		$result = User::getUserById($db,1);	
		$expected = new User(1,"teste", "teste@email.com", 1);
		$this->assertEquals($expected, $result );
	}

	public function testSave(): void
	{
		$db = TestDatabase::create();
		$user= User::getUserById($db,1);	
		$user_type = UserType::getUserTypeById($db,2);
		$user->name= "bleh";
		$user->email= "bleh@bleh.com";
		$user->setUserType($user_type);
		$user->save($db);
		$user = User::getUserById($db,1);	
		$this->assertEquals("bleh", $user->name);
		$this->assertEquals("bleh@bleh.com", $user->email);
		$this->assertEquals($user_type ,$user->getUserType($db));
	}

	public function testGetInvalidException(): void
	{
		$db = TestDatabase::create();
		$this->expectException(Exception::class);
		UserType::getUserTypeById($db,3);
	}
}
?>

