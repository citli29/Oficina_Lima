<?php declare(strict_types=1);
require_once __DIR__ . '/../utils/util.php';
class Schedule{
	 
	private int $id;
	public string $schedule_date;
	public string $description;
	private ?int $car_id;
	private ?int $model_id;
	private ?int $client_id;

	function __construct(int $id, string $schedule_date, string $description,  ?int $car_id = null,?int $model_id = null, ?int $client_id = null)
	{
		$this->id = $id;
		$this->schedule_date = $schedule_date;
		$this->description = $description;
		$this->car_id = $car_id;
		$this->model_id = $model_id;
		$this->client_id = $client_id;
	}

	public static function getSchedulesByCar(PDO $db, Car $car):array
	{
		$stmt = $db->prepare('SELECT id, schedule_date, description, car_id, model_id, client_id FROM schedules WHERE car_id = ?');
		$stmt->execute([$car->getId()]);
		$schedules = array();
		while($schedule = $stmt->fetch()){
			$schedules[] = new Schedule(
				nullableInt($schedule['id']),
				$schedule['schedule_date'],
				$schedule['description'],
				nullableInt($schedule['car_id']),
				nullableInt($schedule['model_id']),
				nullableInt($schedule['client_id'])
			);
		}
		return $schedules;
	}

	public static function getSchedulesByClient(PDO $db, Client $client):array
	{
		$stmt = $db->prepare('SELECT id, schedule_date, description, car_id, model_id, client_id FROM schedules WHERE client_id = ?');
		$stmt->execute([$client->getId()]);
		$schedules = array();
		while($schedule = $stmt->fetch()){
			$schedules[] = new Schedule(
				nullableInt($schedule['id']),
				$schedule['schedule_date'],
				$schedule['description'],
				nullableInt($schedule['car_id']),
				nullableInt($schedule['model_id']),
				nullableInt($schedule['client_id'])
			);
		}
		return $schedules;
	}

	public static function getScheduleById(PDO $db, int $id):?Schedule
	{
		$stmt = $db->prepare('SELECT id, schedule_date, description, car_id, model_id, client_id FROM schedules WHERE id = ?');
		$stmt->execute([$id]);
		$schedule= $stmt->fetch();
		if($schedule){ 
			return new Schedule(
				nullableInt($schedule['id']),
				$schedule['schedule_date'],
				$schedule['description'],
				nullableInt($schedule['car_id']),
				nullableInt($schedule['model_id']),
				nullableInt($schedule['client_id'])
			);
		}
		throw new Exception("Invalid Id: Schedule {{$id}}");
	}

	public function getId()
	{
		return $this->id;
	}

	public function getCar(PDO $db):?Car
	{
		if (is_null($this->car_id)) return null;
		$car = Car::getCarById($db,$this->car_id);
		return $car;
	}

	public function setCar(null|Car $car)
	{
		$this->car_id = $car?$car->getId():null;
	}

	public function getModel(PDO $db):?Model
	{
		if (is_null($this->model_id)) return null;
		$model = Model::getModelById($db,$this->model_id);
		return $model;
	}

	public function setModel(null|Model $model)
	{
		$this->model_id = $model?$model->getId():null;
	}

	public function getClient(PDO $db):?Client
	{
		if (is_null($this->client_id)) return null;
		$client = Client::getClientById($db,$this->client_id);
		return $client;
	}

	public function setClient(null|Client $client)
	{
		$this->client_id = $client?$client->getId():null;
	}

	public function save(PDO $db)
	{
		$stmt = $db->prepare('UPDATE schedules SET schedule_date = ?, description = ?, car_id = ?, model_id = ?, client_id = ? WHERE id = ?');
		$stmt->execute([
			$this->schedule_date,
			$this->description,
			$this->car_id,
			$this->model_id,
			$this->client_id,
			$this->id
		]);
	}

	public function delete(PDO $db)
	{
		$stmt = $db->prepare('DELETE FROM schedules WHERE id = ?');
		$stmt->execute([$this->id]);
	}
}
?>
