<?php declare(strict_types=1);
require_once __DIR__ . '/../utils/util.php';
require_once __DIR__ . '/product.php';
require_once __DIR__ . '/service.php';
class ServiceAppliedProduct{
	 
	private int $id;
	private int $service_id;
	private int $product_id;
	public int $quantity;
	public bool $is_applied;

	// product info 
	function __construct(int $id, int $service_id, int $product_id, int $quantity, bool $is_applied)
	{
		$this->id = $id;
		$this->service_id= $service_id;
		$this->product_id = $product_id;
		$this->quantity = $quantity;
		$this->is_applied = $is_applied;
	}

	public static function getServiceAppliedProductById(PDO $db,int $id):?ServiceAppliedProduct
	{
		$stmt = $db->prepare('SELECT id, service_id, product_id, quantity, is_applied from services_applied_products WHERE id = ?'
		);
		$stmt->execute([$id]);
		$ap_prod= $stmt->fetch();
		while($ap_prod){
			return new ServiceAppliedProduct(
				(int)$ap_prod['id'],
				(int)$ap_prod['service_id'],
				(int)$ap_prod['product_id'],
				(int)$ap_prod['quantity'],
				(bool)$ap_prod['is_applied']
			);
		}
		throw new Exception("Invalid Id: Service Applied Product {{$id}}");
	}

	public static function getAppliedProducts(PDO $db):array
	{
		$stmt = $db->prepare('SELECT id, service_id, product_id, quantity, is_applied from services_applied_products'
		);
		$stmt->execute();
		$ap_prods= array();
		while($ap_prod = $stmt->fetch()){
			$ap_prods[] = new ServiceAppliedProduct(
				(int)$ap_prod['id'],
				(int)$ap_prod['service_id'],
				(int)$ap_prod['product_id'],
				(int)$ap_prod['quantity'],
				(bool)$ap_prod['is_applied']
			);
		}
		return $ap_prods;
	}

	public static function getAppliedProductsByService(PDO $db, Service $service):array
	{
		$stmt = $db->prepare('SELECT id, service_id, product_id, quantity, is_applied from services_applied_products WHERE service_id = ?'
		);
		$stmt->execute([$service->getId()]);
		$ap_prods= array();
		while($ap_prod = $stmt->fetch()){
			$ap_prods[] = new ServiceAppliedProduct(
				(int)$ap_prod['id'],
				(int)$ap_prod['service_id'],
				(int)$ap_prod['product_id'],
				(int)$ap_prod['quantity'],
				(bool)$ap_prod['is_applied']
			);
		}
		return $ap_prods;
	}
	public static function getAppliedProductsByProduct(PDO $db, Product $product):array
	{
		$stmt = $db->prepare('SELECT id, service_id, product_id, quantity, is_applied from services_applied_products WHERE product_id = ?'
		);
		$stmt->execute([$product->getId()]);
		$ap_prods= array();
		while($ap_prod = $stmt->fetch()){
			$ap_prods[] = new ServiceAppliedProduct(
				(int)$ap_prod['id'],
				(int)$ap_prod['service_id'],
				(int)$ap_prod['product_id'],
				(int)$ap_prod['quantity'],
				(bool)$ap_prod['is_applied']
			);
		}
		return $ap_prods;
	}
	public function getService(PDO $db):Service{
		return Service::getServiceById($db, $this->service_id);
	}
	public function setService(Service $service){
		$this->service_id = $service->getId();
	}
	public function getProduct(PDO $db):Product{
		return Product::getProductById($db, $this->service_id);
	}
	public function setProduct(Product $product){
		$this->product_id = $product->getId();
	}
	public function save(PDO $db){
		$stmt = $db->prepare('UPDATE services_applied_products SET service_id = ?, product_id = ?, quantity = ?, is_applied = ? WHERE id = ?');
		$stmt->execute([$this->service_id, $this->product_id, $this->quantity, $this->is_applied, $this->id]);
	}

} ?>
