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
<form name=frmListadoEstConcLiq action=informeSector.php method=post>
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

	if ($_SESSION["LegajoNumerico"] == '1'){
		$sqlLegajo = "to_number(re.\"Legajo\", '999999') AS \"Legajo\"";
	}else{
		$sqlLegajo = "re.\"Legajo\"";
	}

	$Jur = LimpiarNumero($_POST["chkJurisdiccion"]);
	$Act = LimpiarNumero($_POST["chkActividad"]);
	$Pro = LimpiarNumero($_POST["chkPrograma"]);
	$Orden = 'ORDER BY ';
	$filJurisdiccion = LimpiarNumero($_POST["filJurisdiccion"]);
	$Where = '';
	if ($filJurisdiccion != '0') {
		$Where = " and er.jurisdiccion = '$filJurisdiccion' ";
	}
	if ($Jur == '1')
		$Orden .= '2, ';
	if ($Pro == '1')
		$Orden .= '4, ';
	if ($Act == '1')
		$Orden .= '3, ';
	$Orden = substr($Orden, 0, -2);
	if ($Orden == 'ORDER B')
		$Orden = '';
		
	$sql = "
select distinct $sqlLegajo, er.jurisdiccion, er.activ_proy, er.programa, er.categoria, em.\"Apellido\", em.\"Nombre\",

