<?php
declare(strict_types = 1);

namespace App\Services;

use App\Database\Database;
use App\Models\Service;
use App\Models\ServiceAssociation;
use InvalidArgumentException;
use PDOException;

class ServiceAssociationService
{
	private ServiceAssociation $model;
	private Service $serviceModel;

	public function __construct(ServiceAssociation $model, Service $serviceModel)
	{
		$this->model = $model;
		$this->serviceModel = $serviceModel;
	}

	public function list(array $filters, ?array $pagination): array
	{
		return $this->model->getAssociationsWithFilter($filters, $pagination);
	}

	public function listClusterMates(int $serviceId): array
	{
		return $this->model->getClusterMates($serviceId);
	}

	/**
	 * Links two services into one association, in a single transaction —
	 * either both end up linked (and synced) or nothing changes.
	 *
	 * $sourceServiceId (one of the two, optional) is the side whose header
	 * values the association keeps. Joining makes the insert trigger copy
	 * the values of the association's lowest-id service, which isn't
	 * necessarily the side the user picked, so the source's own values are
	 * read before linking and written back afterwards (the header-sync
	 * trigger then copies them to every service in the association).
	 */
	public function link(int $serviceId, int $otherServiceId, ?int $sourceServiceId = null): array
	{
		if ($serviceId === $otherServiceId)
			throw new InvalidArgumentException("Não é possível associar um serviço a si próprio.", 400);

		if ($sourceServiceId !== null && $sourceServiceId !== $serviceId && $sourceServiceId !== $otherServiceId)
			throw new InvalidArgumentException("O serviço de origem tem de ser um dos serviços a associar.", 400);

		try
		{
			return Database::transaction($this->model->getDb(), function () use ($serviceId, $otherServiceId, $sourceServiceId) {
				$this->linkInTransaction($serviceId, $otherServiceId, $sourceServiceId);
				return $this->model->getClusterMates($serviceId);
			});
		} catch (PDOException $e) {
			throw new InvalidArgumentException(dbErrorMessage($e), 400);
		}
	}

	/**
	 * Creates a new service and links it to $serviceId, in a single
	 * transaction — so a failed link never leaves a loose new service
	 * behind (and retrying can't create a duplicate).
	 */
	public function createAndLink(int $serviceId, array $newServiceData): array
	{
		try
		{
			return Database::transaction($this->model->getDb(), function () use ($serviceId, $newServiceData) {
				$newService = $this->serviceModel->createService($newServiceData);
				$this->linkInTransaction($serviceId, (int) $newService['id'], null);
				$this->model->markSameCarNoticeAsAssociation((int) $newService['id']);

				return [
					'service' => $this->serviceModel->getServiceById((int) $newService['id']),
					'cluster_mate_list' => $this->model->getClusterMates($serviceId),
				];
			});
		} catch (PDOException $e) {
			throw new InvalidArgumentException(dbErrorMessage($e), 400);
		}
	}

	// The link itself — callers must already be inside a transaction.
	private function linkInTransaction(int $serviceId, int $otherServiceId, ?int $sourceServiceId): void
	{
		if (!$this->model->serviceExists($serviceId))
			throw new InvalidArgumentException("O serviço indicado não existe.", 404);

		if (!$this->model->serviceExists($otherServiceId))
			throw new InvalidArgumentException("O serviço indicado não existe.", 404);

		$clusterA = $this->model->getClusterNr($serviceId);
		$clusterB = $this->model->getClusterNr($otherServiceId);

		if ($clusterA !== null && $clusterB !== null && $clusterA !== $clusterB)
			throw new InvalidArgumentException("Os serviços já pertencem a clusters diferentes. Utilize a fusão de clusters para os juntar.", 400);

		// Read before linking: the insert trigger may overwrite them.
		$sourceFields = $sourceServiceId !== null ? $this->model->getSyncedFields($sourceServiceId) : null;

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

		if ($sourceFields !== null)
			$this->model->applySyncedFields($sourceServiceId, $sourceFields);
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
			return Database::transaction($this->model->getDb(), function () use ($serviceId) {
				$existing = $this->model->deleteAssociation($serviceId);

				if (!$existing)
					throw new InvalidArgumentException("O serviço não pertence a nenhum cluster.", 404);

				// An association of one isn't an association — dissolve it.
				$solo = $this->model->getSoleClusterMember((int) $existing['cluster_nr']);
				if ($solo !== null)
					$this->model->deleteAssociation($solo);

				return $existing;
			});
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
