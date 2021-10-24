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
<form name="frmListadoART" action="listadoART3.php" method="post">
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

$sql = <<<EOL
SELECT
e."Legajo" AS "Legajo",
MIN(e."Apellido" || ' ' || e."Nombre") AS "Nombre",
MIN(CASE WHEN ed."TipoDocumento" = 1 THEN 'DNI'
     WHEN ed."TipoDocumento" = 2 THEN 'CI'
     WHEN ed."TipoDocumento" = 3 THEN 'PASAPORTE'
     WHEN ed."TipoDocumento" = 4 THEN 'LE'
     WHEN ed."TipoDocumento" = 5 THEN 'LC'
     ELSE ''
END || ' ' || ed."NumeroDocumento") AS "Documento",
MIN(ed."FechaNacimiento"),
SUM(CASE WHEN r."AliasID" IN (33, 114) THEN (r."Descuento" / 0.000466 / 10)::numeric(10,2) ELSE 0 END) AS "SueldoSujeto",
SUM(CASE WHEN r."AliasID" IN (33, 114) THEN (r."Descuento" / 0.000466)::numeric(10,2) ELSE 0 END) AS "Capital",
SUM(CASE WHEN r."AliasID" IN (33, 114) THEN r."Descuento" ELSE 0 END) as "Prima",

MIN(CASE WHEN r."AliasID" = 34 THEN ef."Apellido" || ' ' || ef."Nombres" END) AS "NombreConyuge",
MIN(
CASE WHEN r."AliasID" = 34 THEN 
  CASE WHEN ef."TipoDocumento" = 1 THEN 'DNI'
     WHEN ef."TipoDocumento" = 2 THEN 'CI'
     WHEN ef."TipoDocumento" = 3 THEN 'PASAPORTE'
     WHEN ef."TipoDocumento" = 4 THEN 'LE'
     WHEN ef."TipoDocumento" = 5 THEN 'LC'
     ELSE ''
  END || ' ' || ef."NumeroDocumento" 
END) AS "DocumentoConyuge",
MIN(CASE WHEN r."AliasID" = 34 THEN ef."FechaNacimiento" END) AS "FechaNacimientoConyuge",
SUM(CASE WHEN r."AliasID" = 34 THEN (r."Descuento" / 0.000466 / 10)::numeric(10,2) ELSE 0 END) AS "SueldoSujetoConyuge",
SUM(CASE WHEN r."AliasID" = 34 THEN (r."Descuento" / 0.000466)::numeric(10,2) ELSE 0 END) AS "CapitalConyuge",
SUM(CASE WHEN r."AliasID" = 34 THEN r."Descuento" ELSE 0 END) as "PrimaConyuge"

FROM "tblEmpleados" e
INNER JOIN "tblRecibos" r ON e."EmpresaID" = r."EmpresaID" and e."SucursalID" = r."SucursalID" and e."Legajo" = r."Legajo"
INNER JOIN "tblEmpleadosDatos" ed ON e."EmpresaID" = ed."EmpresaID" and e."SucursalID" = ed."SucursalID" and e."Legajo" = ed."Legajo"
LEFT JOIN "tblEmpleadosFamiliares" ef ON e."EmpresaID" = ef."EmpresaID" and e."SucursalID" = ef."SucursalID" and e."Legajo" = ef."Legajo" AND 

ef."TipoDeVinculo" = 1 AND ef."FechaBaja" IS NULL

WHERE r."Fecha"='@Fecha'
AND r."AliasID" IN (33, 114, 34)
AND r."NumeroLiquidacion" = '@NumeroLiquidacion'

GROUP BY e."Legajo"
ORDER BY to_number(e."Legajo", '999999');


EOL;

$sql = str_replace('@Fecha', $FechaPeriodo, $sql);
$sql = str_replace('@NumeroLiquidacion', $NumeroLiquidacion, $sql);

//	$rs = print( "
	$rs = pg_query($db, $sql) or die(pg_last_error());

	if (!$rs){

		exit;
	}
	$Fecha = date("dmy");
	$Hora = date("Hi");
	$arch = "ART$Fecha.txt";
?>
<H1>Listado Seguro de Vida</H1>
<!--	<a class="tecla" href="javascript:EnviarListado('<?=$arch?>'); void(0);">
	<img src="images/icon24_enviarlistado.gif" alt="Enviar Listado por Mail" width="24" height="24" border="0" align="absmiddle">
	Enviar Listado Por Mail</a>-->
<?
	print "<br><b>Per&iacute;odo: " . Mes(substr($FechaPeriodo, 5, 2)) . " de " . substr($FechaPeriodo, 0, 4);
	print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Numero de Liquidaci&oacute;n: $NumeroLiquidacion</b><br><br>";
?>
	<table width="100%" border="0" cellpadding="5" cellspacing="1" class="datagrid">
	<tr>
    	<th>Legajo</th>
        <th>Apellido y Nombre</th>
        <th>DNI</th>
        <th>Fecha de Nacimiento</th>
        <th>Sueldo sujeto a desc.</th>
        <th>Capital</th>
        <th>Prima</th>
        <th>&nbsp;</th>
    <tr>
<?
	$TotalReg = 0;
	$Detalle = '';
	while($row = pg_fetch_array($rs))
	{
	//	$FechaIng = FechaSQL2WEB($row[6]);
		
?>
	<tr>
            	<td><?=$row[0]?></td>
                <td><?=$row[1]?></td>
                <td><?=$row[2]?></td>
                <td><?=$row[3]?></td>
                <td><?=$row[4]?></td>
                <td><?=$row[5]?></td>
                <td><?=$row[6]?></td>
                <td></td>
        </tr>
<?
	if ($row[7])
	{
?>
	<tr>
            	<td>&nbsp;</td>
                <td><?=$row[7]?></td>
                <td><?=$row[8]?></td>
                <td><?=$row[9]?></td>
                <td>&nbsp;</td>
                <td><?=$row[11]?></td>
                <td><?=$row[12]?></td>
                <td>Conyuge</td>
        </tr>

<?
	}

			$TotalReg++;
	}
	?>
	<?
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