(select sum(\"Haber1\") from \"tblRecibos\" re1 
where re1.\"EmpresaID\" = re.\"EmpresaID\" and re1.\"SucursalID\" = re.\"SucursalID\" and re1.\"Fecha\" = re.\"Fecha\"
and re1.\"NumeroLiquidacion\" = re.\"NumeroLiquidacion\" and re1.\"Legajo\" = re.\"Legajo\" 
and re1.\"ConceptoID\" in (1,2)) as \"Basico\",

(select sum(\"Haber1\") from \"tblRecibos\" re1 
where re1.\"EmpresaID\" = re.\"EmpresaID\" and re1.\"SucursalID\" = re.\"SucursalID\" and re1.\"Fecha\" = re.\"Fecha\"
and re1.\"NumeroLiquidacion\" = re.\"NumeroLiquidacion\" and re1.\"Legajo\" = re.\"Legajo\" 
and re1.\"ConceptoID\" in (10,30)) as \"Antiguedad\",

(select sum(\"Haber1\") from \"tblRecibos\" re1 
where re1.\"EmpresaID\" = re.\"EmpresaID\" and re1.\"SucursalID\" = re.\"SucursalID\" and re1.\"Fecha\" = re.\"Fecha\"
and re1.\"NumeroLiquidacion\" = re.\"NumeroLiquidacion\" and re1.\"Legajo\" = re.\"Legajo\" 
and re1.\"AliasID\" in (88,89)) as \"Vacaciones\",

(select sum(\"Haber1\") from \"tblRecibos\" re1 
where re1.\"EmpresaID\" = re.\"EmpresaID\" and re1.\"SucursalID\" = re.\"SucursalID\" and re1.\"Fecha\" = re.\"Fecha\"
and re1.\"NumeroLiquidacion\" = re.\"NumeroLiquidacion\" and re1.\"Legajo\" = re.\"Legajo\" 
and re1.\"AliasID\" in (27,120)) as \"SACRefrigerio\",

(select sum(\"Haber1\") from \"tblRecibos\" re1 
where re1.\"EmpresaID\" = re.\"EmpresaID\" and re1.\"SucursalID\" = re.\"SucursalID\" and re1.\"Fecha\" = re.\"Fecha\"
and re1.\"NumeroLiquidacion\" = re.\"NumeroLiquidacion\" and re1.\"Legajo\" = re.\"Legajo\" 
and re1.\"AliasID\" in (13,90)) as \"FeriadoHSNocturnas\",

(select sum(\"Haber1\") from \"tblRecibos\" re1 
where re1.\"EmpresaID\" = re.\"EmpresaID\" and re1.\"SucursalID\" = re.\"SucursalID\" and re1.\"Fecha\" = re.\"Fecha\"
and re1.\"NumeroLiquidacion\" = re.\"NumeroLiquidacion\" and re1.\"Legajo\" = re.\"Legajo\"
and re1.\"ConceptoID\" = 99) as \"RemunAportes\",

(select sum(\"Haber1\") from \"tblRecibos\" re1 
where re1.\"EmpresaID\" = re.\"EmpresaID\" and re1.\"SucursalID\" = re.\"SucursalID\" and re1.\"Fecha\" = re.\"Fecha\"
and re1.\"NumeroLiquidacion\" = re.\"NumeroLiquidacion\" and re1.\"Legajo\" = re.\"Legajo\"
and re1.\"ConceptoID\" not in (1,2,10,30,99) and re1.\"AliasID\" not in (88,89,27,120,13,90)) as \"OtrosRemunAportes\",

(select sum(\"Haber2\") from \"tblRecibos\" re1 
where re1.\"EmpresaID\" = re.\"EmpresaID\" and re1.\"SucursalID\" = re.\"SucursalID\" and re1.\"Fecha\" = re.\"Fecha\"
and re1.\"NumeroLiquidacion\" = re.\"NumeroLiquidacion\" and re1.\"Legajo\" = re.\"Legajo\" 
and re1.\"AliasID\" in (128,85,25,86)) as \"Bonificaciones\",

(select sum(\"Haber2\") from \"tblRecibos\" re1 
where re1.\"EmpresaID\" = re.\"EmpresaID\" and re1.\"SucursalID\" = re.\"SucursalID\" and re1.\"Fecha\" = re.\"Fecha\"
and re1.\"NumeroLiquidacion\" = re.\"NumeroLiquidacion\" and re1.\"Legajo\" = re.\"Legajo\" 
and re1.\"AliasID\" in (36,118,37,130,129)) as \"SalarioFamiliar\",

(select sum(\"Haber2\") from \"tblRecibos\" re1 
where re1.\"EmpresaID\" = re.\"EmpresaID\" and re1.\"SucursalID\" = re.\"SucursalID\" and re1.\"Fecha\" = re.\"Fecha\"
and re1.\"NumeroLiquidacion\" = re.\"NumeroLiquidacion\" and re1.\"Legajo\" = re.\"Legajo\" 
and re1.\"AliasID\" in (133)) as \"AyudaEscolar\",

(select sum(\"Haber2\") from \"tblRecibos\" re1 
where re1.\"EmpresaID\" = re.\"EmpresaID\" and re1.\"SucursalID\" = re.\"SucursalID\" and re1.\"Fecha\" = re.\"Fecha\"
and re1.\"NumeroLiquidacion\" = re.\"NumeroLiquidacion\" and re1.\"Legajo\" = re.\"Legajo\" 
and re1.\"AliasID\" in (28)) as \"Presentismo\",

(select sum(\"Haber2\") from \"tblRecibos\" re1 
where re1.\"EmpresaID\" = re.\"EmpresaID\" and re1.\"SucursalID\" = re.\"SucursalID\" and re1.\"Fecha\" = re.\"Fecha\"
and re1.\"NumeroLiquidacion\" = re.\"NumeroLiquidacion\" and re1.\"Legajo\" = re.\"Legajo\" 
and re1.\"ConceptoID\" in (20,21)) as \"HsExtras\",

(select sum(\"Haber2\") from \"tblRecibos\" re1 
where re1.\"EmpresaID\" = re.\"EmpresaID\" and re1.\"SucursalID\" = re.\"SucursalID\" and re1.\"Fecha\" = re.\"Fecha\"
and re1.\"NumeroLiquidacion\" = re.\"NumeroLiquidacion\" and re1.\"Legajo\" = re.\"Legajo\"
and re1.\"ConceptoID\" = 99) as \"RemunSinAportes\",

(select sum(\"Haber2\") from \"tblRecibos\" re1 
where re1.\"EmpresaID\" = re.\"EmpresaID\" and re1.\"SucursalID\" = re.\"SucursalID\" and re1.\"Fecha\" = re.\"Fecha\"
and re1.\"NumeroLiquidacion\" = re.\"NumeroLiquidacion\" and re1.\"Legajo\" = re.\"Legajo\"
and re1.\"ConceptoID\" not in (99,91,20,21) and re1.\"AliasID\" not in (128,85,25,86,36,118,37,130,129,133,28)) as \"OtrosRemunSinAportes\",

(select sum(\"Descuento\") from \"tblRecibos\" re1 
where re1.\"EmpresaID\" = re.\"EmpresaID\" and re1.\"SucursalID\" = re.\"SucursalID\" and re1.\"Fecha\" = re.\"Fecha\"
and re1.\"NumeroLiquidacion\" = re.\"NumeroLiquidacion\" and re1.\"Legajo\" = re.\"Legajo\" 
and re1.\"AliasID\" in (17,18)) as \"Retenciones\",

(select sum(\"Descuento\") from \"tblRecibos\" re1 
LEFT JOIN \"tblConceptos\" co 
	on co.\"EmpresaID\" = re1.\"EmpresaID\"
	and co.\"ConceptoID\" = re1.\"ConceptoID\"
where re1.\"EmpresaID\" = re.\"EmpresaID\" 
	and re1.\"SucursalID\" = re.\"SucursalID\" 
	and re1.\"Fecha\" = re.\"Fecha\"
	and re1.\"NumeroLiquidacion\" = re.\"NumeroLiquidacion\" 
	and re1.\"Legajo\" = re.\"Legajo\" 
	and co.\"ClaseID\" = 3
	and re1.\"AliasID\" NOT in (17,18,114)) as \"OtrasRetenciones\",

(select sum(\"Descuento\") from \"tblRecibos\" re1 
where re1.\"EmpresaID\" = re.\"EmpresaID\" and re1.\"SucursalID\" = re.\"SucursalID\" and re1.\"Fecha\" = re.\"Fecha\"
and re1.\"NumeroLiquidacion\" = re.\"NumeroLiquidacion\" and re1.\"Legajo\" = re.\"Legajo\" 
and re1.\"AliasID\" in (20,21,24,19,33,34,71,72,73,77,78,23,79,80,81,82,83,22,75)) as \"RetencionesSind\",

/*
(select sum(\"Haber1\")+sum(\"Haber2\")-sum(\"Descuento\") from \"tblRecibos\" re1 
where re1.\"EmpresaID\" = re.\"EmpresaID\" and re1.\"SucursalID\" = re.\"SucursalID\" and re1.\"Fecha\" = re.\"Fecha\"
and re1.\"NumeroLiquidacion\" = re.\"NumeroLiquidacion\" and re1.\"Legajo\" = re.\"Legajo\") as \"Cobrado\",
*/

(select sum(\"Aporte\") from \"tblRecibos\" re1 
where re1.\"EmpresaID\" = re.\"EmpresaID\" 
		and re1.\"SucursalID\" = re.\"SucursalID\" 
		and re1.\"Fecha\" = re.\"Fecha\"
		and re1.\"NumeroLiquidacion\" = re.\"NumeroLiquidacion\" 
		and re1.\"Legajo\" = re.\"Legajo\"
		and re1.\"AliasID\" in (29,30)
		) as \"Aportes\"
		
from \"tblRecibos\" re 

inner join \"tblEmpleados\" em
on em.\"EmpresaID\" = re.\"EmpresaID\" AND em.\"SucursalID\" = re.\"SucursalID\" and em.\"Legajo\" = re.\"Legajo\"

inner join \"tblEmpleadosRafam\" er
on er.\"EmpresaID\" = re.\"EmpresaID\" AND er.\"SucursalID\" = re.\"SucursalID\" and er.\"Legajo\" = re.\"Legajo\" $Where

where re.\"EmpresaID\" = $EmpresaID AND re.\"SucursalID\" = $SucursalID AND 
re.\"Fecha\" = '$FechaPeriodo' AND re.\"NumeroLiquidacion\" = $NumeroLiquidacion 
$Orden
";
	
	//print "<br><br>".$sql."<br><br>";
		
	$rs = pg_query($db, $sql);
	if (!$rs){
		exit;
	}
?>
<H1>Informe Por Sector</H1>
<!--	<a class="tecla" href="#" onclick="MM_openBrWindow('listadoEstConcLiqPrint.php?FechaPeriodo=<?=$FechaPeriodo?>&NumeroLiquidacion=<?=$NumeroLiquidacion?>&Jur=<?=$Jur?>&TP=<?=$TP?>&TR=<?=$TipoRelacion?>','printpreview','width=872,height=750')"> 
	<img src="images/icon24_printlistado.gif" alt="Imprimir Listado" width="24" height="24" border="0" align="absmiddle">
	Imprimir Listado</a>&nbsp;<BR><br>-->
<?
	$TotalR1 = 0; $TotalR2 = 0; $TotalR3 = 0; $TotalR4 = 0; $TotalR5 = 0; $TotalR6 = 0; $TotalR7 = 0;
	$TotalR8 = 0; $TotalR9 = 0; $TotalR10 = 0; $TotalR11 = 0; $TotalR12 = 0; $TotalR13 = 0; $TotalR14 = 0;
	$TotalR15 = 0; $TotalR16 = 0; $TotalR17 = 0; $TotalR18 = 0; $TotalR19 = 0;
	$Jurisdiccion = '';
	$Actividad = '';
	$Programa = '';
	$AntJur = '';
	$AntAct = '';
	$AntPro = '';
	$Abrir = 1;
	print "<br><b>Per&iacute;odo: " . Mes(substr($FechaPeriodo, 5, 2)) . " de " . substr($FechaPeriodo, 0, 4);
	print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Numero de Liquidaci&oacute;n: $NumeroLiquidacion</b><br><br>";
	while($row = pg_fetch_array($rs))
	{
		$i=1;
		if ($Jur == '1')
			$Jurisdiccion = $row[1];
		if ($Act == '1')
			$Actividad = $row[2];
		if ($Pro == '1')
			$Programa = $row[3];
		if ($Jurisdiccion != '' && $AntJur != $Jurisdiccion){
			if ($AntJur != '')
				$Cerrar = 1;
		}
		if ($Programa != '' && $AntPro != $Programa){
			if ($AntPro != '')
				$Cerrar = 1;
		}
		if ($Actividad != '' && $AntAct != $Actividad){
			if ($AntAct != '')
				$Cerrar = 1;
		}
		if ($Cerrar == 1){
			$Cerrar = 0;
?>
			<tr><td></th><td><b>Totales</b></td><td><b><?=$TotalR1?></b></td><td><b><?=$TotalR2?></b></td><td><b><?=$TotalR3?></b></td><td><b><?=$TotalR4?></b></td><td><b><?=$TotalR5?></b></td><td><b><?=$TotalR16?></b></td><td><b><?=$TotalR6?></b></td><td><b><?=$TotalR7?></b></td><td><b><?=$TotalR17?></b></td><td><b><?=$TotalR8?></b></td><td><b><?=$TotalR9?></b></td><td><b><?=$TotalR10?></b></td><td><b><?=$TotalR11?></b></td><td><b><?=$TotalR12?></b></td><td><b><?=$TotalR13?></b></td><td><b><?=$TotalR15?></b></td><td><b><?=$TotalR14?></b></td><td><b><?=$TotalR18?></b></td></tr>
<?
			$TotalR1 = 0; $TotalR2 = 0; $TotalR3 = 0; $TotalR4 = 0;	$TotalR5 = 0; $TotalR6 = 0; $TotalR7 = 0;
			$TotalR8 = 0; $TotalR9 = 0;	$TotalR10 = 0; $TotalR11 = 0; $TotalR12 = 0; $TotalR13 = 0;	$TotalR14 = 0;
			$TotalR15 = 0; $TotalR16 = 0; $TotalR17 = 0; $TotalR18 = 0;
			print "</table><br>\n";
		}
		if ($Jurisdiccion != '' && $AntJur != $Jurisdiccion){
			print "<b>Jurisdicci&oacute;n: " . Jurisdiccion($db, $Jurisdiccion) . "</b><br><br>";
			$AntJur = $Jurisdiccion;
			$AntPro = '';
			$AntAct = '';
			$Abrir = 1;
		}
		if ($Programa != '' && $AntPro != $Programa){
			print "<b>Programa: " . $Programa . "</b><br><br>";
			$AntPro = $Programa;
			$AntAct = '';
			$Abrir = 1;
		}
		if ($Actividad != '' && $AntAct != $Actividad){
			print "<b>Actividad: " . $Actividad . "</b><br><br>";
			$AntAct = $Actividad;
			$Abrir = 1;
		}
		if ($Abrir == 1){
			$Abrir = 0;
?>
			<table width="100%" border="0" cellpadding="5" cellspacing="1" class="datagrid">
			<tr><th>Leg</th><th>Apellido y Nombre</th><th>Basico</th><th>Rep<br />Ant</th><th>Vac</th><th>Sac<br />Ref</th><th>Fer<br />Noc</th><th>Otr<br />os</th><th>HAB<br />APO</th><th>Bon</th><th>Otr<br />os</th><th>Sal<br />Fam</th><th>Ayu<br />Esc</th><th>Pres</th><th>Hs.<br />Ext</th><th>HAB<br />s/AP</th><th>Ret</th><th>Otr<br />Ret</th><th>Neto</th><th>Cobrado</th><th>Apor</th><th>Total <br /> Costo</th></tr>
<?
		}
		$R1 = FormatearImporte($row['Basico']);
		$R2 = FormatearImporte($row['Antiguedad']);
		$R3 = FormatearImporte($row['Vacaciones']);
		$R4 = FormatearImporte($row['SACRefrigerio']);
		$R5 = FormatearImporte($row['FeriadoHSNocturnas']);
		$R6 = FormatearImporte($row['RemunAportes']);
		$R7 = FormatearImporte($row['Bonificaciones']);
		$R8 = FormatearImporte($row['SalarioFamiliar']);
		$R9 = FormatearImporte($row['AyudaEscolar']);
		$R10 = FormatearImporte($row['Presentismo']);
		$R11 = FormatearImporte($row['HsExtras']);
		$R12 = FormatearImporte($row['RemunSinAportes']);
		$R13 = FormatearImporte($row['Retenciones']);
		$R19 = FormatearImporte($row['OtrasRetenciones']);
		$R14 = FormatearImporte($row['Aportes']);
		//$R15 = FormatearImporte($row['Cobrado']);
		$R15 = FormatearImporte($R6 +  $R12 - $R13);
		$R20 = FormatearImporte($R15 - $R19);
		$R16 = FormatearImporte($row['OtrosRemunAportes']);
		$R17 = FormatearImporte($row['OtrosRemunSinAportes']);
		//$R18 = FormatearImporte($R6+$R12);
		$R18 = FormatearImporte($R6+$R12+$R14);
		if ($R1 == '') $R1 = 0;
		if ($R2 == '') $R2 = 0;
		if ($R3 == '') $R3 = 0;
		if ($R4 == '') $R4 = 0;
		if ($R5 == '') $R5 = 0;
		if ($R6 == '') $R6 = 0;
		if ($R7 == '') $R7 = 0;
		if ($R8 == '') $R8 = 0;
		if ($R9 == '') $R9 = 0;
		if ($R10 == '') $R10 = 0;
		if ($R11 == '') $R11 = 0;
		if ($R12 == '') $R12 = 0;
		if ($R13 == '') $R13 = 0;
		if ($R14 == '') $R14 = 0;
		if ($R15 == '') $R15 = 0;
		if ($R16 == '') $R16 = 0;
		if ($R17 == '') $R17 = 0;
		if ($R18 == '') $R18 = 0;
		if ($R19 == '') $R19 = 0;
		if ($R20 == '') $R20 = 0;

		
		$TotalR1 += $R1; $TotalR2 += $R2; $TotalR3 += $R3; $TotalR4 += $R4; $TotalR5 += $R5; $TotalR6 += $R6;
		$TotalR7 += $R7; $TotalR8 += $R8; $TotalR9 += $R9; $TotalR10 += $R10; $TotalR11 += $R11; $TotalR12 += $R12;
		$TotalR13 += $R13; $TotalR14 += $R14; $TotalR15 += $R15; $TotalR16 += $R16; $TotalR17 += $R17; $TotalR18 += $R18;
		$TotalR19 += $R19; $TotalR20 += $R20;
		$ApeyNom = $row['Apellido'] . ' ' . $row['Nombre'];
?>
		<tr><td><?=$row['Legajo']?></td><td><?=$ApeyNom?></td><td><?=$R1?></td><td><?=$R2?></td><td><?=$R3?></td>
<td><?=$R4?></td><td><?=$R5?></td><td><?=$R16?></td><td><?=$R6?></td><td><?=$R7?></td><td><?=$R17?></td>
<td><?=$R8?></td><td><?=$R9?></td><td><?=$R10?></td><td><?=$R11?></td><td><?=$R12?></td><td><?=$R13?></td>
<td><?=$R19?></td><td><?=$R15?></td><td><?=$R20?></td><td><?=$R14?></td><td><?=$R18?></td></tr>
<?
	}
?>
			<tr><td></th><td><b>Totales</b></td><td><b><?=$TotalR1?></b></td><td><b><?=$TotalR2?></b></td><td><b><?=$TotalR3?></b></td><td><b><?=$TotalR4?></b></td><td><b><?=$TotalR5?></b></td><td><b><?=$TotalR16?></b></td><td><b><?=$TotalR6?></b></td><td><b><?=$TotalR7?></b></td><td><b><?=$TotalR17?></b></td><td><b><?=$TotalR8?></b></td><td><b><?=$TotalR9?></b></td><td><b><?=$TotalR10?></b></td><td><b><?=$TotalR11?></b></td><td><b><?=$TotalR12?></b></td><td><b><?=$TotalR13?></b></td>
			<td><b><?=$TotalR19?></b></td>
			<td><b><?=$TotalR15?></b></td>
			<td><b><?=$TotalR20?></b></td>
			<td><b><?=$TotalR14?></b></td><td><b><?=$TotalR18?></b></td></tr>
	</table>
<?
}

if ($accion == ''){
	?><H1>Informe Por Sector</H1><?
	include 'selLiquida.php'; ?>
	<TR>
		<TD class="izquierdo">Desglosar por Jurisdicci&oacute;n:</TD><TD class="derecho"><input type=checkbox id=chkJurisdiccion name=chkJurisdiccion value=1></TD>
	</TR>
	<TR>
		<TD class="izquierdo">Desglosar por Programa:</TD><TD class="derecho"><input type=checkbox id=chkPrograma name=chkPrograma value=1></TD>
	</TR>
	<TR>
		<TD class="izquierdo">Desglosar por Actividad-Proyecto:</TD><TD class="derecho"><input type=checkbox id=chkActividad name=chkActividad value=1></TD>
	</TR>
	<TR>
		<TD class="izquierdo">Filtrar Jurisdicci&oacute;n:</TD><TD class="derecho">
			<SELECT name=filJurisdiccion>
			<option value=0>Todas las jurisdicciones</option>
<?
	$rs = pg_query($db, "select jurisdiccion, denominacion from owner_rafam.jurisdicciones where seleccionable = 'S'");
	while($row = pg_fetch_array($rs)) {
		print "<OPTION VALUE=\"".$row[0]."\">".$row[1]."</OPTION>\n";
	}
?>
			</SELECT>
		</TD>
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
