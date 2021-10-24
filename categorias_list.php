<?php 
include("header.php");

if (!($db = Conectar()))
	exit;

$EmpresaID = $_SESSION["EmpresaID"];
$SucursalID = $_SESSION["SucursalID"];

$SEGURIDAD_MODULO_ID = 6;

include 'seguridad.php';

	$rs = pg_query($db, "
SELECT ce.\"Categoria\", ce.\"HorasDiarias\", MAX(\"SueldoBasico\"), MAX(ca.\"denominacion\") as denom
FROM \"tblCategorias\" ce INNER JOIN owner_rafam.\"categorias\" ca ON ce.\"Categoria\" = ca.\"categoria\"
GROUP BY ce.\"Categoria\", ce.\"HorasDiarias\" 
ORDER BY ce.\"Categoria\", ce.\"HorasDiarias\"
");

?>

<H1><img src="images/icon64_empleados.gif" width="64" height="64" align="absmiddle" /> Categorias</H1>

<table width="100%" border="0" cellpadding="5" cellspacing="1" class="datagrid">
	<tr>
		<th>Categ.</th>
		<th>Descripción</th>
		<th>Cant. Horas</th>
		<th>Sueldo Basico</th>
		<th>Acciones</th>
	</tr>

<?
	while($row = pg_fetch_array($rs))
	{

		$Categoria = $row[0];
		$HorasDiarias = $row[1];
		$SueldoBasico = $row[2];
		$Descripcion = $row[3];
?>
	<tr>
		<td><?=$Categoria?></td>
		<td><?=$Descripcion?></td>
		<td><?=$HorasDiarias?></td>
		<td>$ <?=$SueldoBasico?></td>
		<td><a href="javascript:ActualizarNovedad(<?=$AliasID?>,<?=$Ajuste?>); void(0);">
		<img src="images/icon24_editar.gif" alt="Editar Novedad" align="absmiddle" border="0" width="24" height="24"></a></td>
	</tr>
<?php
}
?>
</table>

<?php include("footer.php");?>
