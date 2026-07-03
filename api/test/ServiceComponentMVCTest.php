<?php

use App\Database\Database;
use App\Models\ServiceComponent;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

class ServiceComponentMVCTest extends TestCase 
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
	public function testGETBigSUTs(){
		printf("\n GET SUTs regular: ");
		$response = $this->client->get('/api/services_user_times');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['sut_list']));
		$this->assertEquals(79,count($body['sut_list']));
	}

	public function testGETBigSUTsWithFilters(){

		printf("\n GET SUT Filter service_id 1: ");
		$response = $this->client->get('/api/services_user_times?service_id=19');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['sut_list']));
		$this->assertEquals(1,count($body['sut_list']));

		printf("\n GET SUT Filter service_id 0: ");
		$response = $this->client->get('/api/services_user_times?service_id=300');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['sut_list']));
		$this->assertEquals(0,count($body['sut_list']));

		printf("\n GET SUT Filter service_id multiple: ");
		$response = $this->client->get('/api/services_user_times?service_id=2');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['sut_list']));
		$this->assertEquals(5,count($body['sut_list']));

		printf("\n GET SUT Filter user_name 0: ");
		$response = $this->client->get('/api/services_user_times?user_name=300');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['sut_list']));
		$this->assertEquals(0,count($body['sut_list']));

		printf("\n GET SUT Filter user_name multiple: ");
		$response = $this->client->get('/api/services_user_times?user_name=A');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['sut_list']));
		$this->assertEquals(26,count($body['sut_list']));

		printf("\n GET SUT Filter user_id 0: ");
		$response = $this->client->get('/api/services_user_times?minutes=10');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['sut_list']));
		$this->assertEquals(5,count($body['sut_list']));

		printf("\n GET SUT Filter user_id multiple: ");
		$response = $this->client->get('/api/services_user_times?minutes=1000');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['sut_list']));
		$this->assertEquals(0,count($body['sut_list']));

		printf("\n GET SUT Filter date 0: ");
		$response = $this->client->get('/api/services_user_times?date=2027');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['sut_list']));
		$this->assertEquals(0,count($body['sut_list']));

		printf("\n GET SUT Filter user_id multiple: ");
		$response = $this->client->get('/api/services_user_times?date=2025-02');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['sut_list']));
		$this->assertEquals(5,count($body['sut_list']));
	}

	public function testGETSUTsWithFilters(){
		printf("\n GET SUTs regular: ");
		$response = $this->client->get('/api/services/1/user_times');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['sut_list']));
		$this->assertEquals(4,count($body['sut_list']));
	}

	public function testGETClientsWithFilters(){

		printf("\n GET SUT Filter user_name 1: ");
		$response = $this->client->get('/api/services/2/user_times?user_name=B');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['sut_list']));
		$this->assertEquals(1,count($body['sut_list']));

		printf("\n GET SUT Filter user_name 0: ");
		$response = $this->client->get('/api/services/2/user_times?user_name=oooo');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['sut_list']));
		$this->assertEquals(0,count($body['sut_list']));

		printf("\n GET SUT Filter name multiple: ");
		$response = $this->client->get('/api/services/2/user_times?user_name=C');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['sut_list']));
		$this->assertEquals(2,count($body['sut_list']));

		//user_id
		printf("\n GET SUT Filter user_id 1: ");
		$response = $this->client->get('/api/services/2/user_times?user_id=5');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['sut_list']));
		$this->assertEquals(1,count($body['sut_list']));

		printf("\n GET SUT Filter user_id 0: ");
		$response = $this->client->get('/api/services/2/user_times?user_id=3000');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['sut_list']));
		$this->assertEquals(0,count($body['sut_list']));

		printf("\n GET SUT Filter user_id multiple: ");
		$response = $this->client->get('/api/services/2/user_times?user_id=6');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['sut_list']));
		$this->assertEquals(2,count($body['sut_list']));

		//user_id
		printf("\n GET SUT Filter date 1: ");
		$response = $this->client->get('/api/services/2/user_times?date=2025-02-06');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['sut_list']));
		$this->assertEquals(1,count($body['sut_list']));

		printf("\n GET SUT Filter date 0: ");
		$response = $this->client->get('/api/services/2/user_times?date=2025-02-07');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['sut_list']));
		$this->assertEquals(0,count($body['sut_list']));

		printf("\n GET SUT Filter date multiple: ");
		$response = $this->client->get('/api/services/2/user_times?date=2025-02-05');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['sut_list']));
		$this->assertEquals(4,count($body['sut_list']));
	}

	public function testPOSTSUT(){
		printf("\n POST SUT regular: ");
		$response = $this->client->post('/api/services/2/user_times',
			[
				'json' => [
					'user_id' => '4',
					'minutes' => '15',
					'date' => '2025-02-05'
				]
			]);
		
		$body = json_decode($response->getBody(), true);
		$this->assertEquals(201, $response->getStatusCode());
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['sut']));
		$this->assertEquals(true,isset($body['sut']['sut_id']));
		$this->assertEquals(6,$body['sut']['sut_id']);

		$this->assertEquals(true,isset($body['sut']['service_id']));
		$this->assertEquals(2,$body['sut']['service_id']);
		$this->assertEquals(true,isset($body['sut']['user_id']));
		$this->assertEquals(4,$body['sut']['user_id']);
		$this->assertEquals(true,isset($body['sut']['minutes']));
		$this->assertEquals(15,$body['sut']['minutes']);
		$this->assertEquals(true,isset($body['sut']['date']));
		$this->assertEquals('2025-02-05',$body['sut']['date']);
	}

	public function testPOSTClientsBadRequest(){
		printf("\n POST Clients Invalid Field: less then date ");
		$response = $this->client->post('/api/services/2/user_times',
			[
				'json' => [
					'user_id' => '1',
					'minutes' => '919',
					'date' => '2025-02-04',
				]
			]);
		
		$this->assertEquals(400, $response->getStatusCode());

		printf("\n POST Clients Invalid Field: greater then date");
		$response = $this->client->post('/api/services/2/user_times',
			[
				'json' => [
					'user_id' => '1',
					'minutes' => '919',
					'date' => '2025-02-07',
				]
			]);
		
		$this->assertEquals(400, $response->getStatusCode());

		printf("\n POST Clients Invalid Field: negative minutes");
		$response = $this->client->post('/api/services/2/user_times',
			[
				'json' => [
					'user_id' => '1',
					'minutes' => -1,
					'date' => '2025-02-06',
				]
			]);
		
		$this->assertEquals(400, $response->getStatusCode());

		printf("\n POST Clients Invalid Field: missing");
		$response = $this->client->post('/api/services/2/user_times',
			[
				'json' => [
					'minutes' => 0,
					'date' => '2025-02-06',
				]
			]);
		
		$this->assertEquals(400, $response->getStatusCode());
		$response = $this->client->post('/api/services/2/user_times',
			[
				'json' => [
					'user_id' => 0,
					'date' => '2025-02-06',
				]
			]);
		
		$this->assertEquals(400, $response->getStatusCode());
		$response = $this->client->post('/api/services/2/user_times',
			[
				'json' => [
					'user_id' => 0,
					'minutes' => 0,
				]
			]);
		
		$this->assertEquals(400, $response->getStatusCode());
	}

	public function testGETSUT(){
		printf("\n GET SUT/Id regular: ");
		$response = $this->client->get("/api/services/2/user_times/4");
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['sut']));
		$this->assertEquals(true,isset($body['sut']['service_id']));
		$this->assertEquals(2,$body['sut']['service_id']);
		$this->assertEquals(true,isset($body['sut']['sut_id']));
		$this->assertEquals(4,$body['sut']['sut_id']);
		$this->assertEquals(true,isset($body['sut']['id']));
		$this->assertEquals(8,$body['sut']['id']);
		$this->assertEquals(true,isset($body['sut']['minutes']));
		$this->assertEquals(10,$body['sut']['minutes']);
		$this->assertEquals(true,isset($body['sut']['date']));
		$this->assertEquals("2025-02-05",$body['sut']['date']);
		$this->assertEquals(true,isset($body['sut']['user_id']));
		$this->assertEquals("4",$body['sut']['user_id']);
		$this->assertEquals(true,isset($body['sut']['user_name']));
		$this->assertEquals("A",$body['sut']['user_name']);
	}

	public function testGETSUTIDInvalidID(){
		printf("\n GET SUT/Id Invalid ID: ");
		$response = $this->client->get("/api/services/2/user_times/105");
		$this->assertEquals(404, $response->getStatusCode());
	}

	public function testPUTSUT(){
		printf("\n POST PUT regular: (same values)");
		$response = $this->client->put('/api/services/2/user_times/6',
			[
				'json' => [
					'user_id' => '4',
					'minutes' => '15',
					'date' => '2025-02-05'
				]
			]);
		
		$body = json_decode($response->getBody(), true);
		$this->assertEquals(200, $response->getStatusCode());
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['sut']));
		$this->assertEquals(true,isset($body['sut']['sut_id']));
		$this->assertEquals(6,$body['sut']['sut_id']);

		$this->assertEquals(true,isset($body['sut']['service_id']));
		$this->assertEquals(2,$body['sut']['service_id']);
		$this->assertEquals(true,isset($body['sut']['user_id']));
		$this->assertEquals(4,$body['sut']['user_id']);
		$this->assertEquals(true,isset($body['sut']['minutes']));
		$this->assertEquals(15,$body['sut']['minutes']);
		$this->assertEquals(true,isset($body['sut']['date']));
		$this->assertEquals('2025-02-05',$body['sut']['date']);

		$response = $this->client->put('/api/services/2/user_times/6',
			[
				'json' => [
					'user_id' => '3',
					'minutes' => '10',
					'date' => '2025-02-06'
				]
			]);
		
		$body = json_decode($response->getBody(), true);
		$this->assertEquals(200, $response->getStatusCode());
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['sut']));
		$this->assertEquals(true,isset($body['sut']['sut_id']));
		$this->assertEquals(6,$body['sut']['sut_id']);

		$this->assertEquals(true,isset($body['sut']['service_id']));
		$this->assertEquals(2,$body['sut']['service_id']);
		$this->assertEquals(true,isset($body['sut']['user_id']));
		$this->assertEquals(3,$body['sut']['user_id']);
		$this->assertEquals(true,isset($body['sut']['minutes']));
		$this->assertEquals(10,$body['sut']['minutes']);
		$this->assertEquals(true,isset($body['sut']['date']));
		$this->assertEquals('2025-02-06',$body['sut']['date']);
	}

	public function testPUTClientsBadRequest(){
		printf("\n PUT Clients Invalid Field: less then date ");
		$response = $this->client->put('/api/services/2/user_times/6',
			[
				'json' => [
					'user_id' => '1',
					'minutes' => '919',
					'date' => '2025-02-04',
				]
			]);
		
		$this->assertEquals(400, $response->getStatusCode());

		printf("\n POST Clients Invalid Field: greater then date");
		$response = $this->client->put('/api/services/2/user_times/6',
			[
				'json' => [
					'user_id' => '1',
					'minutes' => '919',
					'date' => '2025-02-07',
				]
			]);
		
		$this->assertEquals(400, $response->getStatusCode());

		printf("\n POST Clients Invalid Field: negative minutes");
		$response = $this->client->put('/api/services/2/user_times/6',
			[
				'json' => [
					'user_id' => '1',
					'minutes' => -1,
					'date' => '2025-02-06',
				]
			]);
		
		$this->assertEquals(400, $response->getStatusCode());

		printf("\n POST Clients Invalid Field: missing");
		$response = $this->client->put('/api/services/2/user_times/6',
			[
				'json' => [
					'minutes' => 0,
					'date' => '2025-02-06',
				]
			]);
		
		$this->assertEquals(400, $response->getStatusCode());
		$response = $this->client->put('/api/services/2/user_times/6',
			[
				'json' => [
					'user_id' => 0,
					'date' => '2025-02-06',
				]
			]);
		
		$this->assertEquals(400, $response->getStatusCode());
		$response = $this->client->put('/api/services/2/user_times/6',
			[
				'json' => [
					'user_id' => 0,
					'minutes' => 0,
				]
			]);
		
		$this->assertEquals(400, $response->getStatusCode());
	}

	public function testDELETESUTID(){
		printf("\n DELETE SUT/Id regular: ");
		$response = $this->client->delete("/api/services/2/user_times/6");
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['sut']));

		$response = $this->client->delete("/api/services/2/user_times/6");
		$this->assertEquals(404, $response->getStatusCode());
	}

	public function testDELETEmakesIDInvalidID(){
		printf("\n DELETE SUT/Id Invalid ID: ");
		$response = $this->client->delete("/api/services/2/user_times/100");
		$this->assertEquals(404, $response->getStatusCode());
	}

	public function testGETBigSAPs(){
		printf("\n GET SAPs regular: ");
		$response = $this->client->get('/api/services_applied_products');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['sap_list']));
		$this->assertEquals(28,count($body['sap_list']));
	}

	public function testGETBigSAPsWithFilters(){

		//Service ID
		printf("\n GET SAP Filter service_id 1: ");
		$response = $this->client->get('/api/services_applied_products?service_id=3');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['sap_list']));
		$this->assertEquals(1,count($body['sap_list']));

		printf("\n GET SAP Filter service_id 0: ");
		$response = $this->client->get('/api/services_applied_products?service_id=300');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['sap_list']));
		$this->assertEquals(0,count($body['sap_list']));

		printf("\n GET SAP Filter service_id multiple: ");
		$response = $this->client->get('/api/services_applied_products?service_id=1');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['sap_list']));
		$this->assertEquals(3,count($body['sap_list']));

		//Product Name 
		printf("\n GET SAP Filter product_name 1: ");
		$response = $this->client->get('/api/services_applied_products?product_name=Bobina');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['sap_list']));
		$this->assertEquals(1,count($body['sap_list']));

		printf("\n GET SAP Filter product_name 0: ");
		$response = $this->client->get('/api/services_applied_products?product_name=GANGANG');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['sap_list']));
		$this->assertEquals(0,count($body['sap_list']));

		printf("\n GET SAP Filter product_name multiple: ");
		$response = $this->client->get('/api/services_applied_products?product_name=Oleo');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['sap_list']));
		$this->assertEquals(5,count($body['sap_list']));

		//Product reference 
		printf("\n GET SAP Filter product_reference 1: ");
		$response = $this->client->get('/api/services_applied_products?product_reference=BI440');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['sap_list']));
		$this->assertEquals(1,count($body['sap_list']));

		printf("\n GET SAP Filter product_reference 0: ");
		$response = $this->client->get('/api/services_applied_products?product_reference=GAN200200');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['sap_list']));
		$this->assertEquals(0,count($body['sap_list']));

		printf("\n GET SAP Filter product_reference multiple: ");
		$response = $this->client->get('/api/services_applied_products?product_reference=OM530');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['sap_list']));
		$this->assertEquals(3,count($body['sap_list']));

		//Product ID 
		printf("\n GET SAP Filter product_id 1: ");
		$response = $this->client->get('/api/services_applied_products?product_id=34');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['sap_list']));
		$this->assertEquals(1,count($body['sap_list']));

		printf("\n GET SAP Filter product_id 0: ");
		$response = $this->client->get('/api/services_applied_products?product_id=200200');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['sap_list']));
		$this->assertEquals(0,count($body['sap_list']));

		printf("\n GET SAP Filter product_id multiple: ");
		$response = $this->client->get('/api/services_applied_products?product_id=4');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['sap_list']));
		$this->assertEquals(3,count($body['sap_list']));

		//Product ID 
		printf("\n GET SAP Filter is_applied 1: ");
		$response = $this->client->get('/api/services_applied_products?is_applied=true');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['sap_list']));
		$this->assertEquals(22,count($body['sap_list']));

		printf("\n GET SAP Filter is_applied multiple: ");
		$response = $this->client->get('/api/services_applied_products?is_applied=false');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['sap_list']));
		$this->assertEquals(6,count($body['sap_list']));
	}

	/*
	public function testGETSAPs(){
		printf("\n GET SAPs regular: ");
		$response = $this->client->get('/api/services/1/applied_products');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['sap_list']));
		$this->assertEquals(4,count($body['sap_list']));
	}

	public function testGETSAPsWithFilters(){

		printf("\n GET SUT Filter user_name 1: ");
		$response = $this->client->get('/api/services/2/user_times?user_name=B');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['sut_list']));
		$this->assertEquals(1,count($body['sut_list']));

		printf("\n GET SUT Filter user_name 0: ");
		$response = $this->client->get('/api/services/2/user_times?user_name=oooo');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['sut_list']));
		$this->assertEquals(0,count($body['sut_list']));

		printf("\n GET SUT Filter name multiple: ");
		$response = $this->client->get('/api/services/2/user_times?user_name=C');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['sut_list']));
		$this->assertEquals(2,count($body['sut_list']));

		//user_id
		printf("\n GET SUT Filter user_id 1: ");
		$response = $this->client->get('/api/services/2/user_times?user_id=5');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['sut_list']));
		$this->assertEquals(1,count($body['sut_list']));

		printf("\n GET SUT Filter user_id 0: ");
		$response = $this->client->get('/api/services/2/user_times?user_id=3000');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['sut_list']));
		$this->assertEquals(0,count($body['sut_list']));

		printf("\n GET SUT Filter user_id multiple: ");
		$response = $this->client->get('/api/services/2/user_times?user_id=6');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['sut_list']));
		$this->assertEquals(2,count($body['sut_list']));

		//user_id
		printf("\n GET SUT Filter date 1: ");
		$response = $this->client->get('/api/services/2/user_times?date=2025-02-06');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['sut_list']));
		$this->assertEquals(1,count($body['sut_list']));

		printf("\n GET SUT Filter date 0: ");
		$response = $this->client->get('/api/services/2/user_times?date=2025-02-07');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['sut_list']));
		$this->assertEquals(0,count($body['sut_list']));

		printf("\n GET SUT Filter date multiple: ");
		$response = $this->client->get('/api/services/2/user_times?date=2025-02-05');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['sut_list']));
		$this->assertEquals(4,count($body['sut_list']));
	}

	public function testPOSTSUT(){
		printf("\n POST SUT regular: ");
		$response = $this->client->post('/api/services/2/user_times',
			[
				'json' => [
					'user_id' => '4',
					'minutes' => '15',
					'date' => '2025-02-05'
				]
			]);
		
		$body = json_decode($response->getBody(), true);
		$this->assertEquals(201, $response->getStatusCode());
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['sut']));
		$this->assertEquals(true,isset($body['sut']['sut_id']));
		$this->assertEquals(6,$body['sut']['sut_id']);

		$this->assertEquals(true,isset($body['sut']['service_id']));
		$this->assertEquals(2,$body['sut']['service_id']);
		$this->assertEquals(true,isset($body['sut']['user_id']));
		$this->assertEquals(4,$body['sut']['user_id']);
		$this->assertEquals(true,isset($body['sut']['minutes']));
		$this->assertEquals(15,$body['sut']['minutes']);
		$this->assertEquals(true,isset($body['sut']['date']));
		$this->assertEquals('2025-02-05',$body['sut']['date']);
	}

	public function testPOSTClientsBadRequest(){
		printf("\n POST Clients Invalid Field: less then date ");
		$response = $this->client->post('/api/services/2/user_times',
			[
				'json' => [
					'user_id' => '1',
					'minutes' => '919',
					'date' => '2025-02-04',
				]
			]);
		
		$this->assertEquals(400, $response->getStatusCode());

		printf("\n POST Clients Invalid Field: greater then date");
		$response = $this->client->post('/api/services/2/user_times',
			[
				'json' => [
					'user_id' => '1',
					'minutes' => '919',
					'date' => '2025-02-07',
				]
			]);
		
		$this->assertEquals(400, $response->getStatusCode());

		printf("\n POST Clients Invalid Field: negative minutes");
		$response = $this->client->post('/api/services/2/user_times',
			[
				'json' => [
					'user_id' => '1',
					'minutes' => -1,
					'date' => '2025-02-06',
				]
			]);
		
		$this->assertEquals(400, $response->getStatusCode());

		printf("\n POST Clients Invalid Field: missing");
		$response = $this->client->post('/api/services/2/user_times',
			[
				'json' => [
					'minutes' => 0,
					'date' => '2025-02-06',
				]
			]);
		
		$this->assertEquals(400, $response->getStatusCode());
		$response = $this->client->post('/api/services/2/user_times',
			[
				'json' => [
					'user_id' => 0,
					'date' => '2025-02-06',
				]
			]);
		
		$this->assertEquals(400, $response->getStatusCode());
		$response = $this->client->post('/api/services/2/user_times',
			[
				'json' => [
					'user_id' => 0,
					'minutes' => 0,
				]
			]);
		
		$this->assertEquals(400, $response->getStatusCode());
	}

	public function testGETSUT(){
		printf("\n GET SUT/Id regular: ");
		$response = $this->client->get("/api/services/2/user_times/4");
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['sut']));
		$this->assertEquals(true,isset($body['sut']['service_id']));
		$this->assertEquals(2,$body['sut']['service_id']);
		$this->assertEquals(true,isset($body['sut']['sut_id']));
		$this->assertEquals(4,$body['sut']['sut_id']);
		$this->assertEquals(true,isset($body['sut']['id']));
		$this->assertEquals(8,$body['sut']['id']);
		$this->assertEquals(true,isset($body['sut']['minutes']));
		$this->assertEquals(10,$body['sut']['minutes']);
		$this->assertEquals(true,isset($body['sut']['date']));
		$this->assertEquals("2025-02-05",$body['sut']['date']);
		$this->assertEquals(true,isset($body['sut']['user_id']));
		$this->assertEquals("4",$body['sut']['user_id']);
		$this->assertEquals(true,isset($body['sut']['user_name']));
		$this->assertEquals("A",$body['sut']['user_name']);
	}

	public function testGETSUTIDInvalidID(){
		printf("\n GET SUT/Id Invalid ID: ");
		$response = $this->client->get("/api/services/2/user_times/105");
		$this->assertEquals(404, $response->getStatusCode());
	}

	public function testPUTSUT(){
		printf("\n POST PUT regular: (same values)");
		$response = $this->client->put('/api/services/2/user_times/6',
			[
				'json' => [
					'user_id' => '4',
					'minutes' => '15',
					'date' => '2025-02-05'
				]
			]);
		
		$body = json_decode($response->getBody(), true);
		$this->assertEquals(200, $response->getStatusCode());
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['sut']));
		$this->assertEquals(true,isset($body['sut']['sut_id']));
		$this->assertEquals(6,$body['sut']['sut_id']);

		$this->assertEquals(true,isset($body['sut']['service_id']));
		$this->assertEquals(2,$body['sut']['service_id']);
		$this->assertEquals(true,isset($body['sut']['user_id']));
		$this->assertEquals(4,$body['sut']['user_id']);
		$this->assertEquals(true,isset($body['sut']['minutes']));
		$this->assertEquals(15,$body['sut']['minutes']);
		$this->assertEquals(true,isset($body['sut']['date']));
		$this->assertEquals('2025-02-05',$body['sut']['date']);

		$response = $this->client->put('/api/services/2/user_times/6',
			[
				'json' => [
					'user_id' => '3',
					'minutes' => '10',
					'date' => '2025-02-06'
				]
			]);
		
		$body = json_decode($response->getBody(), true);
		$this->assertEquals(200, $response->getStatusCode());
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['sut']));
		$this->assertEquals(true,isset($body['sut']['sut_id']));
		$this->assertEquals(6,$body['sut']['sut_id']);

		$this->assertEquals(true,isset($body['sut']['service_id']));
		$this->assertEquals(2,$body['sut']['service_id']);
		$this->assertEquals(true,isset($body['sut']['user_id']));
		$this->assertEquals(3,$body['sut']['user_id']);
		$this->assertEquals(true,isset($body['sut']['minutes']));
		$this->assertEquals(10,$body['sut']['minutes']);
		$this->assertEquals(true,isset($body['sut']['date']));
		$this->assertEquals('2025-02-06',$body['sut']['date']);
	}

	public function testPUTClientsBadRequest(){
		printf("\n PUT Clients Invalid Field: less then date ");
		$response = $this->client->put('/api/services/2/user_times/6',
			[
				'json' => [
					'user_id' => '1',
					'minutes' => '919',
					'date' => '2025-02-04',
				]
			]);
		
		$this->assertEquals(400, $response->getStatusCode());

		printf("\n POST Clients Invalid Field: greater then date");
		$response = $this->client->put('/api/services/2/user_times/6',
			[
				'json' => [
					'user_id' => '1',
					'minutes' => '919',
					'date' => '2025-02-07',
				]
			]);
		
		$this->assertEquals(400, $response->getStatusCode());

		printf("\n POST Clients Invalid Field: negative minutes");
		$response = $this->client->put('/api/services/2/user_times/6',
			[
				'json' => [
					'user_id' => '1',
					'minutes' => -1,
					'date' => '2025-02-06',
				]
			]);
		
		$this->assertEquals(400, $response->getStatusCode());

		printf("\n POST Clients Invalid Field: missing");
		$response = $this->client->put('/api/services/2/user_times/6',
			[
				'json' => [
					'minutes' => 0,
					'date' => '2025-02-06',
				]
			]);
		
		$this->assertEquals(400, $response->getStatusCode());
		$response = $this->client->put('/api/services/2/user_times/6',
			[
				'json' => [
					'user_id' => 0,
					'date' => '2025-02-06',
				]
			]);
		
		$this->assertEquals(400, $response->getStatusCode());
		$response = $this->client->put('/api/services/2/user_times/6',
			[
				'json' => [
					'user_id' => 0,
					'minutes' => 0,
				]
			]);
		
		$this->assertEquals(400, $response->getStatusCode());
	}

	public function testDELETESUTID(){
		printf("\n DELETE SUT/Id regular: ");
		$response = $this->client->delete("/api/services/2/user_times/6");
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['sut']));

		$response = $this->client->delete("/api/services/2/user_times/6");
		$this->assertEquals(404, $response->getStatusCode());
	}

	public function testDELETEmakesIDInvalidID(){
		printf("\n DELETE SUT/Id Invalid ID: ");
		$response = $this->client->delete("/api/services/2/user_times/100");
		$this->assertEquals(404, $response->getStatusCode());
	}
	*/
}
