<?php declare(strict_types =1);
require_once($_SERVER['DOCUMENT_ROOT'].'/templates/common.tpl.php');

$ch = curl_init('http://127.0.0.1:8000/api/search_models.php');

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 3,
]);

$response = curl_exec($ch);

if ($response === false) {
    throw new Exception(curl_error($ch));
}

$response_ar = json_decode($response, true);
$data = $response_ar['data'];
echo drawTable($data);
?>
