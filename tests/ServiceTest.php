<?php declare(strict_types = 1);
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/TestDatabase.php';
require_once __DIR__ . '/../database/service.php';

final class ServiceTest extends TestCase{

	public function testGetAll(): void
	{
		$db = TestDatabase::create();
		$result = Service::getServices($db);	
		$s1 = new Service(1,1);
		$s1->service_description = "Revisao Oleo";
		$s2 = new Service(2,1);
		$s2->service_description = "Revisao Oleo";
		$expected = array($s1,$s2);
		$this->assertEquals($expected, $result);
	}

	public function testGetOne(): void
	{
		$db = TestDatabase::create();
		$result = Service::getServiceById($db,1);	
		$expected = new Service(1,1);
		$expected->service_description = "Revisao Oleo";
		$this->assertEquals($expected, $result );
	}

	public function testSave(): void
	{
		$db = TestDatabase::create();

		$service= Service::getServiceById($db,1);	
		$client = Client::getClientById($db,2);
		$car = Car::getCarById($db,2);
		$schedule = Schedule::getScheduleById($db,2);

		$service->setClient($client);
		$service->kms = 3;
		$service->checkin_date = "01/01/2001";
		$service->checkout_date = "02/01/2001";
		$service->malfunction_description = "Vra";
		$service->service_description = "Vra";
		$service->setCar($car);
		$service->setSchedule($schedule);
		$service->save($db);

		$service= Service::getServiceById($db,1);	
		$this->assertEquals($client, $service->getClient($db));
		$this->assertEquals(3, $service->kms);
		$this->assertEquals("01/01/2001", $service->checkin_date);
		$this->assertEquals("02/01/2001", $service->checkout_date);
		$this->assertEquals("Vra", $service->malfunction_description);
		$this->assertEquals("Vra", $service->service_description);
		$this->assertEquals($car, $service->getCar($db));
		$this->assertEquals($schedule, $service->getSchedule($db));

		$service->setCar(null);
		$service->setSchedule(null);
		$this->assertNull($service->getCar($db));
		$this->assertNull($service->getSchedule($db));
	}

	public function testGetInvalidException(): void
	{
		$db = TestDatabase::create();
		$this->expectException(Exception::class);
		Service::getServiceById($db,10);
	}
}
?>
