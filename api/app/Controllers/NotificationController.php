<?php declare(strict_types=1);

namespace App\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use InvalidArgumentException;
use RuntimeException;
use PDO;

class NotificationController
{
	private NotificationService $service;
	public function __construct(PDO $db)
	{
		$model = new Notification($db);
		$this->service = new NotificationService($model);
	}

	public function getNotifications(): void
	{
		try{
			$filters = [
				'n-type' => isset($_GET['n-type']) ? normalize($_GET['n-type']):null,
				'is_checked' => match ($_GET['is_checked'] ?? null) {
					'true' => 1,
					'false' => 0,
					default => null,
				}
			];
			$notification_list = $this->service->listNotifications($filters);

			http_response_code(200);
			header('Content-Type: application/json');
			echo(json_encode([
				'success'=>true,
				'notification_list'=> $notification_list
			]));
		}catch(RuntimeException $e){
			http_response_code((int)$e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function getNotification(int $id):void
	{
		try{
			$notification = $this->service->showNotification($id);
			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success'=>true,
				'notification' => $notification

			]);
		}catch(RuntimeException $e){
			http_response_code((int)$e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function putNotificationCheck(int $id):void
	{
		try {
			$notification = $this->service->updateNotificationCheck($id, 1);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'notification'=>$notification
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code((int)$e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}
	public function putNotificationUnCheck(int $id):void
	{
		try {
			$notification = $this->service->updateNotificationCheck($id, 0);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'notification'=>$notification
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code((int)$e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}
}
