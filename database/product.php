<?php declare(strict_types=1);
require_once __DIR__ . '/../utils/util.php';

class Product{
	 
	private int $id;
	public string $designation;
	public string $reference;
	private int $product_type_id;

	function __construct(int $id, string $designation, string $reference, int $product_type_id)
	{
		$this->id = $id;
		$this->designation = $designation;
		$this->reference = $reference;
		$this->product_type_id = $product_type_id;
	}

	public static function getProducts(PDO $db):array
	{
		$stmt = $db->prepare('Select id,designation,reference,product_type_id from products');
		$stmt->execute();
		$products = array();
		while($product = $stmt->fetch()){
			$products[] = new Product(
				(int)$product['id'],
				$product['designation'],
				$product['reference'],
				(int)$product['product_type_id']
			);
		}
		return $products;
	}

	public static function getProductById(PDO $db, int $id):?Product
	{
		$stmt = $db->prepare('SELECT id,designation, reference,product_type_id FROM products WHERE id = ?');
		$stmt->execute([$id]);
		$product = $stmt->fetch();
		if($product){ 
			return new Product(
				(int)$product['id'],
				$product['designation'],
				$product['reference'],
				(int)$product['product_type_id']
			);
		}
		throw new Exception("Invalid Id: Product {{$id}}");
	}

	public function getId()
	{
		return $this->id;
	}

	public function getProductType(PDO $db):ProductType
	{
		$product_type = ProductType::getProductTypeById($db,$this->product_type_id);
		return $product_type;
	}

	public function setProductType(ProductType $product_type)
	{
		$this->product_type_id = $product_type->getId();
	}

	public function save(PDO $db)
	{
		$stmt = $db->prepare('UPDATE products SET designation = ?, reference = ?, product_type_id = ? WHERE id = ?');
		$stmt->execute([$this->designation, $this->reference, $this->product_type_id, $this->id]);
	}

}
?>
