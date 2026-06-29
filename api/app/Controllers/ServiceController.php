<?php declare(strict_types=1);

namespace App\Controllers;

use App\Models\Client;
use App\Models\Schedule;
use App\Services\ScheduleService;
use InvalidArgumentException;
use RuntimeException;
use PDO;

class ClientController
{
	private ScheduleService $service;
	public function __construct(PDO $db)
	{
		$model = new Schedule($db);
		$this->service = new ScheduleService($model);
	}

	public function getSchedules(): void
	{
		try{
			$filters = [
				/*'name' => isset($_GET['name']) ? $_GET['name'] : null,
				'phone' => isset($_GET['phone']) ? $_GET['phone'] : null,
				'email' => isset($_GET['email']) ? $_GET['email'] : null,*/
			];
			$schedule_list = $this->service->listSchedules($filters);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'schedule_list'=>$schedule_list
			]);
		}catch(RuntimeException $e){
			http_response_code((int)$e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}
}
