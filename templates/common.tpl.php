<?php
function drawTable(array $data): string
{
    if (empty($data)) {
        return '<p>No data available.</p>';
    }

    $html = '<table border="1" cellpadding="5" cellspacing="0">';

    // Table headers
    $html .= '<thead><tr>';
    foreach (array_keys($data[0]) as $column) {
        $html .= '<th>' . htmlspecialchars($column) . '</th>';
    }
    $html .= '</tr></thead>';

    // Table rows
    $html .= '<tbody>';
    foreach ($data as $row) {
        $html .= '<tr>';
        foreach ($row as $value) {
            $html .= '<td>' . htmlspecialchars((string)$value) . '</td>';
        }
        $html .= '</tr>';
    }
    $html .= '</tbody>';

    $html .= '</table>';

    return $html;
}
function draw_form(array $fields, string $action = '', string $method = 'POST') {?>
	<form action="<?= $_SERVER['DOCUMENT_ROOT'].'/actions/'.$action ?>" method="<?= $method ?>">
		<?php foreach ($fields as $name => $field) {

		$type = $field['type'] ?? 'text';
		$label = $field['label'] ?? ucfirst($name);
		$required = isset($field['required']) ? 'required' : '';
		$placeholder = isset($field['placeholder'])
		? "placeholder=\"{$field['placeholder']}\""
		: '';
		?>

		<label for="<?= $name ?>"> <?= $label ?> </label>
		<input type="<?= $type ?>" name="<?= $name ?>" <?= $placeholder ?> <?= $required ?>>

		<?php }?>

		<button type='submit'>Submit</button>
	</form>
	<?php 
}
function draw_table_header(array $headers){ ?>
<table>
	<tr>
	<?php foreach ($headers as $header){ ?>
		<th><?= $header ?></th>
		<?php } ?>
	</tr>
<?php } 
function draw_table_entry(array $entries){ ?>
<table>
	<tr>
	<?php foreach ($entries as $entry){ ?>
		<th><?= $entry ?></th>
		<?php } ?>
	</tr>
<?php } ?>

<?php function close_table(){ ?>
	</table>
<?php } ?>
