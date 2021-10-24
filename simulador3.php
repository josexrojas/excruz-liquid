<?
include("header.php");

if (!($db = Conectar()))
	exit;
	
if (!($db2 = ConectarSimulador()))
	exit;

$EmpresaID = $_SESSION["EmpresaID"];
$SucursalID = $_SESSION["SucursalID"];
$Legajo = $_REQUEST['Legajo'];
$Periodo = $_REQUEST['Periodo'];

$accion = LimpiarVariable($_POST["accion"]);
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

<div id=divLoading style="display:block">
<table height=100% align=center valign=center>
<tr><td><img src="images/icon32_process.gif" align=absmiddle width=32 height=32 border=0> Generando Listado</td></tr>
</table>
</div>

<div style="margin-left:-166px;">
<?

	if ($accion == 'actualizarcategoria')
	{
		$CategoriaIDNew = $_REQUEST['CategoriaIDNew'];
	
		$sql2 = 'UPDATE "tblEmpleadosRafam2" SET jurisdiccion = \'1110100000\', agrupamiento = 0, categoria = '.$CategoriaIDNew.', cargo = '.$CategoriaIDNew.' WHERE "Legajo" IN (\''.$Legajo.'\')';
	
		$rs2 = pg_query($db2, $sql2);
	
	}
	
	$sql = "
   SELECT
    r.\"Descripcion\",
    r.\"Haber1\",
    r.\"Haber2\",
    r.\"Descuento\",
    r.\"Aporte\",
    CASE WHEN r.\"AliasID\" IN (1, 2, 11, 121, 138, 141) THEN 1 ELSE 0 END AS \"EsBasico\",
    CASE WHEN r.\"AliasID\" IN (3, 4, 134, 150, 170, 171, 172) THEN 1 ELSE 0 END AS \"EsAntiguedad\",
    CASE WHEN r.\"AliasID\" IN (15) THEN 1 ELSE 0 END AS \"EsBruto\"

    FROM \"tblEmpleados\" e
    LEFT JOIN \"tblEmpleadosDatos\" ed ON e.\"EmpresaID\" = ed.\"EmpresaID\" AND e.\"SucursalID\" = ed.\"SucursalID\" AND e.\"Legajo\" = ed.\"Legajo\"
    LEFT JOIN \"tblEmpleadosRafam\" er ON e.\"EmpresaID\" = er.\"EmpresaID\" AND e.\"SucursalID\" = er.\"SucursalID\" AND e.\"Legajo\" = er.\"Legajo\"
    LEFT JOIN owner_rafam.categorias rc ON er.agrupamiento = rc.agrupamiento AND er.categoria = rc.categoria AND rc.jurisdiccion = '1110100000'
    LEFT JOIN owner_rafam.cargos rca ON er.agrupamiento = rca.agrupamiento AND er.categoria = rca.categoria AND er.cargo = rca.cargo AND rca.jurisdiccion = '1110100000'
    INNER JOIN \"tblRecibos\" r ON e.\"EmpresaID\" = r.\"EmpresaID\" AND e.\"SucursalID\" = r.\"SucursalID\" AND e.\"Legajo\" = r.\"Legajo\" AND r.\"Fecha\" = '$Periodo' AND r.\"NumeroLiquidacion\" IN (1, 2)

    WHERE (e.\"FechaEgreso\" > '$Periodo' OR e.\"FechaEgreso\" IS NULL)
    AND (ed.\"FechaIngreso\" <= '$Periodo')

    AND r.\"Legajo\" = '$Legajo'
;
   
";

	$rs = pg_query($db, $sql);
	$rs2 = pg_query($db2, $sql);

	if (!$rs || !$rs2){
		exit;
	}
	
	$sql = "SELECT rc.categoria, case when c.\"SueldoBasico\" is null then '' else '$' || c.\"SueldoBasico\"::varchar || '    ' end || rc.detalle  FROM owner_rafam.categorias rc";
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
	<input type="hidden" name="Legajo" value="<?php echo $Legajo; ?>" />
	<input style="float:right;" type="submit" value="Cambiar categoria SOLO a este empleado" />
	<select name="CategoriaIDNew" style="float:right;">
		<?php for (;$row = pg_fetch_array($cat2); $row) { ?>
			<option value="<?php echo $row[0]; ?>"><?php echo $row[1];?></option>
		<?php } ?>
	</select>
</form>
	
<br style="clear:both;" /><br /><br/><br /><br/><br />

<table width="100%" border="0" cellpadding="3" cellspacing="1" class="datagrid">
<tr>
    <th>Descripcion</th>
    <th>Haber1</th>
    <th>Haber2</th>
    <th>Descuento</th>
    <th>Aporte</th>
    <th bgcolor="#000000"></th>
    <th>Haber1</th>
    <th>Haber2</th>
    <th>Descuento</th>
    <th>Aporte</th>
</tr>

<?
	$arrNuevos = array();
	while ($row2 = pg_fetch_array($rs2))
	{		
		$row2['showed'] = 0;
		array_push($arrNuevos, $row2);
	}
	
	$j = 0;
	while($row = pg_fetch_array($rs))
	{
		$j++;
		
		if ($j == pg_num_rows($rs)-1)
		{
			foreach ($arrNuevos as $r)
				{
					if ($r['showed'] == 1 || $r['Descripcion'] == 'REDONDEO' || $r['Descripcion'] == 'TOTALES') continue;
			?>
					<tr style="background-color:<?=$color?>!important;">
						<td nowrap><?=$r['Descripcion']?></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td bgcolor="#000000"></td>
						<td nowrap>$ <?=number_format($r['Haber1'])?></td>
						<td nowrap>$ <?=number_format($r['Haber2'])?></td>
						<td nowrap>$ <?=number_format($r['Descuento'])?></td>
						<td nowrap>$ <?=number_format($r['Aporte'])?></td>
					</tr>
			<?php	
				}		
		}
		
				
		$color = '';
		
		if ($row['EsBasico'] == '1')
			$color = '#00FF00';
			
		if ($row['EsAntiguedad'] == '1')
			$color = '#CC9900';
			
		if ($row['EsBruto'] == '1')
			$color = '#FFFF00';
		
		$r = array();
		foreach ($arrNuevos as $i=>$row2)
		{			
			if ($row['Descripcion'] == $row2['Descripcion'])
			{
				$r = $row2;	
				$arrNuevos[$i]['showed'] = 1;
			}			
		}
		
?>
		<tr style="background-color:<?=$color?>!important;">
        	<td nowrap><?=$row['Descripcion']?></td>
            <td nowrap>$ <?=number_format($row['Haber1'])?></td>
            <td nowrap>$ <?=number_format($row['Haber2'])?></td>
            <td nowrap>$ <?=number_format($row['Descuento'])?></td>
            <td nowrap>$ <?=number_format($row['Aporte'])?></td>
            <td bgcolor="#000000"></td>
            <?php if (count($r) > 0) { ?>
            <td nowrap>$ <?=number_format($r['Haber1'])?></td>
            <td nowrap>$ <?=number_format($r['Haber2'])?></td>
            <td nowrap>$ <?=number_format($r['Descuento'])?></td>
            <td nowrap>$ <?=number_format($r['Aporte'])?></td>
            <?php } ?>
        </tr>
<?
	}
	
	pg_close($db);
?>
</table>

</div>

<script>
	document.getElementById('divLoading').style.display = 'none';
</script>

<? //include("footer.php"); ?>
