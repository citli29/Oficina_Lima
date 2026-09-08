<?php
declare(strict_types = 1);

namespace App\Services;

use App\Models\Notification;
use InvalidArgumentException;
use PDOException;
use RuntimeException;

class NotificationService
{
	private Notification $notificationModel;

	public function __construct(Notification $notificationModel)
	{
		return $this->notificationModel = $notificationModel;
	}

	public function listNotifications(array $filters, ?array $pagination = null): array
	{
		return $this->notificationModel->getNotificationsWithFilter($filters, $pagination);
	}

	public function showNotification(int $id):array
	{
		if(!$notification = $this->notificationModel->getNotificationById($id))
			throw new RuntimeException("Show Notification [ID Not Found]: {$id}", 403);
		return  $notification;
	}

	public function updateNotificationCheck(int $id,int $is_checked){
		try
		{
			$notification = $this->notificationModel->updateNotificationCheck($id,$is_checked);
			if(!$notification) 
			throw new InvalidArgumentException("Update Notification [Invalid ID]: {$id}.",400);
			return $notification;
		} catch (PDOException $e){
			throw new InvalidArgumentException("Update Notification Check. [{$e->errorInfo[2]}]",400);
		}
	}
}
