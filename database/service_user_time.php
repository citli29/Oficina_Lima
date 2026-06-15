<?php declare(strict_types=1);
require_once __DIR__ . '/../utils/util.php';
class ServiceUserTime{
	 
	private int $id;
	private int $service_id;
	private int $user_id;
	public int $minutes;
	public string $ut_date;

	function __construct(int $id, int $service_id, int $user_id, int $minutes, string $ut_date)
	{
		$this->id = $id;
		$this->service_id = $service_id;
		$this->user_id = $user_id;
		$this->minutes = $minutes;
		$this->ut_date = $ut_date;
	}
	public static function create(PDO $db, Service $service, User $user, int $minutes, string $ut_date):ServiceUserTime
	{
		$stmt = $db->prepare("INSERT INTO services_user_time(service_id,user_id, minutes, ut_date) VALUES (?, ?, ?, ?)");
		$stmt->execute([$service->getId(), $user->getId(), $minutes, $ut_date]);
		
			
		//Last inserted 
		$stmt = $db->prepare( "SELECT * FROM services_user_time WHERE rowid = last_insert_rowid()");
		$stmt->execute();
		$servUT= $stmt->fetch();
		if($servUT){ 
			return new ServiceUserTime(
				(int)$servUT['id'],
				(int)$servUT['service_id'],
				(int)$servUT['user_id'],
				(int)$servUT['minutes'],
				$servUT['ut_date']
			);
		}
		throw new Exception("Create Error: Service User Time");
	}

	public static function getServiceUserTimeByService(PDO $db, Service $service):array
	{
		$stmt = $db->prepare('SELECT id, service_id, user_id, minutes, ut_date FROM services_user_time WHERE service_id = ?');
		$stmt->execute([$service->getId()]);
		$servUTs = array();
		while($servUT = $stmt->fetch()){
			$servUTs[] = new ServiceUserTime(
				(int)$servUT['id'],
				(int)$servUT['service_id'],
				(int)$servUT['user_id'],
				(int)$servUT['minutes'],
				$servUT['ut_date'],
			);
	}
		return $servUTs;
	}

	public static function getServiceUserTimeByUser(PDO $db, User $user):array
	{
		$stmt = $db->prepare('SELECT id, service_id, user_id, minutes, ut_date FROM services_user_time WHERE user_id = ?');
		$stmt->execute([$user->getId()]);
		$servUTs = array();
		while($servUT = $stmt->fetch()){
			$servUTs[] = new ServiceUserTime(
				(int)$servUT['id'],
				(int)$servUT['service_id'],
				(int)$servUT['user_id'],
				(int)$servUT['minutes'],
				$servUT['ut_date'],
			);
	}
		return $servUTs;
	}
	public static function getServiceUserTimes(PDO $db):array
	{
		$stmt = $db->prepare('SELECT id, service_id, user_id, minutes, ut_date FROM services_user_time');
		$stmt->execute();
		$servUTs = array();
		while($servUT = $stmt->fetch()){
			$servUTs[] = new ServiceUserTime(
				(int)$servUT['id'],
				(int)$servUT['service_id'],
				(int)$servUT['user_id'],
				(int)$servUT['minutes'],
				$servUT['ut_date'],
			);
		}
		return $servUTs;
	}

	public static function getServiceUserTimeById(PDO $db, int $id):?ServiceUserTime
	{
		$stmt = $db->prepare('SELECT id, service_id, user_id, minutes, ut_date FROM services_user_time WHERE id = ?');
		$stmt->execute([$id]);
		$servUT= $stmt->fetch();
		if($servUT){ 
			return new ServiceUserTime(
				(int)$servUT['id'],
				(int)$servUT['service_id'],
				(int)$servUT['user_id'],
				(int)$servUT['minutes'],
				$servUT['ut_date']
			);
		}
		throw new Exception("Invalid Id: Service User Time {{$id}}");
	}

	public function getId()
	{
		return $this->id;
	}

	public function getService(PDO $db):Service
	{
		$service = Service::getServiceById($db,$this->service_id);
		return $service;
	}

	public function getUser(PDO $db):User
	{
		$user = User::getUserById($db,$this->user_id);
		return $user;
	}

	public function setService(Service $service)
	{
		$this->service_id = $service->getId();
	}
	public function setUser(User $user)
	{
		$this->user_id = $user->getId();
	}

	public function save(PDO $db)
	{
		$stmt = $db->prepare('UPDATE services_user_time SET service_id = ?, user_id = ?, minutes = ?, ut_date = ? WHERE id = ?');
		$stmt->execute([$this->service_id, $this->user_id, $this->minutes, $this->ut_date, $this->id]);
	}

	public function delete(PDO $db)
	{
		$stmt = $db->prepare('DELETE FROM services_user_time WHERE id = ?');
		$stmt->execute([$this->id]);
	}
}
?>
