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
		printf("\n Get Makes regular: ");
		$response = $this->client->get('/api/makes');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['make_list']));
		$this->assertEquals(4,count($body['make_list']));
	}

	public function testGETmakesWithFilters(){
		printf("\n Get Makes Filter name 1: ");
		$response = $this->client->get('/api/makes?name=opel');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['make_list']));
		$this->assertEquals(1,count($body['make_list']));

		printf("\n Get Makes Filter name 0: ");
		$response = $this->client->get('/api/makes?name=oooo');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['make_list']));
		$this->assertEquals(0,count($body['make_list']));

		printf("\n Get Makes Filter name multiple: ");
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
		printf("\n Get Makes/Id regular: ");
		$response = $this->client->get("/api/makes/5");
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['make']));
	}

	public function testGETmakesIDInvalidID(){
		printf("\n Get Makes/Id Invalid ID: ");
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

		printf("\n Put Makes Duplicated Field: ");
		$response = $this->client->post('/api/makes',
			[
				'json' => [
					'name' => 'Opel',
				]
			]);
		
		$this->assertEquals(400, $response->getStatusCode());
	}

	public function testDELETEmakesID(){
		printf("\n Delete Makes/Id regular: ");
		$response = $this->client->delete("/api/makes/5");
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['make']));
	}

	public function testDELETEmakesIDInvalidID(){
		printf("\n Delete Makes/Id Invalid ID: ");
		$response = $this->client->delete("/api/makes/10");
		$this->assertEquals(404, $response->getStatusCode());
	}
	/*
	public function testGetCars(){
		$response = $this->client->get('/api/cars');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['car_list']));
		$this->assertEquals(13,count($body['car_list']));
	}

	public function testGetCarsWithFilter(){
		$response = $this->client->get('/api/cars?make_name=Renault');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$response = $this->client->get('/api/cars?model_name=Express');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		print_r($body);
		//$this->assertEquals(true,isset($body['car_list']));
		//$this->assertEquals(13,count($body['car_list']));
	}
	*/
}

?>
