<?
include ('header.php');

if (!($db = Conectar()))
	exit;
?>

<form name=frmListadoModHoras method=post>

<H1>Listado historico de Bonificaciones Especiales</H1>
<?php

$rs = pg_query($db, "
select r.\"Legajo\", e.\"Nombre\" || ' ' || e.\"Apellido\", \"Fecha\", SUM(\"Haber1\") from \"tblRecibos\" r inner join \"tblEmpleados\" e on r.\"Legajo\"=e.\"Legajo\" where \"AliasID\" = 25 and \"Fecha\">='2014-01-01'
group by r.\"EmpresaID\", r.\"SucursalID\", r.\"Legajo\", \"Fecha\", e.\"Nombre\", e.\"Apellido\"
order by to_number(r.\"Legajo\", '999999'), \"Fecha\"");



if (!$rs){
	exit;
}


while ($row = pg_fetch_row($rs)) {
	$valor[$row[0]][$row[1]][$row[2]] = $row[3];
	$meses[$row[2]] = 1;

}

ksort($meses);

?>
<table width="100%" border="0" cellpadding="5" cellspacing="1" class="datagrid">
	<tr><th>Legajo</th>
		<th>Nombre</th>
<?php foreach ($meses as $mes => $value) { ?>

	<th style="white-space: nowrap"><?php echo substr($mes, 0, -3); ?></th>

 <?php } ?> </tr>

<tr>

<?php

foreach ($valor as $legajo => $data) {

	$nombre = array_keys($data);
	$nombre = $nombre[0];
	print '<tr>';
	print '<td>'.$legajo.'</td>';
	print '<td style="white-space: nowrap">'.$nombre.'</td>';
	foreach ($meses as $mes => $value) {
		print "<td>".$data[$nombre][$mes]."</td>";

	}
	print '</tr>';

}
 ?>

<tr/>

</table>

<?php

pg_close($db);
?>

</form>
<? include("footer.php"); ?>
