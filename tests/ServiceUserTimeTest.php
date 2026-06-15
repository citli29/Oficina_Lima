<?php declare(strict_types = 1);
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/TestDatabase.php';
require_once __DIR__ . '/../database/service_user_time.php';
require_once __DIR__ . '/../database/service.php';
require_once __DIR__ . '/../database/user.php';

final class ServiceUserTimeTest extends TestCase{

	public function testCreate(): void
	{
		$db = TestDatabase::create();
		$service = Service::getServiceById($db, 2);
		$user = User::getUserById($db, 1);
		$servUT = ServiceUserTime::create($db,$service, $user, 10, "10/10/2020");
		$sss = ServiceUserTime::getServiceUserTimeById($db, $servUT->getId());
		$expected = new ServiceUserTime(6,2,1,10, "10/10/2020");
		$this->assertEquals($expected, $servUT);
		$this->assertEquals($servUT, $sss);
	}
	public function testGetAll(): void
	{
		$db = TestDatabase::create();
		$result = ServiceUserTime::getServiceUserTimes($db);	
		$expected = [
			new ServiceUserTime(1,1,1,90,"01/01/2001"),
			new ServiceUserTime(2,1,2,90,"01/01/2001"),
			new ServiceUserTime(3,2,1,15,"02/01/2001"),
			new ServiceUserTime(4,2,2,90,"01/01/2001"),
			new ServiceUserTime(5,2,1,15,"02/01/2001")
		];
		$this->assertEquals($expected, $result);
	}

	public function testGetByService(): void
	{
		$db = TestDatabase::create();
		$service = Service::getServiceById($db,2);
		$result = ServiceUserTime::getServiceUserTimeByService($db,$service);
		$expected = [
			new ServiceUserTime(3,2,1,15,"02/01/2001"),
			new ServiceUserTime(4,2,2,90,"01/01/2001"),
			new ServiceUserTime(5,2,1,15,"02/01/2001")
		];
		$this->assertEquals($expected, $result);
	}
	public function testGetByUser(): void
	{
		$db = TestDatabase::create();
		$user = User::getUserById($db,1);
		$result = ServiceUserTime::getServiceUserTimeByUser($db,$user);
		$expected = [
			new ServiceUserTime(1,1,1,90,"01/01/2001"),
			new ServiceUserTime(3,2,1,15,"02/01/2001"),
			new ServiceUserTime(5,2,1,15,"02/01/2001")
		];
		$this->assertEquals($expected, $result);
	}

	public function testGetOne(): void
	{
		$db = TestDatabase::create();
		$result = ServiceUserTime::getServiceUserTimeById($db,1);	
		$expected = new ServiceUserTime(1,1,1,90,"01/01/2001");
		$this->assertEquals($expected, $result );
	}

	public function testSave(): void
	{
		$db = TestDatabase::create();
		$servUT = ServiceUserTime::getServiceUserTimeById($db,1);	
		$service = Service::getServiceById($db,2);
		$user = User::getUserById($db,2);
		$servUT->setService($service);
		$servUT->setUser($user);
		$servUT->ut_date = "Vra";
		$servUT->minutes = 33;
		$servUT->save($db);
		$servUT = ServiceUserTime::getServiceUserTimeById($db,1);	
		$this->assertEquals($service, $servUT->getService($db));
		$this->assertEquals($user, $servUT->getUser($db));
		$this->assertEquals(33, $servUT->minutes);
		$this->assertEquals("Vra", $servUT->ut_date);
	}
	public function testDelete(): void
	{
		$db = TestDatabase::create();
		$servUT = ServiceUserTime::getServiceUserTimeById($db,1);	
		$servUT->delete($db);
		$this->expectException(Exception::class);
		ServiceUserTime::getServiceUserTimeById($db,1);	
	}

	public function testGetInvalidException(): void
	{
		$db = TestDatabase::create();
		$this->expectException(Exception::class);
		Service::getServiceById($db,10);
	}
}
?>
