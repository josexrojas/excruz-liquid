<?
require_once('funcs.php');
EstaLogeado();
$accion = LimpiarVariable($_POST["accion"]);

if ($accion == 'Bajar Listado'){
	$arch = LimpiarVariable($_POST["listado"]);
	EnviarArchivo('../listados/', $arch);
	exit;
}

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

$accion = LimpiarVariable($_POST["accion"]);
?>

<div id=divLoading style="display:block">
<table height=100% align=center valign=center>
<tr><td><img src="images/icon32_process.gif" align=absmiddle width=32 height=32 border=0> Generando Listado</td></tr>
</table>
</div>
<script>
	function BajarListado(sArch){
		document.getElementById('accion').value = 'Bajar Listado';
		document.getElementById('listado').value = sArch;
		document.frmListadoConcLiq.submit();
	}
</script>
<form name=frmListadoConcLiq action=listadoConcLiq.php method=post>
<input type=hidden name=accion id=accion>
<input type=hidden name=listado id=listado>

<?
if ($accion == 'Ver Listado'){
	$selPeriodo = $_POST["selPeriodo"];
	$selConcepto = LimpiarNumero($_POST["selConcepto"]);
	$ConcDesc = LimpiarVariable($_POST["ConcDesc"]);
	$i = strpos($selPeriodo, '|');
	if ($i !== false){
		$FechaPeriodo = LimpiarNumero2(substr($selPeriodo, 0, $i));
		$NumeroLiquidacion = LimpiarNumero(substr($selPeriodo, $i+1));
	}
	if ($FechaPeriodo == '' || $NumeroLiquidacion == ''){
		exit;
	}
	$chkTipoPlanta = LimpiarNumero($_POST["chkTipoPlanta"]);
	$chkJurisdiccion = LimpiarNumero($_POST["chkJurisdiccion"]);
	$rdColumnaSB = LimpiarNumero($_POST["rdColumnaSB"]);
	
 
     
	$rs = pg_query($db, "
SELECT $sqlLegajo, em.\"Apellido\", em.\"Nombre\", re.\"Cantidad\", re.\"Haber1\", re.\"Haber2\", re.\"Descuento\", 
re.\"Aporte\", er.jurisdiccion, em.\"TipoRelacion\",
(SELECT CASE WHEN p.\"TipoLiquidacionID\" = 10 THEN re2.\"Haber2\" ELSE re2.\"Haber1\" END FROM \"tblRecibos\" re2
INNER JOIN \"tblPeriodos\" p
ON p.\"FechaPeriodo\" = re2.\"Fecha\" AND p.\"NumeroLiquidacion\" = re2.\"NumeroLiquidacion\" AND p.\"EmpresaID\" = re2.\"EmpresaID\" AND p.\"SucursalID\" = re2.\"SucursalID\"
WHERE re2.\"ConceptoID\" = 99 AND re2.\"EmpresaID\" = $EmpresaID 
AND re2.\"SucursalID\" = $SucursalID AND re2.\"Fecha\" = '$FechaPeriodo' AND re2.\"NumeroLiquidacion\" = $NumeroLiquidacion
AND re2.\"Legajo\" = re.\"Legajo\") as \"SueldoBruto\"
FROM \"tblRecibos\" re
INNER JOIN \"tblEmpleados\" em
ON em.\"EmpresaID\" = re.\"EmpresaID\" AND em.\"SucursalID\" = re.\"SucursalID\" AND em.\"Legajo\" = re.\"Legajo\"
INNER JOIN \"tblEmpleadosRafam\" er
ON er.\"EmpresaID\" = re.\"EmpresaID\" AND er.\"SucursalID\" = re.\"SucursalID\" AND er.\"Legajo\" = re.\"Legajo\"
LEFT JOIN owner_rafam.cargos ca
ON substr(er.jurisdiccion, 1, 5) = substr(ca.jurisdiccion, 1, 5) AND er.agrupamiento = ca.agrupamiento AND
er.categoria = ca.categoria AND er.cargo = ca.cargo
WHERE re.\"AliasID\" = $selConcepto AND re.\"EmpresaID\" = $EmpresaID AND re.\"SucursalID\" = $SucursalID AND
re.\"Fecha\" = '$FechaPeriodo' AND re.\"NumeroLiquidacion\" = $NumeroLiquidacion
ORDER BY ".($chkJurisdiccion == '1' ? '9,':'').($chkTipoPlanta == '1' ? '10,':'')."1
");


	if (!$rs){
		exit;
	}
	$arch = "ConcLiq$selConcepto.txt";
?>
<H1>Conceptos Liquidados</H1>
	<a class="tecla" href='javascript:window.print(); void(0);'> 
	<img src="images/icon24_printlistado.gif" alt="Imprimir Listado" width="24" height="24" border="0" align="absmiddle">
	Imprimir Listado</a> 
	<a class="tecla" href="javascript:BajarListado('<?=$arch?>'); void(0);"> 
	<img src="images/icon24_bajarlistado.gif" alt="Bajar Listado" width="24" height="24" border="0" align="absmiddle"> 
	Bajar Listado</a><br><br>
	<b>Concepto Listado: <?=$ConcDesc?></b><br><br>
<?
	print "<br><b>Per&iacute;odo: " . Mes(substr($FechaPeriodo, 5, 2)) . " de " . substr($FechaPeriodo, 0, 4);
	print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Numero de Liquidaci&oacute;n: $NumeroLiquidacion</b><br><br>";
	$Jurisdiccion = '';
	$AntJur = '';
	$TipoRel = '';
	$AntRel = '';
	$Abrir = 1;
	$TotalImporte = 0;
	$TotalLegajo = 0;
	$Detalle = '';
	$CantEmp = 0;
	while($row = pg_fetch_array($rs))
	{
		$CantEmp++;
		$TotalLegajo++;
		$Legajo = $row[0];
		$Apellido = $row[1];
		$Nombre = $row[2];
		$Cantidad = $row[3];
		$ApeyNom = $Apellido . ', ' . $Nombre;
		$H1 = $row[4];
		$H2 = $row[5];
		$Desc = $row[6];
		$Aporte = $row[7];
		$SueldoBruto = $row[10];
		switch($row[9]){
		case 1:
			$TipoRel = 'Mensualizado';
			break;
		case 2:
			$TipoRel = 'Jornalizado';
			break;
		case 3:
			$TipoRel = 'Contratado';
			break;
		}
		if ($chkJurisdiccion == '1')
			$Jurisdiccion = $row[8];
		if ($H1 != '')
			$Importe = $H1;
		if ($H2 != '')
			$Importe = $H2;
		if ($Desc != '')
			$Importe = $Desc;
		if ($Aporte != '')
			$Importe = $Aporte;
		$TotalImporte += $Importe;
		$Detalle .= '"' . str_pad($Legajo, 10, ' ', STR_PAD_LEFT) . '"';
		$Detalle .= ',"' . str_pad($ApeyNom, 30, ' ', STR_PAD_LEFT) . '"';
		$Detalle .= ',"' . str_pad($Cantidad, 10, ' ', STR_PAD_LEFT) . '"';
		$Detalle .= ',"' . str_pad($Importe, 10, ' ', STR_PAD_LEFT) . '"';
		if ($rdColumnaSB == '1')
			$Detalle .= ',"' . str_pad($SueldoBruto, 10, ' ', STR_PAD_LEFT);
		$Detalle .= "\r\n";
		if ($chkJurisdiccion == '1' && $Jurisdiccion != '' && $AntJur != $Jurisdiccion){
			if ($AntJur != '')
				$Cerrar = 1;
		}
		if ($chkTipoPlanta == '1' && $TipoRel != '' && $AntRel != $TipoRel){
			if ($AntRel != '')
				$Cerrar = 1;
		}
		if ($Cerrar == 1){
			$Cerrar = 0;
			$CantEmp--;
			print "</table><br>\n";
			print "<b>Cantidad de empleados activos: $CantEmp</b><br><br>\n";
			$CantEmp = 1;
		}
		if ($chkJurisdiccion == '1' && $Jurisdiccion != '' && $AntJur != $Jurisdiccion){
			print "<b>Jurisdicci&oacute;n: " . Jurisdiccion($db, $Jurisdiccion) . "</b><br><br>";
			$AntJur = $Jurisdiccion;
			$AntTP = '0';
			$Abrir = 1;
		}
		if ($chkTipoPlanta == '1' && $TipoRel != '' && $AntRel != $TipoRel){
			print "<b>Tipo De Relaci&oacute;n: $TipoRel</b><br><br>";
			$AntRel = $TipoRel;
			$Abrir = 1;
		}
		if ($Abrir == 1){
			$Abrir = 0;
?>
			<table width="100%" border="0" cellpadding="5" cellspacing="1" class="datagrid">
			<tr><th>Legajo</th><th>Apellido y Nombre</th><th>Cant/Por</th><th>Importe</th><? if ($rdColumnaSB == '1') print "<th>Sueldo Bruto</th>"; ?></tr>
<?
		}
?>
		<tr><td><?=$Legajo?></td><td><?=$ApeyNom?></td><td><?=$Cantidad?></td><td><?=$Importe?></td><? if ($rdColumnaSB == '1') print "<td>$SueldoBruto</td>"; ?></tr>
		
<?
	}
	if ($TotalLegajo > 0){
		$fp = fopen('../listados/'.$arch, 'wt');
		fputs($fp, $Detalle);
		fclose($fp);
		print "</table>\n";
		print "<br><b>Total Empleados: $TotalLegajo<br>";
		print "Total Importe: $TotalImporte</b><br><br>";
	}else{
		print "</table>\n";
		Alerta('El concepto indicado no se encontr&oacute; en la liquidaci&oacute;n seleccionada');
	}
}

if ($accion == ''){
	include 'selLiquida.php';
	$rs = pg_query($db, "
SELECT ca.\"AliasID\", ca.\"Descripcion\" FROM \"tblConceptosAlias\" ca
INNER JOIN \"tblConceptos\" co
ON co.\"ConceptoID\"=ca.\"ConceptoID\" AND co.\"Activo\"=true
WHERE ca.\"EmpresaID\" = $EmpresaID AND (ca.\"Liquida\" > 0 OR \"AliasID\" IN (5,6,9,10)) ORDER BY ca.\"Descripcion\"");
	if (!$rs)
		exit;
?>
	<TR>
		<TD width="200" class="izquierdo">Seleccione un concepto:</TD>
		<TD class="derecho">
		<input type=hidden id=ConcDesc name=ConcDesc>
		<select id=selConcepto name=selConcepto> 
<?
	while($row = pg_fetch_array($rs))
	{
		$AliasID = $row[0];
		$Descripcion = $row[1];
		print "<option value=\"$AliasID\">$Descripcion</option>";
	}
?>
	</select></TD></TR>
	<TR>
		<TD class="izquierdo">Desglosar por Tipo De Relaci&oacute;n:</TD><TD class="derecho"><input type=checkbox id=chkTipoPlanta name=chkTipoPlanta value=1></TD>
	</TR>
	<TR>
		<TD class="izquierdo">Desglosar por Jurisdicci&oacute;n:</TD><TD class="derecho"><input type=checkbox id=chkJurisdiccion name=chkJurisdiccion value=1></TD>
	</TR>
	<TR>
		<TD width="200" class="izquierdo">&iquest;Incluir columna de sueldo bruto?:</TD>
		<TD class="derecho"><input type=radio name=rdColumnaSB value=1>Si
		<input type=radio name=rdColumnaSB value=0 checked>No</TD>
	</TR>
	<TR>
		<TD class="izquierdo"></TD><TD class="derecho">
		<input type=submit id=accion name=accion value="Ver Listado" onclick="javascript:frmListadoConcLiq.ConcDesc.value = selConcepto.options[selConcepto.selectedIndex].text;">
	</TD></TR></table>
<?
}
pg_close($db);
?>
</form>
<script>
	document.getElementById('divLoading').style.display = 'none';
</script>
<? include("footer.php"); ?>
