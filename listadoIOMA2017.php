<?
require_once('funcs.php');
EstaLogeado();
$accion = LimpiarVariable($_POST["accion"]);

if ($accion == 'Bajar Listado'){
	$arch = LimpiarVariable($_POST["listado"]);
	EnviarArchivo('../listados/', $arch);
	exit;
}

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
<form name=frmListadoART method=post>
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
  $sqlLegajo,
  ce."Grupo", 
  ce."Categoria",
  re."Fecha",
  em."Apellido" || ' ' || em."Nombre" AS "Nombre",
  REPLACE(ed."CUIT",  '-', '') AS "CUIL",
  ed."FechaNacimiento",
  ed."FechaIngreso",
  re."AliasID" AS "Codigo",
  CASE WHEN re."Haber1" IS NOT NULL THEN re."Haber1" 
       WHEN re."Haber2" IS NOT NULL THEN re."Haber2"
       WHEN re."Descuento" IS NOT NULL THEN re."Descuento" END AS "Importe"

FROM "tblRecibos" re
INNER JOIN "tblEmpleados" em ON re."EmpresaID" = em."EmpresaID" AND re."SucursalID" = em."SucursalID" AND re."Legajo" = em."Legajo"
INNER JOIN "tblEmpleadosDatos" ed ON re."EmpresaID" = ed."EmpresaID" AND re."SucursalID" = ed."SucursalID" AND re."Legajo" = ed."Legajo"
LEFT JOIN "tblCategoriasEmpleado" ce ON re."Legajo" = ce."Legajo"

WHERE re."EmpresaID" = $EmpresaID 
AND re."SucursalID" = $SucursalID 
AND re."Fecha" = '$FechaPeriodo'
AND re."NumeroLiquidacion" = $NumeroLiquidacion
AND re."ConceptoID" <> 99
AND re."Aporte" IS NULL

ORDER BY to_number(em."Legajo", '999999'), re."AliasID"
EOL;
	$rs = pg_query($db, $sql);
	if (!$rs){
		exit;
	}
	$Fecha = substr($FechaPeriodo, 5, 2).substr($FechaPeriodo, 0, 4);
	$Hora = date("Hi");
	$Ente = "137";
	$arch = "S$Ente$Fecha.txt"; 
	$arch2 = "D$Ente$Fecha.txt"; 
?>
<H1>Listado IOMA</H1>
	<a class="tecla" href="javascript:BajarListado('<?=$arch?>'); void(0);"> 
	<img src="images/icon24_bajarlistado.gif" alt="Bajar Listado" width="24" height="24" border="0" align="absmiddle"> 
	Bajar Listado Sueldo</a>
	<a class="tecla" href="javascript:BajarListado('<?=$arch2?>'); void(0);"> 
	<img src="images/icon24_bajarlistado.gif" alt="Bajar Listado" width="24" height="24" border="0" align="absmiddle"> 
	Bajar Listado Detalle</a>
