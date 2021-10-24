<?
include("header.php");

if (!($db = Conectar()))
	exit;

?>

<div id=divLoading style="display:block">
<table height=100% align=center valign=center>
<tr><td><img src="images/icon32_process.gif" align=absmiddle width=32 height=32 border=0> Generando Listado</td></tr>
</table>
</div>
<form name=frmListadoCumples action=listadoCumples.php method=post>
<input type=hidden name=accion id=accion>
<input type=hidden name=listado id=listado>

<?

$rs = pg_query($db, "
SELECT	e.\"Legajo\", 
	e.\"Nombre\", 
	e.\"Apellido\", 
	'Empleado con jurisdiccion, programa y/o actividad incorrecto' AS \"DatosIncorrectos\"
FROM \"tblEmpleados\" e 
LEFT JOIN \"tblEmpleadosRafam\" er ON e.\"Legajo\"= er.\"Legajo\" 
LEFT JOIN owner_rafam.estruc_prog ep ON er.jurisdiccion = ep.jurisdiccion AND er.programa = ep.programa AND er.activ_proy = ep.activ_proy AND 2013 = ep.anio_presup 
WHERE ep.jurisdiccion IS NULL AND e.\"FechaEgreso\" IS NULL
UNION
SELECT 	e.\"Legajo\", 
	e.\"Nombre\", 
	e.\"Apellido\", 
	'Empleado sin Fuente de financiamiento' AS \"DatosIncorrectos\" 
FROM \"tblEmpleados\" e 
INNER JOIN \"tblEmpleadosRafam\" er ON e.\"Legajo\" = er.\"Legajo\" 
WHERE er.codigo_ff IS NULL 
");

if (!$rs)
	exit;
?>
<H1>Listado de empleados con datos de RAFAM incorrectos</H1>
	<a class="tecla" href='javascript:window.print(); void(0);'> 
	<img src="images/icon24_printlistado.gif" alt="Imprimir Listado" width="24" height="24" border="0" align="absmiddle">
	Imprimir Listado</a>&nbsp;<BR><br>
<?
	$Abrir = 1;
	while($row = pg_fetch_array($rs))
	{
		$i = 0;
		$Legajo = $row[$i++];
		$Nombre = $row[$i++];		
		$Apellido = $row[$i++];
		$DatosIncorrecto = $row[$i++];
		
		$ApeyNom = $Apellido . ' ' . $Nombre;

		if ($Abrir == 1){
			$Abrir = 0;
?>
			<table width="100%" border="0" cellpadding="5" cellspacing="1" class="datagrid">
			<tr><th>Legajo</th><th>Apellido y Nombre</th><th>Datos Incorrectos</th></tr>
<?
		}
?>
		<tr><td><?=$Legajo?></td><td><?=$ApeyNom?></td><td><?=$DatosIncorrecto?></td></tr>
<?
	}
	print "</table>\n";

pg_close($db);
?>
</form>
<script>
	document.getElementById('divLoading').style.display = 'none';
</script>
<? include("footer.php"); ?>
