<?
ob_start();

include ('header.php');

if (!($db = Conectar()))
	exit;

$EmpresaID = $_SESSION["EmpresaID"];
$SucursalID = $_SESSION["SucursalID"];

$accion = LimpiarVariable($_POST["accion"]);
?>

<form name=frmListado action=listadoSIPA.php method=post>
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
?>
	<H1>Listado SIPA</H1>
<div id=divLoading style="display:block">
<table height=100% align=center valign=center>
<tr><td><img src="images/icon32_process.gif" align=absmiddle width=32 height=32 border=0> Generando Listado</td></tr>
</table>
</div>
<?

$sql = '
select 

me."Legajo",
me."CUIT",
me."Apellido",
me."Nombre",
me."TipoDocumento",
me."NumeroDocumento",
me."FechaIngreso",
round(MAX(case when r."ConceptoID"=99 then 
	case when r."Haber1" is null then 0 else r."Haber1" end + case when r."Haber2" is null then 0 else r."Haber2" end 
    else 0 end
)) as "Sueldo"

	from
	(
	select

	e."Legajo",
	REPLACE(ed."CUIT", \'-\', \'\') AS "CUIT",
	e."Apellido",
	e."Nombre",
	ed."TipoDocumento",
	ed."NumeroDocumento",
	ed."FechaIngreso"

	from "tblEmpleados" e
	left join "tblEmpleadosDatos" ed on e."Legajo" = ed."Legajo"

	where e."FechaEgreso" is null or e."FechaEgreso" > \''."$dAno-$dMes-01".'\'

	group by 
	e."Legajo",
	ed."CUIT",
	e."Apellido",
	e."Nombre",
	ed."TipoDocumento",
	ed."NumeroDocumento",
	ed."FechaIngreso"
				
	) me

left join "tblRecibos" r on me."Legajo" = r."Legajo" and r."Fecha"=\''."$dAno-$dMes-01".'\'
inner join "tblPeriodos" p on p."FechaPeriodo" = \''."$dAno-$dMes-01".'\' and p."TipoLiquidacionID" in (1,5,7) and r."NumeroLiquidacion" = p."NumeroLiquidacion"

GROUP BY me."Legajo",
me."CUIT",
me."Apellido",
me."Nombre",
me."TipoDocumento",
me."NumeroDocumento",
me."FechaIngreso"
			
having round(MAX(case when r."ConceptoID"=99 then 
	case when r."Haber1" is null then 0 else r."Haber1" end + case when r."Haber2" is null then 0 else r."Haber2" end 
    else 0 end
)) > 0

';

ob_end_clean();

header("Content-Type: application/x-unknown; charset=ISO-8859-1\r\n");
header("Content-Disposition: attachment; filename=\"sipa.txt\"\r\n");



$rs = pg_query($db, $sql);

	if (!$rs){
		exit;
	}
	while($row = pg_fetch_array($rs))
	{
		$linea = "";
		$linea.= str_pad(substr(utf8_decode($row['Apellido']), 0, 30), 30, ' ', STR_PAD_RIGHT);
		$linea.= str_pad(substr(utf8_decode($row['Nombre']), 0, 40), 40, ' ', STR_PAD_RIGHT);
		$linea.= str_pad($row['CUIT'], 11, ' ', STR_PAD_RIGHT);
		if ($row["TipoDocumento"] == '4')
			$row["TipoDocumento"] = '2';
		elseif ($row["TipoDocumento"] == '5')
			$row["TipoDocumento"] = '3';
		elseif ($row["TipoDocumento"] == '3')
			$row["TipoDocumento"] = '4';
		elseif ($row["TipoDocumento"] == '2')
			$row["TipoDocumento"] = '5';
		$linea.= str_pad($row['TipoDocumento'], 2, '0', STR_PAD_LEFT);
		$linea.= str_pad($row['NumeroDocumento'], 11, '0', STR_PAD_LEFT);
		$linea.= str_pad($row['Sueldo'], 8, '0', STR_PAD_LEFT);	// remuneracion	
		$linea.= substr($row['FechaIngreso'], 8, 2).substr($row['FechaIngreso'], 5, 2).substr($row['FechaIngreso'], 0, 4);	
		$linea.= '01'; // codigo situacion

		print $linea."\r\n";
	}
	exit;
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
	<H1>Listado de Personal para SIPA</H1>
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
		<input type=checkbox id=chkTipoRelL name=chkTipoRelL value=1 checked>Loc. de obra
		</TD>
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
