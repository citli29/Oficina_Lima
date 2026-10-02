<?php
declare(strict_types = 1);

namespace App\Services;

use App\Models\Event;
use InvalidArgumentException;
use PDOException;
use RuntimeException;

class EventService
{
	private Event $eventModel;

	public function __construct(Event $eventModel)
	{
		$this->eventModel = $eventModel;
	}

	public function listEvents(array $filters, ?array $pagination = null): array
	{
		return $this->eventModel->getEventsWithFilter($filters, $pagination);
	}

	public function showEvent(int $id): array
	{
		if (!$event = $this->eventModel->getEventById($id))
			throw new RuntimeException("Show Event [ID Not Found]: {$id}.", 404);
		return $event;
	}

	public function createEvent(array $data): array
	{
		try {
			return $this->eventModel->createEvent($data);
		} catch (PDOException $e) {
			throw new InvalidArgumentException(dbErrorMessage($e), 400);
		}
	}

	public function updateEvent(int $id, array $data): array
	{
		try {
			$event = $this->eventModel->updateEvent($id, $data);
			if (!$event)
				throw new InvalidArgumentException("Update Event [Invalid ID]: {$id}.", 404);
			return $event;
		} catch (PDOException $e) {
			throw new InvalidArgumentException(dbErrorMessage($e), 400);
		}
	}

	public function deleteEvent(int $id): array
	{
		try {
			if (!$event = $this->eventModel->deleteEvent($id))
				throw new InvalidArgumentException("Delete Event [Invalid ID]: {$id}.", 404);
			return $event;
		} catch (PDOException $e) {
			throw new InvalidArgumentException(dbErrorMessage($e), 400);
		}
	}
}
