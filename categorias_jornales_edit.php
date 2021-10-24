<?php
include("header.php");

$Concepto = $_REQUEST['id'];
$Alias = $_REQUEST['As'];
$Descripcion = $_REQUEST['desc'];
$SueldoBasico = $_REQUEST['Sb'];
$Action = $_REQUEST['action'];

if (!($db = Conectar()))
	exit;

$EmpresaID = $_SESSION["EmpresaID"];
$SucursalID = $_SESSION["SucursalID"];

$SEGURIDAD_MODULO_ID = 6;

include 'seguridad.php';

?>
<h1><img src="images/icon64_empleados.gif" width="64" height="64" align="absmiddle" /> Actualizar B&aacute;sico Jornal</h1>

<form action="categorias_jornales.php" method="post" name="frmCon">
<input type="hidden" name="action" value="update">
<input type="hidden" value="<?=$Concepto;?>" name="id">
<input type="hidden" value="<?=$Alias;?>" name="As">
<input type="hidden" value="<?=$Descripcion;?>" name="desc">

<table>
	<tr>
		<td><p>Concepto: </p></td>
		<td><input type="text" disabled="disabled" value="<?=$Concepto;?>" size="4"></td>
	</tr>
	<tr>
		<td><p>Alias: </p></td>
		<td><input type="text" disabled="disabled" value="<?=$Alias;?>"></td>
	</tr>
	<tr>
		<td><p>Descripción: </p></td>
		<td><input type="text" disabled="disabled" value="<?=$Descripcion;?>"></td>
	</tr>
	<tr>
		<td><p>B&aacute;sico Jornal: </p></td>
		<td><input type="text" value="<?=$SueldoBasico;?>" size="2" name="Sb"></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td><a href="javascript: frmCon.submit();" class="tecla"><img src="images/icon24_grabar.gif" alt="Volver" width="24" height="23" border="0" align="absmiddle">Aceptar</a></td>
		<td><a href="categorias_jornales.php" class="tecla"><img src="images/icon24_prev.gif" alt="Volver" width="24" height="23" border="0" align="absmiddle">  Volver&nbsp;</a></td>
	</tr>
</table>
</form>

<?php 
include("footer.php");
?>