<?
require_once('funcs.php');
EstaLogeado();
$accion = LimpiarVariable($_POST["accion"]);

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

?>

<script>
	function BajarListado(sArch){
		document.getElementById('accion').value = 'Bajar Listado';
		document.getElementById('listado').value = sArch;
		document.frmListadoART.submit();
	}
	function MM_openBrWindow(theURL,winName,features) { //v2.0
	  window.open(theURL,winName,features);
	}
</script>

<div id=divLoading style="display:block">
<table height=100% align=center valign=center>
<tr><td><img src="images/icon32_process.gif" align=absmiddle width=32 height=32 border=0> Generando Listado</td></tr>
</table>
</div>
<form name="frmListadoART" action="listadoART4.php" method="post">
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
//	$rs = print( "
	$rs = pg_query($db, "
SELECT 
to_number(em.\"Legajo\", '999999') AS \"Legajo\",
ed.\"TipoDocumento\", 
ed.\"NumeroDocumento\", 
em.\"Apellido\", 
em.\"Nombre\", 
ca.detalle, 
ed.\"FechaIngreso\", 
ed.\"Sexo\", 
round(SUM(CASE WHEN re.\"ConceptoID\" = 99 THEN re.\"Haber1\"::numeric ELSE 0 END), 2) AS \"Remuneracion\", 
round(SUM(CASE WHEN re.\"AliasID\" = 31 THEN re.\"Aporte\"::numeric ELSE 0 END), 2) AS \"s1\", 
round(SUM(CASE WHEN re.\"AliasID\" = 32 THEN re.\"Aporte\"::numeric ELSE 0 END), 2) AS \"s2\",
em.\"FechaEgreso\",
ed.\"CUIT\",
round(SUM(CASE WHEN re.\"ConceptoID\" = 99 THEN re.\"Haber1\"::numeric ELSE 0 END), 2) AS \"Haber1\", 
round(SUM(CASE WHEN re.\"ConceptoID\" = 99 THEN re.\"Haber2\"::numeric ELSE 0 END), 2) AS \"Haber2\", 
round(SUM(CASE WHEN re.\"ConceptoID\" = 99 THEN re.\"Descuento\"::numeric ELSE 0 END), 2) AS \"Descuento\", 
emd.\"Calle\", 
emd.\"Numero\", 
emd.\"Piso\", 
emd.\"Departamento\", 
emd.\"CodigoPostal\", 
emd.\"Localidad\", 
'Buenos Aires' as \"Provincia\", 
ca.detalle ,
ed.\"FechaNacimiento\",
re.\"EmpresaID\",
re.\"SucursalID\",
re.\"Fecha\",
re.\"NumeroLiquidacion\",
re.\"Legajo\",
round(SUM(CASE WHEN re.\"AliasID\" IN (166, 185) THEN re.\"Haber2\"::numeric ELSE 0 END), 2) AS \"Presentismo\", 
0 as \"Premios\",
0 as \"Antiguedad\", -- round(SUM(CASE WHEN re.\"ConceptoID\" = 10 THEN re.\"Haber1\"::numeric ELSE 0 END), 2) AS \"Antiguedad\", 
0 AS \"Bonus\", 
0 AS \"Viaticos\", 
round(SUM(CASE WHEN re.\"ConceptoID\" = 20 THEN re.\"Haber1\"::numeric ELSE 0 END), 2) AS \"Refrigerio\", 
-- round(SUM(CASE WHEN re.\"Descripcion\" = 'REDONDEO' THEN re.\"Haber2\"::numeric ELSE 0 END), 2) AS \"Otros Conceptos\", 
0 AS \"Otros Conceptos\", 
0 AS \"Vacaciones\", 
round(SUM(CASE WHEN re.\"ConceptoID\" = 60 THEN re.\"Haber1\"::numeric ELSE 0 END), 2) AS \"S.A.C\", 
round(SUM(CASE WHEN re.\"ConceptoID\" IN(20, 21) OR re.\"AliasID\" IN (163, 164, 165) OR re.\"Descripcion\" = 'AJ.HS. EX. POR CONSULTORIO EXTERNO EN +' OR re.\"Descripcion\" = 'AJ.HS. EX. POR GUARDIA EN +' OR re.\"Descripcion\" = 'AJ.HS. EX. POR TRASLADO EN +'  THEN re.\"Haber2\"::numeric ELSE 0 END), 2) AS \"Horas Extras\",
ed.\"Nacionalidad\",
ed.\"EstadoCivil\",
ce.\"Dependencia\" AS \"Establecimiento\",
tr.\"Descripcion\" AS \"TipoRelacion\"
FROM \"tblEmpleados\" em 
INNER JOIN \"tblEmpleadosDatos\" ed ON ed.\"EmpresaID\" = em.\"EmpresaID\" AND ed.\"SucursalID\" = em.\"SucursalID\" AND ed.\"Legajo\" = em.\"Legajo\" 
INNER JOIN \"tblEmpleadosDomicilio\" emd ON emd.\"EmpresaID\" = em.\"EmpresaID\" AND emd.\"SucursalID\" = em.\"SucursalID\" AND emd.\"Legajo\" = em.\"Legajo\" 
LEFT JOIN \"tblEmpleadosRafam\" er ON er.\"EmpresaID\" = em.\"EmpresaID\" AND er.\"SucursalID\" = em.\"SucursalID\" AND er.\"Legajo\" = em.\"Legajo\"
LEFT JOIN \"tblCategoriasEmpleado\" ce ON ce.\"Legajo\" = em.\"Legajo\"
LEFT JOIN \"tblTipoRelacion\" tr ON em.\"TipoRelacion\" = tr.\"TipoRelacionID\"
INNER JOIN \"tblRecibos\" re ON em.\"EmpresaID\" = re.\"EmpresaID\" AND em.\"SucursalID\" = re.\"SucursalID\" AND em.\"Legajo\" = re.\"Legajo\" 
LEFT JOIN owner_rafam.cargos ca ON substr(ca.jurisdiccion, 1, 5) = substr(er.jurisdiccion, 1, 5) AND ca.agrupamiento = er.agrupamiento AND ca.categoria = er.categoria AND ca.cargo = er.cargo 
WHERE re.\"EmpresaID\" = 1 AND re.\"SucursalID\" = 1 AND re.\"Fecha\" = '".$FechaPeriodo."' AND re.\"NumeroLiquidacion\" = ".$NumeroLiquidacion." AND re.\"ConceptoID\" IN (99 ,81 ,82 ,35 ,10 ,34,60, 20,21, 32, 91)
GROUP BY 1,2,3,4,5,6,7,8,12,13,17,18,19,20,21,22,23,24,25,26,27,28,29,30,32,34,35,37,38, ed.\"Nacionalidad\", ed.\"EstadoCivil\", ce.\"Dependencia\", tr.\"Descripcion\"
ORDER BY 1
") or die(pg_last_error());

	if (!$rs){

		exit;
	}
	$Fecha = date("dmy");
	$Hora = date("Hi");
	$arch = "ART$Fecha.txt";
?>
<H1>Listado ART 2</H1>
<!--	<a class="tecla" href="javascript:EnviarListado('<?=$arch?>'); void(0);">
	<img src="images/icon24_enviarlistado.gif" alt="Enviar Listado por Mail" width="24" height="24" border="0" align="absmiddle">
	Enviar Listado Por Mail</a>-->
<?
	print "<br><b>Per&iacute;odo: " . Mes(substr($FechaPeriodo, 5, 2)) . " de " . substr($FechaPeriodo, 0, 4);
	print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Numero de Liquidaci&oacute;n: $NumeroLiquidacion</b><br><br>";
?>
	<table width="100%" border="0" cellpadding="5" cellspacing="1" class="datagrid">
	<tr>
        <th>CUIL</th>
        <th>Apellido y Nombre</th>
        <th>Sexo</th>
		<th>Nacionalidad</th>
		<th>Otra Nacionalidad</th>
        <th>Fecha Nacimiento</th>
		<th>Estado civil</th>
        <th>Fecha de Ingreso</th>
		<th>Establecimiento</th>
		<th>Tipo contrato</th>
        <th>Tarea</th>
		<th>Sector</th>
		<th>Código CIUO</th>
        <th>Remuneración</th>
		<th>Calle</th>
        <th>Número</th>
        <th>Piso</th>
        <th>Departamento</th>
        <th>Código Postal</th>
        <th>Localidad</th>
        <th>Provincia</th>
        <th>Fecha de Egreso</th>
    <tr>
<?
	$TotalReg = 0;
	$TotalRem = 0;
	$TotalNeto = 0;
	$Detalle = '';
	while($row = pg_fetch_array($rs))
	{
		$Legajo = $row[0];
		$TipoDoc = $row[1];
		if ($TipoDoc == '2')
			$TipoDoc = 'CI';
		else if ($TipoDoc == '3')
			$TipoDoc = 'PAS';
		else if ($TipoDoc == '4')
			$TipoDoc = 'LE';
		else if ($TipoDoc == '5')
			$TipoDoc = 'LC';
		else
			$TipoDoc = 'DNI';
		$NumDoc = $row[2];
		$Ape = $row[3];
		$Nom = $row[4];
		$Cargo = $row[5];
		$FechaIng = FechaSQL2WEB($row[6]);
		
		$Sexo = $row[7];
		$Remuneracion = $row[8];
		$PciaART = $row[9];
		$PciaPor = $row[10];
		if ($PciaART == '')
			$PciaART = 0;
		if ($PciaPor == '')
			$PciaPor = 0;
		$FechaEgr = $row[11];
		$ApeYNom = trim(str_replace(',', ' ', $Ape)) . ' ' . trim(str_replace(',', ' ', $Nom));
		$CUIT = $row[12];
		$Haber1 = $row[13];
		$Haber2 = $row[14];
		$Descuento = $row[15];
		$Neto = $row[8];
		$Calle = $row[16];
		$Numero = $row[17];
		$Piso = $row[18];
		$Departamento = $row[19];
		$CodigoPostal = $row[20];
		$Localidad = $row[21];
		$Provincia = $row[22];
		$Cargo = $row[23];
		$FechaNac = FechaSQL2WEB($row[24]);
		$Presentismo = $row[30];
		$Premios = $row[31];
		$Antiguedad = 0;
		$Bonus = $row[33];
		$Viaticos = $row[34];
		$Refrigerio = $row[35];
		$OtrosConceptos = $row[36];
		$Vacaciones = $row[37];
		$SAC = $row[38];
		$HorasExtras = $row[39];
		
		switch ($row[41])
		{
			case 1: 
				$EstadoCivil = "Soltero";
				break;
			
			case 2:
				$EstadoCivil = "Casado/a";
				break;
			
			case 3:
				$EstadoCivil = "Viudo/a";
				break;
				
			case 4:
				$EstadoCivil = "Divorciado/a";
				break;
				
			default: 
				$EstadoCivil = "";
				break;
		}
				
		if (floatval($Neto)>0 || 1){
?>
			<tr>
                <td><?=$CUIT?></td>
                <td><?=$ApeYNom?></td>
                <td><?=$Sexo?></td>
				<td><?=$row[40]?></td>
				<td></td>
                <td><?=$FechaNac?> </td>
				<td><?=$EstadoCivil?></td>
                <td><?=$FechaIng?></td>
				<td><?=$row[42]?></td>
				<td><?=$row[43]?></td>
                <td><?=$Cargo?> </td>
				<td><?=$row[42]?></td>
				<td></td>
                <td><?=$Remuneracion?></td>
				<td><?=$Calle?> </td>
                <td><?=$Numero?> </td>
                <td><?=$Piso?> </td>
                <td><?=$Departamento?> </td>
                <td><?=$CodigoPostal?> </td>
                <td><?=$Localidad?> </td>
                <td><?=$Provincia?> </td>
				<td><?=$FechaEgr?> </td>
            </tr>
<?
			$TotalReg++;
		}
	}
	
	print "</table>";
	print "<br><b>Cantidad de Legajos Procesados: $TotalReg<br>\n";
}

if ($accion == ''){
	include 'selLiquida.php'; ?>
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

