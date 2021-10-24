<?
include ('header.php');

if (!($db = Conectar()))
	exit;
?>

<form name=frmListadoModHoras method=post>

<H1>Listado historico de Bonificaciones Especiales</H1>
<?php

$sql = "
select er.jurisdiccion, r.\"Legajo\", e.\"Nombre\" || ' ' || e.\"Apellido\", \"Fecha\", SUM(\"Haber1\")
from \"tblRecibos\" r
inner join \"tblEmpleados\" e on r.\"Legajo\"=e.\"Legajo\" and r.\"EmpresaID\"=e.\"EmpresaID\" and r.\"SucursalID\"=e.\"SucursalID\"
inner join \"tblEmpleadosRafam\" er on er.\"Legajo\"=e.\"Legajo\" and er.\"EmpresaID\"=e.\"EmpresaID\" and er.\"SucursalID\"=e.\"SucursalID\"
where \"AliasID\" = 25 and \"Fecha\">='2014-01-01' and \"Fecha\"<'2015-01-01'
group by er.jurisdiccion, r.\"EmpresaID\", r.\"SucursalID\", r.\"Legajo\", \"Fecha\", e.\"Nombre\", e.\"Apellido\"
order by \"Fecha\", jurisdiccion, SUM(\"Haber1\"), r.\"Legajo\"";

//print $sql;

$rs = pg_query($db, $sql);

if (!$rs){
	exit;
}



?>
<table width="100%" border="0" cellpadding="5" cellspacing="1" class="datagrid">
	<tr>
		<th>Fecha</th>
		<th>Jurisdiccion</th>
		<th>Legajo</th>
		<th>Nombre</th>
		<th>Importe</th>
	</tr>

<?php

while ($row = pg_fetch_row($rs)) {

	print '<tr>';
	print '<td>'.$row[3].'</td>';
	print '<td>'.$row[0].'</td>';
	print '<td>'.$row[1].'</td>';
	print '<td>'.$row[2].'</td>';
	print '<td>'.$row[4].'</td>';
	print '</tr>';

}
 ?>

</table>

<?php

pg_close($db);
?>

</form>
<? include("footer.php"); ?>
