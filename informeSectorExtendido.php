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
<form name=frmListadoEstConcLiq method=post>
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

	$sql = "select \"AliasID\", \"Descripcion\" from \"tblConceptosAlias\" ORDER BY \"Descripcion\"";

	$rs = pg_query($db, $sql);
        if (!$rs){
                exit;
        }

$sql = "
  select distinct $sqlLegajo, er.jurisdiccion, er.activ_proy, er.programa, em.\"Apellido\", em.\"Nombre\", ce.\"Categoria\", ce.\"Jornada\",ce.\"Planta\"";

	$TotalR = array();
	$Conceptos = array();
	$countConcepto = 0;
	while(($row = pg_fetch_array($rs)))
	{
		$sql.= ", (select case when sum(\"Haber1\") is null then 0 else  sum(\"Haber1\") end + case when sum(\"Haber2\") is null then 0 else sum(\"Haber2\") end + case when sum(\"Descuento\") is null then 0 else sum(\"Descuento\") end + case when sum(\"Aporte\") is null then 0 else sum(\"Aporte\") end from \"tblRecibos\" re1";
		$sql.= " where re1.\"EmpresaID\" = re.\"EmpresaID\" and re1.\"SucursalID\" = re.\"SucursalID\" and re1.\"Fecha\" = re.\"Fecha\"";
		$sql.= " and re1.\"NumeroLiquidacion\" = re.\"NumeroLiquidacion\" and re1.\"Legajo\" = re.\"Legajo\"";
		$sql.= " and re1.\"AliasID\" = ".$row['AliasID'].") AS \"R".$countConcepto."\" ";
		$Conceptos[] = $row['Descripcion'];
		$countConcepto++;
	}


	$sql.= "
from \"tblRecibos\" re

inner join \"tblEmpleados\" em
on em.\"EmpresaID\" = re.\"EmpresaID\" AND em.\"SucursalID\" = re.\"SucursalID\" and em.\"Legajo\" = re.\"Legajo\"

inner join \"tblEmpleadosRafam\" er
on er.\"EmpresaID\" = re.\"EmpresaID\" AND er.\"SucursalID\" = re.\"SucursalID\" and er.\"Legajo\" = re.\"Legajo\" $Where

left join  \"tblCategoriasEmpleado\" ce
on ce.\"Legajo\" = re.\"Legajo\"

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

	for ($i=0; $i<$countConcepto; $i++)
		$TotalR[$countConcepto] = 0;
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
			<tr><td></th><td><b>Totales</b></td>
			<? for ($i=0; $i<$countConcepto; $i++) print "<td><b>".$TotalR[$i]."</b></td>"; ?></tr>
<?
			for ($i=0; $i<$countConcepto; $i++)
				$TotalR[$countConcepto] = 0;
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
			<tr><th>Leg</th><th>Apellido y Nombre</th><th>Categoria</th><th>Jornada</th><th>Planta</th>
			<? for ($i=0; $i<$countConcepto; $i++) print "<td>".$Conceptos[$i]."</td>"; ?></tr>
<?
		}

		for ($i=0; $i<$countConcepto; $i++)
		{
			$row["R$i"] = FormatearImporte($row["R$i"]);
			if ($row["R$i"] == '') $row["R$i"] = -1;
			$TotalR[$i] += $row["R$i"];
		}

		$ApeyNom = $row['Apellido'] . ' ' . $row['Nombre'];
?>
		<tr><td><?=$row['Legajo']?></td><td><?=$ApeyNom?></td><td><?=$row['Categoria']?></td><td><?=$row['Jornada']?></td><td><?=$row['Planta']?></td>
		<? for ($i=0; $i<$countConcepto; $i++) print "<td>".$row["R$i"]."</td>"; ?></tr>
<?
	}
?>
			<tr><td></th><td></td><td></td><td></td><td><b>Totales</b></td>
			<? for ($i=0; $i<$countConcepto; $i++) print "<td><b>".$TotalR[$i]."</b></td>"; ?></tr>
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
