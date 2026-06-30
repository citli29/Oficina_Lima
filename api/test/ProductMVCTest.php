<?php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

class ProductMVCTest extends TestCase 
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

	public function testGETProducts(){
		printf("\n GET Products regular: ");
		$response = $this->client->get('/api/products');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['product_list']));
		$this->assertEquals(40,count($body['product_list']));
	}

	public function testGETProductTypes(){
		printf("\n GET Product Types regular: ");
		$response = $this->client->get('/api/product_types');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['product_type_list']));
		$this->assertEquals(6,count($body['product_type_list']));
	}
	public function testGETProductWithFilters(){
		//*?name* *?reference* *?p_t_name* *?p_t_id*
		//Name
		printf("\n GET Product Filter Name 1: ");
		$response = $this->client->get('/api/products?name=Lampada H7');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['product_list']));
		$this->assertEquals(1,count($body['product_list']));

		printf("\n GET Product Filter Name 0: ");
		$response = $this->client->get('/api/products?name=oooo');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['product_list']));
		$this->assertEquals(0,count($body['product_list']));

		printf("\n GET Product Types Filter Name multiple: ");
		$response = $this->client->get('/api/products?name=Lampada');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['product_list']));
		$this->assertEquals(2,count($body['product_list']));


		// Reference

		printf("\n GET Product Filter reference 1: ");
		$response = $this->client->get('/api/products?reference=PA7553');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['product_list']));
		$this->assertEquals(1,count($body['product_list']));

		printf("\n GET Product Filter reference 0: ");
		$response = $this->client->get('/api/products?reference=oooo');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['product_list']));
		$this->assertEquals(0,count($body['product_list']));

		printf("\n GET Product Types Filter reference multiple: ");
		$response = $this->client->get('/api/products?reference=PT');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['product_list']));
		$this->assertEquals(2,count($body['product_list']));

		// PT Name
		printf("\n GET Product Filter p_t_name 1: ");
		$response = $this->client->get('/api/products?p_t_name=pneus');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['product_list']));
		$this->assertEquals(1,count($body['product_list']));

		printf("\n GET Product Filter p_t_name 0: ");
		$response = $this->client->get('/api/products?p_t_name=oooo');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['product_list']));
		$this->assertEquals(0,count($body['product_list']));

		printf("\n GET Product Types Filter p_t_name multiple: ");
		$response = $this->client->get('/api/products?p_t_name=liquidos');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['product_list']));
		$this->assertEquals(4,count($body['product_list']));

		// PT Id
		printf("\n GET Product Filter p_t_id 1: ");
		$response = $this->client->get('/api/products?p_t_id=6');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['product_list']));
		$this->assertEquals(1,count($body['product_list']));

		printf("\n GET Product Filter p_t_id 0: ");
		$response = $this->client->get('/api/products?p_t_id=3000');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['product_list']));
		$this->assertEquals(0,count($body['product_list']));

		printf("\n GET Product Types Filter p_t_id multiple: ");
		$response = $this->client->get('/api/products?p_t_id=1');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['product_list']));
		$this->assertEquals(4,count($body['product_list']));
	}
	public function testGETProductTypesWithFilters(){
		printf("\n GET Product Types Filter name 1: ");
		$response = $this->client->get('/api/product_types?name=pecas');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['product_type_list']));
		$this->assertEquals(1,count($body['product_type_list']));

		printf("\n GET Product Types Filter designatino 0: ");
		$response = $this->client->get('/api/product_types?name=oooo');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['product_type_list']));
		$this->assertEquals(0,count($body['product_type_list']));

		printf("\n GET Product Types Filter name multiple: ");
		$response = $this->client->get('/api/product_types?name=tr');
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['product_type_list']));
		$this->assertEquals(3,count($body['product_type_list']));
	}

	public function testGETProductsID(){
		printf("\n GET Products/Id regular: ");
		$response = $this->client->get("/api/products/5");
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['product']));
	}

	public function testGETProductsIDInvalidID(){
		printf("\n GET Products/Id Invalid ID: ");
		$response = $this->client->get("/api/products/100");
		$this->assertEquals(404, $response->getStatusCode());
	}

	public function testGETProductTypesID(){
		printf("\n GET ProductTypes/Id regular: ");
		$response = $this->client->get("/api/product_types/3");
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['product_type']));
	}

	public function testGETProductTypesIDInvalidID(){
		printf("\n GET ProductTypes/Id Invalid ID: ");
		$response = $this->client->get("/api/product_types/100");
		$this->assertEquals(404, $response->getStatusCode());
	}

	public function testPOSTProduct(){
		printf("\n POST Product regular: ");
		$response = $this->client->post('/api/products',
			[
				'json' => [
					'name' => 'CoolP',
					'reference' => 'CoolP',
					'product_type_id' => '1'
				]
			]);
		
		$body = json_decode($response->getBody(), true);
		$this->assertEquals(201, $response->getStatusCode());
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['product']));
		$this->assertEquals('CoolP',$body['product']['name']);
		$this->assertEquals(true,isset($body['product']['id']));
		$this->assertEquals(41,$body['product']['id']);
		$this->assertEquals(true,isset($body['product']['reference']));
		$this->assertEquals('CoolP',$body['product']['reference']);
		$this->assertEquals(true,isset($body['product']['product_type_id']));
		$this->assertEquals(1,$body['product']['product_type_id']);
	}

	public function testPOSTProductBadRequest(){
		printf("\n POST Product Invalid Field: ");
		$response = $this->client->post('/api/products',
			[
				'json' => [
					'na' => 'Cool',
				]
			]);
		
		$this->assertEquals(400, $response->getStatusCode());

		printf("\n POST Product Types Duplicated Field: ");
		$response = $this->client->post('/api/products',
			[
				'json' => [
					'name' => "gang",
					'reference' => 'CoolP',
				]
			]);
		
		$this->assertEquals(400, $response->getStatusCode());

		$response = $this->client->post('/api/products',
			[
				'json' => [
					'name' => "gang",
				]
			]);

		$response = $this->client->post('/api/products',
			[
				'json' => [
					'name' => "gang",
				]
			]);
		
		$this->assertEquals(400, $response->getStatusCode());
	}
	public function testPOSTProductTypes(){
		printf("\n POST Product Types regular: ");
		$response = $this->client->post('/api/product_types',
			[
				'json' => [
					'name' => 'CoolPT',
				]
			]);
		
		$body = json_decode($response->getBody(), true);
		$this->assertEquals(201, $response->getStatusCode());
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['product_type']));
		$this->assertEquals('CoolPT',$body['product_type']['name']);
		$this->assertEquals(true,isset($body['product_type']['id']));
		$this->assertEquals(7,$body['product_type']['id']);
	}

	public function testPOSTProductTypesBadRequest(){
		printf("\n POST Product Types Invalid Field: ");
		$response = $this->client->post('/api/product_types',
			[
				'json' => [
					'na' => 'Cool',
				]
			]);
		
		$this->assertEquals(400, $response->getStatusCode());

		printf("\n POST Product Types Duplicated Field: ");
		$response = $this->client->post('/api/product_types',
			[
				'json' => [
					'name' => 'CoolPT',
				]
			]);
		
		$this->assertEquals(400, $response->getStatusCode());
	}

	public function testDELETEProductsID(){
		printf("\n DELETE Products/Id regular: ");
		$response = $this->client->delete("/api/products/9");
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['product']));
	}

	public function testDELETEProductsIDInvalidID(){
		printf("\n DELETE Products/Id Invalid ID: ");
		$response = $this->client->delete("/api/products/1000");
		$this->assertEquals(404, $response->getStatusCode());
	}

	public function testDELETEProductTypesID(){
		printf("\n DELETE Products/Id regular: ");
		$response = $this->client->delete("/api/product_types/5");
		$this->assertEquals(200, $response->getStatusCode());
		$body = json_decode($response->getBody(), true);
		$this->assertIsArray($body);
		$this->assertEquals(true,isset($body['product_type']));
	}

	public function testDELETEProductTypesIDInvalidID(){
		printf("\n DELETE Products/Id Invalid ID: ");
		$response = $this->client->delete("/api/product_types/1000");
		$this->assertEquals(404, $response->getStatusCode());
	}
}?>
