<?php
function draw_form(array $fields, string $action = '', string $method = 'POST') {?>
	<form action="<?= $action ?>" method="<?= $method ?>">
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
