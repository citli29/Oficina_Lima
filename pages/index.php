<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/templates/common.tpl.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/models/user.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/database/connection.db.php');

$db = getDatabaseConnection();
$users = User::getUsers($db, 10);
$fields = [
    'nome' => [
        'type' => 'text',
        'required' => true,
        'placeholder' => 'Nome completo',
    ],
    'password' => [
        'type' => 'password',
        'required' => true,
        'placeholder' => 'Senha',
    ],
];
draw_table_header(["Nome", "Email"]);
foreach( $users as $user){
	draw_table_entry([$user->email, $user->email]);
}
close_table();
draw_form($fields,'action.add_utilizador.php');
?>
