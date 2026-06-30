<?php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

class CarMVCTest extends TestCase 
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

	public function testGETmakes(){
		printf("\n GET Makes regular: ");
		$response = $this->client->get('/api/makes');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['make_list']));
		$this->assertEquals(4,count($body['make_list']));
	}

	public function testGETmakesWithFilters(){
		printf("\n GET Makes Filter name 1: ");
		$response = $this->client->get('/api/makes?name=opel');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['make_list']));
		$this->assertEquals(1,count($body['make_list']));

		printf("\n GET Makes Filter name 0: ");
		$response = $this->client->get('/api/makes?name=oooo');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['make_list']));
		$this->assertEquals(0,count($body['make_list']));

		printf("\n GET Makes Filter name multiple: ");
		$response = $this->client->get('/api/makes?name=o');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['make_list']));
		$this->assertEquals(2,count($body['make_list']));
	}

	public function testPOSTmakes(){
		printf("\n POST Makes regular: ");
		$response = $this->client->post('/api/makes',
			[
				'json' => [
					'name' => 'CoolMake',
				]
			]);
		
		$body = json_decode($response->getBody(), true);
		$this->assertEquals(201, $response->getStatusCode());
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['make']));
		$this->assertEquals('CoolMake',$body['make']['name']);
		$this->assertEquals(true,isset($body['make']['id']));
		$this->assertEquals(5,$body['make']['id']);
	}

	public function testPOSTmakesBadRequest(){
		printf("\n POST Makes Invalid Field: ");
		$response = $this->client->post('/api/makes',
			[
				'json' => [
					'na' => 'Cool',
				]
			]);
		
		$this->assertEquals(400, $response->getStatusCode());

		printf("\n POST Makes Duplicated Field: ");
		$response = $this->client->post('/api/makes',
			[
				'json' => [
					'name' => 'CoolMake',
				]
			]);
		
		$this->assertEquals(400, $response->getStatusCode());
	}

	public function testGETmakesID(){
		printf("\n GET Makes/Id regular: ");
		$response = $this->client->get("/api/makes/5");
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['make']));
	}

	public function testGETmakesIDInvalidID(){
		printf("\n GET Makes/Id Invalid ID: ");
		$response = $this->client->get("/api/makes/10");
		$this->assertEquals(404, $response->getStatusCode());
	}

	public function testPUTmakes(){
		printf("\n PUT Makes regular same(name): ");
		$response = $this->client->put('/api/makes/5',
			[
				'json' => [
					'name' => 'CoolMake',
				]
			]);
		
		$body = json_decode($response->getBody(), true);
		$this->assertEquals(200, $response->getStatusCode());
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['make']));
		$this->assertEquals('CoolMake',$body['make']['name']);
		$this->assertEquals(true,isset($body['make']['id']));
		$this->assertEquals(5,$body['make']['id']);

		printf("\n PUT Makes regular: ");
		$response = $this->client->put('/api/makes/5',
			[
				'json' => [
					'name' => 'VeryCoolMake',
				]
			]);
		
		$body = json_decode($response->getBody(), true);
		$this->assertEquals(200, $response->getStatusCode());
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['make']));
		$this->assertEquals('VeryCoolMake',$body['make']['name']);
		$this->assertEquals(true,isset($body['make']['id']));
		$this->assertEquals(5,$body['make']['id']);
	}

	public function testPUTmakesBadRequest(){
		printf("\n PUT Makes Invalid Field: ");
		$response = $this->client->put('/api/makes/5',
			[
				'json' => [
					'na' => 'Cool',
				]
			]);
		
		$this->assertEquals(400, $response->getStatusCode());

		printf("\n PUT Makes Duplicated Field: ");
		$response = $this->client->post('/api/makes',
			[
				'json' => [
					'name' => 'Opel',
				]
			]);
		
		$this->assertEquals(400, $response->getStatusCode());
	}

	public function testDELETEmakesID(){
		printf("\n DELETE Makes/Id regular: ");
		$response = $this->client->delete("/api/makes/5");
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['make']));
	}

	public function testDELETEmakesIDInvalidID(){
		printf("\n DELETE Makes/Id Invalid ID: ");
		$response = $this->client->delete("/api/makes/10");
		$this->assertEquals(404, $response->getStatusCode());
	}

	public function testGETModels(){
		printf("\n GET Models regular: ");
		$response = $this->client->get('/api/models');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['model_list']));
		$this->assertEquals(12,count($body['model_list']));
	}

	public function testGETModelsWithFilters(){
		printf("\n GET Models Filter name 1: ");
		$response = $this->client->get('/api/models?name=Astra');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['model_list']));
		$this->assertEquals(1,count($body['model_list']));

		printf("\n GET Models Filter name 0: ");
		$response = $this->client->get('/api/models?name=oooo');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['model_list']));
		$this->assertEquals(0,count($body['model_list']));

		printf("\n GET Models Filter name multiple: ");
		$response = $this->client->get('/api/models?name=o');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['model_list']));
		$this->assertEquals(4,count($body['model_list']));

		printf("\n GET Models Filter name & make_name multiple: ");
	}

	public function testPOSTmodels(){
		printf("\n POST Models regular: ");
		$response = $this->client->post('/api/models',
			[
				'json' => [
					'name' => 'CoolModel',
					'make_id' => '1',
				]
			]);
		
		$body = json_decode($response->getBody(), true);
		$this->assertEquals(201, $response->getStatusCode());
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['model']));
		$this->assertEquals(true,isset($body['model']['id']));
		$this->assertEquals(13,$body['model']['id']);

		$this->assertEquals(true,isset($body['model']['name']));
		$this->assertEquals('CoolModel',$body['model']['name']);
		$this->assertEquals(true,isset($body['model']['make_id']));
		$this->assertEquals(1,$body['model']['make_id']);
	}

	public function testPOSTModelsBadRequest(){
		printf("\n POST Models Invalid Fields: ");
		$response = $this->client->post('/api/models',
			[
				'json' => [
					'na' => 'Cool',
					'make_id' => '1',
				]
			]);
		
		$this->assertEquals(400, $response->getStatusCode());

		$response = $this->client->post('/api/models',
			[
				'json' => [
					'name' => 'Cool',
					'ma' => '1',
				]
			]);
		
		$this->assertEquals(400, $response->getStatusCode());

		printf("\n POST Models Duplicated Field: ");
		$response = $this->client->post('/api/models',
			[
				'json' => [
					'name' => 'Clio',
					'make_id' => '1',
				]
			]);
		
		$this->assertEquals(400, $response->getStatusCode());
	}

	public function testGETModelsID(){
		printf("\n GET Models/Id regular: ");
		$response = $this->client->get("/api/models/13");
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['model']));
	}

	public function testGETModelIDInvalidID(){
		printf("\n GET Makes/Id Invalid ID: ");
		$response = $this->client->get("/api/models/20");
		$this->assertEquals(404, $response->getStatusCode());
	}

	public function testPUTmodels(){
		printf("\n PUT Models regular same(name,make_id): ");
		$response = $this->client->put('/api/models/13',
			[
				'json' => [
					'name' => 'CoolModel',
					'make_id' => '1',
				]
			]);
		
		$body = json_decode($response->getBody(), true);
		$this->assertEquals(200, $response->getStatusCode());
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['model']));
		$this->assertEquals(true,isset($body['model']['id']));
		$this->assertEquals(13,$body['model']['id']);

		$this->assertEquals(true,isset($body['model']['name']));
		$this->assertEquals('CoolModel',$body['model']['name']);
		$this->assertEquals(true,isset($body['model']['make_id']));
		$this->assertEquals(1,$body['model']['make_id']);

		printf("\n PUT Models regular: ");
		$response = $this->client->put('/api/models/13',
			[
				'json' => [
					'name' => 'VeryCoolMake',
					'make_id' => '1',
				]
			]);
		
		$body = json_decode($response->getBody(), true);
		$this->assertEquals(200, $response->getStatusCode());
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['model']));
		$this->assertEquals(true,isset($body['model']['id']));
		$this->assertEquals(13,$body['model']['id']);

		$this->assertEquals(true,isset($body['model']['name']));
		$this->assertEquals('VeryCoolMake',$body['model']['name']);
		$this->assertEquals(true,isset($body['model']['make_id']));
		$this->assertEquals(1,$body['model']['make_id']);

		$response = $this->client->put('/api/models/13',
			[
				'json' => [
					'name' => 'VeryCoolMake',
					'make_id' => '2',
				]
			]);
		
		$body = json_decode($response->getBody(), true);
		$this->assertEquals(200, $response->getStatusCode());
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['model']));
		$this->assertEquals(true,isset($body['model']['id']));
		$this->assertEquals(13,$body['model']['id']);

		$this->assertEquals(true,isset($body['model']['name']));
		$this->assertEquals('VeryCoolMake',$body['model']['name']);
		$this->assertEquals(true,isset($body['model']['make_id']));
		$this->assertEquals(2,$body['model']['make_id']);
	}

	public function testPUTmodelsBadRequest(){
		printf("\n PUT Models Invalid Field: ");
		$response = $this->client->put('/api/models/13',
			[
				'json' => [
					'na' => 'Cool',
					'make_id' => '1',
				]
			]);
		
		$this->assertEquals(400, $response->getStatusCode());

		$response = $this->client->put('/api/models/13',
			[
				'json' => [
					'name' => 'Cool',
					'ma' => '1',
				]
			]);
		
		$this->assertEquals(400, $response->getStatusCode());
		
		printf("\n PUT Models Duplicated Field: ");
		$response = $this->client->put('/api/models/13',
			[
				'json' => [
					'name' => 'Clio',
					'make_id' => '1',
				]
			]);
		
		$this->assertEquals(400, $response->getStatusCode());
	}

	
	public function testDELETEmodelsID(){
		printf("\n DELETE Makes/Id regular: ");
		$response = $this->client->delete("/api/models/13");
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['model']));
	}


	public function testDELETEmodelsIDInvalidID(){
		printf("\n DELETE Makes/Id Invalid ID: ");
		$response = $this->client->delete("/api/models/20");
		$this->assertEquals(404, $response->getStatusCode());
	}

	public function testGETCars(){
		printf("\n GET Cars regular: ");
		$response = $this->client->get('/api/cars');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['car_list']));
		$this->assertEquals(13,count($body['car_list']));
	}

	public function testGETCarsWithFilters(){
		printf("\n GET Cars Filter plate 1: ");
		$response = $this->client->get('/api/cars?plate=AB-00-00');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['car_list']));
		$this->assertEquals(1,count($body['car_list']));

		printf("\n GET Cars plate 0: ");
		$response = $this->client->get('/api/cars?plate=ABABABA');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['car_list']));
		$this->assertEquals(0,count($body['car_list']));

		printf("\n GET Cars Filter plate multiple: ");
		$response = $this->client->get('/api/cars?plate=00-0');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['car_list']));
		$this->assertEquals(10,count($body['car_list']));

		printf("\n GET Cars Filter year 1: ");
		$response = $this->client->get('/api/cars?year=2002');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['car_list']));
		$this->assertEquals(1,count($body['car_list']));

		printf("\n GET Cars year 0: ");
		$response = $this->client->get('/api/cars?year=1999');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['car_list']));
		$this->assertEquals(0,count($body['car_list']));

		printf("\n GET Cars Filter year multiple: ");
		$response = $this->client->get('/api/cars?year=2004');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['car_list']));
		$this->assertEquals(4,count($body['car_list']));

		printf("\n GET Cars Filter month 1: ");
		$response = $this->client->get('/api/cars?month=3');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['car_list']));
		$this->assertEquals(1,count($body['car_list']));

		printf("\n GET Cars month 0: ");
		$response = $this->client->get('/api/cars?month=2');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['car_list']));
		$this->assertEquals(0,count($body['car_list']));

		printf("\n GET Cars Filter month multiple: ");
		$response = $this->client->get('/api/cars?month=11');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['car_list']));
		$this->assertEquals(2,count($body['car_list']));

		printf("\n GET Cars Filter model_name 1: ");
		$response = $this->client->get('/api/cars?model_name=Express');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['car_list']));
		$this->assertEquals(1,count($body['car_list']));

		printf("\n GET Cars model_name 0: ");
		$response = $this->client->get('/api/cars?model_name=Lagun');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['car_list']));
		$this->assertEquals(0,count($body['car_list']));

		printf("\n GET Cars Filter model_name multiple: ");
		$response = $this->client->get('/api/cars?model_name=Cli');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['car_list']));
		$this->assertEquals(2,count($body['car_list']));

		printf("\n GET Cars Filter make_name 1: ");
		$response = $this->client->get('/api/cars?make_name=Opel');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['car_list']));
		$this->assertEquals(1,count($body['car_list']));

		printf("\n GET Cars make_name 0: ");
		$response = $this->client->get('/api/cars?make_name=AAAAA');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['car_list']));
		$this->assertEquals(0,count($body['car_list']));

		printf("\n GET Cars Filter make_name multiple: ");
		$response = $this->client->get('/api/cars?make_name=Renault');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['car_list']));
		$this->assertEquals(4,count($body['car_list']));
	}

	public function testPOSTCars(){
		printf("\n POST Cars regular: ");
		$response = $this->client->post('/api/cars',
			[
				'json' => [
					'plate' => 'AB-00-99',
					'month' => 12,
					'year' => 2012,
					'chassi_nr' => 'WVWZZZ1KZAW123456',
					'cc' => '1600',
					'engine_code' => 'K9K 608',
					'model_id' => '1',
					'make_id' => '1',
				]
			]);
		
		$body = json_decode($response->getBody(), true);
		$this->assertEquals(201, $response->getStatusCode());
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['car']));
		$this->assertEquals(true,isset($body['car']['id']));
		$this->assertEquals(14,$body['car']['id']);

		$this->assertEquals(true,isset($body['car']['plate']));
		$this->assertEquals('AB-00-99',$body['car']['plate']);
		$this->assertEquals(true,isset($body['car']['month']));
		$this->assertEquals(12,$body['car']['month']);
		$this->assertEquals(true,isset($body['car']['year']));
		$this->assertEquals(2012,$body['car']['year']);
		$this->assertEquals(true,isset($body['car']['chassi_nr']));
		$this->assertEquals('WVWZZZ1KZAW123456',$body['car']['chassi_nr']);
		$this->assertEquals(true,isset($body['car']['cc']));
		$this->assertEquals('1600',$body['car']['cc']);
		$this->assertEquals(true,isset($body['car']['engine_code']));
		$this->assertEquals('K9K 608',$body['car']['engine_code']);
		$this->assertEquals(true,isset($body['car']['model_id']));
		$this->assertEquals('1',$body['car']['model_id']);
		$this->assertEquals(true,isset($body['car']['make_id']));
		$this->assertEquals('1',$body['car']['make_id']);

	}

	public function testPOSTCarsBadRequest(){
		printf("\n POST Cars Invalid Fields: ");
		$response = $this->client->post('/api/cars',
			[
				'json' => [
					'pla' => 'AB',
					'make_id' => '1',
				]
			]);
		
		$this->assertEquals(400, $response->getStatusCode());

		$response = $this->client->post('/api/models',
			[
				'json' => [
					'plate' => 'AB-00-10',
					'ma' => '1',
				]
			]);
		
		$this->assertEquals(400, $response->getStatusCode());

		printf("\n POST Cars Duplicated Field: ");
		$response = $this->client->post('/api/models',
			[
				'json' => [
					'plate' => 'AB-00-00',
					'make_id' => '1',
				]
			]);
		
		$this->assertEquals(400, $response->getStatusCode());
	}

	public function testGETCarsID(){
		printf("\n GET Cars/Id regular: ");
		$response = $this->client->get("/api/cars/8");
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['car']));
		$this->assertEquals(true,isset($body['car']['id']));
		$this->assertEquals(8,$body['car']['id']);
	}

	public function testGETCarsIDInvalidID(){
		printf("\n GET Cars/Id Invalid ID: ");
		$response = $this->client->get("/api/models/99");
		$this->assertEquals(404, $response->getStatusCode());
	}

	public function testPUTCars(){
		printf("\n PUT Cars regular same(plate,year,month, make_id, model_id): ");
		$response = $this->client->put('/api/cars/9',
			[
				'json' => [
					'plate' => 'AB-00-08',
					'year' => '2007',
					'month' => '11',
					'make_id' => '3',
					'model_id' => '8',
				]
			]);
		
		$body = json_decode($response->getBody(), true);
		$this->assertEquals(200, $response->getStatusCode());
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['car']));
		$this->assertEquals(true,isset($body['car']['id']));
		$this->assertEquals(9,$body['car']['id']);

		$this->assertEquals(true,isset($body['car']['plate']));
		$this->assertEquals('AB-00-08',$body['car']['plate']);
		$this->assertEquals(true,isset($body['car']['year']));
		$this->assertEquals(2007,$body['car']['year']);
		$this->assertEquals(true,isset($body['car']['month']));
		$this->assertEquals(11,$body['car']['month']);
		$this->assertEquals(true,isset($body['car']['make_id']));
		$this->assertEquals(3,$body['car']['make_id']);
		$this->assertEquals(true,isset($body['car']['model_id']));
		$this->assertEquals(8,$body['car']['model_id']);

		printf("\n PUT Cars regular: ");
		$response = $this->client->put('/api/cars/9',
			[
				'json' => [
					'plate' => 'AB-00-90',
					'year' => '2017',
					'month' => '10',
					'make_id' => '1',
					'model_id' => '3',
				]
			]);
		
		$body = json_decode($response->getBody(), true);
		$this->assertEquals(200, $response->getStatusCode());
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['car']));
		$this->assertEquals(true,isset($body['car']['id']));
		$this->assertEquals(9,$body['car']['id']);

		$this->assertEquals(true,isset($body['car']['plate']));
		$this->assertEquals('AB-00-90',$body['car']['plate']);
		$this->assertEquals(true,isset($body['car']['year']));
		$this->assertEquals(2017,$body['car']['year']);
		$this->assertEquals(true,isset($body['car']['month']));
		$this->assertEquals(10,$body['car']['month']);
		$this->assertEquals(true,isset($body['car']['make_id']));
		$this->assertEquals(1,$body['car']['make_id']);
		$this->assertEquals(true,isset($body['car']['model_id']));
		$this->assertEquals(3,$body['car']['model_id']);
	}

	public function testPUTCarsBadRequest(){
		printf("\n PUT Cars Invalid Field: ");
		$response = $this->client->put('/api/cars/9',
			[
				'json' => [
					'pla' => '',
					'make_id' => '1',
				]
			]);
		
		$this->assertEquals(400, $response->getStatusCode());

		$response = $this->client->put('/api/cars/9',
			[
				'json' => [
					'pla' => '',
					'make_id' => '1',
				]
			]);
		
		printf("\n PUT Cars Duplicated Field: ");
		$response = $this->client->put('/api/models/13',
			[
				'json' => [
					'plate' => 'AB-00-00',
					'make_id' => '1',
				]
			]);
		
		$this->assertEquals(400, $response->getStatusCode());
	}

	
	public function testDELETECarsID(){
		printf("\n DELETE Cars/Id regular: ");
		$response = $this->client->delete("/api/cars/9");
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['car']));
	}


	public function testDELETEcarsIDInvalidID(){
		printf("\n DELETE Cars/Id Invalid ID: ");
		$response = $this->client->delete("/api/cars/20");
		$this->assertEquals(404, $response->getStatusCode());
	}

}
?>
