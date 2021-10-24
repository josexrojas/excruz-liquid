<?
include ('header.php');

if (!($db = Conectar()))
	exit;

$EmpresaID = $_SESSION["EmpresaID"];
$SucursalID = $_SESSION["SucursalID"];

$accion = LimpiarVariable($_POST["accion"]);
?>

<form name=frmListadoPersonalLiq method=post>
<input type=hidden name=accion id=accion>
<input type=hidden name=listado id=listado>
<script type="text/JavaScript">
<!--
function MM_openBrWindow(theURL,winName,features) { //v2.0
  window.open(theURL,winName,features);
}
//-->
</script>

<?
if ($accion == 'Generar Informe'){
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
	$Jur = LimpiarNumero($_POST["chkJurisdiccion"]);
	$TR = LimpiarNumero($_POST["chkTipoPlanta"]);
	$chkTipoRelM = (LimpiarNumero($_POST["chkTipoRelM"]) == '1' ? true : false);
	$chkTipoRelJ = (LimpiarNumero($_POST["chkTipoRelJ"]) == '1' ? true : false);
	$chkTipoRelC = (LimpiarNumero($_POST["chkTipoRelC"]) == '1' ? true : false);
	$TipoRelacion = '';
	if ($chkTipoRelM == true)
		$TipoRelacion .= '1,';
	if ($chkTipoRelJ == true)
		$TipoRelacion .= '2,';
	if ($chkTipoRelC == true)
		$TipoRelacion .= '3,';
	$TipoRelacion = substr($TipoRelacion, 0, -1);
?>
	<H1>Listado de Personal Liquidado + CUIL</H1>
<div id=divLoading style="display:block">
<table height=100% align=center valign=center>
<tr><td><img src="images/icon32_process.gif" align=absmiddle width=32 height=32 border=0> Generando Listado</td></tr>
</table>
</div>
<?
	$sql = "
SELECT DISTINCT re.\"Legajo\", em.\"Nombre\", em.\"Apellido\", ed.\"CUIT\", 
em.\"TipoRelacion\", er.\"Categoria\", er.\"Jornada\", er.\"Grupo\" AS \"Cargo\"
,ed.\"TipoDocumento\", ed.\"NumeroDocumento\",
CASE WHEN re.\"ConceptoID\" IN (99) THEN re.\"Haber1\" ELSE 0 END +
CASE WHEN re.\"ConceptoID\" IN (99) THEN re.\"Haber2\" ELSE 0 END -
CASE WHEN re.\"ConceptoID\" IN (99) THEN re.\"Descuento\" ELSE 0 END AS \"SueldoBruto\",
ed.\"FechaIngreso\", er2.jurisdiccion FROM \"tblRecibos\" re
INNER JOIN  \"tblEmpleados\" em
ON em.\"EmpresaID\" = re.\"EmpresaID\" AND em.\"SucursalID\" = re.\"SucursalID\" AND em.\"Legajo\" = re.\"Legajo\"".
($TipoRelacion != '' ? " AND em.\"TipoRelacion\" in ($TipoRelacion)" : '')." 
INNER JOIN \"tblEmpleadosDatos\" ed
ON ed.\"EmpresaID\" = re.\"EmpresaID\" AND ed.\"SucursalID\" = re.\"SucursalID\" AND ed.\"Legajo\" = re.\"Legajo\"
LEFT JOIN \"tblCategoriasEmpleado\" er
ON er.\"Legajo\" = re.\"Legajo\"

INNER JOIN \"tblEmpleadosRafam\" er2
ON er2.\"EmpresaID\" = em.\"EmpresaID\" AND em.\"SucursalID\" = er2.\"SucursalID\" AND em.\"Legajo\" = er2.\"Legajo\"

WHERE re.\"EmpresaID\" = $EmpresaID AND re.\"SucursalID\" = $SucursalID AND re.\"ConceptoID\" = 99 AND
re.\"Fecha\" >= '$dAno-$dMes-01' AND re.\"Fecha\" < '$dAno-$dMes-01'::timestamp + interval '1 month' 
AND re.\"NumeroLiquidacion\" IN (1,2,4)
ORDER BY 13,5,3";
	$rs = pg_query($db, $sql);
	if (!$rs){
		exit;
	}
	$Jurisdiccion = '';
	$AntJur = '';
	$TipoRel = '';
	$AntRel = '';
	$Abrir = 1;
	$CantEmp = 0;
	$CantGEmp = 0;
	 print "<b>Per&iacute;odo: " . Mes($dMes) . " de $dAno</b><br><br>";
?>
<?
	while($row = pg_fetch_array($rs))
	{
		$CantEmp++;
		$CantGEmp++;
		$Legajo = $row[0];
		$ApeYNom = trim($row[2] . ', ' . $row[1]);
		$CUIL = $row[3];
		$Cat = $row[5];
		$Horas = $row[6];
		$TipoDoc = $row[8];
		if ($TipoDoc == '2')
			$TipoDoc = 'C.I.';
		else if ($TipoDoc == '3')
			$TipoDoc = 'PAS';
		else if ($TipoDoc == '4')
			$TipoDoc = 'L.E.';
		else if ($TipoDoc == '5')
			$TipoDoc = 'L.C.';
		else
			$TipoDoc = 'D.N.I.';
		$NumDoc = $row[9];
		$Sueldo = (int)($row[10]);
		$FIngreso = $row[11];
		switch($row[4]){
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

		if ($Jur == '1')
			$Jurisdiccion = $row[12];
		$Car = $row[7];

		if ($Jurisdiccion != '' && $AntJur != $Jurisdiccion){
			if ($AntJur != '')
				$Cerrar = 1;
		}
		if ($TR == '1' && $TipoRel != '' && $AntRel != $TipoRel){
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
		if ($Jurisdiccion != '' && $AntJur != $Jurisdiccion){
			print "<b>Jurisdicci&oacute;n: " . Jurisdiccion($db, $Jurisdiccion) . "</b><br><br>";
			$AntJur = $Jurisdiccion;
			$AntTP = '0';
			$Abrir = 1;
		}
		if ($TR == '1' && $TipoRel != '' && $AntRel != $TipoRel){
			print "<b>Tipo De Relaci&oacute;n: $TipoRel</b><br><br>";
			$AntRel = $TipoRel;
			$Abrir = 1;
		}
		if ($Abrir == 1){
			$Abrir = 0;
?>
			<table width="100%" border="0" cellpadding="5" cellspacing="1" class="datagrid">
			<tr><th>Legajo</th><th>Apellido y Nombre</th><th>CUIL</th><th>Documento</th><th>Categoria</th><th>Cargo</th><th>Horas Diarias</th><th>Planta</th><th>Sueldo Neto</th><th>F. Ingreso</th></tr>
<?
		}
?>
		<tr><td><?=$Legajo?></td><td><?=$ApeYNom?></td><td><?=$CUIL?></td><td><?=$TipoDoc.' '.$NumDoc?></td><td><?=$Cat?></td><td><?=$Car?></td><td><?=$Horas?></td><td><?=$TipoRel?></td><td><?=$Sueldo?></td><td><?=substr($FIngreso, 8, 2)."-".substr($FIngreso, 5, 2)."-".substr($FIngreso, 0, 4)?></td></tr>
<?
	}
	print "</table><br>\n";
	print "<b>Cantidad de empleados activos: $CantEmp</b><br>\n";
	print "<br><b>Cantidad Total de empleado activos: $CantGEmp</b><br>";
?>
<script>
	document.getElementById('divLoading').style.display = 'none';
</script>
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
	<H1>Listado de Personal Liquidado + CUIL</H1>
	<table class="datauser" align="left">
	<TR>
		<TD class="izquierdo">Seleccione Per&iacute;odo:</TD><TD class="derecho2"><select id=selPeriodo name=selPeriodo>
<?
	while($row = pg_fetch_array($rs)){
		$dAno = $row[0];
		$dMes = Mes($row[1]);
		print "<option value=$row[0]|$row[1]>$dMes DE $dAno</option>\n";
	}
?>
	</select></TD></TR>
	<TR>
		<TD class="izquierdo">Tipo De Relaci&oacute;n:</TD><TD class="derecho2">
		<input type=checkbox id=chkTipoRelM name=chkTipoRelM value=1 checked>Mensualizados
		<input type=checkbox id=chkTipoRelJ name=chkTipoRelJ value=1 checked>Jornalizados
		<input type=checkbox id=chkTipoRelC name=chkTipoRelC value=1 checked>Contratados
		</TD>
	</TR>
	<TR>
		<TD class="izquierdo">Desglosar por Tipo De Relaci&oacute;n:</TD><TD class="derecho"><input type=checkbox id=chkTipoPlanta name=chkTipoPlanta value=1></TD>
	</TR>
	<TR>
		<TD class="izquierdo">Desglosar por Jurisdicci&oacute;n:</TD><TD class="derecho"><input type=checkbox id=chkJurisdiccion name=chkJurisdiccion value=1></TD>
	</TR>
	<TR>
		<TD class="izquierdo"></TD><TD class="derecho">
		<input type=submit id=accion name=accion value="Generar Informe">
		<? Volver(); ?>
		</TD></TR></table>
<?
}
pg_close($db);
?>
</form>
<? include("footer.php"); ?>
