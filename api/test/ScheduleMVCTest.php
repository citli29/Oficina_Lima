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
		printf("\n GET Schedules regular: ");
		$response = $this->client->get('/api/schedules');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['schedule_list']));
		$this->assertEquals(20,count($body['schedule_list']));
	}


	public function testGETSchedulesWithFilters(){
		//Date
		printf("\n GET Schedules Filter date 1: ");
		$response = $this->client->get('/api/schedules?date=12-05-2025');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['schedule_list']));
		$this->assertEquals(1,count($body['schedule_list']));

		printf("\n GET Schedules Filter date 0: ");
		$response = $this->client->get('/api/schedules?date=13-05-2025');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['schedule_list']));
		$this->assertEquals(0,count($body['schedule_list']));

		printf("\n GET Schedules Filter date multiple: ");
		$response = $this->client->get('/api/schedules?date=11-05-2025');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['schedule_list']));
		$this->assertEquals(3,count($body['schedule_list']));

		//Plate
		printf("\n GET Schedules Filter car plate1: ");
		$response = $this->client->get('/api/schedules?car_plate=AB-00-06');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['schedule_list']));
		$this->assertEquals(2,count($body['schedule_list']));

		printf("\n GET Schedules Filter car_plate 0: ");
		$response = $this->client->get('/api/schedules?car_plate=OO-00-00');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['schedule_list']));
		$this->assertEquals(0,count($body['schedule_list']));

		printf("\n GET Schedules Filter car_plate multiple: ");
		$response = $this->client->get('/api/schedules?car_plate=AB-00-0');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['schedule_list']));
		$this->assertEquals(16,count($body['schedule_list']));

		//Car Model
		printf("\n GET Schedules Filter car model 1: ");
		$response = $this->client->get('/api/schedules?car_model=megane');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['schedule_list']));
		$this->assertEquals(1,count($body['schedule_list']));

		printf("\n GET Schedules Filter car_model 0: ");
		$response = $this->client->get('/api/schedules?car_model=aaaaa');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['schedule_list']));
		$this->assertEquals(0,count($body['schedule_list']));

		printf("\n GET Schedules Filter car_model multiple: ");
		$response = $this->client->get('/api/schedules?car_model=clio');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['schedule_list']));
		$this->assertEquals(3,count($body['schedule_list']));

		//Car Make
		printf("\n GET Schedules Filter car make 1: ");
		$response = $this->client->get('/api/schedules?car_make=opel');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['schedule_list']));
		$this->assertEquals(1,count($body['schedule_list']));

		printf("\n GET Schedules Filter car_model 0: ");
		$response = $this->client->get('/api/schedules?car_make=aaaaa');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['schedule_list']));
		$this->assertEquals(0,count($body['schedule_list']));

		printf("\n GET Schedules Filter car_model multiple: ");
		$response = $this->client->get('/api/schedules?car_make=renault');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['schedule_list']));
		$this->assertEquals(7,count($body['schedule_list']));

		//Client Name
		printf("\n GET Schedules Filter client name1: ");
		$response = $this->client->get('/api/schedules?client_name=maria');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['schedule_list']));
		$this->assertEquals(1,count($body['schedule_list']));

		printf("\n GET Schedules Filter client name 0: ");
		$response = $this->client->get('/api/schedules?client_name=aaaaa');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['schedule_list']));
		$this->assertEquals(0,count($body['schedule_list']));

		printf("\n GET Schedules Filter client_name multiple: ");
		$response = $this->client->get('/api/schedules?client_name=pedro');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['schedule_list']));
		$this->assertEquals(4,count($body['schedule_list']));

		//Client id
		printf("\n GET Schedules Filter client id 1: ");
		$response = $this->client->get('/api/schedules?client_id=3');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['schedule_list']));
		$this->assertEquals(1,count($body['schedule_list']));

		printf("\n GET Schedules Filter client name 0: ");
		$response = $this->client->get('/api/schedules?client_id=1000');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['schedule_list']));
		$this->assertEquals(0,count($body['schedule_list']));

		printf("\n GET Schedules Filter client_name multiple: ");
		$response = $this->client->get('/api/schedules?client_id=10');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['schedule_list']));
		$this->assertEquals(3,count($body['schedule_list']));
	}
	public function testGETSchedulesID(){
		printf("\n GET Schedules/Id regular: ");
		$response = $this->client->get("/api/schedules/5");
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['schedule']));
	}

	public function testGETSchedulesIDInvalidID(){
		printf("\n GET Schedules/Id Invalid ID: ");
		$response = $this->client->get("/api/schedules/100");
		$this->assertEquals(404, $response->getStatusCode());
	}

	public function testPOSTSchedules(){
		printf("\n POST Schedules regular: ");
		$response = $this->client->post('/api/schedules',
			[
				'json' => [
					'date' => '30-6-2026',
					'description' => 'Carro come gelados com a testa',
					'car_id' =>1,
					'model_id' =>1,
					'client_id' =>1,
				]
			]);
		
		$body = json_decode($response->getBody(), true);
		$this->assertEquals(201, $response->getStatusCode());
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['schedule']));
		$this->assertEquals(true,isset($body['schedule']['id']));
		$this->assertEquals(21,$body['schedule']['id']);

		$this->assertEquals(true,isset($body['schedule']['date']));
		$this->assertEquals('30-6-2026',$body['schedule']['date']);
		$this->assertEquals(true,isset($body['schedule']['description']));
		$this->assertEquals('Carro come gelados com a testa',$body['schedule']['description']);
		$this->assertEquals(true,isset($body['schedule']['car_id']));
		$this->assertEquals(1,$body['schedule']['car_id']);
		$this->assertEquals(true,isset($body['schedule']['car_model_id']));
		$this->assertEquals(1,$body['schedule']['car_model_id']);
		$this->assertEquals(true,isset($body['schedule']['client_id']));
		$this->assertEquals(1,$body['schedule']['car_id']);
	}

	public function testPOSTSchedulesBadRequest(){
		printf("\n POST Schedules Invalid Field: ");
		$response = $this->client->post('/api/schedules',
			[
				'json' => [
					'da' => 'Cool',
					'description' => 'Cool',
				]
			]);
		
		$this->assertEquals(400, $response->getStatusCode());

		$response = $this->client->post('/api/schedules',
			[
				'json' => [
					'date' => 'Cool',
					'descr' => 'Cool',
				]
			]);
		
		$this->assertEquals(400, $response->getStatusCode());

		printf("\n POST Schedules Incoherent Field: ");
		$response = $this->client->post('/api/schedules',
			[
				'json' => [
					'date' => '30-6-2026',
					'description' => 'Carro come gelados com a testa',
					'car_id' =>1,
					'model_id' =>2,
					'client_id' =>1,
				]
			]);
		
		$this->assertEquals(400, $response->getStatusCode());
	}

	public function testPUTSchedulesID(){
		printf("\n PUT Schedules regular: ");
		$response = $this->client->put('/api/schedules/21',
			[
				/**\*date* *\*description* *car_id* *model_id* *client_id*
*/
				'json' => [
					'date' => '30-6-2027',
					'description' => 'Carro come gelados com a boca',
					'car_id' =>3,
					'model_id' =>2,
					'client_id' =>5,
				]
			]);
		
		$body = json_decode($response->getBody(), true);
		$this->assertEquals(200, $response->getStatusCode());
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['schedule']));
		$this->assertEquals(true,isset($body['schedule']['id']));
		$this->assertEquals(21,$body['schedule']['id']);

		$this->assertEquals(true,isset($body['schedule']['date']));
		$this->assertEquals('30-6-2027',$body['schedule']['date']);
		$this->assertEquals(true,isset($body['schedule']['description']));
		$this->assertEquals('Carro come gelados com a boca',$body['schedule']['description']);
		$this->assertEquals(true,isset($body['schedule']['car_id']));
		$this->assertEquals(3,$body['schedule']['car_id']);
		$this->assertEquals(true,isset($body['schedule']['car_model_id']));
		$this->assertEquals(2,$body['schedule']['car_model_id']);
		$this->assertEquals(true,isset($body['schedule']['client_id']));
		$this->assertEquals(5,$body['schedule']['client_id']);
	}

	public function testPUTSchedulesIDBadRequest(){
		printf("\n PUT Schedules Invalid Field: ");
		$response = $this->client->put('/api/schedules/21',
			[
				'json' => [
					'da' => 'Cool',
					'description' => 'Cool',
				]
			]);
		
		$this->assertEquals(400, $response->getStatusCode());

		$response = $this->client->put('/api/schedules/21',
			[
				'json' => [
					'date' => 'Cool',
					'descr' => 'Cool',
				]
			]);
		
		$this->assertEquals(400, $response->getStatusCode());

		printf("\n PUT Schedules Incoherent Field: ");
		$response = $this->client->put('/api/schedules/21',
			[
				'json' => [
					'date' => '30-6-2026',
					'description' => 'Carro come gelados com a testa',
					'car_id' =>1,
					'model_id' =>2,
					'client_id' =>1,
				]
			]);
		
		$this->assertEquals(400, $response->getStatusCode());
	}

	public function testDELETESchduleID(){
		printf("\n DELETE Cars/Id regular: ");
		$response = $this->client->delete("/api/schedules/21");
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['schedule']));
	}


	public function testDELETEcarsIDInvalidID(){
		printf("\n DELETE Cars/Id Invalid ID: ");
		$response = $this->client->delete("/api/schedules/30");
		$this->assertEquals(404, $response->getStatusCode());
	}
}
