<?php
include ('header.php');

$Concepto = $_REQUEST['id'];
$Alias = $_REQUEST['As'];
$Descripcion = $_REQUEST['desc'];
$SueldoBasico = $_REQUEST['Sb'];
$Action = $_REQUEST['action'];

if (!($db = Conectar()))
	exit;

$EmpresaID = $_SESSION["EmpresaID"];
$SucursalID = $_SESSION["SucursalID"];

$SEGURIDAD_MODULO_ID = 6;

include 'seguridad.php';

if ($Action == 'update')
{
	if ($Concepto != NULL AND $Alias != NULL AND $Descripcion != NULL AND $SueldoBasico != NULL)
	{
		if ($Alias == '121' OR $Alias == '138' OR $Alias == '141')
		{ 
			if ($Alias == '121') $Jornal = round($SueldoBasico / 6, 2);
			if ($Alias == '138') $Jornal = round($SueldoBasico / 8, 2);
			if ($Alias == '141') $Jornal = round($SueldoBasico / 9, 2);





			$sqlCPV = pg_query($db, " UPDATE \"tblConceptosParametrosValores\" SET \"Valor\" = $SueldoBasico WHERE \"ConceptoID\" = $Concepto AND \"AliasID\" = $Alias");
			$sqlN = pg_query($db, " UPDATE \"tblNovedades\" SET \"Valores\"[2] = $SueldoBasico WHERE \"ConceptoID\" = $Concepto AND \"AliasID\" = $Alias AND \"Ajuste\" = 0");
			$sqlCPV = pg_query($db, " UPDATE \"tblConceptosParametrosValores\" SET \"Valor\" = $Jornal WHERE \"ConceptoID\" = 21 AND \"ParametroID\" = 1");
			$sqlN = pg_query($db, " UPDATE \"tblNovedades\" SET \"Valores\"[1] = $Jornal WHERE \"ConceptoID\" = 21");
		
			if ($sqlCPV == true AND $sqlN == true)
			{
				print "Los datos se cargaron correctamente.";
			}
			else
			{
				print "No se puedieron cargar los datos.";
			}
		}
		else
		{
			print "No se actualizaron los datos ya que esta intentando modificar un concepto indebido.";
		}
	}
	else
	{
		print "Debe Completar todos los datos.";
	}
}

$rs = pg_query($db, "
SELECT ca.\"ConceptoID\", ca.\"AliasID\", \"Descripcion\", cpv.\"Valor\" FROM \"tblConceptosAlias\" ca INNER JOIN \"tblConceptosParametrosValores\" cpv ON ca.\"ConceptoID\" = cpv.\"ConceptoID\" AND ca.\"AliasID\" = cpv.\"AliasID\" WHERE cpv.\"ConceptoID\" = 2 ORDER BY ca.\"Descripcion\" ASC");
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
		<th>Conc.</th>
		<th>Alias</th>
		<th>Descripción</th>
		<th>Sueldo Basico</th>
		<th>Acciones</th>
	</tr>

<?
	while($row = pg_fetch_array($rs))
	{

		$Concepto = $row[0];
		$Alias = $row[1];
		$Descripcion = $row[2];
		$SueldoBasico = $row[3];

?>
	<tr>
		<td><?=$Concepto?></td>
		<td><?=$Alias?></td>
		<td><?=$Descripcion?></td>
		<td>$ <?=$SueldoBasico?></td>
		<td><a href="categorias_jornales_edit.php?id=<?=$Concepto?>&As=<?=$Alias?>&desc=<?=$Descripcion?>&Sb=<?=$SueldoBasico?>">
		<img src="images/icon24_editar.gif" alt="Editar Concepto" onclick="submit" align="absmiddle" border="0" width="24" height="24"></a></td>
	</tr>
<?php
	}
?>
</table>

<br />

<?php
include ('footer.php');
?>
