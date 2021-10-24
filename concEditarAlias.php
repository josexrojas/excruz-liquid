<? include('header.php');

if (!($db = Conectar()))
	exit;

$EmpresaID = $_SESSION["EmpresaID"];
$SucursalID = $_SESSION["SucursalID"];
if ($_SESSION["LegajoNumerico"] == '1'){
	$sqlLegajo = "to_number(em.\"Legajo\", '999999') AS \"Legajo\"";
}else{
	$sqlLegajo = "em.\"Legajo\"";
}

$SEGURIDAD_MODULO_ID = 2;

include 'seguridad.php';

$accion = LimpiarVariable($_POST["accion"]);

?>
<script>
	var bDescripcion = false;
	function Aceptar(){
		if (!ChequearSeguridad(1))
			return false;
		document.frmEditarAlias.accion.value = 'Aceptar';
		document.frmEditarAlias.submit();
	}
	function CambioDescripcion(){
		if (bDescripcion)
			return true;
		bDescripcion = true;
		alert('Si cambia la descripcion de este concepto, se vera reflejada en las proximas liquidaciones y la descripcion anterior ya no sera valida');
	}
	function CambioActivo(iCual, iOrg){
		if (iOrg == 1){
			if (iCual == 2){
				alert('Si desactiva este concepto mientras haya novedades del mismo vigentes, estan seran eliminadas');
			}
		}
	}
</script>
<H1 style="display:inline"><img src="images/icon64_conceptosalias.gif" width="64" height="64" align="absmiddle" /> Editar Alias de Concepto</h1>
<br><br>
<form name=frmEditarAlias action=concEditarAlias.php method=post>
<input type=hidden name=accion id=accion>
<?
if ($accion == 'Aceptar'){
	if (!ChequearSeguridad(1))
		exit;
	
	$AID = LimpiarNumero($_POST["selConcepto"]);
	$Desc = LimpiarVariable($_POST["Descripcion"]);
	$Pri = intval(LimpiarNumero($_POST["chkPrimera"]));
	$Seg = intval(LimpiarNumero($_POST["chkSegunda"]));
	$Men = intval(LimpiarNumero($_POST["chkMensual"]));
	$SAC = intval(LimpiarNumero($_POST["chkSAC"]));
	$Vac = intval(LimpiarNumero($_POST["chkVacacion"]));
	$Loc = intval(LimpiarNumero($_POST["chkLocacion"]));
	$Esp = intval(LimpiarNumero($_POST["chkEspecial"]));
	$CodIPS = LimpiarNumero($_POST["CodIPS"]);
	$TipIPS = LimpiarVariable($_POST["selTipConIPS"]);
	$Vig = LimpiarNumero($_POST["selVigencia"]);
	$Act = LimpiarNumero($_POST["selActivo"]);
	// Funcion -> funcion asociada, no cambia
	// param$i -> parametros asociados, no cambian

	if ($Pri == '') $Pri = 0;
	if ($Seg == '') $Seg = 0;
	if ($Men == '') $Men = 0;
	if ($SAC == '') $SAC = 0;
	if ($Vac == '') $Vac = 0;
	if ($Loc == '') $Loc = 0;
	if ($Esp == '') $Esp = 0;
	$Liquida = $Pri + $Seg + $Men + $SAC + $Vac + $Loc + $Esp;

	if ($Desc == '' || $Liquida < 1 || $Act == '')
		exit;

	if ($Vig == 1)
		$Dur = 1;
	else if ($Vig == 2)
		$Dur = 2;
	else
		$Dur = 3;

	if ($Act == 1)
		$Act = 'true';
	else
		$Act = 'false';

	if (!pg_exec($db, "UPDATE \"tblConceptosAlias\" SET \"Descripcion\" = '$Desc', \"Liquida\" = $Liquida,
\"CodigoIPS\" = '$CodIPS', \"TipoConceptoIPS\" = '$TipIPS', \"DuracionConcepto\" = $Dur, \"Activo\" = $Act
WHERE \"EmpresaID\" = $EmpresaID AND \"AliasID\" = $AID"))
		Alerta('Se produjo un error al actualizar el concepto');
	else
		Alerta('El concepto fue actualizado correctamente');
	$accion = '';
}

