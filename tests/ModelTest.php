<?php declare(strict_types = 1);
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/TestDatabase.php';
require_once __DIR__ . '/../database/model.php';

final class ModelTest extends TestCase{

	public function testGetAllByMake(): void
	{
		$db = TestDatabase::create();
		$ren = Make::getMakeById($db,1);	
		$ope = Make::getMakeById($db,2);	
		$res_ren = Model::getModelsByMake($db, $ren);
		$exp_ren= array(
			new Model(1,"Clio",1),
			new Model(2,"Megane",1),
			new Model(3,"Express",1),
		);
		$this->assertEquals($exp_ren, $res_ren);
		$res_ope = Model::getModelsByMake($db, $ope);
		$exp_ope= array(
			new Model(4,"Corsa",2),
			new Model(5,"Combo",2),
			new Model(6,"Astra",2),
		);
		$this->assertEquals($exp_ope, $res_ope);
	}

	public function testGetOne(): void
	{
		$db = TestDatabase::create();
		$result = Model::getModelById($db,4);	
		$expected = new Model(4,"Corsa", 2);
		$this->assertEquals($expected, $result);
	}

	public function testSave(): void
	{
		$db = TestDatabase::create();
		$model = Model::getModelById($db,1);	
		$model->name = "cliu";
		$model->save($db);
		$model = Model::getModelById($db,1);	
		$this->assertEquals("cliu", $model->name);
	}

	public function testDelete(): void
	{
		$db = TestDatabase::create();
		$model = Model::getModelById($db,1);	
		$model->delete($db);
		$this->expectException(Exception::class);
		Model::getModelById($db,1);
	}

	public function testGetInvalidException(): void
	{
		$db = TestDatabase::create();
		$this->expectException(Exception::class);
		Model::getModelById($db,10);
	}
}
?>
