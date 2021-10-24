<?
require_once('funcs.php');
EstaLogeado();
$accion = LimpiarVariable($_POST["accion"]);

if ($accion == 'Bajar Listado'){
	$arch = LimpiarVariable($_POST["listado"]);
	$lTam = filesize('../listados/'.$arch);
	header("Content-Type: application/octet-stream\r\n");
	header("Content-Disposition: attachment; filename=\"$arch\"\r\n");
	header("Content-Length: $lTam\r\n\r\n");
	$fp = fopen('../listados/'.$arch, 'rb');
	while(!feof($fp)){
		$c = fgetc($fp);
		print $c;
	}
	fclose($fp);
	exit;
}
include("header.php");

if (!($db = Conectar()))
	exit;

$EmpresaID = $_SESSION["EmpresaID"];
$SucursalID = $_SESSION["SucursalID"];

?>

<script>
	function BajarListado(sArch){
		document.getElementById('accion').value = 'Bajar Listado';
		document.getElementById('listado').value = sArch;
		document.frmListadoRafam.submit();
	}

	function Generar(){
		document.getElementById('accion').value = 'Generar';
		document.frmListadoRafam.submit();
	}
</script>

<form name=frmListadoRafam action=listadoRafam.php method=post>
<input type=hidden name=listado id=listado>
<input type=hidden id=accion name=accion>
<?
if ($accion == 'Generar'){
	$selPeriodo = $_POST["selPeriodo"];
	$i = strpos($selPeriodo, '|');
	if ($i !== false){
		$FechaPeriodo = LimpiarNumero2(substr($selPeriodo, 0, $i));
		$NumeroLiquidacion = LimpiarNumero(substr($selPeriodo, $i+1));
	}
	if ($FechaPeriodo == '' || $NumeroLiquidacion == ''){
		exit;
	}
	$Mes = substr($FechaPeriodo, 5, 2);
	$Anio = substr($FechaPeriodo, 0, 4);
	$arch = "RAF$Anio$Mes.txt";
	/*$FechaDesde = "$Anio-$Mes-01";
	$Mes++;
	if ($Mes > 12){
		$Anio++;
		$Mes = 1;
	}
	$FechaHasta = "$Anio-$Mes-01";*/
	$rs = pg_query($db, "
SELECT jurisdiccion, case when codigo_ff is null then 110 else codigo_ff end as codigo_ff, inciso, par_prin, par_parc, par_subp, activ_prog, activ_proy, case when activ_obra is null then 0 else activ_obra end as activ_obra, 
	SUM(\"Importe\") AS \"Importe\"
FROM \"tblImputacionesRafam\" 
WHERE \"EmpresaID\" = $EmpresaID AND \"SucursalID\" = $SucursalID AND \"Fecha\" = '$FechaPeriodo' 
AND \"NumeroLiquidacion\" = $NumeroLiquidacion
GROUP BY jurisdiccion, codigo_ff, inciso, par_prin, par_parc, par_subp, activ_prog, activ_proy, case when activ_obra is null then 0 else activ_obra end
ORDER BY 1,2,3,4,5,6,7,8,9
	");
	if (!$rs){
		exit;
	}
	$fp = fopen('../listados/'.$arch, 'wt');
	$Neg = '';
	while($row = pg_fetch_array($rs)){
		$Linea = $Anio;
		if ($row[0] == '' || $row[0] == '0')
			continue;
		$Linea .= $row[0];
		$Linea .= $row[1];
		$Linea .= $row[2];
		$Linea .= $row[3];
		$Linea .= $row[4];
		$Linea .= $row[5];
		$Linea .= str_pad($row[6], 2, '0', STR_PAD_LEFT);
		$Linea .= str_pad($row[7], 2, '0', STR_PAD_LEFT);
		$Linea .= str_pad($row[8], 2, '0', STR_PAD_LEFT);
		$Importe = $row[9];
		if ($Importe >= 0){
			$i = strpos($Importe, '.');
			if ($i === false){
				$Importe = str_pad($Importe, 12, '0', STR_PAD_LEFT) . '.00';
			}else{
				$decimal = str_pad(substr($Importe, $i+1, 2), 2, '0');
				$Importe = substr($Importe, 0, $i);
				$Importe = str_pad($Importe, 12, '0', STR_PAD_LEFT) . '.' . $decimal;
			}
			$Linea .= $Importe . "\r\n";
			fputs($fp, $Linea);
		}else{
			$Neg .= "Jurisdiccion: $row[0] FF: $row[1]<br>\n";
			$Neg .= "Inciso: $row[2] Par_Prin: $row[3] Par_Parc: $row[4] Par_Subp: $row[5]<br>\n";
			$Neg .= "Programa: $row[6] Proyecto: $row[7] Obra: $row[8]<br>\n";
			$Neg .= "Importe: $Importe<br><br>\n";
		}
	}
	fclose($fp);
	Alerta('El archivo se genero correctamente');
	if ($Neg != '')
		print "<b>Imputaciones Negativas</b><br><br>$Neg\n";
?>
	<a class="tecla" href="javascript:BajarListado('<?=$arch?>'); void(0);"> 
	<img src="images/icon24_bajarlistado.gif" alt="Bajar Listado" width="24" height="24" border="0" align="absmiddle"> 
	Bajar Listado</a>
<?
}
/*
if ($accion == ''){
?>
	Mes a Generar: <input type=text id=Mes name=Mes value="<? print date("m"); ?>"><br><br>
	<input type=button onclick="javascript:Generar();" value="Generar">
<?
}*/

if ($accion == ''){
	include 'selLiquida.php'; ?>
	<TR>
		<TD class="izquierdo"></TD><TD class="derecho"><input type=submit id=accion name=accion value="Generar"></TD></TR></table>
<?
}
pg_close($db);
?>
</form>
<? include("footer.php"); ?>
