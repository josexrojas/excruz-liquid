<?
include("header.php");

if (!($db = Conectar()))
	exit;

$EmpresaID = $_SESSION["EmpresaID"];
$SucursalID = $_SESSION["SucursalID"];

$accion = LimpiarVariable($_POST["accion"]);
?>

<script>
<!--
function MM_openBrWindow(theURL,winName,features) { //v2.0
  window.open(theURL,winName,features);
}
//-->
</script>

<div id=divLoading style="display:block">
<table height=100% align=center valign=center>
<tr><td><img src="images/icon32_process.gif" align=absmiddle width=32 height=32 border=0> Generando Listado</td></tr>
</table>
</div>
<form name=frmListadoEstConcLiq action=listadoEstConcLiq.php method=post>
<input type=hidden name=accion id=accion>
<input type=hidden name=listado id=listado>

<?
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
	$Jur = LimpiarNumero($_POST["chkJurisdiccion"]);
	$TP = LimpiarNumero($_POST["chkTipoPlanta"]);
	$chkTipoRelM = (LimpiarNumero($_POST["chkTipoRelM"]) == '1' ? true : false);
	$chkTipoRelJ = (LimpiarNumero($_POST["chkTipoRelJ"]) == '1' ? true : false);
	$chkTipoRelC = (LimpiarNumero($_POST["chkTipoRelC"]) == '1' ? true : false);
	$chkTipoRelL = (LimpiarNumero($_POST["chkTipoRelL"]) == '1' ? true : false);
	$TipoRelacion = '';
	if ($chkTipoRelM == true)
		$TipoRelacion .= '1,';
	if ($chkTipoRelJ == true)
		$TipoRelacion .= '2,';
	if ($chkTipoRelC == true)
		$TipoRelacion .= '3,';
	if ($chkTipoRelL == true)
		$TipoRelacion .= '4,';
	$TipoRelacion = substr($TipoRelacion, 0, -1);
	if ($TipoRelacion != '')
		$TP = '1';
	$sql = "
