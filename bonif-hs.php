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
<form name=frmListadoEstConcLiq action=bonif-hs.php method=post>
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
select distinct $sqlLegajo, er.jurisdiccion, er.activ_proy, er.programa, em.\"Apellido\", em.\"Nombre\",

(select sum(case when \"Haber2\" is null then 0 else \"Haber2\" end) from \"tblRecibos\" re1 
where re1.\"EmpresaID\" = re.\"EmpresaID\" and re1.\"SucursalID\" = re.\"SucursalID\" and re1.\"Fecha\" = re.\"Fecha\"
and re1.\"NumeroLiquidacion\" = re.\"NumeroLiquidacion\" and re1.\"Legajo\" = re.\"Legajo\" 
and re1.\"ConceptoID\" in (25,139)) as \"Bonif\",

(select sum(\"Cantidad\") from \"tblRecibos\" re1 
where re1.\"EmpresaID\" = re.\"EmpresaID\" and re1.\"SucursalID\" = re.\"SucursalID\" and re1.\"Fecha\" = re.\"Fecha\"
and re1.\"NumeroLiquidacion\" = re.\"NumeroLiquidacion\" and re1.\"Legajo\" = re.\"Legajo\" 
and re1.\"ConceptoID\" in (7,8,122,123,139)) as \"Extras1\",

(select sum(\"Haber2\") from \"tblRecibos\" re1 
where re1.\"EmpresaID\" = re.\"EmpresaID\" and re1.\"SucursalID\" = re.\"SucursalID\" and re1.\"Fecha\" = re.\"Fecha\"
and re1.\"NumeroLiquidacion\" = re.\"NumeroLiquidacion\" and re1.\"Legajo\" = re.\"Legajo\" 
and re1.\"AliasID\" in (7,8,122,123,139)) as \"Extras2\"
		
from \"tblRecibos\" re 

inner join \"tblEmpleados\" em
on em.\"EmpresaID\" = re.\"EmpresaID\" AND em.\"SucursalID\" = re.\"SucursalID\" and em.\"Legajo\" = re.\"Legajo\"

inner join \"tblEmpleadosRafam\" er
on er.\"EmpresaID\" = re.\"EmpresaID\" AND er.\"SucursalID\" = re.\"SucursalID\" and er.\"Legajo\" = re.\"Legajo\" $Where

where re.\"EmpresaID\" = $EmpresaID AND re.\"SucursalID\" = $SucursalID AND 
re.\"Fecha\" = '$FechaPeriodo' AND re.\"NumeroLiquidacion\" = $NumeroLiquidacion 
$Orden
";
	
	print "<br><br>".$sql."<br><br>";
		
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
	$TotalR1 = 0; $TotalR2 = 0; $TotalR3 = 0; 
	
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
			<tr><th>Legajo</th><th>Apellido y Nombre</th><th>Bonif</th><th>Cant Hs. Ext.</th><th>Monto Hs. Ext.</th></tr>
<?
		}
		//$R1 = FormatearImporte($row['Bonificacion']);
		//$R2 = FormatearImporte($row['CantHsExtras']);
		//$R3 = FormatearImporte($row['MontoHsExtras']);

		$R1 = FormatearImporte($row[6]);
		$R2 = FormatearImporte($row[7]);
		$R3 = FormatearImporte($row[8]);

		if ($R1 == '') $R1 = 0;
		if ($R2 == '') $R2 = 0;
		if ($R3 == '') $R3 = 0;

		
		$TotalR1 += $R1; $TotalR2 += $R2; $TotalR3 += $R3;
		
		$ApeyNom = $row['Apellido'] . ' ' . $row['Nombre'];
?>
		<tr><td><?=$row['Legajo']?></td><td><?=$ApeyNom?></td><td><?=$R1?></td><td><?=$R2?></td><td><?=$R3?></td>

<?
	}
?>
			<tr><td></th><td><b>Totales</b></td><td><b><?=$TotalR1?></b></td><td><b><?=$TotalR2?></b></td><td><b><?=$TotalR3?></b></td></tr>
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
