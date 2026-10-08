<?php declare(strict_types=1);

namespace App\Controllers;

use App\Models\Service;
use App\Models\ServiceAssociation;
use App\Services\ServiceAssociationService;
use InvalidArgumentException;
use RuntimeException;
use PDO;

require_once __DIR__ .'/../../../utils/util.php';

class ServiceAssociationController
{
	private ServiceAssociationService $service;

	public function __construct(PDO $db)
	{
		$model = new ServiceAssociation($db);
		$this->service = new ServiceAssociationService($model, new Service($db));
	}

	public function getServiceAssociations(): void
	{
		try{
			$filters = [
				'cluster_nr' => isset($_GET['cluster_nr']) ? $_GET['cluster_nr'] : null,
				'service_id' => isset($_GET['service_id']) ? $_GET['service_id'] : null,
			];
			$pagination = parsePagination($_GET);

			$result = $this->service->list($filters, $pagination);

			$response = [
				'success' => true,
				'service_association_list' => $result['rows'],
			];

			if ($pagination !== null) {
				$response['pagination'] = [
					'page' => $pagination['page'],
					'per_page' => $pagination['per_page'],
					'total' => $result['total'],
					'total_pages' => (int) ceil($result['total'] / $pagination['per_page']),
				];
			}

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode($response);
		}catch(RuntimeException $e){
			http_response_code(httpStatusFromException($e));
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function getServiceAssociationsByService(int $s_id): void
	{
		try{
			$cluster_mate_list = $this->service->listClusterMates($s_id);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'cluster_mate_list' => $cluster_mate_list,
			]);
		}catch(RuntimeException $e){
			http_response_code(httpStatusFromException($e));
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function postServiceAssociation(int $s_id): void
	{
		try {
			$data = json_decode(file_get_contents('php://input'), true);

			// Two forms, each done in one transaction:
			//  - { new_service: {...} }: create that service and link it here.
			//  - { service_id, source_service_id? }: link an existing service;
			//    source_service_id (either of the two) is the side whose
			//    header values the association keeps.
			if (is_array($data) && isset($data['new_service']) && is_array($data['new_service'])) {
				$result = $this->service->createAndLink($s_id, $data['new_service']);

				http_response_code(201);
				header('Content-Type: application/json');
				echo json_encode(['success' => true] + $result);
				return;
			}

			if (is_null($data) || empty($data['service_id']))
				throw new InvalidArgumentException("JSON Body Invalid.", 400);

			$sourceServiceId = !empty($data['source_service_id']) ? (int) $data['source_service_id'] : null;
			$cluster_mate_list = $this->service->link($s_id, (int) $data['service_id'], $sourceServiceId);

			http_response_code(201);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'cluster_mate_list' => $cluster_mate_list,
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code(httpStatusFromException($e));
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function putServiceAssociation(int $s_id): void
	{
		try {
			$data = json_decode(file_get_contents('php://input'), true);
			if (is_null($data) || empty($data['service_id']))
				throw new InvalidArgumentException("JSON Body Invalid.", 400);

			$cluster_mate_list = $this->service->move($s_id, (int) $data['service_id']);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'cluster_mate_list' => $cluster_mate_list,
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code(httpStatusFromException($e));
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function deleteServiceAssociation(int $s_id): void
	{
		try {
			$association = $this->service->unlink($s_id);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'service_association' => $association,
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code(httpStatusFromException($e));
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function putServiceAssociationMerge(): void
	{
		try {
			$data = json_decode(file_get_contents('php://input'), true);
			if (is_null($data) || empty($data['service_id_a']) || empty($data['service_id_b']))
				throw new InvalidArgumentException("JSON Body Invalid.", 400);

			$cluster = $this->service->merge((int) $data['service_id_a'], (int) $data['service_id_b']);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'cluster' => $cluster,
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code(httpStatusFromException($e));
			echo json_encode(['error' => $e->getMessage()]);
		}
	}
}