SELECT
	(CASE co.\"ClaseID\" WHEN 0 THEN 9 ELSE co.\"ClaseID\" END) AS \"Orden\", re.\"AliasID\", re.\"Descripcion\", 
	count(1) AS \"CantidadLegajos\", sum(\"Cantidad\") AS \"CantidadLiquidada\", round(sum(\"Haber1\")::numeric, 2) AS 
	\"Haber1\", round(sum(\"Haber2\")::numeric, 2) AS \"Haber2\", round(sum(\"Descuento\")::numeric, 2) AS \"Descuentos\", 
	round(sum(\"Aporte\")::numeric, 2)".($Jur=='1'?",er.jurisdiccion":"").($TP=='1'?",em.\"TipoRelacion\"":"")."
FROM \"tblRecibos\" re
INNER JOIN \"tblConceptos\" co
ON co.\"EmpresaID\" = re.\"EmpresaID\" AND co.\"ConceptoID\" = re.\"ConceptoID\"
INNER JOIN \"tblEmpleados\" em
ON em.\"EmpresaID\" = re.\"EmpresaID\" AND em.\"SucursalID\" = re.\"SucursalID\" AND em.\"Legajo\" = re.\"Legajo\"".
($TipoRelacion != '' ? " AND em.\"TipoRelacion\" in ($TipoRelacion)" : '')." 
LEFT JOIN \"tblEmpleadosRafam\" er
ON er.\"EmpresaID\" = re.\"EmpresaID\" AND er.\"SucursalID\" = re.\"SucursalID\" AND er.\"Legajo\" = re.\"Legajo\"
WHERE re.\"ConceptoID\" <> 99 AND re.\"EmpresaID\" = $EmpresaID AND re.\"SucursalID\" = $SucursalID AND
re.\"Fecha\" = '$FechaPeriodo' AND re.\"NumeroLiquidacion\" = $NumeroLiquidacion
GROUP BY 2, 3, 1".($Jur=='1'?",10":"").($Jur=='1'&&$TP=='1'?",11":"").($Jur!='1'&&$TP=='1'?",10":"")."
ORDER BY ".($Jur=='1'?"10,":"").($Jur=='1'&&$TP=='1'?"11,":"").($Jur!='1'&&$TP=='1'?"10,":"")." 1, 3
";

	$rs = pg_query($db, $sql);

	if (!$rs){
		exit;
	}
?>
<H1>Estadistica de conceptos liquidados</H1>
	<a class="tecla" href="#" onclick="MM_openBrWindow('listadoEstConcLiqPrint2.php?FechaPeriodo=<?=$FechaPeriodo?>&NumeroLiquidacion=<?=$NumeroLiquidacion?>&Jur=<?=$Jur?>&TP=<?=$TP?>&TR=<?=$TipoRelacion?>','printpreview','width=872,height=750')"> 
	<img src="images/icon24_printlistado.gif" alt="Imprimir Listado" width="24" height="24" border="0" align="absmiddle">
	Imprimir Listado</a>&nbsp;<BR><br>
<?
	$TotalH1 = 0;
	$TotalH2 = 0;
	$TotalDesc = 0;
	$TotalAporte = 0;
	$TotalGH1 = 0;
	$TotalGH2 = 0;
	$TotalGDesc = 0;
	$TotalGAporte = 0;
	$Jurisdiccion = '';
	$AntJur = '';
	$TipoPlanta = '';
	$AntTP = '';
	$Abrir = 1;
	print "<br><b>Per&iacute;odo: " . Mes(substr($FechaPeriodo, 5, 2)) . " de " . substr($FechaPeriodo, 0, 4);
	print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Numero de Liquidaci&oacute;n: $NumeroLiquidacion</b><br><br>";
	while($row = pg_fetch_array($rs))
	{
		$i=1;
		$CID = $row[$i++];
		$Descr = $row[$i++];
		$CantLeg = $row[$i++];
		$CantLiq = $row[$i++];
		$H1 = $row[$i++];
		$H2 = $row[$i++];
		$Desc = $row[$i++];
		$Aporte = $row[$i++];
		if ($Jur == '1')
			$Jurisdiccion = $row[$i++];
		if ($TP == '1')
			$TipoPlanta = $row[$i++];

		if ($Jurisdiccion != '' && $AntJur != $Jurisdiccion){
			if ($AntJur != '')
				$Cerrar = 1;
		}
		if ($TipoPlanta != '' && $AntTP != $TipoPlanta){
			if ($AntTP != '')
				$Cerrar = 1;
		}
		if ($Cerrar == 1){
			$Cerrar = 0;
?>
			<tr><td></td><td><b>Totales</b></td><td></td><td></td><td><b><?=$TotalH1?></b></td><td><b><?=$TotalH2?></b></td><td><b><?=$TotalDesc?></b></td><td><b><?=$TotalAporte?></b></td></tr>
<?
			$TotalH1 = 0;
			$TotalH2 = 0;
			$TotalDesc = 0;
			$TotalAporte = 0;
			print "</table><br>\n";
		}
		if ($Jurisdiccion != '' && $AntJur != $Jurisdiccion){
			print "<b>Jurisdicci&oacute;n: " . Jurisdiccion($db, $Jurisdiccion) . "</b><br><br>";
			$AntJur = $Jurisdiccion;
			$AntTP = '0';
			$Abrir = 1;
		}
		if ($TipoPlanta != '' && $AntTP != $TipoPlanta){
			print "<b>Tipo De Relaci&oacute;n: ";
			if ($TipoPlanta == '1')
				print "Mensualizado";
			else if ($TipoPlanta == '2')
				print "Jornalizado";
			else if ($TipoPlanta == '3')
				print "Contratado";
			print "</b><br><br>";
			$AntTP = $TipoPlanta;
			$Abrir = 1;
		}
		if ($Abrir == 1){
			$Abrir = 0;
?>
			<table width="100%" border="0" cellpadding="5" cellspacing="1" class="datagrid">
			<tr><th>Concepto</th><th>Descripci&oacute;n</th><th>Cant.Leg.</th><th>Cant.Liq.</th><th>Haber C/Desc</th><th>Haber S/Desc</th><th>Descuentos</th><th>Aportes</th></tr>
<?
		}
		if ($H1 != ''){
			$TotalH1 += $H1;
			$TotalGH1 += $H1;
		}
		if ($H2 != ''){
			$TotalH2 += $H2;
			$TotalGH2 += $H2;
		}
		if ($Desc != ''){
			$TotalDesc += $Desc;
			$TotalGDesc += $Desc;
		}
		if ($Aporte != ''){
			$TotalAporte += $Aporte;
			$TotalGAporte += $Aporte;
		}
?>
		<tr><td><?=$CID?></td><td><?=$Descr?></td><td><?=$CantLeg?></td><td><?=$CantLiq?></td><td><?=$H1?></td><td><?=$H2?></td><td><?=$Desc?></td><td><?=$Aporte?></td></tr>
<?
	}
?>
		<tr><td></td><td><b>Totales</b></td><td></td><td></td><td><b><?=$TotalH1?></b></td><td><b><?=$TotalH2?></b></td><td><b><?=$TotalDesc?></b></td><td><b><?=$TotalAporte?></b></td></tr>
		<tr><td></td><td><b>Total General</b></td><td></td><td></td><td><b><?=$TotalGH1?></b></td><td><b><?=$TotalGH2?></b></td><td><b><?=$TotalGDesc?></b></td><td><b><?=$TotalGAporte?></b></td></tr>
<?
	$TotalNeto = $TotalGH1 + $TotalGH2 - $TotalGDesc;
	print "</table>\n";
	print "<br><b>Total Neto General: $TotalNeto</b><br>";
}

if ($accion == ''){
	include 'selLiquida.php'; ?>
	<TR>
		<TD class="izquierdo">Tipo De Relaci&oacute;n:</TD><TD class="derecho2">
		<input type=checkbox id=chkTipoRelM name=chkTipoRelM value=1 checked>Mensualizados
		<input type=checkbox id=chkTipoRelJ name=chkTipoRelJ value=1 checked>Jornalizados
		<input type=checkbox id=chkTipoRelC name=chkTipoRelC value=1 checked>Contratados
		<input type=checkbox id=chkTipoRelL name=chkTipoRelL value=1 checked>Loc. de obra
		</TD>
	</TR>
	<TR>
		<TD class="izquierdo">Desglosar por Tipo De Relaci&oacute;n:</TD><TD class="derecho"><input type=checkbox id=chkTipoPlanta name=chkTipoPlanta value=1></TD>
	</TR>
	<TR>
		<TD class="izquierdo">Desglosar por Jurisdicci&oacute;n:</TD><TD class="derecho"><input type=checkbox id=chkJurisdiccion name=chkJurisdiccion value=1></TD>
	</TR>
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
