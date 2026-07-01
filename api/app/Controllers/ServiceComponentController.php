<?php declare(strict_types=1);

namespace App\Controllers;

use App\Models\ServiceComponent;
use App\Services\ServiceComponentService;
use InvalidArgumentException;
use RuntimeException;
use PDO;

class ServiceComponentController
{
	private ServiceComponentService $service;
	public function __construct(PDO $db)
	{
		$model = new ServiceComponent($db);
		$this->service = new ServiceComponentService($model);
	}

	public function getSUTs(int $s_id): void
	{
	}
	public function getSUTById(int $s_id,int $id): void
	{
		echo $s_id;
		echo " wwwweeewwww ";
		echo $id;
	}
}
