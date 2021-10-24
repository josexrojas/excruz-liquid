<?
ob_start();

include ('header.php');

if (!($db = Conectar()))
	exit;

$EmpresaID = $_SESSION["EmpresaID"];
$SucursalID = $_SESSION["SucursalID"];

$accion = LimpiarVariable($_POST["accion"]);
?>

<form name=frmListado action=listadoSICOSS.php method=post>
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
	<H1>Listado SICOSS (version 35)</H1>
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
me."ApellidoYNombre",
me."Conyuge",
me."Hijos",
round(SUM(case when r."ConceptoID"=99 then 
	case when r."Haber1" is null then 0 else r."Haber1" end 
    else 0 end
))-round(SUM(
    case when r."ConceptoID"=60 and r."Haber1" is not null then r."Haber1" else 0 end
))+round(SUM(CASE WHEN r."AliasID" IN (166, 185) THEN r."Haber2"::numeric ELSE 0 END)) as "Sueldo",
round(SUM(
    case when r."ConceptoID"=60 and r."Haber1" is not null then r."Haber1" else 0 end
)) as "SAC",
round(SUM(CASE WHEN r."ConceptoID" IN(20, 21) OR r."AliasID" IN (163, 164, 165) OR r."Descripcion" = \'AJ.HS. EX. POR CONSULTORIO EXTERNO EN +\' OR r."Descripcion" = \'AJ.HS. EX. POR GUARDIA EN +\' OR r."Descripcion" = \'AJ.HS. EX. POR TRASLADO EN +\'  THEN r."Haber2"::numeric ELSE 0 END)) as "HorasExtras",
round(SUM(CASE WHEN r."ConceptoID" IN(20, 21) OR r."AliasID" IN (163, 164, 165) OR r."Descripcion" = \'AJ.HS. EX. POR CONSULTORIO EXTERNO EN +\' OR r."Descripcion" = \'AJ.HS. EX. POR GUARDIA EN +\' OR r."Descripcion" = \'AJ.HS. EX. POR TRASLADO EN +\'  THEN r."Cantidad"::numeric ELSE 0 END)) as "CantHorasExtras"

	from
	(
	select

	e."Legajo",
	REPLACE(ed."CUIT", \'-\', \'\') AS "CUIT",
	e."Apellido" || \' \' || e."Nombre" as "ApellidoYNombre",
	MAX(case when ef."TipoDeVinculo" = 1 then 1 else 0 end) as "Conyuge",
	SUM(case when ef."TipoDeVinculo" = 2 then 1 else 0 end) as "Hijos"

	from "tblEmpleados" e
	left join "tblEmpleadosDatos" ed on e."Legajo" = ed."Legajo"
	left join "tblEmpleadosFamiliares" ef on e."Legajo" = ef."Legajo"

	where e."FechaEgreso" is null or e."FechaEgreso" > \''."$dAno-$dMes-01".'\'

	group by 
	e."Legajo",
	ed."CUIT",
	e."Apellido",
	e."Nombre"

	) me

left join "tblRecibos" r on me."Legajo" = r."Legajo" and r."Fecha"=\''."$dAno-$dMes-01".'\'
inner join "tblPeriodos" p on p."FechaPeriodo" = \''."$dAno-$dMes-01".'\' and p."TipoLiquidacionID" in (1,5,7,9) and r."NumeroLiquidacion" = p."NumeroLiquidacion"

GROUP BY me."Legajo",
me."CUIT",
me."ApellidoYNombre",
me."Conyuge",
me."Hijos"

having round(MAX(case when r."ConceptoID"=99 then 
	case when r."Haber1" is null then 0 else r."Haber1" end 
    else 0 end
))-round(MAX(
    case when r."ConceptoID"=60 and r."Haber1" is not null then r."Haber1" else 0 end
)) > 0

ORDER BY to_number(me."Legajo", \'999999\')
';

ob_end_clean();

header("Content-Type: application/x-unknown\r\n");
header("Content-Disposition: attachment; filename=\"sicoss.txt\"\r\n");



$rs = pg_query($db, $sql);

	if (!$rs){
		exit;
	}
	while($row = pg_fetch_array($rs))
	{
		$linea = "";
		$linea.= str_pad($row['CUIT'], 11, ' ', STR_PAD_RIGHT);
		$linea.= str_pad(substr($row['ApellidoYNombre'], 0, 30), 30, ' ', STR_PAD_RIGHT);
		$linea.= str_pad($row['Conyugue'], 1, '0', STR_PAD_LEFT);
		$linea.= str_pad($row['Hijos'], 2, '0', STR_PAD_LEFT);
		$linea.= '01'; // codigo situacion
		$linea.= '01'; // codigo condicion
		$linea.= '921'; // actividad
		$linea.= '05'; // zona
		$linea.= '00000'; // porcentaje de aporte adicional ss
		$linea.= '008'; // modalidad de contratacion
		$linea.= '000000'; // codigo obra social
		$linea.= '00'; // cantidad de adherentes
		$linea.= str_pad($row['Sueldo'], 12, '0', STR_PAD_LEFT);	// remuneracion	
		$linea.= str_pad($row['Sueldo'] + $row['SAC'] + $row['HorasExtras'], 12, '0', STR_PAD_LEFT);	// rem imponible 1
		$linea.= str_pad(0, 9, '0', STR_PAD_LEFT);	// asignaciones fam. pagas
		$linea.= '000000000';	// imp aporte voluntario
		$linea.= '000000000';	// adicional obra social
		$linea.= '000000000'; 	// excedentes ss
		$linea.= '000000000';	// excedentes os
		$linea.= str_pad('Buenos Aires - Resto de la Provincia', 50, ' ');
		$linea.= str_pad($row['Sueldo'] + $row['SAC'] + $row['HorasExtras'] , 12, '0', STR_PAD_LEFT);        // rem imponible 2
		//$linea.= str_pad($row['Sueldo'], 10, '0', STR_PAD_LEFT);        // rem imponible 2
		$linea.= str_pad($row['Sueldo'] + $row['SAC'] + $row['HorasExtras'], 12, '0', STR_PAD_LEFT);        // rem imponible 3
		$linea.= str_pad($row['Sueldo'] + $row['SAC'] + $row['HorasExtras'], 12, '0', STR_PAD_LEFT);        // rem imponible 4
		$linea.= '00'; // codigo de siniestrado
		$linea.= '0'; // coresponde reduccion
		$linea.= '000000000';	// lrt
		$linea.= '0'; // tipo empresa
		$linea.= '000000000'; //aporte adicional os
		$linea.= '1'; // regimen
		$linea.= '01'; // revista 1
		$linea.= '01'; // situacion de revista 1
		$linea.= '00'; // revista 2
		$linea.= '00'; // situacion de revista 2
		$linea.= '00'; // revista 3
		$linea.= '00'; // situacion de revista 3
		$linea.= str_pad($row['Sueldo'], 12, '0', STR_PAD_LEFT);        // sueldo + adicionales
		$linea.= str_pad($row['SAC'], 12, '0', STR_PAD_LEFT);       
		$linea.= str_pad($row['HorasExtras'], 12, '0', STR_PAD_LEFT);       
		$linea.= str_pad(0, 12, '0', STR_PAD_LEFT);        // zona desfavorable
		$linea.= str_pad(0, 12, '0', STR_PAD_LEFT);        // vacaciones
		$linea.= str_pad(28, 9, '0', STR_PAD_LEFT);        // cant de dias trab
		$linea.= str_pad($row['Sueldo'] + $row['SAC'] + $row['HorasExtras'], 12, '0', STR_PAD_LEFT);        // rem imponible 5
		$linea.= '0'; 	// convencionado
		$linea.= str_pad($row['Sueldo'] + $row['SAC'] + $row['HorasExtras'], 12, '0', STR_PAD_LEFT);        // rem imponible 6
		$linea.= '0'; 	// tipo de operacion
		$linea.= str_pad(0, 12, '0', STR_PAD_LEFT);        // adicionales
		$linea.= str_pad(0, 12, '0', STR_PAD_LEFT);        // premios
		$linea.= str_pad($row['Sueldo'] + $row['SAC'] + $row['HorasExtras'], 12, '0', STR_PAD_LEFT);        // rem imponible 8
		$linea.= str_pad($row['Sueldo'] + $row['SAC'] + $row['HorasExtras'], 12, '0', STR_PAD_LEFT);        // rem imponible 7
		$linea.= str_pad(($row['HorasExtras'] > 0 && $row['CantHorasExtras'] == 0) ? 1 : $row['CantHorasExtras'], 3, '0', STR_PAD_LEFT);
		$linea.= str_pad(0, 12, '0', STR_PAD_LEFT);        // conceptos no remun
		$linea.= str_pad($row['Maternidad'], 12, '0', STR_PAD_LEFT);        // maternidad
		$linea.= str_pad(0, 9, '0', STR_PAD_LEFT);        // rectificacion
		$linea.= str_pad($row['Sueldo'] + $row['SAC'] + $row['HorasExtras'], 12, '0', STR_PAD_LEFT);        // rem imponible 9
		$linea.= '000000000'; //tarea diferencial
		$linea.= '000'; //horas trabajadas
		$linea.= '1'; //seguro colectivo
		 

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
	<H1>Listado de Personal Liquidado</H1>
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
