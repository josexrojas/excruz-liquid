<?
include("header.php");

if (!($db = Conectar()))
	exit;

$EmpresaID = $_SESSION["EmpresaID"];
$SucursalID = $_SESSION["SucursalID"];

$accion = LimpiarVariable($_POST["accion"]);
?>

<div id=divLoading style="display:block">
<table height=100% align=center valign=center>
<tr><td><img src="images/icon32_process.gif" align=absmiddle width=32 height=32 border=0> Generando Listado</td></tr>
</table>
</div>
<form name=frmListadoVacaciones action=listadoVacaciones.php method=post>
<input type=hidden name=accion id=accion>
<input type=hidden name=listado id=listado>

<?
if ($accion == 'Ver Listado'){
	$Fecha = FechaWEB2SQL(LimpiarNumero($_POST["FechaCalc"]));
	$Jur = LimpiarNumero($_POST["chkJurisdiccion"]);
	$TP = LimpiarNumero($_POST["chkTipoPlanta"]);
	if ($Fecha == '')
		exit;
	$rs = pg_query($db, "
SELECT em.\"Legajo\", em.\"Apellido\", em.\"Nombre\", 
	\"AntiguedadEmpleado2\"(em.\"EmpresaID\", em.\"SucursalID\", em.\"Legajo\", '$Fecha')
	".($Jur=='1'?",er.jurisdiccion":"").($TP=='1'?",er.\"TipoDePlanta\"":"")."
FROM \"tblEmpleados\" em
INNER JOIN \"tblEmpleadosRafam\" er
ON er.\"EmpresaID\" = em.\"EmpresaID\" AND er.\"SucursalID\" = em.\"SucursalID\" AND er.\"Legajo\" = em.\"Legajo\"
WHERE em.\"EmpresaID\" = $EmpresaID AND em.\"SucursalID\" = $SucursalID AND em.\"FechaEgreso\" IS NULL
ORDER BY ".($Jur=='1'?"5,":"").($Jur=='1'&&$TP=='1'?"6,":"").($Jur!='1'&&$TP=='1'?"5,":"")." 2
");
	if (!$rs)
		exit;
?>
<H1>Listado de Vacaciones</H1>
	<a class="tecla" href='javascript:window.print(); void(0);'> 
	<img src="images/icon24_printlistado.gif" alt="Imprimir Listado" width="24" height="24" border="0" align="absmiddle">
	Imprimir Listado</a>&nbsp;<BR><br>
<?
	$Jurisdiccion = '';
	$AntJur = '';
	$TipoPlanta = '';
	$AntTP = '';
	$Abrir = 1;
	while($row = pg_fetch_array($rs))
	{
		$i = 0;
		$Legajo = $row[$i++];
		$Apellido = $row[$i++];
		$Nombre = $row[$i++];
		$Antiguedad = $row[$i++];
		$Ano = intval($Antiguedad);
		$Mes = 0;
		$Dia = 0;
		$Ant = '';
		$Antiguedad -= $Ano;
		if ($Antiguedad > 0){
			$Mes = intval($Antiguedad * 12);
			$Antiguedad -= ($Mes / 12);
			if ($Antiguedad > 0){
				$Antiguedad *= 365;
				$Dia = intval($Antiguedad);
			}
		}
		if ($Ano > 0){
			if (($Ano > 20) || ($Ano == 20 && ($Mes > 0 || $Dia > 0)))
				$DiasVac = 35;
			else if (($Ano > 10) || ($Ano == 10 && ($Mes > 0 || $Dia > 0)))
				$DiasVac = 28;
			else if (($Ano > 5) || ($Ano == 5 && ($Mes > 0 || $Dia > 0)))
				$DiasVac = 21;
			else
				$DiasVac = 14;				
			$Ant .= "$Ano a&ntilde;os";
		}else{
			if ($Mes > 5)
				$DiasVac = 14;
			else {
				// 1 dia cada 20
				$TotD = $Mes *30 + $Dia;
				$TotD /= 20;
				$DiasVac = intval($TotD);
			}
		}
		if ($Mes > 0)
			$Ant .= " $Mes meses";
		if ($Dia > 0)
			$Ant .= " $Dia d&iacute;as";
		$ApeyNom = $Apellido . ' ' . $Nombre;
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
			print "</table><br>\n";
		}
		if ($Jurisdiccion != '' && $AntJur != $Jurisdiccion){
			print "<b>Jurisdicci&oacute;n: " . Jurisdiccion($db, $Jurisdiccion) . "</b><br><br>";
			$AntJur = $Jurisdiccion;
			$AntTP = '0';
			$Abrir = 1;
		}
		if ($TipoPlanta != '' && $AntTP != $TipoPlanta){
			print "<b>Tipo De Planta: ". ($TipoPlanta == '1' ? 'Permanente':'Temporal') ."</b><br><br>";
			$AntTP = $TipoPlanta;
			$Abrir = 1;
		}
		if ($Abrir == 1){
			$Abrir = 0;
?>
			<table width="100%" border="0" cellpadding="5" cellspacing="1" class="datagrid">
			<tr><th>Legajo</th><th>Apellido y Nombre</th><th>Antig&uuml;edad (en a&ntilde;os)</th><th>D&iacute;as de Vacaciones</th></tr>
<?
		}
?>
		<tr><td><?=$Legajo?></td><td><?=$ApeyNom?></td><td><?=$Ant?></td><td><?=$DiasVac?></td></tr>
<?
	}
	print "</table>\n";
}

if ($accion == ''){
	$FecCalc = date("d-m-Y");
?>
	<table class="datauser" align="left">
	<TR>
		<TD class="izquierdo">Fecha a Calcular</TD><TD class="derecho">
		<input type=text name=FechaCalc id=FechaCalc onfocus="showCalendarControl(this);" readonly size=11 value="<?=$FecCalc?>"></TD>
	</TR>
	<TR>
		<TD class="izquierdo">Desglosar por Tipo De Planta</TD><TD class="derecho"><input type=checkbox id=chkTipoPlanta name=chkTipoPlanta value=1></TD>
	</TR>
	<TR>
		<TD class="izquierdo">Desglosar por Jurisdicci&oacute;n</TD><TD class="derecho"><input type=checkbox id=chkJurisdiccion name=chkJurisdiccion value=1></TD>
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
