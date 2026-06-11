<?php declare(strict_types = 1);
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/TestDatabase.php';
require_once __DIR__ . '/../database/car.php';

final class CarTest extends TestCase{

	public function testGetAllByModel(): void
	{
		$db = TestDatabase::create();
		$express = Model::getModelById($db,3);	
		$corsa = Model::getModelById($db,4);	
		$res_express = Car::getCarsByModel($db, $express);
		$exp_express= array(
			new Car(3,"AB-00-02",3),
			new Car(6,"AB-00-05",3)
		);
		$this->assertEquals($exp_express, $res_express);
		$res_corsa = Car::getCarsByModel($db, $corsa);
		$exp_corsa= array(
			new Car(4,"AB-00-03",4),
		);
		$this->assertEquals($exp_corsa, $res_corsa);
	}

	public function testGetOne(): void
	{
		$db = TestDatabase::create();
		$result = Car::getCarById($db,7);	
		$expected = new Car(7,"AB-00-06", 6);
		$this->assertEquals($expected, $result);
	}

	public function testSave(): void
	{
		$db = TestDatabase::create();
		$model = Model::getModelById($db,6);	
		$car = Car::getCarById($db,1);	
		$car->plate = "Matricula";
		$car->setModel($model);
		$car->chassi_nr = "01";
		$car->year = 2012;
		$car->month = 11;
		$car->cc = 1200;
		$car->engine_code = "ABAB";
		$car->color_code = "ABAB";
		$car->save($db);
		$car = Car::getCarById($db,1);	
		$this->assertEquals("Matricula", $car->plate);
		$this->assertEquals($model, $car->getModel($db));
		$this->assertEquals("01", $car->chassi_nr);
		$this->assertEquals(2012, $car->year);
		$this->assertEquals(11, $car->month);
		$this->assertEquals(1200, $car->cc);
		$this->assertEquals("ABAB", $car->engine_code);
		$this->assertEquals("ABAB", $car->color_code);
	}

	public function testDelete(): void
	{
		$db = TestDatabase::create();
		$car = Car::getCarById($db,1);	
		$car->delete($db);
		$this->expectException(Exception::class);
		Car::getCarById($db,1);
	}

	public function testGetInvalidException(): void
	{
		$db = TestDatabase::create();
		$this->expectException(Exception::class);
		Car::getCarById($db,10);
	}
}
?>