if ($accion == ''){
?>
<table class="datauser">
	<TR>
		<TD class="izquierdo">Seleccione un Concepto:</td><TD class=derecho>
		<select id=selConcepto name=selConcepto onchange="document.frmEditarAlias.submit();"> 
<?
	$AID = LimpiarNumero($_POST["selConcepto"]);
	$rs = pg_query($db, "
SELECT ca.\"AliasID\", ca.\"Descripcion\", ca.\"Liquida\", ca.\"CodigoIPS\", ca.\"TipoConceptoIPS\", ca.\"DuracionConcepto\",
ca.\"Activo\", co.\"Funcion\", co.\"ConceptoID\", co.\"Obligatorio\"
FROM \"tblConceptosAlias\" ca
INNER JOIN \"tblConceptos\" co
ON co.\"ConceptoID\" = ca.\"ConceptoID\" AND co.\"Activo\" = true
WHERE ca.\"EmpresaID\" = $EmpresaID AND ca.\"Liquida\" > 0 ORDER BY 2");
	if (!$rs)
		exit;
	while($row = pg_fetch_array($rs))
	{
		$AliasID = $row[0];
		print "<option value=\"$AliasID\"";
		if ($AID == '' || $AID == $row[0]){
			if ($AID == '')
				$AID = $AliasID;
			print " selected";
			$Descripcion = $row[1];
			$Liquida = $row[2];
			$CodigoIPS = $row[3];
			$TipoConceptoIPS = $row[4];
			$DuracionConcepto = $row[5];
			$Activo = $row[6];
			$Funcion = $row[7];
			$ConceptoID = $row[8];
			$Obligatorio = ($row[9] == 't' ? true : false);
			if ($Obligatorio)
				$DuracionConcepto = '3';
		}
		print ">$row[1]</option>";
	}
?>
		</select></td>
	</TR>
	<TR>
		<TD class="izquierdo">Descripci&oacute;n:</td>
		<TD class=derecho2><input type=text id=Descripcion name=Descripcion value="<?=$Descripcion?>" size=64 onkeyup="CambioDescripcion();"></td>
	</TR>
	<TR>
		<TD class="izquierdo">Cuando Liquida:</td>
		<TD class=derecho2>
		<input type=checkbox id=chkPrimera name=chkPrimera value=2 <? print (($Liquida & 2) == 2 ? 'checked' : ''); ?>>Primera Quincena
		<input type=checkbox id=chkSegunda name=chkSegunda value=4 <? print (($Liquida & 4) == 4 ? 'checked' : ''); ?>>Segunda Quincena<br>
		<input type=checkbox id=chkMensual name=chkMensual value=1 <? print (($Liquida & 1) == 1 ? 'checked' : ''); ?>>Mensual
		<input type=checkbox id=chkSAC name=chkSAC value=8 <? print (($Liquida & 8) == 8 ? 'checked' : ''); ?>>Aguinaldo
		<input type=checkbox id=chkVacacion name=chkVacacion value=16 <? print (($Liquida & 16) == 16 ? 'checked' : ''); ?>>Vacaciones
		<input type=checkbox id=chkLocacion name=chkLocacion value=32 <? print (($Liquida & 32) == 32 ? 'checked' : ''); ?>>Locacion de obra
		<input type=checkbox id=chkEspecial name=chkEspecial value=64 <? print (($Liquida & 64) == 64 ? 'checked' : ''); ?>>Especial
		</TD>
	</TR>
	<TR>
		<TD class="izquierdo">C&oacute;digo IPS:</td>
		<TD class=derecho><input type=text id=CodIPS name=CodIPS value="<?=$CodigoIPS?>"></td>
	</TR>
	<TR>
		<TD class="izquierdo">Tipo Concepto IPS:</td>
		<TD class=derecho><select id=selTipConIPS name=selTipConIPS>
			<option value='' <? print ($TipoConceptoIPS == '' ? 'selected' : ''); ?>>-- No asignado --</option>
			<option value=RCA <? print ($TipoConceptoIPS == 'RCA' ? 'selected' : ''); ?>>Remuneraciones con Aportes</option>
			<option value=RSA <? print ($TipoConceptoIPS == 'RSA' ? 'selected' : ''); ?>>Remuneraciones sin Aportes</option>
			<option value=DPS <? print ($TipoConceptoIPS == 'DPS' ? 'selected' : ''); ?>>Descuento Previsional (IPS)</option>
			<option value=DAS <? print ($TipoConceptoIPS == 'DAS' ? 'selected' : ''); ?>>Descuento Asistencial (IOMA)</option>
			<option value=AFA <? print ($TipoConceptoIPS == 'AFA' ? 'selected' : ''); ?>>Asignaciones Familiares</option>
			<option value=DES <? print ($TipoConceptoIPS == 'DES' ? 'selected' : ''); ?>>Otros Descuentos</option>
		</select></td>
	</TR>
	<TR>
		<TD class="izquierdo">Vigencia del Concepto:</td>
		<TD class=derecho><select id=selVigencia name=selVigencia <? print ($Obligatorio ? 'disabled' : ''); ?>>
			<option value=1 <? print ($DuracionConcepto == '1' ?  'selected' : ''); ?>>Definida por el usuario</option>
			<option value=2 <? print ($DuracionConcepto == '2' ?  'selected' : ''); ?>>Definida por la liquidacion</option>
			<option value=3 <? print ($DuracionConcepto == '3' ?  'selected' : ''); ?>>Duracion Indefinida</option>
		</td>
	</TR>
	<TR>
		<TD class="izquierdo">Concepto-Alias Activo:</td>
		<TD class=derecho><select id=selActivo name=selActivo onchange="CambioActivo(this.value, '<? print ($Activo == true ? '1' : '2'); ?>');">
			<option value=1 <? print ($Activo == true ?  'selected' : ''); ?>>Si</option>
			<option value=2 <? print ($Activo == false ?  'selected' : ''); ?>>No</option>
		</td>
	</TR>
	<TR>
		<TD class="izquierdo">Funci&oacute;n Asociada:</td>
		<TD class=derecho><input type=text id=Funcion name=Funcion value="<?=$Funcion?>" size=30 disabled></td>
	</TR>
	<TR>
		<TD colspan=2><br><B>Parametros</B></td>
	</TR>
	<TR>
		<TD colspan=2><br>Los siguientes son los parametros que toma la funci&oacute;n asociada a este concepto.<br>
		La descripci&oacute;n de los mismos no puede ser modificada directamente, para ello se debe asociar a otra
		funci&oacute;n.<br>
		Los valores que no se cargan aqu&iacute; son aquellos que estar&aacute;n disponibles al momento de cargar la novedad.
		<br><br></td>
	</TR>
<?
	$rs = pg_query($db, "
SELECT cp.\"ParametroID\", cp.\"Descripcion\", cpv.\"Valor\"
FROM \"tblConceptosParametros\" cp
LEFT JOIN \"tblConceptosParametrosValores\" cpv
ON cpv.\"EmpresaID\" = cp.\"EmpresaID\" AND cpv.\"ConceptoID\" = cp.\"ConceptoID\" AND cpv.\"AliasID\" = $AID
AND cp.\"ParametroID\" = cpv.\"ParametroID\"
WHERE cp.\"EmpresaID\" = $EmpresaID AND cp.\"ConceptoID\" = $ConceptoID ORDER BY 1");
	if (!$rs)
		exit;
	if (pg_numrows($rs) > 0){
		while($row = pg_fetch_array($rs)){
			$i = $row[0];
?>
		<TR>
			<TD class="izquierdo">Descripci&oacute;n:</td>
			<TD class=derecho><input type=text value="<?=$row[1]?>" size=48 disabled></td>
		</TR>
		<TR>
			<TD class="izquierdo">Valor Fijo:</td>
			<TD class=derecho><input type=text id=param<?=$i?> name=param<?=$i?> value="<?=$row[2]?>" disabled></td>
		</TR>
<?
		}
		print "</TABLE>\n";
	}else{
		print "</TABLE>\n";
		Alerta('Este concepto no tiene parametros asociados');
	}
?>
	<br><br>
	<center>
	<a href="javascript:Aceptar(); void(0);" class="tecla"> 
	<img src="images/icon24_grabar.gif" alt="Aceptar" width="24" height="23" border="0" align="absmiddle">  Aceptar </a>
	&nbsp;&nbsp;&nbsp;<a href="conceptos.php" class="tecla"> 
	<img src="images/icon24_prev.gif" alt="Volver" width="24" height="23" border="0" align="absmiddle">  Volver </a>
	</center>
<?
}
print "</form>\n";
pg_close($db);
include('footer.php'); ?>
