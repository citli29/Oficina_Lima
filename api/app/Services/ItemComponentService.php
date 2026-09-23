<?php
declare(strict_types = 1);

namespace App\Services;

use App\Models\ItemComponent;
use InvalidArgumentException;
use PDOException;
use RuntimeException;

class ItemComponentService
{
	private ItemComponent $itemComponentModel;

	public function __construct(ItemComponent $itemComponentModel)
	{
		$this->itemComponentModel = $itemComponentModel;
	}

	public function listProperties(array $filters, ?array $pagination = null): array
	{
		return $this->itemComponentModel->getPropertiesWithFilter($filters, $pagination);
	}

	public function listActions(array $filters, ?array $pagination = null): array
	{
		return $this->itemComponentModel->getActionsWithFilter($filters, $pagination);
	}

	public function listActionTabledValues(array $filters, ?array $pagination = null): array
	{
		return $this->itemComponentModel->getActionTabledValuesWithFilter($filters, $pagination);
	}

	public function showProperty(int $id): array
	{
		if(!$property = $this->itemComponentModel->getPropertyById($id))
			throw new RuntimeException("Show Property [ID Not Found]: {$id}.",404);
		return $property;
	}

	public function showAction(int $id): array
	{
		if(!$action = $this->itemComponentModel->getActionById($id))
			throw new RuntimeException("Show Action [ID Not Found]: {$id}.",404);
		return $action;
	}

	public function showActionTabledValue(int $id): array
	{
		if(!$action_tabled_value = $this->itemComponentModel->getActionTabledValueById($id))
			throw new RuntimeException("Show Action Tabled Value [ID Not Found]: {$id}.",404);
		return $action_tabled_value;
	}

	public function createProperty(array $data): array
	{
		try
		{
			return $this->itemComponentModel->createProperty($data);
		} catch (PDOException $e){
			throw new InvalidArgumentException(dbErrorMessage($e), 400);
		}
	}

	public function createAction(array $data): array
	{
		try
		{
			return $this->itemComponentModel->createAction($data);
		} catch (PDOException $e){
			throw new InvalidArgumentException(dbErrorMessage($e), 400);
		}
	}

	public function createActionTabledValue(array $data): array
	{
		try
		{
			return $this->itemComponentModel->createActionTabledValue($data);
		} catch (PDOException $e){
			throw new InvalidArgumentException(dbErrorMessage($e), 400);
		}
	}

	public function updateProperty(int $id, array $data): array
	{
		try
		{
			$property = $this->itemComponentModel->updateProperty($id, $data);
			if(!$property)
			throw new InvalidArgumentException("Update Property [Invalid ID]: {$id}.",404);
			return $property;
		} catch (PDOException $e){
			throw new InvalidArgumentException(dbErrorMessage($e), 400);
		}
	}

	public function updateAction(int $id, array $data): array
	{
		try
		{
			$action = $this->itemComponentModel->updateAction($id, $data);
			if(!$action)
			throw new InvalidArgumentException("Update Action [Invalid ID]: {$id}.",404);
			return $action;
		} catch (PDOException $e){
			throw new InvalidArgumentException(dbErrorMessage($e), 400);
		}
	}

	public function updateActionTabledValue(int $id, array $data): array
	{
		try
		{
			$action_tabled_value = $this->itemComponentModel->updateActionTabledValue($id, $data);
			if(!$action_tabled_value)
			throw new InvalidArgumentException("Update Action Tabled Value [Invalid ID]: {$id}.",404);
			return $action_tabled_value;
		} catch (PDOException $e){
			throw new InvalidArgumentException(dbErrorMessage($e), 400);
		}
	}

	public function deleteProperty(int $id): array
	{
		try
		{
			if(!$property = $this->itemComponentModel->deleteProperty($id))
				throw new InvalidArgumentException("Delete Property [Invalid ID]: {$id}.",404);
			return $property;
		}catch(PDOException $e)
		{
			throw new InvalidArgumentException(dbErrorMessage($e), 409);
		}
	}

	public function deleteAction(int $id): array
	{
		try
		{
			if(!$action = $this->itemComponentModel->deleteAction($id))
				throw new InvalidArgumentException("Delete Action [Invalid ID]: {$id}.",404);
			return $action;
		}catch(PDOException $e)
		{
			throw new InvalidArgumentException(dbErrorMessage($e), 409);
		}
	}

	public function deleteActionTabledValue(int $id): array
	{
		try
		{
			if(!$action_tabled_value = $this->itemComponentModel->deleteActionTabledValue($id))
				throw new InvalidArgumentException("Delete Action Tabled Value [Invalid ID]: {$id}.",404);
			return $action_tabled_value;
		}catch(PDOException $e)
		{
			throw new InvalidArgumentException(dbErrorMessage($e), 409);
		}
	}
}
