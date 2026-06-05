<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/templates/common.tpl.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/models/user.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/database/connection.db.php');

$db = getDatabaseConnection();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Car Search</title>
</head>
<body>

<h2>Search cars</h2>

<input type="text" id="search" placeholder="Type model or brand..." />

<ul id="results"></ul>

<script>
const input = document.getElementById("search");
const results = document.getElementById("results");

let timeout = null;

input.addEventListener("input", function () {
    clearTimeout(timeout);

    timeout = setTimeout(() => {
        fetch('/api/search_models.php?q=' + encodeURIComponent(input.value))
            .then(res => res.json())
            .then(data => {
                results.innerHTML = "";

                data.forEach(item => {
                    const li = document.createElement("li");
                    li.textContent = item.marca + " - " + item.nome;
                    results.appendChild(li);
                });
            });
    }, 300); // debounce (important)
});
</script>

</body>
</html>
<?php
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

draw_form($fields,'action.add_user.php');
?>
