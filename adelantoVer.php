<? include("header.php");

if (!($db = Conectar()))
	exit;

$SEGURIDAD_MODULO_ID = 6;

include 'seguridad.php';

$sql = "SELECT a.*, e.\"Nombre\", e.\"Apellido\" FROM \"tblAdelantos\" a INNER JOIN \"tblEmpleados\" e ON a.\"EmpresaID\" = e.\"EmpresaID\" AND a.\"SucursalID\" = e.\"SucursalID\" AND a.\"Legajo\" = e.\"Legajo\"";
$rs = pg_query($db, $sql);


?>
<H1><img src="images/icon64_novedadesSola.gif" width="64" height="64" align="absmiddle" />Adelantos de sueldos</H1>

<table width="100%" border="0" cellpadding="5" cellspacing="1" class="datagrid">
	<tr>
		<th>Legajo</th>
		<th>Nombre y apellido</th>
		<th>Monto</th>
		<th>Fecha</th>
		<th>Acciones</th>
	</tr>

	<? while ($row = pg_fetch_array($rs)) { ?>
	<tr>
		<td><?=$row['Legajo']?></td>
		<td><?=$row['Apellido'].', '.$row['Nombre']?></td>
		<td><?=$row['Monto']?></td>
		<td><?=$row['FechaAlta']?></td>
	</tr>
	<? } ?>

</table>
