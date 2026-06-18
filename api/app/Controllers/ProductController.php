<?php declare(strict_types=1);

namespace App\Controllers;

use App\Models\Product;
use App\Services\ProductService;
use InvalidArgumentException;
use RuntimeException;
use PDO;

class ProductController
{
	private ProductService $service;
	public function __construct(PDO $db)
	{
		$model = new Product($db);
		$this->service = new ProductService($model);
	}
}
