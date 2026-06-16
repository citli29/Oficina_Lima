<?php declare(strict_types=1);
require_once __DIR__ . '/../utils/util.php';
require_once __DIR__ . '/product.php';
require_once __DIR__ . '/service.php';
class ServiceAppliedProduct{
	 
	private int $service_id;
	public Product $product;
	public int $quantity;
	public bool $is_applied;

	// product info 
	function __construct(int $service_id, Product $product, int $quantity, bool $is_applied)
	{
		$this->service_id= $service_id;
		$this->product = $product;
		$this->quantity = $quantity;
		$this->is_applied = $is_applied;
	}

	public static function getAppliedProducts(PDO $db):array
	{
		$stmt = $db->prepare('SELECT
			p.id AS p_id,
			p.designation AS p_designation,
			p.reference AS p_reference,
			p.product_type_id AS p_type_id,
			sap.service_id AS service_id,
			sap.quantity AS quantity,
			sap.is_applied AS is_applied
			FROM products p
			JOIN services_applied_products sap
			ON p.id = sap.product_id'
		);
		$stmt->execute();
		$ap_prods= array();
		while($ap_prod = $stmt->fetch()){
			$ap_prods[] = new ServiceAppliedProduct(
				(int)$ap_prod['service_id'],
				new Product(
					(int)$ap_prod['p_id'],
					$ap_prod['p_designation'],
					$ap_prod['p_reference'],
					(int)$ap_prod['p_type_id']),
				(int)$ap_prod['quantity'],
				(bool)$ap_prod['is_applied']
			);
		}
		return $ap_prods;
	}

	public static function getAppliedProductsByService(PDO $db, Service $service):array
	{
		$stmt = $db->prepare('SELECT
			p.id AS p_id,
			p.designation AS p_designation,
			p.reference AS p_reference,
			p.product_type_id AS p_type_id,
			sap.service_id AS service_id,
			sap.quantity AS quantity,
			sap.is_applied AS is_applied
			FROM products p
			JOIN services_applied_products sap
			ON p.id = sap.product_id
			WHERE sap.service_id = ?'
		);
		$stmt->execute([$service->getId()]);
		$ap_prods= array();
		while($ap_prod = $stmt->fetch()){
			$ap_prods[] = new ServiceAppliedProduct(
				(int)$ap_prod['service_id'],
				new Product(
					(int)$ap_prod['p_id'],
					$ap_prod['p_designation'],
					$ap_prod['p_reference'],
					(int)$ap_prod['p_type_id']),
				(int)$ap_prod['quantity'],
				(bool)$ap_prod['is_applied']
			);
		}
		return $ap_prods;
	}
	public static function getAppliedProductsByProduct(PDO $db, Product $product):array
	{
		$stmt = $db->prepare('SELECT
			p.id AS p_id,
			p.designation AS p_designation,
			p.reference AS p_reference,
			p.product_type_id AS p_type_id,
			sap.service_id AS service_id,
			sap.quantity AS quantity,
			sap.is_applied AS is_applied
			FROM products p
			JOIN services_applied_products sap
			ON p.id = sap.product_id
			WHERE sap.product_id = ?'
		);
		$stmt->execute([$product->getId()]);
		$ap_prods= array();
		while($ap_prod = $stmt->fetch()){
			$ap_prods[] = new ServiceAppliedProduct(
				(int)$ap_prod['service_id'],
				new Product(
					(int)$ap_prod['p_id'],
					$ap_prod['p_designation'],
					$ap_prod['p_reference'],
					(int)$ap_prod['p_type_id']),
				(int)$ap_prod['quantity'],
				(bool)$ap_prod['is_applied']
			);
		}
		return $ap_prods;
	}
}
?>
