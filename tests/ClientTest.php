<?php declare(strict_types = 1);
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/TestDatabase.php';
require_once __DIR__ . '/../database/client.php';

final class ClientTest extends TestCase{

	public function testGetAll(): void
	{
		$db = TestDatabase::create();
		$result = Client::getClients($db);	
		$expected = array(
			new Client(1,"client1", "phone1"),
			new Client(2,"client2","phone2")
		);
		$this->assertEquals($expected, $result);
	}

	public function testGetOne(): void
	{
		$db = TestDatabase::create();
		$result = Client::getClientById($db,1);	
		$expected = new Client(1,"client1", "phone1");
		$this->assertEquals($expected, $result);
	}

	public function testSaveFull(): void
	{
		$db = TestDatabase::create();
		$client = Client::getClientById($db,1);	
		$client->name = "ui";
		$client->phone = "ui";
		$client->address = "ui";
		$client->email = "ui";
		$client->zip_code = "ui";
		$client->tax_nr = "ui";
		$client->save($db);
		$client = Client::getClientById($db,1);	
		$this->assertEquals("ui", $client->name);
		$this->assertEquals("ui", $client->phone);
		$this->assertEquals("ui", $client->address);
		$this->assertEquals("ui", $client->email);
		$this->assertEquals("ui", $client->zip_code);
		$this->assertEquals("ui", $client->tax_nr);
	}
	public function testSaveNotFull(): void
	{
		$db = TestDatabase::create();
		$client = Client::getClientById($db,1);	
		$client->name = "ui";
		$client->phone = "ui";
		$client->save($db);
		$client = Client::getClientById($db,1);	
		$this->assertEquals("ui", $client->name);
		$this->assertEquals("ui", $client->phone);
		$this->assertNull($client->address, "Should be null");
		$this->assertNull($client->email, "Should be null");
		$this->assertNull($client->zip_code, "Should be null");
		$this->assertNull($client->tax_nr, "Should be null");
	}

	public function testDelete(): void
	{
		$db = TestDatabase::create();
		$client = Client::getClientById($db,1);	
		$client->delete($db);
		$this->expectException(Exception::class);
		Client::getClientById($db,1);
	}

	public function testGetInvalidException(): void
	{
		$db = TestDatabase::create();
		$this->expectException(Exception::class);
		Client::getClientById($db,10);
	}
}
?>
