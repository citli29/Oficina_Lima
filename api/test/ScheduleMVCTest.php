<?php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

class ScheduleMVCTest extends TestCase 
{
	private Client $client;

	public static function setUpBeforeClass():void
	{
		$path = __DIR__.'/../../database';
		$cmd = "sqlite3 ". $path ."/database.db < " .$path ."/database.sql";
		exec($cmd);
	}

	protected function setUp():void
	{
		$this->client = new Client([
			'base_uri' => 'http://localhost:8000',
			'http_errors' => false
		]);
	}

	public function testGETSchedules(){
		printf("\n Get Schedules regular: ");
		$response = $this->client->get('/api/schedules');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['schedule_list']));
		$this->assertEquals(20,count($body['schedule_list']));
	}


	public function testGETSchedulesWithFilters(){
		//Date
		printf("\n Get Schedules Filter date 1: ");
		$response = $this->client->get('/api/schedules?date=12-05-2025');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['schedule_list']));
		$this->assertEquals(1,count($body['schedule_list']));

		printf("\n Get Schedules Filter date 0: ");
		$response = $this->client->get('/api/schedules?date=13-05-2025');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['schedule_list']));
		$this->assertEquals(0,count($body['schedule_list']));

		printf("\n Get Schedules Filter date multiple: ");
		$response = $this->client->get('/api/schedules?date=11-05-2025');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['schedule_list']));
		$this->assertEquals(3,count($body['schedule_list']));

		//Plate
		printf("\n Get Schedules Filter car plate1: ");
		$response = $this->client->get('/api/schedules?car_plate=AB-00-06');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['schedule_list']));
		$this->assertEquals(2,count($body['schedule_list']));

		printf("\n Get Schedules Filter car_plate 0: ");
		$response = $this->client->get('/api/schedules?car_plate=OO-00-00');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['schedule_list']));
		$this->assertEquals(0,count($body['schedule_list']));

		printf("\n Get Schedules Filter car_plate multiple: ");
		$response = $this->client->get('/api/schedules?car_plate=AB-00-0');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['schedule_list']));
		$this->assertEquals(16,count($body['schedule_list']));

	}
}
