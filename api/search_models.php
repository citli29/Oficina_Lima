<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/database/connection.db.php');

header('Content-Type: application/json; charset=utf-8');

$db = getDatabaseConnection();

$query = trim($_GET['q'] ?? '');
$limit = (int)trim($_GET['limit'] ?? 100);
$offset = (int)trim($_GET['offset'] ?? 0);

try {

	if ($query !== '') {
		$stmt = $db->prepare("
			SELECT m.id, m.nome as modelo, ma.nome AS marca
			FROM modelos m
			JOIN marcas ma ON ma.id = m.marca_id
			WHERE m.nome LIKE :query OR ma.nome LIKE :query
			ORDER BY m.nome
			LIMIT :limit
			OFFSET :offset
			");
		$stmt->bindValue(':query', '%'.$query.'%', PDO::PARAM_STR);
	} else {
		$stmt = $db->prepare("
			SELECT m.id, m.nome as modelo, ma.nome AS marca
			FROM modelos m
			JOIN marcas ma ON ma.id = m.marca_id
			ORDER BY m.nome
			LIMIT :limit
			OFFSET :offset
			");
	}
	$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
	$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

	$stmt->execute();
	$data = $stmt->fetchAll();

	http_response_code(200);

	echo json_encode([
		'success' => true,
		'count' => count($data),
		'data' => $data
	]);

} catch (Exception $e) {

	http_response_code(500);

	echo json_encode([
		'success' => false,
		'message' => $e->getMessage()
	]);
}
