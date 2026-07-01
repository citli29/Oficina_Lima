<?php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

class ServiceMVCTest extends TestCase 
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

	public function testGETServices(){
		printf("\n GET Services regular: ");
		$response = $this->client->get('/api/services');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['service_list']));
		$this->assertEquals(20,count($body['service_list']));
	}
	public function testGETServicesWithFilters(){

		//Client Name
		printf("\n GET Services Filter client_name 0: ");

		$response = $this->client->get('/api/services?client_name=OOOO');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['service_list']));
		$this->assertEquals(0,count($body['service_list']));

		printf("\n GET Services Filter client_name 1: ");

		$response = $this->client->get('/api/services?client_name=Maria');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['service_list']));
		$this->assertEquals(1,count($body['service_list']));
		
		printf("\n GET Services Filter client_name multiple: ");

		$response = $this->client->get('/api/services?client_name=Antonio');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['service_list']));
		$this->assertEquals(2,count($body['service_list']));
	}

	
	public function testPOSTServices(){
		printf("\n POST Services regular: ");
		$response = $this->client->post('/api/services',
			[
				'json' => [
					'client_id' => '1',
					'kms' => 100000,
					'checkin' => '2026-06-01',
					'checkout' => '2026-06-08',
					'malfunction' => 'Carro chia',
					'service' => 'Tirou lhe o chio',
					'car_id' => 1,
				]
			]);
		
		$body = json_decode($response->getBody(), true);
		$this->assertEquals(201, $response->getStatusCode());
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['service']));
		$this->assertEquals(true,isset($body['service']['id']));
		$this->assertEquals(21,$body['service']['id']);
		$this->assertEquals(true,isset($body['service']['kms']));
		$this->assertEquals(100000,$body['service']['kms']);
		$this->assertEquals(true,isset($body['service']['checkin']));
		$this->assertEquals('2026-06-01',$body['service']['checkin']);
		$this->assertEquals(true,isset($body['service']['checkout']));
		$this->assertEquals('2026-06-08',$body['service']['checkout']);

		$this->assertEquals(true,isset($body['service']['malfunction']));
		$this->assertEquals( 'Carro chia',$body['service']['malfunction']);
		$this->assertEquals(true,isset($body['service']['service']));
		$this->assertEquals( 'Tirou lhe o chio',$body['service']['service']);
		$this->assertEquals(true,isset($body['service']['car_id']));
		$this->assertEquals(1,$body['service']['car_id']);
	}

	public function testPOSTServicesBadRequest(){

		printf("\n POST Services Invalid Field: ");
		$response = $this->client->post('/api/services',
			[
				'json' => [
					'cli' => '1',
					'checkin' => '2026-05-01',
				]
			]);
		
		$this->assertEquals(400, $response->getStatusCode());

		printf("\n POST Services Invalid Field No kms Checkout: ");
		$response = $this->client->post('/api/services',
			[
				'json' => [
					'client_id' => '1',
					'car_id' => '1',
					'checkin' => '2026-05-01',
					'checkout' => '2026-05-08',
				]
			]);
		
		$this->assertEquals(400, $response->getStatusCode());

		printf("\n POST Services Invalid Field Checkout < Checkin: ");
		$response = $this->client->post('/api/services',
			[
				'json' => [
					'client_id' => '1',
					'checkin' => '2026-05-08',
					'checkout' => '2026-05-01',
				]
			]);
		
		$this->assertEquals(400, $response->getStatusCode());

		printf("\n POST Services Invalid Field: ");
		$response = $this->client->post('/api/services',
			[
				'json' => [
					'client_id' => '1',
					'check' => '01-05-2026',
				]
			]);
		
		$this->assertEquals(400, $response->getStatusCode());

	}
/*

	public function testGETServicesID(){
		printf("\n GET Clients/Id regular: ");
		$response = $this->client->get("/api/services/5");
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);

		$this->assertEquals(true,isset($body['service']));
		$this->assertEquals(true,isset($body['service']['id']));
		$this->assertEquals(21,$body['service']['id']);
		$this->assertEquals(true,isset($body['service']['checkin_date']));
		$this->assertEquals(true,isset($body['service']['checkout_date']));

		$this->assertEquals(true,isset($body['service']['client_id']));
		$this->assertEquals(true,isset($body['service']['client_name']));
		$this->assertEquals(true,isset($body['service']['client_address']));
		$this->assertEquals(true,isset($body['service']['client_email']));
		$this->assertEquals(true,isset($body['service']['client_zip_code']));
		$this->assertEquals(true,isset($body['service']['client_tax_nr']));

		$this->assertEquals(true,isset($body['service']['car_id']));
		$this->assertEquals(true,isset($body['service']['car_chassi_nr']));
		$this->assertEquals(true,isset($body['service']['car_cc']));
		$this->assertEquals(true,isset($body['service']['car_make_name']));
		$this->assertEquals(true,isset($body['service']['car_model_name']));
		$this->assertEquals(true,isset($body['service']['car_month']));
		$this->assertEquals(true,isset($body['service']['car_year']));
		$this->assertEquals(true,isset($body['service']['car_kms']));
		$this->assertEquals(true,isset($body['service']['car_engine_code']));
		$this->assertEquals(true,isset($body['service']['car_color_code']));
		$this->assertEquals(true,isset($body['service']['car_plate']));
	}

	public function testGETServicesIDInvalidID(){
		printf("\n GET Services/Id Invalid ID: ");
		$response = $this->client->get("/api/services/55");
		$this->assertEquals(404, $response->getStatusCode());
	}

	public function testPUTServices(){
		printf("\n PUT Service regular same(client_id): ");
		$response = $this->client->put('/api/services/21',
			[
				'json' => [
					'client_id' => '',
				]
			]);
		
		$body = json_decode($response->getBody(), true);
		$this->assertEquals(200, $response->getStatusCode());
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['services']));
		$this->assertEquals(true,isset($body['services']['id']));
		$this->assertEquals('21',$body['client']['name']);

		printf("\n PUT Services regular: ");
		$response = $this->client->put('/api/services/21',
			[
				'json' => [
					'client_id' => '',
				]
			]);
		
		$body = json_decode($response->getBody(), true);
		$this->assertEquals(200, $response->getStatusCode());
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['services']));
		$this->assertEquals(true,isset($body['services']['id']));
		$this->assertEquals(21,$body['services']['id']);
	}

	public function testPUTClientsBadRequest(){
		printf("\n PUT Services Invalid Field: ");
		$response = $this->client->put('/api/services/14',
			[
				'json' => [
					'cli' => '1',
				]
			]);
		
		$this->assertEquals(400, $response->getStatusCode());
	}

	*/
	public function testDELETEServicesID(){
		printf("\n DELETE Services/Id regular: ");
		$response = $this->client->delete("/api/services/21");
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['service']));
	}

	public function testDELETEServicesIDInvalidID(){
		printf("\n DELETE Service/Id Invalid ID: ");
		$response = $this->client->delete("/api/services/55");
		$this->assertEquals(404, $response->getStatusCode());
	}
}
