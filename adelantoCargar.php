<? include("header.php");

if (!($db = Conectar()))
	exit;

$Legajo		= LimpiarVariable($_REQUEST['Legajo']);
$EmpresaID	= 1;
$SucursalID	= 1;
$Monto		= LimpiarNumero2(isset($_REQUEST['Monto']) ? $_REQUEST['Monto'] : 0);
$Cuotas		= LimpiarNumero(isset($_REQUEST['Cuotas']) ? $_REQUEST['Cuotas'] : 0);
$Usuario	= LimpiarNumero($_SESSION['ID']);

$SEGURIDAD_MODULO_ID = 6;

include 'seguridad.php';

if ($_REQUEST['sent'])
{
	$sql = "SELECT \"CargarAdelanto\"('$Legajo', $EmpresaID, $SucursalID, $Monto, $Cuotas, $Usuario);";	
	print $sql;
	$rs = pg_query($db, $sql);
	$row = pg_fetch_array($rs);

	if (!$row)
	{
		?>
		<script>
		alert('Verifique que ha cargado todos los campos');
		</script>
		<?
	}
	else
	{
		switch ($row[0])
		{
		case 0:
			?>
			<script>
			alert('Adelanto cargado satisfactoriamente');
			window.location.href = 'adelantoVerPendiente.php';
			</script>
			<?
			exit;

		case -1:
			?>
			<script>
			alert('El monto no puede ser negativo ni cero');
			</script>
			<?
			break;

		case -2:
			?>
			<script>
			alert('La cantidad de cuotas no puede superar la cantidad de meses que hay hasta diciembre. Ni ser cero.');
			</script>
			<?
			break;

		case -3:
			?>
			<script>
			alert('El empleado no existe');
			</script>
			<?
			break;

		}
	}
}

?>
<H1><img src="images/icon64_novedadesSola.gif" width="64" height="64" align="absmiddle" />Carga de adelantos de sueldo</H1>

<form method="post">
<input type="hidden" id="sent" name="sent" value="1" />
<table>
	<tr>
		<td>Legajo</td>
		<td><input type="text" id="Legajo" name="Legajo" value="<?=$Legajo?>" size="10" maxlength="5" /></td>
	</tr>
	<tr>
		<td>Monto de adelanto</td>
		<td><input type="text" id="Monto" name="Monto" value="<?=$Monto?>" maxlength="7" /></td>
	</tr>
	<tr>
		<td>Cantidad de cuotas<br>
			<font size="-2">
				(La cant. de cuotas no puede ser mayor<br> a la cantidad de meses que hay hasta diciembre)
			</font>
		</td>
		<td><input type="text" id="Cuotas" name="Cuotas" value="<?=$Cuotas?>" maxlength="2" /></td>
	</tr>
	
	<tr>
		<td colspan="2">
			<input type="submit" value="Cargar" />
		</td>
	</tr>
	
</table>
</form>
