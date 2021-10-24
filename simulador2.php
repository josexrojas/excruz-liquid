<?
include("header.php");

if (!($db = Conectar()))
	exit;

if (!($db2 = ConectarSimulador()))
	exit;

$EmpresaID = $_SESSION["EmpresaID"];
$SucursalID = $_SESSION["SucursalID"];
$CategoriaID = $_REQUEST['CategoriaID'];
$CargoID = $_REQUEST['CargoID'];
$Periodo = $_REQUEST['Periodo'];
$AgrupamientoID = $_REQUEST['AgrupamientoID'];


$accion = LimpiarVariable($_POST["accion"]);

$sql = "

SELECT
r.\"Legajo\",
e.\"Apellido\" || ' ' || e.\"Nombre\" AS \"Empleado\",
SUM(CASE WHEN r.\"AliasID\" IN (1, 2, 11, 121, 138, 141) THEN r.\"Haber1\" ELSE 0 END) AS \"Basico\",
SUM(CASE WHEN r.\"AliasID\" IN (3, 4, 134, 150, 170, 171, 172) THEN r.\"Haber1\" ELSE 0 END) AS \"Antiguedad\",
SUM(CASE WHEN r.\"AliasID\" IN (15) THEN r.\"Haber1\" + r.\"Haber2\" ELSE 0 END) AS \"Bruto\"

FROM \"tblEmpleados\" e
LEFT JOIN \"tblEmpleadosDatos\" ed ON e.\"EmpresaID\" = ed.\"EmpresaID\" AND e.\"SucursalID\" = ed.\"SucursalID\" AND e.\"Legajo\" = ed.\"Legajo\"
LEFT JOIN \"tblEmpleadosRafam\" er ON e.\"EmpresaID\" = er.\"EmpresaID\" AND e.\"SucursalID\" = er.\"SucursalID\" AND e.\"Legajo\" = er.\"Legajo\"
LEFT JOIN owner_rafam.categorias rc ON er.agrupamiento = rc.agrupamiento AND er.categoria = rc.categoria  AND rc.jurisdiccion = '1110100000'
LEFT JOIN owner_rafam.cargos rca ON er.agrupamiento = rca.agrupamiento AND er.categoria = rca.categoria AND er.cargo = rca.cargo  AND rca.jurisdiccion = '1110100000'
INNER JOIN \"tblRecibos\" r ON e.\"EmpresaID\" = r.\"EmpresaID\" AND e.\"SucursalID\" = r.\"SucursalID\" AND e.\"Legajo\" = r.\"Legajo\" AND r.\"Fecha\" = '$Periodo' AND r.\"NumeroLiquidacion\" IN (1, 2)

WHERE (e.\"FechaEgreso\" > '$Periodo' OR e.\"FechaEgreso\" IS NULL)
AND (ed.\"FechaIngreso\" <= '$Periodo')
AND r.\"Descripcion\" NOT LIKE 'AJ.%' AND r.\"ConceptoID\" NOT IN (91)
AND (
\"AliasID\" IN (1, 2, 11, 121, 138, 141)
OR \"AliasID\" IN (3, 4, 134, 150, 170, 171, 172)
OR \"AliasID\" IN (15)
)

AND \"AliasID\" NOT IN (27, 19, 22, 35, 29, 30, 31, 7, 36, 32, 174, 25, 143, 144, 37, 8, 34, 74, 23, 26, 23, 75, 81, 156, 78, 156, 69, 70, 78, 84, 73, 146, 80, 5, 6, 13, 90, 169, 163, 118, 66, 79, 130, 149, 166, 9, 56, 10, 82, 67, 165, 50, 51, 137, 86, 155, 145, 168, 14, 126, 24, 139, 140, 164, 124, 125, 129, 132, 131, 12, 158, 142, 114, 122, 123, 46, 47)

AND er.categoria = $CategoriaID AND er.cargo = '$CargoID' and er.agrupamiento = '$AgrupamientoID'

GROUP BY r.\"Legajo\", e.\"Apellido\", e.\"Nombre\"
;

";

if ($accion == 'actualizarcategoria')
{
	$CategoriaIDNew = $_REQUEST['CategoriaIDNew'];
	
	$legajos = array();
	$rs = pg_query($db, $sql);
	while($row = pg_fetch_array($rs))
	{
		$legajos[] = "'".$row[0]."'";
	}
	
	$sql2 = 'UPDATE "tblEmpleadosRafam2" SET jurisdiccion = \'1110100000\', agrupamiento = 0, categoria = '.$CategoriaIDNew.', cargo = '.$CategoriaIDNew.' WHERE "Legajo" IN ('.implode($legajos, ",").')';
	
	$rs2 = pg_query($db2, $sql2);
	
}
?>
<style>

.datagrid td,th {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	text-align:center;
	font-size: 9px!important;
	color:#666666;
	}
	
	.datagrid a {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	text-align:center;
	font-size: 9px!important;
	color:#666666;
	text-decoration:underline;
	}


</style>
<script language="javascript">
document.getElementById('barMenu').style.display = 'none';
</script>

<div style="margin-left:-166px;">

<?php

	

	$rs = pg_query($db, $sql);
	
	$rs2 = pg_query($db2, $sql);

	if (!$rs || !$rs2){
		exit;
	}
	
	$sql = "SELECT rc.categoria, case when c.\"SueldoBasico\" is null then '' else '$' || c.\"SueldoBasico\"::varchar || '    ' end || rc.detalle , rca.detalle  FROM owner_rafam.categorias rc";
	$sql.= " LEFT JOIN owner_rafam.cargos rca ON rc.categoria = rca.categoria";
	$sql.= " LEFT JOIN \"tblCategorias\" c ON rc.categoria = c.\"Categoria\"";
	$sql.= " WHERE rc.jurisdiccion = '1110100000'";
	$sql.= " ORDER BY c.\"SueldoBasico\", rc.categoria";
	
	$cat2 = pg_query($db2, $sql);
	
?>
<H1> Simulador</H1>


<input style="float:left;" type="button" value="Volver" onclick="javascript: window.history.back();" />

<form method="post">
	<input type="hidden" name="accion" value="actualizarcategoria" />
	<input type="hidden" name="EmpresaID" value="<?php echo $EmpresaID; ?>" />
	<input type="hidden" name="SucursalID" value="<?php echo $SucursalID; ?>" />
	<input type="hidden" name="Periodo" value="<?php echo $Periodo; ?>" />
	<input type="hidden" name="CategoriaID" value="<?php echo $CategoriaID; ?>" />
	<input type="hidden" name="CargoID" value="<?php echo $CargoID; ?>" />
	<input style="float:right;" type="submit" value="Cambiar categoria a TODOS los empleados" />
	<select name="CategoriaIDNew" style="float:right;">
		<?php for (;$row = pg_fetch_array($cat2); $row) { ?>
			<option value="<?php echo $row[0]; ?>"><?php echo $row[1]." - ".$row[2];?></option>
		<?php } ?>
	</select>
</form>

<br style="clear:both;" /><br /><br/><br /><br/><br />

<table width="100%" border="0" cellpadding="3" cellspacing="1" class="datagrid">
<tr>
    <th>Legajo</th>
    <th>Empleado</th>
    <th>Básico</th>
    <th>Antiguedad</th>
    <th>Bruto</th>	
    <th bgcolor="#000000"></th>
    <th>Básico</th>
    <th>Antiguedad</th>
    <th>Bruto</th>	
</tr>

<?
	$arrNuevos = array();
	while ($row2 = pg_fetch_array($rs2))
	{		
		$row2['showed'] = 0;
		array_push($arrNuevos, $row2);
	}
	
	$i = 0;
	while($row = pg_fetch_array($rs))
	{	
		$row2 = array();
		foreach ($arrNuevos as $i=>$r2)
		{			
			if ($row['Legajo'] == $r2['Legajo'])
			{
				$row2 = $r2;	
				$arrNuevos[$i]['showed'] = 1;
			}			
		}
		
		$color = (number_format($row['Bruto']) > number_format($row2[4])) ? "bgcolor=\"#FF9900\"" : "";
?>
		<tr>
        	<td nowrap><a href="simulador3.php?Legajo=<?=$row['Legajo']?>&Periodo=<?=$Periodo?>"><?=$row['Legajo']?></a></td>
            <td nowrap><a href="simulador3.php?Legajo=<?=$row['Legajo']?>&Periodo=<?=$Periodo?>"><?=$row['Empleado']?></a></td>
            <td nowrap>$ <?=number_format($row['Basico'])?></td>
            <td nowrap>$ <?=number_format($row['Antiguedad'])?></td>
            <td nowrap>$ <?=number_format($row['Bruto'])?></td>
            <td bgcolor="#000000"></td>
            <td nowrap>$ <?=number_format($row2[2])?></td>
            <td nowrap>$ <?=number_format($row2[3])?></td>
            <td nowrap <?=$color?>>$ <?=number_format($row2[4])?></td>
        </tr>
<?
		$i++;
	}

	foreach ($arrNuevos as $row)
	{
		if ($row['showed'] == 1) continue;
		
		$color = "";
		
?>
		<tr>
        	<td nowrap><a href="simulador3.php?Legajo=<?=$row['Legajo']?>&Periodo=<?=$Periodo?>"><?=$row['Legajo']?></a></td>
            <td nowrap><a href="simulador3.php?Legajo=<?=$row['Legajo']?>&Periodo=<?=$Periodo?>"><?=$row['Empleado']?></a></td>
            <td nowrap></td>
            <td nowrap></td>
            <td nowrap></td>
            <td bgcolor="#000000"></td>
            <td nowrap>$ <?=number_format($row[2])?></td>
            <td nowrap>$ <?=number_format($row[3])?></td>
            <td nowrap <?=$color?>>$ <?=number_format($row[4])?></td>
        </tr>
<?php
	}
	
	pg_close($db);
?>
</table>
</div>

<script>
	document.getElementById('divLoading').style.display = 'none';
</script>

<? //include("footer.php"); ?>
