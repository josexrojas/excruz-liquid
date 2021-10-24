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
		document.frmListadoBanco.submit();
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

<form name=frmListadoBanco method=post>
<input type=hidden name=accion id=accion>
<input type=hidden name=listado id=listado>
<?
if ($accion == ''){
	$rs = pg_query($db, "
SELECT a.*, (case ed.\"LugarPago\" when 2 then '7134' when 3 then '7134' when 4 then '7135' when 5 then '7135' end) AS \"LugarPago\",
    ed.\"TipoCuenta\", ed.\"NumeroCuenta\"
FROM \"VerAdelantosPendientesRafam\"() a
INNER JOIN \"tblEmpleadosDatos\" ed
ON a.\"EmpresaID\" = ed.\"EmpresaID\" AND a.\"SucursalID\" = ed.\"SucursalID\" AND a.\"Legajo\" = ed.\"Legajo\"
AND ed.\"NumeroCuenta\" IS NOT NULL AND ed.\"LugarPago\" IN (2,3,4,5)
");
	if (!$rs){
		exit;
	}
	$Fecha = date("dmy");
	$Hora = date("Hi");
	$arch = "IDH210";
?>
<H1><img src="images/icon64_banco.gif" width="64" height="64" align="absmiddle" /> Bancos</H1>
	<a class="tecla" href="javascript:BajarListado('<?=$arch?>'); void(0);"> 
	<img src="images/icon24_bajarlistado.gif" alt="Bajar Listado" width="24" height="24" border="0" align="absmiddle"> 
	Bajar Listado</a>
<!--	<a class="tecla" href="javascript:EnviarListado('<?=$arch?>'); void(0);">
	<img src="images/icon24_enviarlistado.gif" alt="Enviar Listado por Mail" width="24" height="24" border="0" align="absmiddle">
	Enviar Listado Por Mail</a>-->
	<a class="tecla" href='#' onclick="MM_openBrWindow('adelantoBancoPrint.php','printpreview','width=872,height=750')"> 
	<img src="images/icon24_printlistado.gif" alt="Imprimir Listado" width="24" height="24" border="0" align="absmiddle">
	Imprimir Listado</a>&nbsp;<BR />&nbsp;


	<table width="100%" border="0" cellpadding="5" cellspacing="1" class="datagrid">
	<tr><th>Sucursal Benef.</th><th>Cuenta y Digito</th><th>Codigo Operacion</th><th>Importe</th><th>Referencia</th><th>Rubro Cuenta</th></tr>
<?
	$Header  = '0000000000';	// Ceros
	$Header .= 'IDH210';		// Rotulo del archivo
	$Header .= '0';				// cDP
	$Header .= "$Fecha";		// Fecha ddmmaa
	$Header .= "$Hora";			// Hora hhmm
	$Header .= '014' . str_repeat(' ', 49) . "0\r\n";
	$TotalReg = 0;
	$TotalPesos = 0;
	while($row = pg_fetch_array($rs))
	{
		$LugarPago = $row['LugarPago'];
		$TipoCuenta = $row['TipoCuenta'];
		$NumeroCuenta = $row['NumeroCuenta'];
		$Legajo = $row['Legajo'];
		$Apellido = $row['Apellido'];
		$Nombre = $row['Nombre'];
		$Monto = $row['Monto'];
		$ApeYNom = trim(str_replace(',', ' ', $Apellido)) . ' ' . trim(str_replace(',', ' ', $Nombre));
		if ($Importe == '')
			$Importe = '0';
		$Importe = $Monto;

		$i = strpos($Monto, '.');
		if ($i === false){
			$Monto = str_pad($Monto, 9, '0', STR_PAD_LEFT) . '00';
		}else{
			$decimal = str_pad(substr($Monto, $i+1, 2), 2, '0');
			$Monto = substr($Monto, 0, $i);
			$Monto = str_pad($Monto, 9, '0', STR_PAD_LEFT) . $decimal;
		}

		if ($Importe > 0){
			$TotalPesosSucursales{$LugarPago} += $Importe;
			$Detalle .= $LugarPago;		// Casa beneficiaria
			$Detalle .= $LugarPago;		// Casa receptora
			$Detalle .= '0096';			// Codigo de operacion
			$Detalle .= $TipoCuenta;	// Tipo de cuenta
			//$Detalle .= '0';			// Relleno
			$Detalle .= str_pad($NumeroCuenta, 7, '0', STR_PAD_LEFT);	// Numero de cuenta
			$Detalle .= '00';			// Relleno
			$Detalle .= $Monto;		// Sueldo a depositar
			$Detalle .= '00';			// Relleno
			$Detalle .= str_pad($Legajo, 6, '0', STR_PAD_LEFT);			// Legajo
			$Detalle .= str_pad(substr($ApeYNom, 0, 22), 22, ' ', STR_PAD_RIGHT);	// Apellido y nombre
			$Detalle .= str_repeat(' ', 16) . "0\r\n";
			$TotalRegs++;
			$TotalPesos+=$Importe;
?>
		<tr><td><?=$LugarPago?></td><td><?=$NumeroCuenta?></td><td>????????</td><td><?=$Importe?></td><td><?=$Legajo?></td><td><?=$TipoCuenta?></td></tr>
<?
		}
	}
	$i = strpos($TotalPesos, '.');
	if ($i === false){
		$TotalP = str_pad($TotalPesos, 12, '0', STR_PAD_LEFT) . '00';
	}else{
		$decimal = substr($TotalPesos . '00', $i+1, 2);
		$TotalP = substr($TotalPesos, 0, $i);
		$TotalP = str_pad($TotalP, 12, '0', STR_PAD_LEFT) . $decimal;
	}
	$Cierre .= '9999999999';		// Identificacion del registro de cierre
	$Cierre .= str_pad($TotalRegs, 6, '0', STR_PAD_LEFT);	// Cantidad de registros procesados
	$Cierre .= $TotalP;										// Importe involucrado
	$Cierre .= str_repeat(' ', 49) . "0\r\n".chr(26);			// Corte final
	print "</table>\n";
	if ($TotalRegs == 0)
		Alerta('No hay pagos a realizar para la liquidacion seleccionada');
	else{
		print "<br><b>Cantidad de Registros Procesados: $TotalRegs<br>\n";
		print "Importe Involucrado en Pesos: $TotalPesos<br>\n";
		print "TOTAL ADELANTOS CAPILLA : ".($TotalPesosSucursales{'7134'}==''?0:$TotalPesosSucursales{'7134'})."<br>\n";
		print "TOTAL ADELANTOS CARDALES: ".($TotalPesosSucursales{'7135'}==''?0:$TotalPesosSucursales{'7135'})."<br>\n";
	}

	$fp = fopen('../listados/'.$arch, 'wb');
	fputs($fp, $Header);
	fputs($fp, $Detalle);
	fputs($fp, $Cierre);
	fclose($fp);
}




pg_close($db);
?>
</form>
<script>
	document.getElementById('divLoading').style.display = 'none';
</script>
<? include("footer.php"); ?>
