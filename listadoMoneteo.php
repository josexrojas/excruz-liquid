<?
include("header.php");

if (!($db = Conectar()))
	exit;

$EmpresaID = $_SESSION["EmpresaID"];
$SucursalID = $_SESSION["SucursalID"];
if ($_SESSION["LegajoNumerico"] == '1'){
	$sqlLegajo = "to_number(em.\"Legajo\", '999999') AS \"Legajo\"";
}else{
	$sqlLegajo = "em.\"Legajo\"";
}

?>

<div id=divLoading style="display:block">
<table height=100% align=center valign=center>
<tr><td><img src="images/icon32_process.gif" align=absmiddle width=32 height=32 border=0> Generando Listado</td></tr>
</table>
</div>
<form name=frmListadoMoneteo action=listadoMoneteo.php method=post>
<input type=hidden name=accion id=accion>
<input type=hidden name=listado id=listado>

<?
$accion = LimpiarVariable($_POST["accion"]);
if ($accion == 'Ver Listado'){
	$selPeriodo = $_POST["selPeriodo"];
	$i = strpos($selPeriodo, '|');
	if ($i !== false){
		$FechaPeriodo = LimpiarNumero2(substr($selPeriodo, 0, $i));
		$NumeroLiquidacion = LimpiarNumero(substr($selPeriodo, $i+1));
	}
	if ($FechaPeriodo == '' || $NumeroLiquidacion == ''){
		exit;
	}
	$bTotaliza = (LimpiarNumero($_POST["chkTotaliza"]) == '1' ? true : false);
	$selLugarPago = LimpiarNumero($_POST["selLugarPago"]);
//AND (ed.\"NumeroCuenta\" IS NULL OR ed.\"LugarPago\" NOT IN (2,3,4,5))
	$rs = pg_query($db, "
SELECT $sqlLegajo, em.\"Apellido\" || ', ' || em.\"Nombre\", lp.\"Descripcion\", ROUND
(sum(\"Haber1\")+sum(\"Haber2\")-sum(\"Descuento\")) AS \"Neto\"
FROM \"tblRecibos\" re
INNER JOIN \"tblEmpleados\" em
ON em.\"EmpresaID\" = re.\"EmpresaID\" AND em.\"SucursalID\" = re.\"SucursalID\"
INNER JOIN \"tblEmpleadosDatos\" ed
ON ed.\"EmpresaID\" = re.\"EmpresaID\" AND ed.\"SucursalID\" = re.\"SucursalID\" AND ed.\"Legajo\" = em.\"Legajo\"
INNER JOIN \"tblLugaresDePago\" lp
ON lp.\"EmpresaID\" = re.\"EmpresaID\" AND lp.\"LugarPago\" = ed.\"LugarPago\" 
" . ($selLugarPago != 0 ? "AND lp.\"LugarPago\" = $selLugarPago" : "AND lp.\"TipoPago\" = 1") . "
WHERE re.\"EmpresaID\" = $EmpresaID AND re.\"SucursalID\" = $SucursalID AND re.\"Legajo\" = em.\"Legajo\" AND
re.\"ConceptoID\" = 99 AND re.\"Fecha\" = '$FechaPeriodo' AND re.\"NumeroLiquidacion\" = $NumeroLiquidacion
GROUP BY 1, 2, 3
ORDER BY 3, 1
");
	if (!$rs){
		exit;
	}
?>
<H1><img src="images/icon64_banco.gif" width="64" height="64" align="absmiddle" /> Listado Moneteo Por Lugar De Pago</H1>
<!--	<a class="tecla" href='#' onclick="MM_openBrWindow('listadoBancoPrint.php?FechaPeriodo=<?=$FechaPeriodo?>&NumeroLiquidacion=<?=$NumeroLiquidacion?>','printpreview','width=872,height=750')"> 
	<img src="images/icon24_printlistado.gif" alt="Imprimir Listado" width="24" height="24" border="0" align="absmiddle">
	Imprimir Listado</a>&nbsp;<BR />&nbsp;-->
<?
	print "<br><b>Per&iacute;odo: " . Mes(substr($FechaPeriodo, 5, 2)) . " de " . substr($FechaPeriodo, 0, 4);
	print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Numero de Liquidaci&oacute;n: $NumeroLiquidacion</b><br><br>";
	if (pg_numrows($rs) > 0){
		$TotalAPagar = 0;
		$CantEmp = 0;
		$AntLP = '';
		$Abrir = 1;
		$Total = 0;
		$Importes = Array();
		while($row = pg_fetch_array($rs)){
			$Legajo = $row[0];
			$ApeYNom = $row[1];
			$LP = $row[2];
			$Paga = $row[3];
			if ($LP != $AntLP && $bTotaliza){
				if ($AntLP != '')
					$Cerrar = 1;
			}
			if ($Cerrar == 1){
				$Cerrar = 0;
				print "<tr><td><b>Total</b></td><td></td><td></td><td><b>$Total</b></td></tr>\n";
				print "</table>";
				$Abrir = 1;
				$Total = 0;
				Moneteo($Importes);
				$Importes = array();
				print "<br>";
			}
			if ($LP != $AntLP && $bTotaliza){
				print "Lugar de Pago: $LP<br><br>\n";
				$AntLP = $LP;
			}
			if ($Abrir == 1){
?>
				<table width="100%" border="0" cellpadding="5" cellspacing="1" class="datagrid">
				<tr><th>Legajo</th><th>Apellido y Nombre</th><th>Lugar De Pago</th><th>Importe</th></tr>
<?
				$Abrir = 0;			
			}
			if ($Paga > 0){
				$Total += $Paga;
				array_push($Importes, $Paga);
				$TotalAPagar += $Paga;
?>
				<tr><td><?=$Legajo?></td><td><?=$ApeYNom?></td><td><?=$LP?></td><td><?=$Paga?></td></tr>
<?
				$CantEmp++;
			}
		}
		print "<tr><td><b>Total</b></td><td></td><td></td><td><b>$Total</b></td></tr>\n";
?>
		</table>
<?	
		Moneteo($Importes);
		print "\n<br><b>Total A Pagar en Mano: $TotalAPagar<br>\n";
		print "\nCantidad de Empleados: $CantEmp</b><br><br>\n";
	}else{
		Alerta('No hay empleados en esa liquidaci&oacute;n');
	}
}

if ($accion == ''){
	include 'selLiquida.php';
	$rs = pg_query($db, "
SELECT lp.\"LugarPago\", lp.\"Descripcion\"
FROM \"tblLugaresDePago\" lp
WHERE lp.\"EmpresaID\" = $EmpresaID AND lp.\"Activo\" = true AND lp.\"TipoPago\" = 1
ORDER BY 2
	");
	if (!$rs){
		exit;
	}
?>
	<TR>
		<TD class="izquierdo">Totalizar por Lugar de Pago:</TD><TD class="derecho">
		<input type=checkbox id=chkTotaliza name=chkTotaliza value=1 checked>
		</TD>
	</TR>
	<TR>
		<TD class="izquierdo">Seleccione Lugar de Pago:</TD><TD class="derecho"><select id=selLugarPago name=selLugarPago>
		<option value=0 selected>Todos</option>
<?
	while($row = pg_fetch_array($rs)){
		print "<option value=" . $row[0] . ">" . $row[1] . "</option>\n";
	}
?>
	</select></TD></TR>
	<TR>
		<TD class="izquierdo"></TD><TD class="derecho"><input type=submit id=accion name=accion value="Ver Listado"></TD></TR></table>
<?
}
pg_close($db);
?>
</form>
<script>
	document.getElementById('divLoading').style.display = 'none';
</script>
<? include("footer.php"); ?>
