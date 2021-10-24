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
		$dAno = LimpiarNumero(substr($selPeriodo, 0, $i));
		$dMes = LimpiarNumero(substr($selPeriodo, $i+1));
	}
	if ($dAno == '' || $dMes == ''){
		exit;
	}
	if (strlen($dMes) < 2)
		$dMes = "0$dMes";
	$FechaPeriodo = "$dAno-$dMes-01";
	if ($_SESSION["LegajoNumerico"] == '1'){
		$sqlLegajo = "to_number(re.\"Legajo\", '999999') AS \"Legajo\"";
	}else{
		$sqlLegajo = "re.\"Legajo\"";
	}

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

	$Jur = LimpiarNumero($_POST["chkJurisdiccion"]);
	$Act = LimpiarNumero($_POST["chkActividad"]);
	$Pro = LimpiarNumero($_POST["chkPrograma"]);
	$Orden = 'ORDER BY ';
	$filJurisdiccion = LimpiarNumero($_POST["filJurisdiccion"]);
	$Where = '';
	//if ($filJurisdiccion != '0') {
	//	$Where = " and er.jurisdiccion = '$filJurisdiccion' ";
	//}
	if ($Jur == '1')
		$Orden .= '2, ';
	if ($Pro == '1')
		$Orden .= '4, ';
	if ($Act == '1')
		$Orden .= '3, ';
	$Orden = substr($Orden, 0, -2);
	if ($Orden == 'ORDER B')
		$Orden = '';

	$sql = "select \"AliasID\", \"Descripcion\" from \"tblConceptosAlias\" where \"ConceptoID\" <= 99 ORDER BY \"ConceptoID\", \"Descripcion\"";

	$rs = pg_query($db, $sql);
        if (!$rs){
                exit;
        }

//Dependencia |    Grupo    | Planta | Categoria | Jornada | Sueldo
	$sql = "
select distinct $sqlLegajo, er.jurisdiccion, er.activ_proy, er.programa, em.\"Apellido\", em.\"Nombre\", c.\"Dependencia\", c.\"Grupo\", c.\"Planta\", c.\"Jornada\", c.\"Categoria\"";

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

left join \"tblCategoriasEmpleado\" c
on em.\"Legajo\" = c.\"Legajo\"

left join \"tblEmpleadosRafam\" er
on er.\"EmpresaID\" = re.\"EmpresaID\" AND er.\"SucursalID\" = re.\"SucursalID\" and er.\"Legajo\" = re.\"Legajo\" $Where

where re.\"EmpresaID\" = $EmpresaID AND re.\"SucursalID\" = $SucursalID AND 
re.\"Fecha\" = '$FechaPeriodo' AND 
em.\"TipoRelacion\" in ($TipoRelacion)
$Orden 
";
	
	//print "<br><br>".$sql."<br><br>";
		
	$rs = pg_query($db, $sql);
	if (!$rs){
		print pg_last_error();
		exit;
	}
?>
<H1>Informe Presupuesto</H1>
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
			<tr><td></th><td><b>Totales</b></td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
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
			<tr><th>Leg</th><th>Apellido y Nombre</th><th>Dependencia</th><th>Grupo</th><th>Planta</th><th>Categoria</th><th>Jornada</th>
			<? for ($i=0; $i<$countConcepto; $i++) print "<th>".$Conceptos[$i]."</th>"; ?></tr>
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
		<tr><td><?=$row['Legajo']?></td><td><?=$ApeyNom?></td><td><?=$row['Dependencia']?></td><td><?=$row['Grupo']?></td><td><?=$row['Planta']?></td><td><?=$row['Categoria']?></td><td><?=$row['Jornada']?></td>
		<? for ($i=0; $i<$countConcepto; $i++) print "<td>".$row["R$i"]."</td>"; ?></tr>
<?
	}
?>
			<tr><td></th><td><b>Totales</b></td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
			<? for ($i=0; $i<$countConcepto; $i++) print "<td><b>".$TotalR[$i]."</b></td>"; ?></tr>
	</table>
<?
}

if ($accion == ''){
	$rs = pg_query($db, "
SELECT DISTINCT extract('year' from \"FechaPeriodo\"), extract('month' from \"FechaPeriodo\")
FROM \"tblPeriodos\"
ORDER BY 1 DESC, 2 DESC
	");
	if (!$rs){
		exit;
	}
?>
	<H1>Listado de Personal Liquidado</H1>
	<table class="datauser" align="left">
	<TR>
		<TD class="izquierdo">Seleccione Per&iacute;odo:</TD><TD class="derecho2"><select id=selPeriodo name=selPeriodo>
<?
	while($row = pg_fetch_array($rs)){
		$dAno = $row[0];
		$dMes = Mes($row[1]);
		print "<option value=\"$row[0]|$row[1]\">$dMes DE $dAno</option>\n";
	}
?>
	</select></TD></TR>
	<TR>
		<TD class="izquierdo">Tipo De Relaci&oacute;n:</TD><TD class="derecho2">
		<input type=checkbox id=chkTipoRelM name=chkTipoRelM value=1 checked>Mensualizados
		<input type=checkbox id=chkTipoRelJ name=chkTipoRelJ value=1 checked>Jornalizados
		<input type=checkbox id=chkTipoRelC name=chkTipoRelC value=1 checked>Contratados
		<input type=checkbox id=chkTipoRelL name=chkTipoRelL value=1 checked>Loc. de obra
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
