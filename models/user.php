<?php 
class User{
	public int $id;	
	public string $email;
	public string $name;

	function __construct(int $id, string $email, string $name){
		$this->id = $id;
		$this->email = $email;
		$this->name = $name;
	}

	static function getUsers(PDO $db, int $count) : array {
		$stmt = $db->prepare(' SELECT id, nome, email FROM utilizadores LIMIT ?');
		$stmt->execute(array($count));

		$users = array();

		while ($user = $stmt->fetch()) {
			$users[] = new User(
				$user['id'], 
				$user['email'],
				$user['nome']
			);
		}

		return $users;
	}
	static function createUser(PDO $db) : ?User {
		throw new Exception('not implemented yer');
	}
}
?>
