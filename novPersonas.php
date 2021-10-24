<? include("header.php");

if (!($db = Conectar()))
	exit;

$EmpresaID = $_SESSION["EmpresaID"];
$SucursalID = $_SESSION["SucursalID"];

$SEGURIDAD_MODULO_ID = 6;

include 'seguridad.php';

?>
<H1><img src="images/icon64_novedadesSola.gif" width="64" height="64" align="absmiddle" /> Novedades por Persona</H1>
<?

if ($_SESSION["NumeroLiquidacion"] != ''){
	print "<b>N&uacute;mero de Liquidaci&oacute;n Activa:" . $_SESSION["NumeroLiquidacion"];
	print "</b><br>Las novedades a cargar corresponden a la liquidaci&oacute;n arriba mencionada.";
	print "<br>Si esto no es as&iacute; puede cambiarla desde el men&uacute; per&iacute;odos<br>";
}

?>
<script language="JavaScript">
	function BorrarNovedad(AliasID, Ajuste)
	{
		if (!confirm("Esta seguro que quiere borrar esta novedad?"))
			return false;
		document.frmNovedades.accion.value = 'Borrar Novedad';
		document.frmNovedades.CID.value = AliasID;
		document.frmNovedades.Ajuste.value = Ajuste;
		document.frmNovedades.submit();
	}
	function ActualizarNovedad(AliasID, Ajuste)
	{
		document.frmNovedades.accion.value = 'Actualizar Novedad';
		document.frmNovedades.CID.value = AliasID;
		document.frmNovedades.Ajuste.value = Ajuste;
		document.frmNovedades.submit();
	}
	function HabilitarNovedad(AliasID, Ajuste)
	{
		document.frmNovedades.accion.value = 'Habilitar Novedad';
		document.frmNovedades.CID.value = AliasID;
		document.frmNovedades.Ajuste.value = Ajuste;
		document.frmNovedades.submit();
	}
	function DeshabilitarNovedad(AliasID, Ajuste)
	{
		document.frmNovedades.accion.value = 'Deshabilitar Novedad';
		document.frmNovedades.CID.value = AliasID;
		document.frmNovedades.Ajuste.value = Ajuste;
		document.frmNovedades.submit();
	}
	function AgregarNovedad(iCual)
	{
		if (iCual == 0){
			document.frmNovedades.CID.value = '';
			document.frmNovedades.accion.value = 'Agregar Novedad';
			submitEmpleado();
		}else{
			var bError = false;
			try {
				var FechaDesde = new String(document.getElementById('ParamDesde').value);
				var FechaHasta = new String(document.getElementById('ParamHasta').value);
				var dDesde = FechaDesde.split("-");
				var dHasta = FechaHasta.split("-");

				if (dHasta[2] < dDesde[2]){
					bError = true;
				}else{
					if (dHasta[2] == dDesde[2]){
						if (dHasta[1] < dDesde[1]){
							bError = true;
						}else{
							if (dHasta[1] == dDesde[1] && dHasta[0] < dDesde[0])
								bError = true;
						}
					}
				}
			} catch(e) {}
			if (bError){
				alert('La fecha de finalizacion no puede ser menor a la fecha de comienzo');
				return;
			}
			document.frmNovedades.CID.value = document.frmNovedades.selConcepto.options[document.frmNovedades.selConcepto.selectedIndex].value;
			document.frmNovedades.accion.value = 'Aceptar';
		}
		document.frmNovedades.submit();
	}

	var objXMLHttp;
	if (window.ActiveXObject){ //for IE
		objXMLHttp = new ActiveXObject("Microsoft.XMLHTTP");
	} else if (window.XMLHttpRequest){ //for Mozilla
		objXMLHttp = new XMLHttpRequest();
	}

	function Actualizar(AID, ID, Valor, index)
	{
		var objXML;
		var obj;

		objXMLHttp.open("GET", "/UpdateAlias.php?AID="+AID+"&ID="+ID+"&Valor="+Valor, false, "", "");
		if (window.XMLHttpRequest){
			objXMLHttp.send(null);
		}else{
			objXMLHttp.send();
		}
		if ((objXMLHttp.readyState==4) && (objXMLHttp.status==200)){
			document.getElementById('dvTexto'+AID).innerHTML = objXMLHttp.responseTEXT;
			index++;
			obj = document.getElementById('EdicionRapida'+index);
			if (obj != null)
				obj.focus();
				obj.select();
			//document.getElementById('tdDesc'+AID).style.fontWeight = 'bold';
			return true;
		}
		return false;
	}

	function ObtenerXML(CID, Agregar, ID, Ajuste)
	{
		var objXML;

		objXMLHttp.open("GET", "/ParametrosConcepto.php?CID="+CID+"&Agregar="+Agregar+"&ID="+ID+"&Ajuste="+Ajuste, false, "", "");
		if (window.XMLHttpRequest){
			objXMLHttp.send(null);
		}else{
			objXMLHttp.send();
		}
		if ((objXMLHttp.readyState==4) && (objXMLHttp.status==200)){
			objXML = objXMLHttp.responseTEXT;
			return objXML;		
		}
		return false;
	}
	function VerAjuste(){
		var dvAjuste = document.getElementById('dvAjuste');
		if (dvAjuste.style.display=='none'){
			dvAjuste.style.display='block';
			dvParametros.style.display='none';
		}else{
			dvAjuste.style.display='none';
			dvParametros.style.display='block';
		}
	}
	function CargarParametrosConcepto(CID, Agregar, ID, Ajuste)
	{
		var sTexto = ObtenerXML(CID, Agregar, ID, Ajuste);
	
		document.getElementById('dvCamposForm').innerHTML = sTexto;
	}
	function CambioNumCID(txtCID, Agregar, ID, Ajuste)
	{
		if (window.event.keyCode == 13){
			var i, sel;

			sel = document.getElementById('selConcepto').options;
			for(i=0; i<sel.length; i++){
				if (sel[i].value == txtCID.value){
					sel[i].selected = true;
					break;
				}
			}
			if (i == sel.length){
				alert('El numero de concepto ingresado no existe');
				return;
			}
			CargarParametrosConcepto(txtCID.value, Agregar, ID, Ajuste);
		}
	}
	function VerNeto()
	{
		var sValor;

		if (!ChequearSeguridad(1))
			return;

		if (document.getElementById('txtLegajo').value == ''){
			sValor = document.getElementById('selLegajo').options[document.getElementById('selLegajo').selectedIndex].value;
		}else{
			sValor = document.getElementById('txtLegajo').value;
		}
		document.getElementById('ID').value = sValor;
		document.getElementById('accion').value = 'VerNeto';
		document.frmNovedades.submit();
	}
	function IECapturarTeclas(e)
	{
		if (window.event.shiftKey){
			if (window.event.keyCode == 65 || window.event.keyCode == 97){
				// Agregar novedades
				if (document.getElementById('txtCID') == null){
					AgregarNovedad(0);
				}else{
					AgregarNovedad(1);
				}
				return;
			}
			if (window.event.keyCode == 69 || window.event.keyCode == 101){
				// Liquidar y ver neto
				VerNeto();
				return;
			}
			if (window.event.keyCode == 76 || window.event.keyCode == 108){
				// Ir a numero de legajo
				document.getElementById('txtLegajo').focus();
				window.event.keyCode = 0;
				return;
			}
			if (window.event.keyCode == 78 || window.event.keyCode == 110){
				// Ver novedades
				submitEmpleado();
				return;
			}
			if (window.event.keyCode == 86 || window.event.keyCode == 118){
				// Volver
				document.frmNovedades.CID.value = '';
				document.frmNovedades.submit();
				return;
			}
		}
	}
	// Captura las teclas del explorador
	document.onkeypress = IECapturarTeclas;
