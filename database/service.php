<?php declare(strict_types=1);
require_once __DIR__ . '/../utils/util.php';
class Service{
	 
// adicionar o constraint de se car_id != NULL, kms nao podem ser NULL
	private int $id;
	private int $client_id;
	public ?int $kms;
	public ?string $checkin_date;
	public ?string $checkout_date;
	public ?string $malfunction_description;
	public ?string $service_description;
	private ?int $car_id;
	private ?int $schedule_id;

	function __construct(int $id, int $client_id, ?int $kms= null, ?string $checkin_date = null, ?string $checkout_date = null, ?string $malfunction_description = null, ?string $service_description = null, ?int $car_id = null, ?int $schedule_id = null )
	{
		$this->id = $id;
		$this->client_id = $client_id;
		$this->kms = $kms;
		$this->checkin_date = $checkin_date;
		$this->checkout_date = $checkout_date;
		$this->malfunction_description = $malfunction_description;
		$this->service_description = $service_description;
		$this->car_id = $car_id;
		$this->schedule_id = $schedule_id;
	}

	public static function getServices(PDO $db):array
	{
		$stmt = $db->prepare('SELECT id,client_id, kms, checkin_date, checkout_date, malfunction_description, service_description, car_id, schedule_id FROM services');
		$stmt->execute();
		$services = array();
		while($service = $stmt->fetch()){
			$services[] = new Service(
				(int)$service['id'],
				(int)$service['client_id'],
				nullableInt($service['kms']),
				$service['checkin_date'],
				$service['checkout_date'],
				$service['malfunction_description'],
				$service['service_description'],
				nullableInt($service['car_id']),
				nullableInt($service['schedule_id'])
				
			);
		}
		return $services;
	}

	public static function getServiceById(PDO $db, int $id):?Service
	{
		$stmt = $db->prepare('SELECT id, client_id, kms, checkin_date, checkout_date, malfunction_description, service_description, car_id, schedule_id FROM services WHERE id = ?');
		$stmt->execute([$id]);
		$service= $stmt->fetch();
		if($service){ 
			return new Service(
				(int)$service['id'],
				(int)$service['client_id'],
				nullableInt($service['kms']),
				$service['checkin_date'],
				$service['checkout_date'],
				$service['malfunction_description'],
				$service['service_description'],
				nullableInt($service['car_id']),
				nullableInt($service['schedule_id'])
			);
		}
		throw new Exception("Invalid Id: Service {{$id}}");
	}

	public function getId()
	{
		return $this->id;
	}

	public function getClient(PDO $db):Client
	{
		$client = Client::getClientById($db,$this->client_id);
		return $client;
	}
	public function getCar(PDO $db):?Car
	{
		if(is_null($this->car_id)) return null;
		$car = Car::getCarById($db,$this->car_id);
		return $car;
	}
	public function getSchedule(PDO $db):?Schedule
	{
		if(is_null($this->schedule_id)) return null;
		$schedule = Schedule::getScheduleById($db,$this->schedule_id);
		return $schedule;
	}

	public function setClient(Client $client)
	{
		$this->client_id = $client->getId();
	}

	public function setCar(null|Car $car)
	{
		if(is_null($car)) $this->car_id = null;
		else $this->car_id = $car->getId();
	}

	public function setSchedule(null|Schedule $schedule)
	{
		if(is_null($schedule)) $this->schedule_id = null;
		else $this->schedule_id = $schedule->getId();
	}

	public function save(PDO $db)
	{
		$stmt = $db->prepare('UPDATE services SET client_id = ?, kms = ?, checkin_date = ?, checkout_date = ?, malfunction_description = ?, service_description = ?, car_id = ?, schedule_id = ? WHERE id = ?');
		$stmt->execute([$this->client_id, $this->kms, $this->checkin_date, $this->checkout_date, $this->malfunction_description, $this->service_description, $this->car_id, $this->schedule_id, $this->id]);
	}

}
?>
