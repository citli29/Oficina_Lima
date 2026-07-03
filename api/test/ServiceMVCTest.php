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

		//Check-in
		printf("\n GET Services Filter checkin 0: ");

		$response = $this->client->get('/api/services?checkin=OOOO');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['service_list']));
		$this->assertEquals(0,count($body['service_list']));

		printf("\n GET Services Filter checkin 1: ");

		$response = $this->client->get('/api/services?checkin=2025-02');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['service_list']));
		$this->assertEquals(1,count($body['service_list']));
		
		printf("\n GET Services Filter checkin multiple: ");

		$response = $this->client->get('/api/services?checkin=2025-05-06');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['service_list']));
		$this->assertEquals(3,count($body['service_list']));

		//Check-out
		printf("\n GET Services Filter checkout 0: ");

		$response = $this->client->get('/api/services?checkout=OOOO');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['service_list']));
		$this->assertEquals(0,count($body['service_list']));

		printf("\n GET Services Filter checkout 1: ");

		$response = $this->client->get('/api/services?checkout=2025-02-06');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['service_list']));
		$this->assertEquals(1,count($body['service_list']));
		
		printf("\n GET Services Filter checkin multiple: ");

		$response = $this->client->get('/api/services?checkout=2025-05-10');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['service_list']));
		$this->assertEquals(3,count($body['service_list']));
		
		//Car plate
		printf("\n GET Services Filter checkout 0: ");

		$response = $this->client->get('/api/services?car_plate=OOOO');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['service_list']));
		$this->assertEquals(0,count($body['service_list']));

		printf("\n GET Services Filter checkout 1: ");

		$response = $this->client->get('/api/services?car_plate=00-01');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['service_list']));
		$this->assertEquals(1,count($body['service_list']));
		
		printf("\n GET Services Filter checkout multiple: ");

		$response = $this->client->get('/api/services?car_plate=00-00');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['service_list']));
		$this->assertEquals(2,count($body['service_list']));

		//Car model
		printf("\n GET Services Filter Car Model 0: ");

		$response = $this->client->get('/api/services?car_model=OOOO');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['service_list']));
		$this->assertEquals(0,count($body['service_list']));

		printf("\n GET Services Filter Car Model 1: ");

		$response = $this->client->get('/api/services?car_model=megane');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['service_list']));
		$this->assertEquals(1,count($body['service_list']));
		
		printf("\n GET Services Filter Car Model multiple: ");

		$response = $this->client->get('/api/services?car_model=clio');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['service_list']));
		$this->assertEquals(3,count($body['service_list']));

		//Car make
		printf("\n GET Services Filter Car Make 0: ");

		$response = $this->client->get('/api/services?car_make=OOOO');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['service_list']));
		$this->assertEquals(0,count($body['service_list']));

		printf("\n GET Services Filter Car Make 1: ");

		$response = $this->client->get('/api/services?car_make=opel');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['service_list']));
		$this->assertEquals(1,count($body['service_list']));
		
		printf("\n GET Services Filter Car Model multiple: ");

		$response = $this->client->get('/api/services?car_make=renault');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['service_list']));
		$this->assertEquals(6,count($body['service_list']));
	
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

	public function testGETServicesID(){
		$response = $this->client->post("/api/clients",[
				'json' => [
					'name' => 'ClientEx',
					'phone' => '91111',
					'address' => 'Gang St.',
					'email' => 'em@em.com',
					'zip_code' => '4500-000',
					'tax_nr' => '222000111',
				]
			]);
		$this->assertEquals(201, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['client']));
		$this->assertEquals(true,isset($body['client']['id']));
		$cId = $body['client']['id'];

		$response = $this->client->post("/api/cars",[
				'json' => [
					'plate' => '04-20-WI',
					'month' => 4,
					'year' => 2020,
					'chassi_nr' => 'Chassi NR',
					'cc' => '1500',
					'engine_code' => 'Engine COD',
					'color_code' => 'Color COD',
					'model_id' => 1,
					'make_id' => 1,
				]
			]);
		$this->assertEquals(201, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['car']));
		$this->assertEquals(true,isset($body['car']['id']));
		$carId = $body['car']['id'];

		$response = $this->client->post("/api/services",[
				'json' => [
					'client_id' => $cId,
					'kms' => 420000,
					'checkin' => '2026-04-20',
					'checkout' => '2026-04-20',
					'malfunction' => 'serious damage',
					'service' => 'serious service',
					'car_id' => $carId,
					'schedule_id' => 1,
				]
			]);

		$this->assertEquals(201, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['service']));
		$this->assertEquals(true,isset($body['service']['id']));
		$serviceId = $body['service']['id'];

		printf("\n GET Clients/Id regular: ");
		$response = $this->client->get("/api/services/{$serviceId}");
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);

		$this->assertEquals(true,isset($body['service']));
		$this->assertEquals(true,isset($body['service']['id']));
		$this->assertEquals($serviceId,$body['service']['id']);
		$this->assertEquals(true,isset($body['service']['checkin']));
		$this->assertEquals("2026-04-20",$body['service']['checkin']);
		$this->assertEquals(true,isset($body['service']['checkout']));
		$this->assertEquals("2026-04-20",$body['service']['checkout']);
		$this->assertEquals(true,isset($body['service']['malfunction']));
		$this->assertEquals("serious damage",$body['service']['malfunction']);
		$this->assertEquals(true,isset($body['service']['service']));
		$this->assertEquals("serious service",$body['service']['service']);
		$this->assertEquals(true,isset($body['service']['checkout']));
		$this->assertEquals("2026-04-20",$body['service']['checkout']);

		$this->assertEquals(true,isset($body['service']['client_id']));
		$this->assertEquals($cId,$body['service']['client_id']);
		$this->assertEquals(true,isset($body['service']['client_name']));
		$this->assertEquals("ClientEx",$body['service']['client_name']);
		$this->assertEquals(true,isset($body['service']['client_address']));
		$this->assertEquals("Gang St.",$body['service']['client_address']);
		$this->assertEquals(true,isset($body['service']['client_email']));
		$this->assertEquals("em@em.com",$body['service']['client_email']);
		$this->assertEquals(true,isset($body['service']['client_zip_code']));
		$this->assertEquals("4500-000",$body['service']['client_zip_code']);
		$this->assertEquals(true,isset($body['service']['client_tax_nr']));
		$this->assertEquals("222000111",$body['service']['client_tax_nr']);

		$this->assertEquals(true,isset($body['service']['car_id']));
		$this->assertEquals($carId,$body['service']['car_id']);
		$this->assertEquals(true,isset($body['service']['car_chassi_nr']));
		$this->assertEquals("Chassi NR",$body['service']['car_chassi_nr']);
		$this->assertEquals(true,isset($body['service']['car_cc']));
		$this->assertEquals("1500",$body['service']['car_cc']);
		$this->assertEquals(true,isset($body['service']['car_make_name']));
		$this->assertEquals("Renault",$body['service']['car_make_name']);
		$this->assertEquals(true,isset($body['service']['car_model_name']));
		$this->assertEquals("Clio",$body['service']['car_model_name']);
		$this->assertEquals(true,isset($body['service']['car_month']));
		$this->assertEquals(4,$body['service']['car_month']);
		$this->assertEquals(true,isset($body['service']['car_year']));
		$this->assertEquals(2020,$body['service']['car_year']);
		$this->assertEquals(true,isset($body['service']['kms']));
		$this->assertEquals(420000,$body['service']['kms']);
		$this->assertEquals(true,isset($body['service']['car_engine_code']));
		$this->assertEquals("Engine COD",$body['service']['car_engine_code']);
		$this->assertEquals(true,isset($body['service']['car_color_code']));
		$this->assertEquals("Color COD",$body['service']['car_color_code']);
		$this->assertEquals(true,isset($body['service']['car_plate']));
		$this->assertEquals("04-20-WI",$body['service']['car_plate']);
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
		$this->assertEquals(200, $response->getStatusCode());
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

		printf("\n PUT Services regular: ");
		$response = $this->client->put('/api/services/21',
			[
				'json' => [
					'client_id' => '2',
					'kms' => 100001,
					'checkin' => '2026-06-02',
					'checkout' => '2026-06-09',
					'malfunction' => 'Carro chia muito',
					'service' => 'Tirou lhe o chio todo',
					'car_id' => 2,
				]
			]);
		
		$body = json_decode($response->getBody(), true);
		$this->assertEquals(200, $response->getStatusCode());
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['service']));
		$this->assertEquals(true,isset($body['service']['id']));
		$this->assertEquals(21,$body['service']['id']);
		$this->assertEquals(true,isset($body['service']['kms']));
		$this->assertEquals(100001,$body['service']['kms']);
		$this->assertEquals(true,isset($body['service']['checkin']));
		$this->assertEquals('2026-06-02',$body['service']['checkin']);
		$this->assertEquals(true,isset($body['service']['checkout']));
		$this->assertEquals('2026-06-09',$body['service']['checkout']);

		$this->assertEquals(true,isset($body['service']['malfunction']));
		$this->assertEquals( 'Carro chia muito',$body['service']['malfunction']);
		$this->assertEquals(true,isset($body['service']['service']));
		$this->assertEquals( 'Tirou lhe o chio todo',$body['service']['service']);
		$this->assertEquals(true,isset($body['service']['car_id']));
		$this->assertEquals(2,$body['service']['car_id']);
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

	public function testDELETEServicesID(){
		printf("\n DELETE Services/Id regular: ");
		$response = $this->client->delete("/api/services/21");
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['service']));
		$response = $this->client->delete("/api/services/21");
		$this->assertEquals(404, $response->getStatusCode());
	}

	public function testDELETEServicesIDInvalidID(){
		printf("\n DELETE Service/Id Invalid ID: ");
		$response = $this->client->delete("/api/services/55");
		$this->assertEquals(404, $response->getStatusCode());
	}
}
