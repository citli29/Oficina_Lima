<?php
declare(strict_types = 1);

namespace App\Services;

use App\Models\ServiceAssociation;
use InvalidArgumentException;
use PDOException;

class ServiceAssociationService
{
	private ServiceAssociation $model;

	public function __construct(ServiceAssociation $model)
	{
		$this->model = $model;
	}

	public function list(array $filters, ?array $pagination): array
	{
		return $this->model->getAssociationsWithFilter($filters, $pagination);
	}

	public function listClusterMates(int $serviceId): array
	{
		return $this->model->getClusterMates($serviceId);
	}

	public function link(int $serviceId, int $otherServiceId): array
	{
		if ($serviceId === $otherServiceId)
			throw new InvalidArgumentException("Não é possível associar um serviço a si próprio.", 400);

		try
		{
			if (!$this->model->serviceExists($serviceId))
				throw new InvalidArgumentException("O serviço indicado não existe.", 404);

			if (!$this->model->serviceExists($otherServiceId))
				throw new InvalidArgumentException("O serviço indicado não existe.", 404);

			$clusterA = $this->model->getClusterNr($serviceId);
			$clusterB = $this->model->getClusterNr($otherServiceId);

			if ($clusterA !== null && $clusterB !== null && $clusterA !== $clusterB)
				throw new InvalidArgumentException("Os serviços já pertencem a clusters diferentes. Utilize a fusão de clusters para os juntar.", 400);

			if ($clusterA !== null) {
				if ($clusterB === null)
					$this->model->insertAssociation($otherServiceId, $clusterA);
			} elseif ($clusterB !== null) {
				$this->model->insertAssociation($serviceId, $clusterB);
			} else {
				$newCluster = $this->model->getNextClusterNr();
				$this->model->insertAssociation($serviceId, $newCluster);
				$this->model->insertAssociation($otherServiceId, $newCluster);
			}

			return $this->model->getClusterMates($serviceId);
		} catch (PDOException $e) {
			throw new InvalidArgumentException(dbErrorMessage($e), 400);
		}
	}

	public function move(int $serviceId, int $otherServiceId): array
	{
		if ($serviceId === $otherServiceId)
			throw new InvalidArgumentException("Não é possível mover um serviço para si próprio.", 400);

		try
		{
			$oldCluster = $this->model->getClusterNr($serviceId);
			if ($oldCluster === null)
				throw new InvalidArgumentException("O serviço não pertence a nenhum cluster.", 400);

			$targetCluster = $this->model->getClusterNr($otherServiceId);
			if ($targetCluster === null)
				throw new InvalidArgumentException("O serviço destino não pertence a nenhum cluster.", 400);

			if ($oldCluster === $targetCluster)
				return $this->model->getClusterMates($serviceId);

			$this->model->updateClusterNr($serviceId, $targetCluster);

			$solo = $this->model->getSoleClusterMember($oldCluster);
			if ($solo !== null)
				$this->model->deleteAssociation($solo);

			return $this->model->getClusterMates($serviceId);
		} catch (PDOException $e) {
			throw new InvalidArgumentException(dbErrorMessage($e), 400);
		}
	}

	public function unlink(int $serviceId): array
	{
		try
		{
			$existing = $this->model->deleteAssociation($serviceId);

			if (!$existing)
				throw new InvalidArgumentException("O serviço não pertence a nenhum cluster.", 404);

			$solo = $this->model->getSoleClusterMember((int) $existing['cluster_nr']);
			if ($solo !== null)
				$this->model->deleteAssociation($solo);

			return $existing;
		} catch (PDOException $e) {
			throw new InvalidArgumentException(dbErrorMessage($e), 409);
		}
	}

	public function merge(int $serviceIdA, int $serviceIdB): array
	{
		if ($serviceIdA === $serviceIdB)
			throw new InvalidArgumentException("Não é possível fundir um serviço com ele próprio.", 400);

		try
		{
			$clusterA = $this->model->getClusterNr($serviceIdA);
			$clusterB = $this->model->getClusterNr($serviceIdB);

			if ($clusterA === null || $clusterB === null)
				throw new InvalidArgumentException("Ambos os serviços têm de pertencer a um cluster para poderem ser fundidos.", 400);

			if ($clusterA === $clusterB)
				return $this->model->getAssociationsWithFilter(['cluster_nr' => $clusterA])['rows'];

			$min = min($clusterA, $clusterB);
			$max = max($clusterA, $clusterB);

			$this->model->reassignClusterNr($max, $min);

			return $this->model->getAssociationsWithFilter(['cluster_nr' => $min])['rows'];
		} catch (PDOException $e) {
			throw new InvalidArgumentException(dbErrorMessage($e), 400);
		}
	}
}
