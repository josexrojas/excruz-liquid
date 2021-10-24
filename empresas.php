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
	$v1 = LimpiarVariable($_POST["Descripcion"]);
	$v2 = LimpiarVariable($_POST["CliID"]);
	if ($v1 == "" || $v2 == "")
		$err = "Debe completar empresa y cliente";
	else{
		if ($ID == 0){
			// Agrega nuevo
			if (!pg_exec($db, "INSERT INTO \"tblEmpresas\" (\"Descripcion\", \"ClienteID\") VALUES ('$v1', $v2)")){
				$err = "Se produjo un error interno al agregar la empresa";
			}else{
				$err = "La empresa se agrego con exito";
			}
		}else if ($ID > 0){
			// Editar
			if (!pg_exec($db, "UPDATE \"tblEmpresas\" SET \"Descripcion\" = '$v1', \"ClienteID\" = $v2 WHERE \"EmpresaID\" = $ID")){
				$err = "Se produjo un error interno al actualizar la empresa";
			}else{
				$err = "La empresa se actualizo con exito";
			}
		}
	}
}else if (isset($accion) && $accion == 'Borrar Empresa')
{
	$ID = LimpiarNumero($_POST['ID']);
	if (!pg_exec($db, "DELETE FROM \"tblEmpresas\" WHERE \"EmpresaID\" = $ID")){
		$err = "Se produjo un error interno al borrar la empresa";
	}else{
		$err = "La empresa se borro con exito";
	}
}
$rs = pg_query($db, "SELECT em.\"EmpresaID\", em.\"Descripcion\", em.\"ClienteID\", cl.\"Apellido\" || ', ' || cl.\"Nombre\" AS \"ApeYNom\" FROM \"tblEmpresas\" em INNER JOIN \"tblClientes\" cl ON em.\"ClienteID\" = cl.\"ClienteID\"");
if (!$rs)
{
	pg_close($db);
	header("Location: /admin/error.php");
	exit;
}
$rs1 = pg_query($db, "SELECT cl.\"ClienteID\", cl.\"Apellido\" || ', ' || cl.\"Nombre\" AS \"ApeYNom\" FROM \"tblClientes\" cl");
if (!$rs1)
{
	pg_close($db);
	header("Location: /admin/error.php");
	exit;
}
?>

<font color=red size=4><?=$err?></font>

<form name=frmEmpresas action=empresas.php method=post>
<script language="JavaScript">
	function SetVals(ID, v1, v2)
	{
		document.getElementById("ID").value = ID;
		document.getElementById("Descripcion").value = v1;
		document.getElementById("CliID").value = v2;
	}
	function SetID(ID)
	{
		document.getElementById("ID").value = ID;
	}
	function AgregarEmpresa()
	{
		EditarEmpresa(0, '', '');
	}
	function EditarEmpresa(ID, v1, v2)
	{
		for(i=0;i<document.frmEmpresas.selID.options.length;i++){
			if (document.frmEmpresas.selID.options[i].value == v2){
				document.frmEmpresas.selID.selectedIndex = i;
				break;
			}
		}
		SetVals(ID, v1, v2);
		document.getElementById('listaEmpresa').style.display = 'none';
		document.getElementById('edicionEmpresa').style.display = 'block';
	}
	function Cancelar()
	{
		document.getElementById('listaEmpresa').style.display = 'block';
		document.getElementById('edicionEmpresa').style.display = 'none';
	}
</script>

<input type=hidden id=ID name=ID>
<input type=hidden id=CliID name=CliID>
<div id=edicionEmpresa style="display:none">

	Descripcion: <input type=text id=Descripcion name=Descripcion value="<?=$Descripcion?>"><br>
	Cliente: <select id=selID>
<?
while($row = pg_fetch_array($rs1))
{
	$ClienteID = $row[0];
	$Cliente = $row[1];
	print "<option value=\"$ClienteID\">$Cliente</option>";
}
?>
	</select>
	<input type=submit id=accion name=accion value="Aceptar"
		onclick="javascript:document.getElementById('CliID').value=document.frmEmpresas.selID.options[document.frmEmpresas.selID.selectedIndex].value;">
	<input type=button id=accion name=accion value="Cancelar"
		onClick="javascript:Cancelar();">
</div>
<div id=listaEmpresa style="display:block">
<input type=button id=accion name=accion value="Agregar Empresa"
	onClick="javascript:AgregarEmpresa();">
<table>
	<tr>
		<td>Descripcion</td>
		<td>Cliente</td>
		<td>Accion</td>
	</tr>
<?
while($row = pg_fetch_array($rs))
{
	$EmpID = $row[0];
	$Descripcion = $row[1];
	$CliID = $row[2];
	$Cliente = $row[3];
	?>

	<tr>
		<td><?=$Descripcion?></td><td><?=$Cliente?></td><td>
		<input type=button id=accion name=accion value="Editar Empresa" 
			onClick="javascript:EditarEmpresa(<?=$EmpID?>,'<?=$Descripcion?>','<?=$CliID?>');">
		<input type=submit id=accion name=accion value="Borrar Empresa" 
			onClick="javascript:SetID(<?=$EmpID?>);"></td>
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
