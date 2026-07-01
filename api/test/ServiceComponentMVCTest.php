<?php

use App\Database\Database;
use App\Models\ServiceComponent;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

class ServiceComponentMVCTest extends TestCase 
{
	
	private Client $client;
	private ServiceComponent $sc;

	public static function setUpBeforeClass():void
	{
		$path = __DIR__.'/../../database';
		$cmd = "sqlite3 ". $path ."/database.db < " .$path ."/database.sql";
		exec($cmd);
	}

	protected function setUp():void
	{
		$this->sc = new ServiceComponent(Database::getConnection());
	}
	public function test(){
		$var = $this->sc->getServiceUserTimesByServiceWithFilter(1,['user_name' => "A"]);
		print_r($var);
	}
}
