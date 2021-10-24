<?
require 'funcs.php';

session_start();

if ($_SESSION["logged"] != '1')
        header("Location: /login.php");

if (!($db = Conectar()))
	exit;

$accion = LimpiarVariable($_POST["accion"]);
if (isset($accion) && $accion == 'Aceptar')
{
	// Agregar o Editar cliente
	$ID = LimpiarNumero($_POST['ID']);
	$v1 = LimpiarVariable($_POST["Nombre"]);
	$v2 = LimpiarVariable($_POST["Apellido"]);

	if ($v1 == "" || $v2 == "")
		$err = "Debe completar nombre y apellido";
	else{
		if ($ID == 0){
			// Agrega nuevo
			if (!pg_exec($db, "INSERT INTO \"tblClientes\" (\"Nombre\", \"Apellido\") VALUES ('$v1', '$v2')")){
				$err = "Se produjo un error interno al agregar el cliente";
			}else{
				$err = "El cliente se agrego con exito";
			}
		}else if ($ID > 0){
			// Editar
			if (!pg_exec($db, "UPDATE \"tblClientes\" SET \"Nombre\" = '$v1', \"Apellido\" = '$v2' WHERE \"ClienteID\" = $ID")){
				$err = "Se produjo un error interno al actualizar el cliente";
			}else{
				$err = "El cliente se actualizo con exito";
			}
		}
	}
}else if (isset($accion) && $accion == 'Borrar Cliente')
{
	$ID = LimpiarNumero($_POST['ID']);
	if (!pg_exec($db, "DELETE FROM \"tblClientes\" WHERE \"ClienteID\" = $ID")){
		$err = "Se produjo un error interno al borrar el cliente";
	}else{
		$err = "El cliente se borro con exito";
	}
}
$rs = pg_query($db, "SELECT \"ClienteID\", \"Nombre\", \"Apellido\" FROM \"tblClientes\" ORDER BY \"Apellido\", \"Nombre\"");
if (!$rs)
{
	pg_close($db);
	header("Location: /admin/error.php");
	exit;
}
?>

<font color=red size=4><?=$err?></font>

<form name=frmClientes action=clientes.php method=post>
<script language="JavaScript">
	function SetVals(CliID, sNombre, sApellido)
	{
		document.getElementById("ID").value = CliID;
		document.getElementById("Nombre").value = sNombre;
		document.getElementById("Apellido").value = sApellido;
	}
	function SetID(CliID)
	{
		document.getElementById("ID").value = CliID;
	}
	function AgregarCliente()
	{
		EditarCliente(0, '', '');
	}
	function EditarCliente(CliID, sNombre, sApellido)
	{
		SetVals(CliID, sNombre, sApellido);
		document.getElementById('listaClientes').style.display = 'none';
		document.getElementById('edicionCliente').style.display = 'block';
	}
	function Cancelar()
	{
		document.getElementById('listaClientes').style.display = 'block';
		document.getElementById('edicionCliente').style.display = 'none';
	}
</script>

<input type=hidden id=ID name=ID>
<div id=edicionCliente style="display:none">

	Nombre: <input type=text id=Nombre name=Nombre value="<?=$Nombre?>"><br>
	Apellido: <input type=text id=Apellido name=Apellido value="<?=$Apellido?>"><br>

	<input type=submit id=accion name=accion value="Aceptar">
	<input type=button id=accion name=accion value="Cancelar"
		onClick="javascript:Cancelar();">
</div>
<div id=listaClientes style="display:block">
<input type=button id=accion name=accion value="Agregar Cliente"
	onClick="javascript:AgregarCliente();">
<table border=1>
	<tr>
		<td>Nombre</td>
		<td>Apellido</td>
		<td>Accion</td>
	</tr>
<?
while($row = pg_fetch_array($rs))
{
	$CliID = $row[0];
	$Nombre = $row[1];
	$Apellido = $row[2];
	?>

	<tr>
		<td><?=$Nombre?></td><td><?=$Apellido?></td><td>
		<input type=button id=accion name=accion value="Editar Cliente" 
			onClick="javascript:EditarCliente(<?=$CliID?>,'<?=$Nombre?>','<?=$Apellido?>');">
		<input type=submit id=accion name=accion value="Borrar Cliente" 
			onClick="javascript:SetID(<?=$CliID?>);"></td>
	</tr>
	<?
}
pg_close($db);

?>
</table>
</div>
<a href=sueldos.php>Volver</a>
</form>
</body>
</html>
