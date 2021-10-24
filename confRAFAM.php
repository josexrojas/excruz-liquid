<?
include("header.php");

if (!($db = Conectar()))
	exit;

$EmpresaID = $_SESSION["EmpresaID"];
$SucursalID = $_SESSION["SucursalID"];

$SEGURIDAD_MODULO_ID = 8;

include 'seguridad.php';

?>
<H1><img src="images/icon64_liquidacion.gif" width="64" height="64" align="absmiddle" /> Configuraci&oacute;n RAFAM</H1>
<form name=frmConfRafam action=confRAFAM.php method=post>
<?
$accion = LimpiarVariable($_POST["accion"]);

if ($accion == 'Aceptar'){
	$Host = LimpiarVariable($_POST["Host"]);
	$SID = LimpiarVariable($_POST["SID"]);
	$Usuario = LimpiarVariable($_POST["Usuario"]);
	$Password = LimpiarVariable($_POST["Password"]);
	if (ChequearSeguridad(2)){
		$rs = pg_query($db, "
SELECT count(1) FROM \"tblServidorRAFAM\" sr
WHERE sr.\"EmpresaID\" = $EmpresaID AND sr.\"SucursalID\" = $SucursalID");
		if (!$rs){
			exit;
		}
		$bError = true;
		$row = pg_fetch_array($rs);
		if ($row[0] == '1'){
			if (pg_exec($db, "
UPDATE \"tblServidorRAFAM\" 
SET \"Host\" = '$Host', \"Oracle_SID\" = '$SID', \"Usuario\" = '$Usuario'
".($Password == 'LiquidSueldosSETEADO' ? "":",\"Password\" = '$Password'")."
WHERE \"EmpresaID\" = $EmpresaID AND \"SucursalID\" = $SucursalID"))
				$bError = false;
		}else{
			if (pg_exec($db, "
INSERT INTO \"tblServidorRAFAM\" (\"EmpresaID\", \"SucursalID\", \"Host\", \"Oracle_SID\", \"Usuario\", \"Password\")
VALUES ($EmpresaID, $SucursalID, '$Host', '$SID', '$Usuario', '$Password')"))
				$bError = false;
		}
		if ($bError)
			Alerta('Hubo un error al actualizar la configuraci&oacute;n');
		else
			Alerta('La configuraci&oacute;n se actualiz&oacute; con &eacute;xito');
	}
	$accion = '';
}

if ($accion == 'Cancelar' || $accion == ''){
	// Tiene que seleccionar la liquidacion a realizar
	$rs = pg_query($db, "
SELECT sr.\"Host\", sr.\"Oracle_SID\", sr.\"Usuario\", sr.\"Password\"
FROM \"tblServidorRAFAM\" sr
WHERE sr.\"EmpresaID\" = $EmpresaID AND sr.\"SucursalID\" = $SucursalID
	");
	if (!$rs){
		exit;
	}
	$row = pg_fetch_array($rs);
	$Host = $row[0];
	$SID = $row[1];
	$Usuario = $row[2];
	$Password = $row[3];
	if ($Password != '')
		$Password = 'LiquidSueldosSETEADO';
	$bDisabled = false;
	if (!ChequearSeguridad(2) && !ChequearSeguridad(1)){
		$bDisabled = true;
		$Host = '--------';
		$SID = '--------';
		$Usuario = '--------';
		$Password = '--------';
	}
?>
<script>
	function Aceptar(){
		if (!ChequearSeguridad(2))
			return false;
		document.getElementById('accion').value = 'Aceptar';
		document.frmConfRafam.submit();
	}
	function Cancelar(){
		document.location.href='configuracion.php';
	}
</script>
	<table class=datauser align="left">
	<TR>
	<TD class="izquierdo">Host:</td><TD class=derecho2>
	<input type=text name=Host id=Host <? print ($bDisabled == true ? 'disabled' : ''); ?> value="<?=$Host?>">
	</td></tr>
	<TR>
	<TD class="izquierdo">Oracle SID:</td><TD class=derecho2>
	<input type=text name=SID id=SID <? print ($bDisabled == true ? 'disabled' : ''); ?> value="<?=$SID?>">
	</td></tr>
	<TR>
	<TD class="izquierdo">Usuario:</td><TD class=derecho2>
	<input type=text name=Usuario id=Usuario <? print ($bDisabled == true ? 'disabled' : ''); ?> value="<?=$Usuario?>">
	</td></tr>
	<TR>
	<TD class="izquierdo">Password:</td><TD class=derecho2>
	<input type=password name=Password id=Password <? print ($bDisabled == true ? 'disabled' : ''); ?> value="<?=$Password?>">
	</td></tr>
	<TR>
	<TD class="izquierdo"></td><TD class=derecho2>
	<input type=hidden id=accion name=accion>
	<input type=button value="Aceptar" onclick="javascript:Aceptar();">
	<input type=button value="Cancelar" onclick="javascript:Cancelar();"></td></tr></table>
<?
}
pg_close($db);
?>
</form>
<? include("footer.php"); ?>
