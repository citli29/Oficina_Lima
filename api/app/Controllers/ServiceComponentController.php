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
		try{
			$filters = [
				'user_name' => isset($_GET['user_name']) ? $_GET['user_name'] : null,
				'user_id' => isset($_GET['user_id']) ? $_GET['user_id'] : null,
				'date' => isset($_GET['date']) ? $_GET['date'] : null,
			];
			$sut_list = $this->service->listSUTByService($s_id,$filters);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'client_list'=>$sut_list
			]);
		}catch(RuntimeException $e){
			http_response_code((int)$e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function postSUTs(int $s_id): void
	{
	}
	/*
 * dont know if here but:
 *
 * GET: check if s_id matches the sut(id).service_id 
 * 	if not: 404
 * 	if yes: show
 * 
 * POST: dont send the service_id, get it from the url
 * */
	public function getSUTById(int $s_id,int $id): void
	{
		echo $s_id;
		echo " wwwweeewwww ";
		echo $id;
	}
}
