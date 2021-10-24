<?php

$data = array();

$data['id_item'][0] = '1';
$data['desc_item'][0] = 'Descripcion 1';
$data['monto_item'][0] = '120';
$data['cantidad_item'][0] = '1';

$data['id_item'][1] = '2';
$data['desc_item'][1] = 'Descripcion 2';
$data['monto_item'][1] = '140';
$data['cantidad_item'][1] = '1';


$data['monto_operacion'] = '260';

echo '<pre>';
print_r($data);

?>
<form action="inicio.php" method="post">

<?php foreach ($data['id_item'] as $i=>$o) : ?>
<input type="hidden" name="id_item[<?php echo $i; ?>]" value="<?php echo $o; ?>">
<?php endforeach; ?>

<?php foreach ($data['desc_item'] as $i=>$o) : ?>
<input type="hidden" name="desc_item[<?php echo $i; ?>]" value="<?php echo $o; ?>">
<?php endforeach; ?>

<?php foreach ($data['monto_item'] as $i=>$o) : ?>
<input type="hidden" name="monto_item[<?php echo $i; ?>]" value="<?php echo $o; ?>">
<?php endforeach; ?>

<?php foreach ($data['cantidad_item'] as $i=>$o) : ?>
<input type="hidden" name="cantidad_item[<?php echo $i; ?>]" value="<?php echo $o; ?>">
<?php endforeach; ?>

<input type="hidden" name="monto_operacion" value="<?php echo $data['monto_operacion']; ?>">

<input type="submit">

</form>