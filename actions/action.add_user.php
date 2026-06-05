<?php
require_once($_SERVER['DOCUMENT_ROOT'] .'/utils/session.php');

$session = new Session();
$db = getDatabaseConnection();
if(!isset($_POST['nome']) || !isset($_POST['email']))	{
	$session->addMessage('error', 'Preencha todos os campos.');
}

$user = User::createUser($db);

$session->addMessage('success', 'Utilizador adicionado com sucesso.');
exit;
