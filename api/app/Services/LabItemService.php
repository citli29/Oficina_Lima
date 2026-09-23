<?php
declare(strict_types = 1);

namespace App\Services;

use App\Models\LabItem;
use InvalidArgumentException;
use PDOException;
use RuntimeException;

class LabItemService
{
	private LabItem $labItemModel;

	public function __construct(LabItem $labItemModel)
	{
		$this->labItemModel = $labItemModel;
	}

	public function listByService(int $s_id, array $filters): array
	{
		return $this->labItemModel->getLabItemsByServiceWithFilter($s_id, $filters);
	}

	public function show(int $s_id, int $id): array
	{
		if(!$labItem = $this->labItemModel->getLabItemBySidId($s_id, $id))
			throw new RuntimeException("Show Lab Item [ID Not Found]: {$s_id} : {$id}.",404);
		return $labItem;
	}

	public function create(int $s_id, array $data): array
	{
		try
		{
			return $this->labItemModel->createLabItem($s_id, $data);
		} catch (PDOException $e){
			throw new InvalidArgumentException(dbErrorMessage($e), 400);
		}
	}

	public function delete(int $s_id, int $id): array
	{
		try
		{
			if(!$labItem = $this->labItemModel->deleteLabItemBySidId($s_id, $id))
				throw new InvalidArgumentException("Delete Lab Item [Invalid ID]: {$s_id} - {$id}.", 404);
			return $labItem;
		}catch(PDOException $e)
		{
			throw new InvalidArgumentException(dbErrorMessage($e), 409);
		}
	}
}
