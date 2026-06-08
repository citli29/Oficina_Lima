<?php declare(strict_types =1);
class UserType{
	public int $id;
	public string $designation;
	function __construct(int $id, string $designation){
		$this->designation = $designation;
		$this->id = $id;
	}

	public static function getUserTypes(PDO $db):array{
		$stmt = $db->prepare('Select id,designation from user_types');
		$stmt->execute();
		$user_types = array();
		while($user_type = $stmt->fetch()){
			$user_types[] = new UserType(
				(int)$user_type['id']
				,$user_type['designation']);
		}
		return $user_types;
	}
	public static function getUserTypeById(PDO $db, int $id):?UserType{
		$stmt = $db->prepare('SELECT id,designation FROM user_types WHERE id=?');
		$stmt->execute([$id]);
		$user_type = $stmt->fetch();
		if($user_type){ 
			return new UserType((int)$user_type['id'],$user_type['designation']);
		}
		throw new Exception("Invalid Id: User Type.{{$id}}");
	}
}
?>

