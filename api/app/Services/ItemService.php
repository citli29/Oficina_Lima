<?php
declare(strict_types = 1);

namespace App\Services;

use App\Models\Item;
use InvalidArgumentException;
use PDOException;
use RuntimeException;

class ItemService
{
	private Item $itemModel;

	public function __construct(Item $itemModel)
	{
		$this->itemModel = $itemModel;
	}

	public function listItems(?array $pagination = null): array
	{
		return $this->itemModel->getItems($pagination);
	}

	public function showItem(int $id): array
	{
		if(!$item = $this->itemModel->getItemById($id))
			throw new RuntimeException("Show Item [ID Not Found]: {$id}.",404);
		return $item;
	}

	public function createItem(array $data): array
	{
		try
		{
			return $this->itemModel->createItem($data);
		} catch (PDOException $e){
			throw new InvalidArgumentException(dbErrorMessage($e), 400);
		}
	}

	public function updateItem(int $id, array $data): array
	{
		try
		{
			$item = $this->itemModel->updateItem($id, $data);
			if(!$item)
			throw new InvalidArgumentException("Update Item [Invalid ID]: {$id}.",404);
			return $item;
		} catch (PDOException $e){
			throw new InvalidArgumentException(dbErrorMessage($e), 400);
		}
	}

	public function deleteItem(int $id): array
	{
		try
		{
			if(!$item = $this->itemModel->deleteItem($id))
				throw new InvalidArgumentException("Delete Item [Invalid ID]: {$id}.",404);
			return $item;
		}catch(PDOException $e)
		{
			throw new InvalidArgumentException(dbErrorMessage($e), 400);
		}
	}
}
