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
		print_r($body);
		printf("AQUIIII %d\n",$body['sut']['sut_id']);
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

}
