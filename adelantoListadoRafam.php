<? include("header.php");

if (!($db = Conectar()))
	exit;
?>
<H1><img src="images/icon64_Recibos.gif" width="64" height="64" align="absmiddle" /> Listado RAFAM Adelantos</H1>

<?
$rs = pg_query($db, "select a.*, ed.\"NumeroCuenta\" from \"VerAdelantosPendientesRafam\"() a LEFT JOIN \"tblEmpleadosDatos\" ed ON a.\"EmpresaID\" = ed.\"EmpresaID\" AND a.\"SucursalID\" = ed.\"SucursalID\" AND a.\"Legajo\" = ed.\"Legajo\"
AND ed.\"NumeroCuenta\" IS NOT NULL AND ed.\"LugarPago\" IN (2,3,4,5);");
if (!$rs){
	exit;
}
$Jurisdiccion = '';
$AntJur = '';
$MontoJ = 0;
$MontoT = 0;
$MontoMJ = 0;
$MontoMT = 0;
while($row = pg_fetch_array($rs))
{
	$Legajo = $row['Legajo'];
	$ApeYNom = trim($row['Apellido'] . ', ' . $row['Nombre']);
	$Cat = $row['Categoria'];
	$Horas = $row['HorasDiarias'];
	$Monto = $row['Monto'];
	$Cuotas = $row['Cuotas'];
	$Jurisdiccion = $row['Jurisdiccion'];
	$Car = $row['Cargo'];

	if ($Jurisdiccion != '' && $AntJur != $Jurisdiccion){
		if ($AntJur != '')
			$Cerrar = 1;
	}
	if ($Cerrar == 1){
		$Cerrar = 0;
		print "</table><br>\n";
		print "<b>Total Jurisdiccion: " . $MontoJ . "</b><br>";
		print "<b>Total Jurisdiccion por Banco: " . ($MontoJ - $MontoMJ) . "</b><br>";
		print "<b>Total Jurisdiccion por Caja: " . $MontoMJ . "</b><br><br><br>";
		$MontoJ = 0;
		$MontoMJ = 0;
	}
	if ($Jurisdiccion != '' && $AntJur != $Jurisdiccion){
		print "<b>Jurisdicci&oacute;n: " . Jurisdiccion($db, $Jurisdiccion) . "</b><br><br>";
		$AntJur = $Jurisdiccion;
		$Abrir = 1;
	}
	
	if ($Abrir == 1){
		$Abrir = 0;
?>
		<table width="100%" border="0" cellpadding="5" cellspacing="1" class="datagrid">
		<tr><th>Legajo</th><th>Apellido y Nombre</th><th>Categoria</th><th>Cargo</th><th>Monto</th><th>Cuotas</th></tr>
<?
	}

	if ($row['NumeroCuenta'] == '') {
	?>
		<tr style="background-color:#ff9999;"><td><?=$Legajo?></td><td><?=$ApeYNom?></td><td><?=$Cat?></td><td><?=$Car?></td><td><?=$Monto?></td><td><?=$Cuotas?></td></tr>
		<?
		$MontoMT += $Monto;
		$MontoMJ += $Monto;
	}
	else
	{
	?>
		<tr><td><?=$Legajo?></td><td><?=$ApeYNom?></td><td><?=$Cat?></td><td><?=$Car?></td><td><?=$Monto?></td><td><?=$Cuotas?></td></tr>
	<?
	}

	$MontoT += $Monto;
	$MontoJ += $Monto;
}
print "</table><br>\n";
print "<b>Total Jurisdiccion: " . $MontoJ . "</b><br>";
print "<b>Total Jurisdiccion por Banco: " . ($MontoJ - $MontoMJ) . "</b><br>";
print "<b>Total Jurisdiccion por Caja: " . $MontoMJ . "</b><br><br><br>";
print "<b>Total Periodo: " . $MontoT . "</b><br>";
print "<b>Total Periodo por Banco: " . ($MontoT - $MontoMT) . "</b><br>";
print "<b>Total Periodo por Caja: " . $MontoMT . "</b><br><br><br>";
?>
