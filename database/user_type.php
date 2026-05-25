<?php declare(strict_types =1);
class UserType{
	public int $id;
	public string $designation;
	function __construct(int $id, string $designation){
		$this->designation = $designation;
		$this->id = $id;
	}

	public static function getUserTypes(PDO $db):array{
		$stmt = $db->prepare('Select * from user_types');
		$stmt->execute();
		$user_types = array();
		while($user_type = $stmt->fetch()){
			$user_types[] = new UserType(
				$user_type['id']
				,$user_type['designation']);
		}
		
		return $user_types;
		// Select * from user_types;

	}

}
?>

