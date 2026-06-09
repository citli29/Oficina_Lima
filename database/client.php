<?php declare(strict_types=1);
class Client{

	private int $id;
	public string $name;
	public string $phone;
	public ?string $address;
	public ?string $email;
	public ?string $zip_code;
	public ?string $tax_nr;

	public function __construct(int $id, string $name, string $phone, ?string $address = null, ?string $email = null, ?string $zip_code = null, ?string $tax_nr = null)
	{
		$this->id = $id;
		$this->name = $name;
		$this->phone = $phone;
		$this->address = $address;
		$this->email = $email;
		$this->zip_code = $zip_code;
		$this->tax_nr = $tax_nr;
	}

	public function getId(){
		return $this->id;
	}

	public static function getClients(PDO $db):array
	{
		$stmt = $db->prepare('Select id, name, phone, address, email, zip_code, tax_nr from clients');
		$stmt->execute();
		$clients = array();
		while($client = $stmt->fetch()){
			$clients[] = new Client(
				(int)$client['id'],
				$client['name'],
				$client['phone'],
				$client['address'],
				$client['email'],
				$client['zip_code'],
				$client['tax_nr']
			);
		}
		return $clients;
	}

	public static function getClientById(PDO $db, int $id):?Client
	{
		$stmt = $db->prepare('SELECT id, name, phone, address, email, zip_code, tax_nr FROM clients WHERE id=?');
		$stmt->execute([$id]);
		$client = $stmt->fetch();
		if($client){ 
			return new Client(
				(int)$client['id'],
				$client['name'],
				$client['phone'],
				$client['address'],
				$client['email'],
				$client['zip_code'],
				$client['tax_nr']
			);
		}
		throw new Exception("Invalid Id: Client {{$id}}");
	}

	public function save(PDO $db)
	{
		$stmt = $db->prepare('UPDATE clients SET name = ?, phone = ?, address = ?, email = ?, zip_code = ?, tax_nr = ? WHERE id = ?');
		$stmt->execute([$this->name, $this->phone, $this->address, $this->email, $this->zip_code, $this->tax_nr,$this->id]);
	}

	public function delete(PDO $db)
	{
		$stmt = $db->prepare('DELETE FROM clients WHERE id = ?');
		$stmt->execute([$this->id]);
	}
}
?>
