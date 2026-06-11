<?php declare(strict_types = 1);
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/TestDatabase.php';
require_once __DIR__ . '/../database/schedule.php';

final class ScheduleTest extends TestCase{

	public function testGetAllByCar(): void
	{
		$db = TestDatabase::create();
		$car = Car::getCarById($db,3);	
		$res = Schedule::getSchedulesByCar($db, $car);
		$exp = array(
			new Schedule(1,"05-01-2024","Revisao",3,null,1),
			new Schedule(2,"05-01-2025","Revisao",3,null,1),
			new Schedule(4,"05-01-2026","Revisao",3,null,1)
		);
		$this->assertEquals($exp, $res);
	}

	public function testGetOne(): void
	{
		$db = TestDatabase::create();
		$result = Schedule::getScheduleById($db,5);	
		$expected = new Schedule(5,'05-01-2026', 'Problema de juntas', 4,NULL, 2);
		$this->assertEquals($expected, $result);
	}

	public function testSave(): void
	{
		$db = TestDatabase::create();
		$model = Model::getModelById($db,2);	
		$schedule = Schedule::getScheduleById($db,6);	
		$schedule->schedule_date = "04-01-2026";
		$schedule->description = "O carro doilhe";
		$schedule->setCar(null);
		$schedule->setModel($model);
		$schedule->setClient(null);

		$schedule->save($db);
		$schedule = Schedule::getScheduleById($db,6);	
		$this->assertEquals("04-01-2026", $schedule->schedule_date);
		$this->assertEquals("O carro doilhe", $schedule->description);
		$this->assertNull($schedule->getCar($db),"Schedule: car");
		$this->assertEquals($schedule->getModel($db),$model);
		$this->assertNull($schedule->getClient($db),"Schedule: client");
	}

	public function testDelete(): void
	{
		$db = TestDatabase::create();
		$schedule = Schedule::getScheduleById($db,1);	
		$schedule->delete($db);
		$this->expectException(Exception::class);
		Schedule::getScheduleById($db,1);
	}

	public function testGetInvalidException(): void
	{
		$db = TestDatabase::create();
		$this->expectException(Exception::class);
		Schedule::getScheduleById($db,10);
	}
}
?>
