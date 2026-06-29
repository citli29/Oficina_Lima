<?php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

class ClientMVCTest extends TestCase 
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

	public function testGETClients(){
		printf("\n Get Clients regular: ");
		$response = $this->client->get('/api/clients');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['client_list']));
		$this->assertEquals(13,count($body['client_list']));
	}

	public function testGETClientsWithFilters(){
		printf("\n Get Clients Filter name 1: ");
		$response = $this->client->get('/api/clients?name=Antonio');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['client_list']));
		$this->assertEquals(1,count($body['client_list']));

		printf("\n Get Clients Filter name 0: ");
		$response = $this->client->get('/api/clients?name=oooo');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['client_list']));
		$this->assertEquals(0,count($body['client_list']));

		printf("\n Get Clients Filter name multiple: ");
		$response = $this->client->get('/api/clients?name=o');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['client_list']));
		$this->assertEquals(7,count($body['client_list']));

		printf("\n Get Clients Filter phone 1: ");
		$response = $this->client->get('/api/clients?phone=923');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['client_list']));
		$this->assertEquals(1,count($body['client_list']));

		printf("\n Get Clients Filter email 1: ");
		$response = $this->client->get('/api/clients?name=Antonio');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['client_list']));
		$this->assertEquals(1,count($body['client_list']));
	}

	public function testPOSTClients(){
		printf("\n POST Clients regular: ");
		$response = $this->client->post('/api/clients',
			[
				'json' => [
					'name' => 'CoolPerson',
					'phone' => '919',
					'address' => 'R. 29 de Maio',
					'email' => 'cp@email.com',
					'zip_code' => '4575-010',
					'tax_nr' => '222000111',
				]
			]);
		
		$body = json_decode($response->getBody(), true);
		$this->assertEquals(201, $response->getStatusCode());
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['client']));
		$this->assertEquals('CoolPerson',$body['client']['name']);
		$this->assertEquals(true,isset($body['client']['phone']));
		$this->assertEquals('919',$body['client']['phone']);
		$this->assertEquals(true,isset($body['client']['address']));
		$this->assertEquals('R. 29 de Maio',$body['client']['address']);
		$this->assertEquals(true,isset($body['client']['email']));
		$this->assertEquals('cp@email.com',$body['client']['email']);
		$this->assertEquals(true,isset($body['client']['zip_code']));
		$this->assertEquals('4575-010',$body['client']['zip_code']);
		$this->assertEquals(true,isset($body['client']['tax_nr']));
		$this->assertEquals('222000111',$body['client']['tax_nr']);
	}

	public function testPOSTClientsBadRequest(){
		printf("\n POST Clients Invalid Field: ");
		$response = $this->client->post('/api/clients',
			[
				'json' => [
					'na' => 'Cool',
					'phone' => '919',
				]
			]);
		
		$this->assertEquals(400, $response->getStatusCode());

		$response = $this->client->post('/api/clients',
			[
				'json' => [
					'name' => 'Cool',
					'pho' => '919',
				]
			]);
		
		$this->assertEquals(400, $response->getStatusCode());
	}

	public function testGETClientsID(){
		printf("\n Get Clients/Id regular: ");
		$response = $this->client->get("/api/clients/5");
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['client']));
	}

	public function testGETClientsIDInvalidID(){
		printf("\n Get Clients/Id Invalid ID: ");
		$response = $this->client->get("/api/clients/55");
		$this->assertEquals(404, $response->getStatusCode());
	}

	public function testPUTClients(){
		printf("\n PUT Clients regular same(name, phone): ");
		$response = $this->client->put('/api/clients/14',
			[
				'json' => [
					'name' => 'CoolPerson',
					'phone' => '919',
				]
			]);
		
		$body = json_decode($response->getBody(), true);
		$this->assertEquals(200, $response->getStatusCode());
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['client']));
		$this->assertEquals(true,isset($body['client']['name']));
		$this->assertEquals('CoolPerson',$body['client']['name']);
		$this->assertEquals(true,isset($body['client']['id']));
		$this->assertEquals(14,$body['client']['id']);

		printf("\n PUT Clients regular: ");
		$response = $this->client->put('/api/clients/14',
			[
				'json' => [
					'name' => 'VeryCoolPerson',
					'phone' => '921',
				]
			]);
		
		$body = json_decode($response->getBody(), true);
		$this->assertEquals(200, $response->getStatusCode());
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['client']));
		$this->assertEquals(true,isset($body['client']['id']));
		$this->assertEquals(14,$body['client']['id']);
		$this->assertEquals(true,isset($body['client']['name']));
		$this->assertEquals('VeryCoolPerson',$body['client']['name']);
		$this->assertEquals(true,isset($body['client']['phone']));
		$this->assertEquals('921',$body['client']['phone']);
	}

	public function testPUTClientsBadRequest(){
		printf("\n PUT Clients Invalid Field: ");
		$response = $this->client->put('/api/clients/14',
			[
				'json' => [
					'na' => 'Cool',
					'phone' => '919',
				]
			]);
		
		$this->assertEquals(400, $response->getStatusCode());

		$response = $this->client->put('/api/clients/14',
			[
				'json' => [
					'name' => 'Cool',
					'pho' => '919',
				]
			]);
		
		$this->assertEquals(400, $response->getStatusCode());
	}

	public function testDELETEClientsID(){
		printf("\n Delete Clients/Id regular: ");
		$response = $this->client->delete("/api/clients/14");
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['client']));
	}

	public function testDELETEmakesIDInvalidID(){
		printf("\n Delete Client/Id Invalid ID: ");
		$response = $this->client->delete("/api/clients/55");
		$this->assertEquals(404, $response->getStatusCode());
	}
}
?>
