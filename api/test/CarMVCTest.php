<?php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

class CarMVCTest extends TestCase 
{
	private Client $client;

	protected function setUp():void
	{
		$this->client = new Client([
			'base_uri' => 'http://localhost:8000',
			'http_errors' => false
		]);
	}

	public function testGetCarsReturns200(){
		$response = $this->client->get('/api/cars');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		print_r($body);
	}
}

?>
