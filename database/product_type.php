<?php declare(strict_types =1);
require_once __DIR__ . '/../utils/util.php';
class ProductType{

	private int $id;
	public string $designation;

	function __construct(int $id, string $designation)
	{
		$this->designation = $designation;
		$this->id = $id;
	}

	public static function getProductTypes(PDO $db):array
	{
		$stmt = $db->prepare('Select id,designation from product_types');
		$stmt->execute();
		$product_types = array();
		while($product_type = $stmt->fetch()){
			$product_types[] = new ProductType(
				(int)$product_type['id'],
				$product_type['designation']
			);
		}
		return $product_types;
	}

	public static function getProductTypeById(PDO $db, int $id):?ProductType
	{
		$stmt = $db->prepare('SELECT id,designation FROM product_types WHERE id=?');
		$stmt->execute([$id]);
		$product_type = $stmt->fetch();
		if($product_type){ 
			return new ProductType(
				(int)$product_type['id'],
				$product_type['designation']
			);
		}
		throw new Exception("Invalid Id: ProductType Type {{$id}}");
	}

	public function getId()
	{
		return $this->id;
	}

	public function save(PDO $db)
	{
		$stmt = $db->prepare('UPDATE product_types SET designation = ? WHERE id = ?');
		$stmt->execute([$this->designation,$this->id]);
	}
	public function delete(PDO $db)
	{
		$stmt = $db->prepare('DELETE FROM product_types WHERE id = ?');
		$stmt->execute([$this->id]);
	}
}
?>