<?
	print "<br><b>Per&iacute;odo: " . Mes(substr($FechaPeriodo, 5, 2)) . " de " . substr($FechaPeriodo, 0, 4);
	print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Numero de Liquidaci&oacute;n: $NumeroLiquidacion</b><br><br>";
	print "<pre>";
	$curLegajo = 0;
	$data = array();
	$Detalle = '';
	$TotalReg = 0;
	while($row = pg_fetch_array($rs))
	{
		if ($row['Legajo'] != $curLegajo)
		{
			if ($data)
			{
				$line = str_pad($data['Legajo'], 12) . ",";
				$line.= str_pad(substr(ltrim($data['Grupo']), 0, 12), 12) . ",";
				$line.= str_pad(substr(ltrim($data['Categoria']), 0, 12), 12) . ",";
				$line.= str_pad(substr(ltrim($data['Categoria']), 0, 12), 12) . ",";
				$line.= substr($FechaPeriodo, 5, 2) .'-'. substr($FechaPeriodo, 0, 4) .",";
				$line.= str_pad(substr(ltrim($data['Nombre']), 0, 50), 50) . ",";
				$line.= str_pad(substr(ltrim($data['CUIL']), 0, 12), 12, '0', STR_PAD_LEFT) . ",";
				$line.= substr($data['FechaNacimiento'], 8, 2) .'-'. substr($data['FechaNacimiento'], 5, 2) .'-'. substr($data['FechaNacimiento'], 0, 4) . "  ,";
				$line.= substr($data['FechaIngreso'], 8, 2) .'-'. substr($data['FechaIngreso'], 5, 2) .'-'. substr($data['FechaIngreso'], 0, 4) . "  ,";
				for ($i=1; $i<=50; $i++)
				{
					$line.= str_pad(substr(ltrim($data['Codigo'.$i]), 0, 12), 12, ' ', STR_PAD_LEFT) . ",";
					$line.= str_pad(substr(ltrim(abs(floor($data['Importe'.$i]))), 0, 10), 10, '0', STR_PAD_LEFT) ;
					$line.= str_pad(substr(ltrim((abs($data['Importe'.$i]) * 100) % 100), 0, 2), 2, '0', STR_PAD_LEFT)."," ;
				}

				$Detalle.= substr($line, 0, -1);
				$Detalle.= "\n";
				$TotalReg++;
			}

			$data = $row;
			$i = 1;
			$curLegajo = $row['Legajo'];
		}
		else
		{
			$data['Codigo' . $i] = $row['Codigo'];
			$data['Importe' . $i] = $row['Importe'];
			$i++;
		}
	}

	print "<br><b>Cantidad de Legajos Procesados: $TotalReg<br>\n";
	$fp = fopen('../listados/'.$arch, 'wb');
	fputs($fp, $Detalle); 
	fclose($fp);






	$sql = 'SELECT TRIM(ce."Grupo") FROM "tblCategoriasEmpleado" ce GROUP BY TRIM(ce."Grupo") ORDER BY TRIM(ce."Grupo")';
	$rs = pg_query($db, $sql);
	if (!$rs){
		exit;
	}

	$Detalle = ",AGRUPACION,\n";
	while($row = pg_fetch_array($rs))
	{
		$line = '';
		$line.= str_pad(substr(ltrim($row[0]), 0, 12), 12) . ",";
		$line.= str_pad(substr(ltrim($row[0]), 0, 50), 50) . ",";
		$Detalle.= $line;
		$Detalle.= "\n";
	}


	$sql = 'SELECT TRIM(ce."Categoria") FROM "tblCategoriasEmpleado" ce GROUP BY TRIM(ce."Categoria") ORDER BY TRIM(ce."Categoria")';
	$rs = pg_query($db, $sql);
	if (!$rs){
		exit;
	}

	$Detalle.= ",CATEGORIA,\n";
	while($row = pg_fetch_array($rs))
	{
		$line = '';
		$line.= str_pad(substr(ltrim($row[0]), 0, 12), 12) . ",";
		$line.= str_pad(substr(ltrim($row[0]), 0, 50), 50) . ",";
		$Detalle.= $line;
		$Detalle.= "\n";
	}

	$sql = 'SELECT TRIM(ce."Categoria") FROM "tblCategoriasEmpleado" ce GROUP BY TRIM(ce."Categoria") ORDER BY TRIM(ce."Categoria")';
	$rs = pg_query($db, $sql);
	if (!$rs){
		exit;
	}

	$Detalle.= ",CARGO,\n";
	while($row = pg_fetch_array($rs))
	{
		$line = '';
		$line.= str_pad(substr(ltrim($row[0]), 0, 12), 12) . ",";
		$line.= str_pad(substr(ltrim($row[0]), 0, 50), 50) . ",";
		$Detalle.= $line;
		$Detalle.= "\n";
	}


	$sql = <<<EOL
SELECT
  re."AliasID",
  re."Descripcion",
  SUM(CASE WHEN re."Haber1" > 0 THEN 1 END) AS "Aporta"

FROM "tblRecibos" re
INNER JOIN "tblEmpleados" em ON re."EmpresaID" = em."EmpresaID" AND re."SucursalID" = em."SucursalID" AND re."Legajo" = em."Legajo"
INNER JOIN "tblEmpleadosDatos" ed ON re."EmpresaID" = ed."EmpresaID" AND re."SucursalID" = ed."SucursalID" AND re."Legajo" = ed."Legajo"
LEFT JOIN "tblCategoriasEmpleado" ce ON re."Legajo" = ce."Legajo"

WHERE re."EmpresaID" = $EmpresaID 
AND re."SucursalID" = $SucursalID 
AND re."Fecha" = '$FechaPeriodo'
AND re."NumeroLiquidacion" = $NumeroLiquidacion
AND re."ConceptoID" <> 99
AND re."Aporte" IS NULL
AND re."Descripcion" NOT LIKE 'AJ.%'

GROUP BY re."AliasID", re."Descripcion"

ORDER BY re."AliasID"
EOL;
	$rs = pg_query($db, $sql);
	if (!$rs){
		exit;
	}

	$Detalle.= ",DETALLE,\n";
	while($row = pg_fetch_array($rs))
	{
		$line = '';
		$line.= str_pad(substr(ltrim($row[0]), 0, 12), 12) . ",";
		$line.= str_pad(substr(ltrim($row[1]), 0, 50), 50) . ",";
		$line.= $row[2] ? 'SI' : '  ';
		$Detalle.= $line;
		$Detalle.= "\n";
	}


	$fp = fopen('../listados/'.$arch2, 'wb');
	fputs($fp, $Detalle); 
	fclose($fp);

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
