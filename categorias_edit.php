<?php 
include("header.php");

$Categoria = LimpiarVariable($_REQUEST['id']);
$HorasDiarias = LimpiarVariable($_REQUEST['hrs']);
$Descripcion = LimpiarVariable($_REQUEST['desc']);
$SueldoBasico = LimpiarVariable($_REQUEST['Sb']);
$Bonificacion = LimpiarVariable($_REQUEST['B']);
$Cargo = LimpiarVariable($_REQUEST['idcargo']);
$Action = LimpiarVariable($_REQUEST['action']);

if (!($db = Conectar()))
	exit;

$EmpresaID = $_SESSION["EmpresaID"];
$SucursalID = $_SESSION["SucursalID"];

$SEGURIDAD_MODULO_ID = 6;

include 'seguridad.php';


if ($Action == 'update')
{
	if ($Categoria != NULL AND $HorasDiarias != NULL AND $Descripcion != NULL AND $SueldoBasico != NULL){
		$sql = pg_query ($db, "UPDATE \"tblCategorias\" SET \"SueldoBasico\"= $SueldoBasico WHERE \"EmpresaID\" = '1' AND \"Categoria\" = $Categoria AND \"HorasDiarias\" = $HorasDiarias;");
	 }
	 else{
	 	echo "Debe ingresar el sueldo b&aacute;sico.";
	 }
}

$rs = pg_query($db, "
SELECT ce.\"Categoria\", ce.\"HorasDiarias\", MAX(\"SueldoBasico\"), MAX(ca.\"denominacion\") as denom
FROM \"tblCategorias\" ce INNER JOIN owner_rafam.\"categorias\" ca ON ce.\"Categoria\" = ca.\"categoria\"
GROUP BY ce.\"Categoria\", ce.\"HorasDiarias\" 
ORDER BY ce.\"Categoria\", ce.\"HorasDiarias\" ASC
");

?>

<H1><img src="images/icon64_empleados.gif" width="64" height="64" align="absmiddle" /> Categorias</H1>
<table>
	<tr>
		<td><a href="categorias.php" class="tecla"><img src="images/icon24_prev.gif" alt="Volver" width="24" height="23" border="0" align="absmiddle">  Volver&nbsp;</a></td>
	</tr>
</table>
<br />
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
		<td><a href="categorias_update.php?id=<?=$row[0]?>&hrs=<?=$HorasDiarias?>&desc=<?=$Descripcion?>&Sb=<?=$SueldoBasico?>">
		<img src="images/icon24_editar.gif" alt="Editar Categor&iacute;a" onclick="submit" align="absmiddle" border="0" width="24" height="24"></a></td>
	</tr>
<?php
	}
?>

</table>
<br />
<?php 
include("footer.php");
?>
