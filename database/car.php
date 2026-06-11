<?php declare(strict_types=1);
require_once __DIR__ . '/../utils/util.php';
class Car{
	 
	private int $id;
	public string $plate;
	private int $model_id;
	public ?string $chassi_nr;
	public ?int $year;
	public ?int $month;
	public ?int $cc;
	public ?string $engine_code;
	public ?string $color_code;

	function __construct(int $id, string $plate, int $model_id,  ?string $chassi_nr = null, ?int $year = null, ?int $month = null, ?int $cc = null, ?string $engine_code = null, ?string $color_code = null)
	{
		$this->id = $id;
		$this->plate = $plate;
		$this->model_id = $model_id;
		$this->chassi_nr = $chassi_nr;
		$this->year = $year;
		$this->month = $month;
		$this->cc = $cc;
		$this->engine_code = $engine_code;
		$this->color_code = $color_code;
	}

	public static function getCarsByModel(PDO $db, Model $model):array
	{
		$stmt = $db->prepare('SELECT id,plate, model_id, chassi_nr, year, month, cc, engine_code, color_code FROM cars WHERE model_id = ?');
		$stmt->execute([$model->getId()]);
		$cars = array();
		while($car = $stmt->fetch()){
			$cars[] = new Car(
				nullableInt($car['id']),
				$car['plate'],
				nullableInt($car['model_id']),
				$car['chassi_nr'],
				nullableInt($car['year']),
				nullableInt($car['month']),
				nullableInt($car['cc']),
				$car['engine_code'],
				$car['color_code'],
			);
	}
		return $cars;
	}

	public static function getCarById(PDO $db, int $id):?Car
	{
		$stmt = $db->prepare('SELECT id,plate, model_id, chassi_nr, year, month, cc, engine_code, color_code FROM cars WHERE id = ?');
		$stmt->execute([$id]);
		$car= $stmt->fetch();
		if($car){ 
			return $cars[] = new Car(
				nullableInt($car['id']),
				$car['plate'],
				nullableInt($car['model_id']),
				$car['chassi_nr'],
				nullableInt($car['year']),
				nullableInt($car['month']),
				nullableInt($car['cc']),
				$car['engine_code'],
				$car['color_code'],
			);
		}
		throw new Exception("Invalid Id: Car {{$id}}");
	}

	public function getId()
	{
		return $this->id;
	}

	public function getModel(PDO $db):Model
	{
		$model = Model::getModelById($db,$this->model_id);
		return $model;
	}

	public function setModel(Model $model)
	{
		$this->model_id = $model->getId();
	}

	public function save(PDO $db)
	{
		$stmt = $db->prepare('UPDATE cars SET plate = ?, model_id = ?, chassi_nr = ?, year = ?, month = ?, cc = ?, engine_code = ?, color_code = ? WHERE id = ?');
		$stmt->execute([
			$this->plate, 
			$this->model_id, 
			$this->chassi_nr, 
			$this->year, 
			$this->month, 
			$this->cc, 
			$this->engine_code, 
			$this->color_code,
			$this->id
		]);
	}

	public function delete(PDO $db)
	{
		$stmt = $db->prepare('DELETE FROM cars WHERE id = ?');
		$stmt->execute([$this->id]);
	}
}
?>
