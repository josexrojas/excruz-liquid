<? include("header.php");

if (!($db = Conectar()))
	exit;

$EmpresaID = $_SESSION["EmpresaID"];
$SucursalID = $_SESSION["SucursalID"];
$LegajoNumerico = $_SESSION["LegajoNumerico"];

if ($LegajoNumerico == '1'){
	$sqlLegajo = "to_number(re.\"Legajo\", '999999') AS \"Legajo\"";
}else{
	$sqlLegajo = "re.\"Legajo\"";
}
?>
<script type="text/JavaScript">
<!--
function MM_openBrWindow(theURL,winName,features) { //v2.0
  window.open(theURL,winName,features);
}
//-->
</script>

<H1><img src="images/icon64_Recibos.gif" width="64" height="64" align="absmiddle" /> Recibos</H1>
<form name=frmRecibos action=recibos.php method=post style="display:inline">
<?
$accion = LimpiarVariable($_POST["accion"]);
if ($accion == 'Ver Recibos'){
	$selPeriodo = $_POST["selPeriodo"];
	$i = strpos($selPeriodo, '|');
	if ($i !== false){
		$FechaPeriodo = LimpiarNumero2(substr($selPeriodo, 0, $i));
		$NumeroLiquidacion = LimpiarNumero(substr($selPeriodo, $i+1));
	}
	if ($FechaPeriodo == '' || $NumeroLiquidacion == '')
		exit;

	$Jur = LimpiarNumero($_POST["chkJurisdiccion"]);
	$selCentroCostos = LimpiarNumero($_POST["selCentroCostos"]);
	$chkTipoRelM = (LimpiarNumero($_POST["chkTipoRelM"]) == '1' ? true : false);
	$chkTipoRelJ = (LimpiarNumero($_POST["chkTipoRelJ"]) == '1' ? true : false);
	$chkTipoRelC = (LimpiarNumero($_POST["chkTipoRelC"]) == '1' ? true : false);
	$chkTipoRelL = (LimpiarNumero($_POST["chkTipoRelL"]) == '1' ? true : false);
	$chkTipoRelP = (LimpiarNumero($_POST["chkTipoRelP"]) == '1' ? true : false);
	$LP = LimpiarNumero($_POST["chkLugarPago"]);
	$LegDesde = LimpiarNumero($_POST["LegDesde"]);
	$LegHasta = LimpiarNumero($_POST["LegHasta"]);
	$TipoRelacion = '';
	if ($chkTipoRelM == true)
		$TipoRelacion .= '1,';
	if ($chkTipoRelJ == true)
		$TipoRelacion .= '2,';
	if ($chkTipoRelC == true)
		$TipoRelacion .= '3,';
	if ($chkTipoRelL == true)
		$TipoRelacion .= '4,';
	if ($chkTipoRelP == true)
		$TipoRelacion .= '5,';
	$TipoRelacion = substr($TipoRelacion, 0, -1);
	print "<br><b>Per&iacute;odo: " . Mes(substr($FechaPeriodo, 5, 2)) . " de " . substr($FechaPeriodo, 0, 4);
	print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Numero de Liquidaci&oacute;n: $NumeroLiquidacion</b><br><br>";
?>
	<a href="#" class="tecla" onclick="MM_openBrWindow('recibosPrint2.php?CentroCostos=<?=$selCentroCostos?>&TipoRelacion=<?=$TipoRelacion?>&FechaPeriodo=<?=$FechaPeriodo?>&NumeroLiquidacion=<?=$NumeroLiquidacion?>&LegDesde=<?=$LegDesde?>&LegHasta=<?=$LegHasta?>&Jur=<?=$Jur?>&LP=<?=$LP?>','printpreview','width=872,height=750')">
	<img src="images/icon24_print.gif" alt="Imprimir" width="24" height="23" border="0" align="absmiddle">  Imprimir (Libro de Sueldos) </a>
	<a href="#" class="tecla" onclick="MM_openBrWindow('listadoRecibosPrint.php?CentroCostos=<?=$selCentroCostos?>&TipoRelacion=<?=$TipoRelacion?>&FechaPeriodo=<?=$FechaPeriodo?>&NumeroLiquidacion=<?=$NumeroLiquidacion?>&LegDesde=<?=$LegDesde?>&LegHasta=<?=$LegHasta?>&LP=<?=$LP?>','printpreview','width=872,height=750')">
	<img src="images/icon24_print.gif" alt="Imprimir" width="24" height="23" border="0" align="absmiddle">  Imprimir (Recibos de Sueldo) </a><br />
<?
	$sJoin = "INNER JOIN \"tblEmpleados\" em ON em.\"EmpresaID\" = re.\"EmpresaID\" AND
	em.\"SucursalID\" = re.\"SucursalID\" AND em.\"Legajo\" = re.\"Legajo\" AND ";
	if ($selCentroCostos > 0)
		$sJoin .= "em.\"CentroCostos\" = $selCentroCostos AND ";
	if ($TipoRelacion != '')
		$sJoin .= "em.\"TipoRelacion\" in ($TipoRelacion) AND ";
	if ($LegajoNumerico == '1'){
		if ($LegDesde != '')
			$sJoin .= "to_number(em.\"Legajo\", '999999') >= $LegDesde AND ";
		if ($LegHasta != '')
			$sJoin .= "to_number(em.\"Legajo\", '999999') <= $LegHasta AND ";
	}
	$sJoin = substr($sJoin, 0, -5);

	$rs = pg_query($db, "
SELECT count(DISTINCT re.\"Legajo\")
FROM \"tblRecibos\" re
$sJoin
WHERE re.\"EmpresaID\" = $EmpresaID AND re.\"SucursalID\" = $SucursalID
AND re.\"Legajo\" = em.\"Legajo\" AND re.\"Fecha\" = '$FechaPeriodo' AND re.\"NumeroLiquidacion\" = $NumeroLiquidacion");
	if (!$rs)
		exit;
	$row = pg_fetch_array($rs);
	$Cantidad = $row[0];
	if ($Cantidad > 0){
?>
<div id=divLoading style="display:block">
<table height=100% align=center valign=center>
<tr><td nowrap="nowrap"><img src="images/icon32_process.gif" align=absmiddle width=32 height=32 border=0> Cargando <?=$Cantidad?> Recibos</td></tr>
</table>
</div><br>
<?
	$rs = pg_query($db, "
SELECT $sqlLegajo, re.\"Fecha\", re.\"ConceptoID\", re.\"Descripcion\", re.\"Cantidad\", re.\"Haber1\", re.\"Haber2\", 
re.\"Descuento\", re.\"Aporte\", (CASE co.\"ClaseID\" WHEN 0 THEN 9 ELSE co.\"ClaseID\" END) AS \"Orden\",
em.\"Apellido\" || ' ' || em.\"Nombre\" AS \"ApeYNom\", ed.\"FechaIngreso\", re.\"AliasID\", er.jurisdiccion, 
ed.\"LugarPago\", lp.\"Descripcion\"
FROM \"tblRecibos\" re
$sJoin
INNER JOIN \"tblConceptos\" co
ON co.\"EmpresaID\" = re.\"EmpresaID\" AND co.\"ConceptoID\" = re.\"ConceptoID\" AND co.\"ImprimeEnRecibo\" = true
INNER JOIN \"tblEmpleadosDatos\" ed
ON ed.\"EmpresaID\" = re.\"EmpresaID\" AND ed.\"SucursalID\" = re.\"SucursalID\" AND ed.\"Legajo\" = re.\"Legajo\"
LEFT JOIN \"tblEmpleadosRafam\" er
ON er.\"EmpresaID\" = re.\"EmpresaID\" AND er.\"SucursalID\" = re.\"SucursalID\" AND er.\"Legajo\" = re.\"Legajo\"
LEFT JOIN \"tblLugaresDePago\" lp
ON lp.\"EmpresaID\" = re.\"EmpresaID\" AND lp.\"LugarPago\" = ed.\"LugarPago\" AND lp.\"Activo\" = true
WHERE re.\"EmpresaID\" = $EmpresaID AND re.\"SucursalID\" = $SucursalID
AND re.\"Legajo\" = em.\"Legajo\" AND re.\"Fecha\" = '$FechaPeriodo' AND re.\"NumeroLiquidacion\" = $NumeroLiquidacion
ORDER BY ".($Jur=='1'?"er.jurisdiccion,":($LP == '1'?"ed.\"LugarPago\",":""))."1, \"Orden\", re.\"ConceptoID\", re.\"Descripcion\"");
	if (!$rs)
		exit;
	$Neto = 0;
	$AntLegajo = '';
	$AntLP = '';
	$AntJur = '';
	while($row = pg_fetch_array($rs)){
		$Legajo = $row[0];
		$ApeYNom = $row[10];
		$FechaIng = FechaSQL2WEB($row[11]);
		$Jurisdiccion = $row[13];
		$LugarPago = $row[14];
		$LPDesc = $row[15];
		if ($Legajo != $AntLegajo){
			if ($AntLegajo != ''){
				print "<TR><TD colspan=6 style='text-align:right'>NETO A COBRAR: <B>$Neto</B><br></TD></TR></table><br>\n";
			}
			$AntLegajo = $Legajo;
			if ($Jur == '1'){
				if ($Jurisdiccion != $AntJur){
					print "<b>Jurisdicci&oacute;n: " . Jurisdiccion($db, $Jurisdiccion) . "</b><br><br>";
					$AntJur = $Jurisdiccion;
				}
			}else if ($LP == '1'){
				if ($LugarPago != $AntLP){
					print "<b>Lugar De Pago: $LugarPago ($LPDesc)</b><br><br>";
					$AntLP = $LugarPago;
				}
			}else{
				print "<br><br>";
			}
?>
	<table width="100%" border="0" cellpadding="5" cellspacing="1" class="datagrid">
	<tr><td colspan="6" style="text-align:left">Legajo Nro.: <B><?=$Legajo?></B>&nbsp;&nbsp;&nbsp;<B><?=$ApeYNom?></B>&nbsp;&nbsp;&nbsp;Fecha Ingreso: <B><?=$FechaIng?></B></td></tr>
		<th>Conc.</th><th>Descripcion</th><th>Cant.</th><th nowrap="nowrap">Haber c/desc</th><th nowrap="nowrap">Haber s/desc</th><th>Desc.</th>
	<?
		} // END IF
		$Concepto = $row[2];
		$Descripcion = $row[3];
		$Cantidad = $row[4];
		$Haber1 = $row[5];
		$Haber2 = $row[6];
		$Descuento = $row[7];
		$AliasID = $row[12];
		if ($Haber1 != '')
			$Haber1 = round($Haber1, 2);
		if ($Haber2 != '')
			$Haber2 = round($Haber2, 2);
		if ($Descuento != '')
			$Descuento = round($Descuento, 2);
		if ($Concepto == 99){
			$Neto = $Haber1 + $Haber2 - $Descuento;
		}
	?>
	<tr>
		<td><?=$AliasID?></td><td><?=$Descripcion?></td><td><?=$Cantidad?></td><td><?=$Haber1?></td><td><?=$Haber2?></td><td><?=$Descuento?></td>
	</tr>
	<?
	} // END WHILE
	if ($AntLegajo != ''){
		print "<TR><TD colspan=6 style='text-align:right'>NETO A COBRAR: <B>$Neto</B><br></TD></TR></table>";
	}else{
		Alerta('No se encontraron recibos para el criterio seleccionado');
	}
?>
	<script>
		document.getElementById('divLoading').style.display = 'none';
	</script>
<?
	}else{
		Alerta('No se encontraron recibos para el criterio seleccionado');
	}
}

