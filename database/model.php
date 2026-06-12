<?php declare(strict_types=1);
require_once __DIR__ . '/../utils/util.php';
class Model{
	 
	private int $id;
	public string $name;
	private int $make_id;

	function __construct(int $id, string $name, int $make_id)
	{
		$this->id = $id;
		$this->name = $name;
		$this->make_id = $make_id;
	}

	public static function getModelsByMake(PDO $db, Make $make):array
	{
		$stmt = $db->prepare('SELECT id,name, make_id FROM models WHERE make_id = ?');
		$stmt->execute([$make->getId()]);
		$models = array();
		while($model = $stmt->fetch()){
			$models[] = new Model(
				(int)$model['id'],
				$model['name'],
				(int)$model['make_id']
			);
	}
		return $models;
	}

	public static function getModelById(PDO $db, int $id):?Model
	{
		$stmt = $db->prepare('SELECT id, name, make_id FROM models WHERE id = ?');
		$stmt->execute([$id]);
		$model= $stmt->fetch();
		if($model){ 
			return new Model(
				(int)$model['id'],
				$model['name'],
				(int)$model['make_id']
			);
		}
		throw new Exception("Invalid Id: Model {{$id}}");
	}

	public function getId()
	{
		return $this->id;
	}

	public function getMake(PDO $db):Make
	{
		$make = Make::getMakeById($db,$this->make_id);
		return $make;
	}

	public function setMake(Make $make)
	{
		$this->make_id = $make->getId();
	}

	public function save(PDO $db)
	{
		$stmt = $db->prepare('UPDATE models SET name = ?, make_id = ? WHERE id = ?');
		$stmt->execute([$this->name, $this->make_id, $this->id]);
	}

	public function delete(PDO $db)
	{
		$stmt = $db->prepare('DELETE FROM models WHERE id = ?');
		$stmt->execute([$this->id]);
	}
}
?>
