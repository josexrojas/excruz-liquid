<?php
include("header.php");

$Categoria = LimpiarVariable($_REQUEST['id']);
$HorasDiarias = LimpiarVariable($_REQUEST['hrs']);
$Descripcion = LimpiarVariable($_REQUEST['desc']);
$SueldoBasico = LimpiarVariable($_REQUEST['Sb']);
$Bonificacion = LimpiarVariable($_REQUEST['B']);
$Cargo = LimpiarVariable($_REQUEST['idcargo']);
$Action = LimpiarVariable($_REQUEST['action']);

if (!($db = Conectar()))
	exit;

$EmpresaID = $_SESSION["EmpresaID"];
$SucursalID = $_SESSION["SucursalID"];

$SEGURIDAD_MODULO_ID = 6;

include 'seguridad.php';

?>
<h1><img src="images/icon64_empleados.gif" width="64" height="64" align="absmiddle" /> Actualizar Sueldos B&aacute;sicos</h1>

<form action="categorias_edit.php" method="post" name="frmCat">
<input type="hidden" name="action" value="update">
<input type="hidden" value="<?=$Categoria;?>" name="id">
<input type="hidden" value="<?=$Cargo;?>" name="idcargo">
<input type="hidden" value="<?=$Descripcion;?>" name="desc">
<input type="hidden" name="hrs" value="<?=$HorasDiarias;?>">

<table>
	<tr>
		<td><p>Categor&iacute;a: </p></td>
		<td><input type="text" disabled="disabled" value="<?=$Categoria;?>" size="4"></td>
	</tr>
	<tr>
		<td><p>Descripción: </p></td>
		<td><input type="text" disabled="disabled" value="<?=$Descripcion;?>" size="50"></td>
	</tr>
	<tr>
		<td><p>Cantidad de Horas: </p></td>
		<td><input type="text" disabled="disabled" value="<?=$HorasDiarias;?>" size="2"></td>
	</tr>
	<tr>
		<td><p>Sueldo B&aacute;sico: </p></td>
		<td><input type="text" value="<?=$SueldoBasico;?>" name="Sb"></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td><a href="javascript: frmCat.submit();" class="tecla"><img src="images/icon24_grabar.gif" alt="Volver" width="24" height="23" border="0" align="absmiddle">Aceptar</a></td>
		<td><a href="categorias_edit.php" class="tecla"><img src="images/icon24_prev.gif" alt="Volver" width="24" height="23" border="0" align="absmiddle">  Volver&nbsp;</a></td>
	</tr>
</table>
</form>


<?php 
include("footer.php");
?>