</script>
<form name=frmNovedades action=novPersonas.php method=post>
<?
$ID = LimpiarVariable($_POST["ID"]);
$CID = LimpiarNumero($_POST["CID"]);
print "<input type=hidden id=ID name=ID value=\"$ID\">";
print "<input type=hidden id=CID name=CID value=\"$CID\">";
$accion = LimpiarVariable($_POST["accion"]);
if ($accion == 'Aceptar')
{
	if ($ID != "" && $CID != ""){
		$rs = pg_query($db, "
SELECT ca.\"ConceptoID\", cp.\"ParametroID\", cp.\"Descripcion\"
FROM \"tblConceptosAlias\" ca
LEFT JOIN \"tblConceptosParametros\" cp
ON ca.\"ConceptoID\" = cp.\"ConceptoID\" AND ca.\"EmpresaID\" = cp.\"EmpresaID\"
WHERE ca.\"EmpresaID\" = $EmpresaID AND ca.\"AliasID\" = $CID AND ca.\"Activo\" = true ORDER BY cp.\"ParametroID\"");
		if (!$rs){
			pg_close($db);
		}
		$i = 0;
		$val = "";
		while($row = pg_fetch_array($rs)){
			$i++;
			$Con = $row[0];
			$var = LimpiarVariable($_POST["Param$i"]);
			if ($val != "")
				$val .= ', ';
			$val .= $var;		
		}
		$Ajuste = LimpiarNumero($_POST["Ajuste"]);
		if ($Ajuste == ''){
			$chkAjuste = LimpiarNumero($_POST["chkAjuste"]);
			$rdEn = LimpiarNumero($_POST["rdEn"]);
			$Ajuste = ($chkAjuste == 1 ? ($rdEn == 1 ? 1 : 2) : 0);
		}
		if ($Ajuste > 0){
			$val = LimpiarNumero2($_POST["AjusteValor"]);
			if ($Ajuste == 2)
				$val = "-$val";
		}
		$rs = pg_query($db, "
SELECT 1 FROM \"tblNovedades\" no
WHERE no.\"EmpresaID\" = $EmpresaID AND no.\"SucursalID\" = $SucursalID AND no.\"Legajo\" = '$ID' AND no.\"AliasID\" = $CID
AND no.\"Ajuste\" = $Ajuste");
		if (!$rs){
			exit;
		}
		$vDesde = FechaWEB2SQL(LimpiarNumero($_POST["ParamDesde"]));
		$vHasta = FechaWEB2SQL(LimpiarNumero($_POST["ParamHasta"]));
		if ($vDesde == "")
			$vDesde = "null";
		else
			$vDesde = "'$vDesde'";
		if ($vHasta == "")
			$vHasta = "null";
		else
			$vHasta = "'$vHasta'";
		if ($val == "")
			$val = "null";
		else
			$val = "'\{$val}'";
		if (pg_numrows($rs) > 0){
			$sql = "
UPDATE \"tblNovedades\" SET \"FechaDesde\" = $vDesde, \"FechaHasta\" = $vHasta, \"Valores\" = $val
WHERE \"EmpresaID\" = $EmpresaID AND \"SucursalID\" = $SucursalID AND \"Legajo\" = '$ID' AND \"AliasID\" = $CID
AND \"Ajuste\" = $Ajuste";
			if (!pg_exec($db, $sql)){
				Alerta('Se produjo un error interno al actualizar la novedad');
			}else{
				Alerta('La novedad se actualizo con exito');
			}
		}else{
			$sql = "
INSERT INTO \"tblNovedades\" VALUES ($Con, $EmpresaID, $SucursalID, '$ID', $CID, $vDesde, $vHasta, $val, $Ajuste, true)";
			if (!pg_exec($db, $sql)){
				Alerta('Se produjo un error interno al agregar la novedad');
			}else{
				Alerta('La novedad se agrego con exito');
			}
		}
		$accion = 'Agregar Novedad';
	}
}else if ($accion == 'VerNeto'){
	$Fecha = $_SESSION["FechaPeriodo"];
	$NumLiq = $_SESSION["NumeroLiquidacion"];
	if ($NumLiq == '')
		exit;
	$rs = pg_query($db, "
SELECT pe.\"Estado\" FROM \"tblPeriodos\" pe
WHERE pe.\"EmpresaID\" = $EmpresaID AND pe.\"SucursalID\" = $SucursalID
AND pe.\"NumeroLiquidacion\" = $NumLiq AND pe.\"FechaPeriodo\" = '$Fecha'
	");
	if (!$rs){
		exit;
	}
	if (!ChequearSeguridad(1))
		exit;
	$row = pg_fetch_array($rs);
	if ($row[0] == '3'){
?>
		<script>
			alert('No se puede realizar esta accion en una liquidacion confirmada');
		</script>
<?
	}else{
		if ($ID == "")
			exit;

		$rs = pg_query($db, "SELECT \"LiquidacionEmpleado\"($EmpresaID, $SucursalID, '$ID', '$Fecha'::date, $NumLiq::int2, true)");
		if (!$rs){
			exit;
		}
		$row = pg_fetch_array($rs);
		$res = $row[0];
		if ($res == '1'){
			$rs1 = pg_query($db, "SELECT \"Haber1\", \"Haber2\", \"Descuento\" FROM \"tblRecibos\"
WHERE \"EmpresaID\" = $EmpresaID AND \"SucursalID\" = $SucursalID AND \"Legajo\" = '$ID'
AND \"Fecha\" = '$Fecha' AND \"NumeroLiquidacion\" = $NumLiq AND \"ConceptoID\" = 99");
			if (!$rs1){
				exit;
			}
			$row = pg_fetch_array($rs1);
			$H1 = $row[0];
			$H2 = $row[1];
			$De = $row[2];
			if ($H1 == '')
				$H1 = 0;
			if ($H2 == '')
				$H2 = 0;
			if ($De == '')
				$De = 0;
			$Neto = $H1 + $H2 - $De;
?>
		<script>
			alert('El neto del empleado asciende a $<?=$Neto?>');
		</script>
<?
		}else{
			// No se pudo calcular el neto
?>
		<script>
			alert('No se pudo determinar el neto del empleado');
		</script>
<?
		}
	}
	$accion = '';
}else if ($accion == 'Deshabilitar Novedad'){
	if ($ID != "" && $CID != ""){
		$Ajuste = LimpiarNumero($_POST["Ajuste"]);
		if (!pg_exec($db, "UPDATE \"tblNovedades\" SET \"ValidoLiquidacion\" = false 
WHERE \"EmpresaID\" = $EmpresaID AND \"SucursalID\" = $SucursalID AND \"Legajo\" = '$ID' AND \"AliasID\" = $CID AND \"Ajuste\" = $Ajuste")){
			Alerta('Se produjo un error interno al deshabilitar la novedad');
		}else{
			Alerta('La novedad se deshabilito con exito');
		}
	}
	$accion = '';
}else if ($accion == 'Habilitar Novedad'){
	if ($ID != "" && $CID != ""){
		$Ajuste = LimpiarNumero($_POST["Ajuste"]);
		if (!pg_exec($db, "UPDATE \"tblNovedades\" SET \"ValidoLiquidacion\" = true
WHERE \"EmpresaID\" = $EmpresaID AND \"SucursalID\" = $SucursalID AND \"Legajo\" = '$ID' AND \"AliasID\" = $CID AND \"Ajuste\" = $Ajuste")){
			Alerta('Se produjo un error interno al habilitar la novedad');
		}else{
			Alerta('La novedad se habilito con exito');
		}
	}
	$accion = '';
}else if ($accion == 'Borrar Novedad'){	
	if ($ID != "" && $CID != ""){
		$Ajuste = LimpiarNumero($_POST["Ajuste"]);
		if (!pg_exec($db, "DELETE FROM \"tblNovedades\" WHERE \"EmpresaID\" = $EmpresaID AND \"SucursalID\" = $SucursalID AND \"Legajo\" = '$ID' AND \"AliasID\" = $CID AND \"Ajuste\" = $Ajuste")){
			Alerta('Se produjo un error interno al borrar la novedad');
		}else{
			Alerta('La novedad se borro con exito');
		}
		$accion = '';
	}
}
if ($accion == 'Agregar Novedad' || $accion == 'Actualizar Novedad'){
	$rs = pg_query($db, "
SELECT ca.\"AliasID\", ca.\"Descripcion\" FROM \"tblConceptosAlias\" ca
INNER JOIN \"tblConceptos\" co
ON co.\"ConceptoID\"=ca.\"ConceptoID\" AND co.\"Activo\"=true
WHERE ca.\"EmpresaID\" = $EmpresaID AND ca.\"Liquida\" > 0 AND ca.\"Activo\" = true ORDER BY ca.\"Descripcion\"");
	if (!$rs)
	{
		pg_close($db);
		exit;
	}
	$Ajuste = LimpiarNumero($_POST["Ajuste"]);
	print "<b>Agregando novedad al Legajo: $ID</b><br><br>\n";
?><table class="datauser" align="left" border="0">
	<TR>
		<TD width="200" class="izquierdo">Seleccione un concepto:</TD>
		<TD class="derecho2"><select id=selConcepto <?=($accion=='Agregar Novedad' ? "" : "disabled")?> onchange="javascript:CargarParametrosConcepto(this.options[selectedIndex].value, '<?=($accion=='Agregar Novedad' ? 1 : 0)?>', '<?=$ID?>', '<?=$Ajuste?>'); document.getElementById('txtCID').value = this.options[selectedIndex].value";>
<?
	while($row = pg_fetch_array($rs))
	{
		$AliasID = $row[0];
		$Descripcion = $row[1];
		print "<option value=\"$AliasID\"";
		if ($AliasID == $CID)
			print " selected";
		print ">$Descripcion</option>";
	}
?>
	</select></TD></TR>
	<TR>
		<TD width="200" class="izquierdo">&oacute; Ingrese N&uacute;mero de Concepto</TD>
		<TD class="derecho"><input type=txtCID id=txtCID onkeypress="javascript:CambioNumCID(this, '<?=($accion=='Agregar Novedad' ? 1 : 0)?>', '<?=$ID?>', '<?=$Ajuste?>');" size=5></TD>
	</TR>
	</table><br><br>
	<div id=dvCamposForm style="position:relative; clear:both;">
		<!-- Se carga dinamica, por XML-->
	</div>
<script>
	var CID = document.frmNovedades.selConcepto.options[document.frmNovedades.selConcepto.selectedIndex].value;
	CargarParametrosConcepto(CID, <?=($accion=='Agregar Novedad' ? 1 : 0)?>, '<?=$ID?>', '<?=$Ajuste?>')
	document.getElementById('txtCID').focus();
	document.getElementById('txtCID').value = CID;
</script>
	<input type=hidden name=accion id=accion>
	<input type=hidden name=Ajuste id=Ajuste value="<?=$Ajuste?>">
	<DIV style="clear:both"><table class="datauser" align="left" border="0">
	<TR>
		<TD width="200" class="izquierdo" >&nbsp;</TD><td class=derecho2><a href="javascript:AgregarNovedad(1); void(0);" class="tecla"> 
	<img src="images/icon24_grabar.gif" alt="Aceptar" width="24" height="23" border="0" align="absmiddle">  Aceptar (Shift+A) </a>
	&nbsp;&nbsp;&nbsp;<a href="javascript:document.frmNovedades.CID.value = ''; document.frmNovedades.submit(); void(0);" class="tecla"> 
	<img src="images/icon24_prev.gif" alt="Volver" width="24" height="23" border="0" align="absmiddle">  Volver (Shift+V) </a></TD></TR></table></DIV>
	<br />
<?
}

if ($accion == 'Ver Novedades' || $accion == 'Cancelar' || $accion == ''){
	ComboEmpleado($db, $ID, 'Ver Novedades', $EmpresaID, $SucursalID);
?>
	<br />
	<input type=hidden name=Ajuste id=Ajuste>
<?
	if ($ID != ""){
		// Primer query = Novedades caragadas que no figuren como Edicion Rapida.
		// Segundo query = Novedades cargadas que figuren como Edicion Rapida y no sean ajustes.
		// Tercer query = Novedades cargadas que figuren como Edicion Rapida y sean ajustes.

		if ($_SESSION["Liquida"] != ''){
			$ExtraLiq = "AND ca.\"Liquida\" & " . $_SESSION["Liquida"] . " = " . $_SESSION["Liquida"];
		}else{
			$ExtraLiq = '';
		}

		$rs = pg_query($db, "
SELECT no.\"ConceptoID\", no.\"AliasID\", no.\"FechaDesde\", no.\"FechaHasta\", ca.\"Descripcion\", no.\"Ajuste\", no.\"Valores\"[1], no.\"ValidoLiquidacion\", ca.\"EdicionRapida\"
FROM \"tblNovedades\" no
INNER JOIN \"tblConceptosAlias\" ca
ON no.\"AliasID\" = ca.\"AliasID\" AND no.\"EmpresaID\" = ca.\"EmpresaID\" AND ca.\"EdicionRapida\" = false
WHERE no.\"EmpresaID\" = $EmpresaID AND no.\"SucursalID\" = $SucursalID AND no.\"Legajo\" = '$ID'

UNION

SELECT ca.\"ConceptoID\", ca.\"AliasID\", no.\"FechaDesde\", no.\"FechaHasta\", ca.\"Descripcion\", no.\"Ajuste\", no.\"Valores\"[1], true AS \"ValidoLiquidacion\", ca.\"EdicionRapida\"
FROM \"tblConceptosAlias\" ca
LEFT JOIN \"tblNovedades\" no
ON no.\"EmpresaID\" = $EmpresaID AND no.\"SucursalID\" = $SucursalID AND no.\"Legajo\" = '$ID' AND 
no.\"AliasID\" = ca.\"AliasID\" AND no.\"Ajuste\" = 0
WHERE ca.\"EmpresaID\" = $EmpresaID AND ca.\"EdicionRapida\" = true $ExtraLiq 

UNION

SELECT ca.\"ConceptoID\", ca.\"AliasID\", no.\"FechaDesde\", no.\"FechaHasta\", ca.\"Descripcion\", no.\"Ajuste\", no.\"Valores\"[1], true AS \"ValidoLiquidacion\", ca.\"EdicionRapida\"
FROM \"tblConceptosAlias\" ca
INNER JOIN \"tblNovedades\" no
ON no.\"EmpresaID\" = $EmpresaID AND no.\"SucursalID\" = $SucursalID AND no.\"Legajo\" = '$ID' AND
no.\"AliasID\" = ca.\"AliasID\" AND no.\"Ajuste\" <> 0
WHERE ca.\"EmpresaID\" = $EmpresaID AND ca.\"EdicionRapida\" = true $ExtraLiq 

ORDER BY 1");
		if (!$rs){
			exit;
		}
		$rs1 = pg_query($db, "
SELECT \"Legajo\" FROM \"tblEmpleados\" 
WHERE \"EmpresaID\" = $EmpresaID AND \"SucursalID\" = $SucursalID AND \"Legajo\" = '$ID' AND \"FechaEgreso\" IS NULL");
		if (!$rs1){
			exit;
		}
		if (pg_numrows($rs1) > 0){
			if ($_SESSION["NumeroLiquidacion"] != ''){
?>
	<a href="javascript:VerNeto(); void(0);" class="tecla"> 
	<img src="images/icon24_grabar.gif" alt="Ver Neto" width="24" height="23" border="0" align="absmiddle">  Liquidar y Ver Neto (Shift+E) </a><br><br>
<?
			}
?>
		<table width="100%" border="0" cellpadding="5" cellspacing="1" class="datagrid">
		<tr>
			<th>Concep.</th><th>Descripci&oacute;n</th><th>Desde<br>Hasta</th><th>Edici&oacute;n Rapida</th><th>Valor</th><th>Acciones</th>
		</tr>
		<?
		$index = 1;
		while($row = pg_fetch_array($rs))
		{
			$Concepto = $row[0];
			$AliasID = $row[1];
			$FechaDesde = FechaSQL2WEB($row[2]);
			$FechaHasta = FechaSQL2WEB($row[3]);
			$Ajuste = $row[5];
			$Valor = $row[6];
			$Valido = ($row[7] == 't' ? true : false);
			$EdicionRapida = ($row[8] == 't' ? true : false);
			$Descripcion = $row[4];
			if ($Ajuste == 1)
				$Descripcion = 'AJ.' . $Descripcion . ' EN +';
			else if ($Ajuste == 2)
				$Descripcion = 'AJ.' . $Descripcion . ' EN -';

			if ($Ajuste > 0){
				$Texto = 'Valor De Ajuste: ' . $row[6];
				$EdicionRapida = false;
			}else{
				$Texto = '';
				if ($Valido && $Ajuste != ''){
					$rs1 = pg_query($db, "
select cp.\"Descripcion\", no.\"Valores\"[cp.\"ParametroID\"]
from \"tblConceptosParametros\" cp
inner join \"tblNovedades\" no
on no.\"EmpresaID\" = cp.\"EmpresaID\" and no.\"SucursalID\" = $SucursalID and no.\"Legajo\" = '$ID' and 
no.\"ConceptoID\" = cp.\"ConceptoID\" and no.\"Ajuste\" = $Ajuste AND no.\"AliasID\" = $AliasID
WHERE cp.\"EmpresaID\" = $EmpresaID AND cp.\"ConceptoID\" = $Concepto
");
					if ($rs1){
						while($row = pg_fetch_array($rs1)){
							if ($row[1] != ''){
								if ($Texto != '')
									$Texto .= '<br>';
								$Texto .= $row[0] . ':' . $row[1];
							}
						}
					}else{
						$Texto = 'Error al cargar los valores.';
					}
				}
				if ($EdicionRapida == true){
					$rs1 = pg_query($db, "
SELECT no.\"Valores\"[s1.\"ParametroID\"]
FROM \"tblNovedades\" no, 
(SELECT cp.\"ParametroID\"
FROM \"tblConceptosParametros\" cp
INNER JOIN \"tblConceptosParametrosValores\" cpv
ON cp.\"EmpresaID\" = cpv.\"EmpresaID\" AND cp.\"ConceptoID\" = cpv.\"ConceptoID\" AND cpv.\"AliasID\"= $AliasID
WHERE cp.\"EmpresaID\" = $EmpresaID
EXCEPT
SELECT cpv.\"ParametroID\"
FROM \"tblConceptosParametrosValores\" cpv
WHERE cpv.\"EmpresaID\" = $EmpresaID AND cpv.\"AliasID\" = $AliasID) s1
WHERE no.\"EmpresaID\" = $EmpresaID AND no.\"SucursalID\" = $SucursalID AND no.\"Legajo\" = '$ID' AND no.\"AliasID\" = $AliasID
");
					$row = pg_fetch_array($rs1);
					if ($row[0] != '')
						$Valor = $row[0];
					if ($Ajuste == '')
						$Ajuste = 0;
				}
			}
?>
		<tr>
				<td><?=$AliasID?></td><td id=tdDesc<?=$AliasID?> <? if (!$Valido) print "class='borrado'";?>><?=$Descripcion?></td><td><?=$FechaDesde?><br><?=$FechaHasta?></td>
<?
	if ($EdicionRapida == true){
?>
		<td><input type=text id=EdicionRapida<?=$index?> value="<?=$Valor?>" size=3 
			onKeyPress="javascript: if (window.event.keyCode==13) document.getElementById('btnEdicionRapida<?=$index?>').click();">
		<input type=button id=btnEdicionRapida<?=$index?> value="OK" onclick="javascript:Actualizar('<?=$AliasID?>', 
'<?=$ID?>', document.getElementById('EdicionRapida<?=$index?>').value, '<?=$index?>');"></td>
<?
		$index++;
	}else{
		print "<td></td>";
	}
?>
				<td>&nbsp;
<?if ($Ajuste == 0){?>
	<div id=dvTexto<?=$AliasID?>>
<?}
	if ($Texto != ''){
?>
<ilayer><layer onmouseover="return escape('<?=$Texto?>');"><img src="images/icon24_valor.gif" align="absmiddle" border="0" width="24" height="24" onmouseover="return escape('<?=$Texto?>');"></layer></ilayer>
<?
	}
?>
				</div></td>
				<td><a href="javascript:ActualizarNovedad(<?=$AliasID?>,<?=$Ajuste?>); void(0);">
				<img src="images/icon24_editar.gif" alt="Editar Novedad" align="absmiddle" border="0" width="24" height="24"></a><a href="javascript:BorrarNovedad(<?=$AliasID?>,<?=$Ajuste?>); void(0);">
				<img src="images/icon24_borrar.gif" alt="Borrar Novedad" align="absmiddle" border="0" width="24" height="24"></a>
<?
			if ($Valido){ ?>
				<a href="javascript:DeshabilitarNovedad(<?=$AliasID?>,<?=$Ajuste?>); void(0);">
				<img src="images/icon24_novedad_deshab_off.gif" alt="Deshabilitar Novedad Hasta Confirmacion" align="absmiddle" border="0" width="24" height="24"></a>
<?			} else { ?>
				<a href="javascript:HabilitarNovedad(<?=$AliasID?>,<?=$Ajuste?>); void(0);">
				<img src="images/icon24_novedad_deshab_on.gif" alt="Habilitar Novedad" align="absmiddle" border="0" width="24" height="24"></a>
<?			} ?>
	</td>
  </tr>
		<?
		}
?>
	</table>
	<script>
		document.getElementById('EdicionRapida1').focus();
	</script>
<?
		}else{
			Alerta('El empleado seleccionado no existe');
		}
	}
}
pg_close($db);
?>
</form>
<? include("footer.php");

function ComboEmpleado($db, $ID, $Boton, $EmpresaID, $SucursalID){
	$rs = pg_query($db, "
SELECT em.\"Legajo\", em.\"Apellido\" || ', ' || em.\"Nombre\" || ' (' || em.\"Legajo\" || ')' AS \"ApeYNom\" 
FROM \"tblEmpleados\" em 
WHERE em.\"EmpresaID\" = $EmpresaID AND em.\"SucursalID\" = $SucursalID AND em.\"FechaEgreso\" IS NULL
ORDER BY 2");
	if (!$rs)
	{
		pg_close($db);
		exit;
	}
?><table class='datauser' border=0>
		<TR>
		<TD class='izquierdo' width=200>
Seleccione Empleado:</TD><TD class='derecho'><select id=selLegajo>
<?
while($row = pg_fetch_array($rs))
{
	$Legajo = $row[0];
	$ApeyNom = $row[1];
	?>
	<option value="<?=$Legajo?>"
	<?
	if ($Legajo == $ID)
		print " selected";
	print ">$ApeyNom</option>";
	
}?>
</select></td></tr><TR>
		<TD class='izquierdo'>
o Ingrese N&uacute;mero de Legajo (Shift+L):</TD><TD class='derecho'><input type=text id=txtLegajo size=5 onKeyPress="javascript: if (window.event.keyCode==13) submitEmpleado();">
<input type=hidden id=accion name=accion value="<?=$Boton?>">

<script>
	function submitEmpleado(){
		var sValor;

		if (document.getElementById('txtLegajo').value == ''){
			sValor = document.getElementById('selLegajo').options[document.getElementById('selLegajo').selectedIndex].value;
		}else{
			sValor = document.getElementById('txtLegajo').value;
		}
		document.getElementById('ID').value = sValor;
		document.forms[0].submit();
	}
</script>

</td></tr><TR nowrap>
<TD class="izquierdo" nowrap>&nbsp;</TD>
<TD width=200 class='derecho' nowrap>
<table width="100%"  cellspacing="2" border="0" cellpadding="0">
  <tr>
    <td nowrap="nowrap">
		<a href="javascript:submitEmpleado(); void(0);" id=VerNov class="tecla"> 
		<img src="images/icon24_ver.gif" alt="Ver" width="24" height="24" border="0" 
		align="absmiddle">  <?=$Boton?> (Shift+N) </a>
	</td>
	<td nowrap="nowrap">&nbsp;&nbsp;
		<a href="javascript:AgregarNovedad(0); void(0);" class="tecla">
		<img src="images/icon24_addnovedades.gif" alt="Agregar Novedad" width="24" height="24" border="0" align="absmiddle"> Agregar Novedad (Shift+A) </a></td>
  </tr>
</table>

</TD>
</TR></table>
<?
}
?>
