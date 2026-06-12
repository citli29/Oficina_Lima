<?php declare(strict_types=1);
require_once __DIR__ . '/../utils/util.php';
class User{
	 
	private int $id;
	public string $name;
	public string $email;
	private int $user_type_id;
	public ?string $profile_pic;

	function __construct(int $id, string $name, string $email, int $user_type_id,?string $profile_pic = null)
	{
		$this->id = $id;
		$this->name = $name;
		$this->email = $email;
		$this->user_type_id = $user_type_id;
		$this->profile_pic = $profile_pic;
	}

	public static function getUsers(PDO $db):array
	{
		$stmt = $db->prepare('Select id,name, email,user_type_id, profile_pic from users');
		$stmt->execute();
		$users = array();
		while($user = $stmt->fetch()){
			$users[] = new User(
				(int)$user['id'],
				$user['name'],
				$user['email'],
				(int)$user['user_type_id'],
				$user['profile_pic'] ?? null
			);
		}
		return $users;
	}

	public static function getUserById(PDO $db, int $id):?User
	{
		$stmt = $db->prepare('SELECT id,name, email,user_type_id, profile_pic FROM users WHERE id = ?');
		$stmt->execute([$id]);
		$user= $stmt->fetch();
		if($user){ 
			return new User(
				(int)$user['id'],
				$user['name'],
				$user['email'],
				(int)$user['user_type_id'],
				$user['profile_pic'],
				
			);
		}
		throw new Exception("Invalid Id: User {{$id}}");
	}

	public function getId()
	{
		return $this->id;
	}

	public function getUserType(PDO $db):UserType
	{
		$user_type = UserType::getUserTypeById($db,$this->user_type_id);
		return $user_type;
	}

	public function setUserType(UserType $user_type)
	{
		$this->user_type_id = $user_type->getId();
	}

	public function save(PDO $db)
	{
		$stmt = $db->prepare('UPDATE users SET name = ?, email = ?, user_type_id = ?, profile_pic = ? WHERE id = ?');
		$stmt->execute([$this->name, $this->email, $this->user_type_id, $this->profile_pic, $this->id]);
	}

}
?>