if ($accion == ''){
	include 'selLiquida.php'; ?>
	<TR>
	<TD class="izquierdo">Tipo de Relaci&oacute;n:</td><TD class=derecho2>
	<input type=checkbox id=chkTipoRelM name=chkTipoRelM value=1>Mensual.
	<input type=checkbox id=chkTipoRelJ name=chkTipoRelJ value=1>Jornal.
	<input type=checkbox id=chkTipoRelC name=chkTipoRelC value=1>Contratados
	<input type=checkbox id=chkTipoRelL name=chkTipoRelL value=1>Loc. de obra
	<input type=checkbox id=chkTipoRelP name=chkTipoRelP value=1>Pasantia
	</td></tr>
	<TR>
		<TD class="izquierdo">Desglosar por Jurisdiccion:</TD><TD class="derecho"><input type=checkbox id=chkJurisdiccion name=chkJurisdiccion value=1></TD>
	</TR>
	<TR>
		<TD class="izquierdo">Desglosar por Lugar de Pago:</TD><TD class="derecho"><input type=checkbox id=chkLugarPago name=chkLugarPago value=1></TD>
	</TR>
<?
	if ($LegajoNumerico == '1'){
?>
	<tr><TD class="izquierdo">Legajo Desde:</td><TD class=derecho2><input type=text name=LegDesde size=5>
	Si no se completa comienza por el primer Legajo</td></tr>
	<tr><TD class="izquierdo">Legajo Hasta:</td><TD class=derecho2><input type=text name=LegHasta size=5>
	Si no se completa continua hasta el ultimo Legajo</td></tr>
<?
	}
?>
	<TR>
		<TD class="izquierdo">Centro de Costos:</TD><TD class="derecho"><select id=selCentroCostos name=selCentroCostos>
	<option value=0>Todos</option>
<?
	$rs = pg_query($db, "
SELECT cc.\"CentroDeCostoID\", cc.\"Descripcion\"
FROM \"tblCentroDeCostos\" cc
WHERE cc.\"EmpresaID\" = $EmpresaID AND cc.\"SucursalID\" = $SucursalID
ORDER BY \"Descripcion\"");
	if (!$rs){
		exit;
	}
	while($row = pg_fetch_array($rs)){
		print "<option value=$row[0]>";
		if ($row[1] == '')
			print "$row[0]</option>\n";
		else
			print "$row[1]</option>\n";
	}
?>
	</select></TD></TR>
	<TR>
		<TD class="izquierdo"></TD><TD class="derecho"><input type=submit id=accion name=accion value="Ver Recibos"></TD></TR></table>
<?
}
pg_close($db);
?>
</form>
<? include("footer.php"); ?>
