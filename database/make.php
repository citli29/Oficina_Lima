<?php declare(strict_types =1);
require_once __DIR__ . '/../utils/util.php';

class Make{

	private int $id;
	public string $name;

	function __construct(int $id, string $name)
	{
		$this->name = $name;
		$this->id = $id;
	}

	public static function getMakes(PDO $db):array
	{
		$stmt = $db->prepare('Select id,name from makes');
		$stmt->execute();
		$makes = array();
		while($make = $stmt->fetch()){
			$makes[] = new Make(
				nullableInt($make['id']),
				$make['name']);
		}
		return $makes;
	}

	public static function getMakeById(PDO $db, int $id):?Make
	{
		$stmt = $db->prepare('SELECT id,name FROM makes WHERE id=?');
		$stmt->execute([$id]);
		$make = $stmt->fetch();
		if($make){ 
			return new Make(
				nullableInt($make['id']),
				$make['name']
			);
		}
		throw new Exception("Invalid Id: Make {{$id}}");
	}

	public function getId()
	{
		return $this->id;
	}

	public function save(PDO $db)
	{
		$stmt = $db->prepare('UPDATE makes SET name = ? WHERE id = ?');
		$stmt->execute([$this->name,$this->id]);
	}
	public function delete(PDO $db)
	{
		$stmt = $db->prepare('DELETE FROM makes WHERE id = ?');
		$stmt->execute([$this->id]);
	}
}
?>
