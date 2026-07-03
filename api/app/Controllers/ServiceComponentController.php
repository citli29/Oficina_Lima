<?php declare(strict_types=1);

namespace App\Controllers;

use App\Models\ServiceComponent;
use App\Services\ServiceComponentService;
use InvalidArgumentException;
use RuntimeException;
use PDO;

class ServiceComponentController
{
	private ServiceComponentService $service;
	public function __construct(PDO $db)
	{
		$model = new ServiceComponent($db);
		$this->service = new ServiceComponentService($model);
	}

	public function getSUTs(int $s_id): void
	{
		try{
			$filters = [
				'user_name' => isset($_GET['user_name']) ? $_GET['user_name'] : null,
				'user_id' => isset($_GET['user_id']) ? $_GET['user_id'] : null,
				'date' => isset($_GET['date']) ? $_GET['date'] : null,
			];
			$sut_list = $this->service->listSUTByService($s_id,$filters);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'sut_list'=>$sut_list
			]);
		}catch(RuntimeException $e){
			http_response_code((int)$e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function postSUTs(int $s_id): void
	{
		try {
			$data = json_decode(file_get_contents('php://input'), true);
			if(is_null($data))
			throw new InvalidArgumentException( "JSON Body Invalid.", 400);

			$sut = $this->service->createSUT($s_id,$data);

			http_response_code(201);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'sut'=>$sut
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code((int)$e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}
	/*
 * dont know if here but:
 *
 * GET: check if s_id matches the sut(id).service_id 
 * 	if not: 404
 * 	if yes: show
 * 
 * POST: dont send the service_id, get it from the url
 * */
	public function getSUT(int $s_id,int $id): void
	{
		try {
			$sut = $this->service->showSUT($s_id,$id);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'sut'=>$sut
			]);
		} catch (RuntimeException$e) {
			http_response_code((int)$e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function putSUT(int $s_id, int $id): void
	{
		try {
			$data = json_decode(file_get_contents('php://input'), true);

			if(is_null($data))
			throw new InvalidArgumentException( "JSON Body Invalid.", 400);

			$sut = $this->service->updateSUT($s_id,$id, $data);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'sut'=>$sut
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code((int)$e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function deleteSUT(int $s_id, int $id): void
	{
		try {

			$sut = $this->service->deleteSUT($s_id,$id);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'sut' => $sut
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code((int)$e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function getAppliedProducts(int $s_id): void
	{
		try{
			$filters = [
				'product_name' => isset($_GET['product_name']) ? $_GET['product_name'] : null,
				'product_id' => isset($_GET['product_id']) ? $_GET['product_id'] : null,
				'product_reference' => isset($_GET['product_reference']) ? $_GET['product_reference'] : null,
				'is_applied' => isset($_GET['is_applied']) ? $_GET['is_applied'] : null,
			];
			$sap_list = $this->service->listSAPByService($s_id,$filters);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'sap_list'=>$sap_list
			]);
		}catch(RuntimeException $e){
			http_response_code((int)$e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function postAppliedProducts(int $s_id): void
	{
		try {
			$data = json_decode(file_get_contents('php://input'), true);
			if(is_null($data))
			throw new InvalidArgumentException( "JSON Body Invalid.", 400);

			$sap = $this->service->createSAP($s_id,$data);

			http_response_code(201);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'sap'=>$sap
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code((int)$e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}
	/*
 * dont know if here but:
 *
 * GET: check if s_id matches the sut(id).service_id 
 * 	if not: 404
 * 	if yes: show
 * 
 * POST: dont send the service_id, get it from the url
 * */
	public function getAppliedProduct(int $s_id,int $id): void
	{
		try {
			$sap = $this->service->showSAP($s_id,$id);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'sap'=>$sap
			]);
		} catch (RuntimeException $e) {
			http_response_code((int)$e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function putAppliedProduct(int $s_id, int $id): void
	{
		try {
			$data = json_decode(file_get_contents('php://input'), true);

			if(is_null($data))
			throw new InvalidArgumentException( "JSON Body Invalid.", 400);

			$sap = $this->service->updateSAP($s_id,$id, $data);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'sap'=>$sap
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code((int)$e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}

	public function deleteAppliedProduct(int $s_id, int $id): void
	{
		try {
			$sap = $this->service->deleteSAP($s_id,$id);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'sap' => $sap
			]);
		} catch (InvalidArgumentException $e) {
			http_response_code((int)$e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}
	}
	public function getServiceUserTimes()
	{
		try{
			$filters = [
				'service_id' => isset($_GET['service_id']) ? $_GET['service_id'] : null,
			];
			$sut_list = $this->service->listSUTs($filters);

			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode([
				'success' => true,
				'sut_list'=>$sut_list
			]);
		}catch(RuntimeException $e){
			http_response_code((int)$e->getCode());
			echo json_encode(['error' => $e->getMessage()]);
		}

	}
}
