<? include("header.php");

if (!($db = Conectar()))
	exit;

$EmpresaID = $_SESSION["EmpresaID"];
$SucursalID = $_SESSION["SucursalID"];

?>
<H1><img src="images/icon64_borrarnovedades.gif" width="64" height="64" align="absmiddle" /> Borrado Multiple De Novedades</H1>

<script language="JavaScript">
	function Aceptar()
	{
		var CID = document.getElementById('selConcepto');
		document.frmNovedades.CID.value = CID.options[CID.selectedIndex].value;
		document.frmNovedades.accion.value = 'Aceptar';
		document.frmNovedades.submit();
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
</script>
<form name=frmNovedades action=novBorrarMultiple.php method=post>
<?
$CID = LimpiarNumero($_POST["CID"]);
print "<input type=hidden id=CID name=CID value=\"$CID\">";
$accion = LimpiarVariable($_POST["accion"]);
if ($accion == 'Aceptar')
{
	if ($CID != ""){
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
SELECT em.\"Legajo\", no.\"AliasID\" FROM \"tblEmpleados\" em
LEFT JOIN \"tblNovedades\" no
ON no.\"EmpresaID\"=em.\"EmpresaID\" AND no.\"SucursalID\"=em.\"SucursalID\" AND
no.\"Legajo\"=em.\"Legajo\" AND no.\"AliasID\"=$CID AND no.\"Ajuste\" = $Ajuste
WHERE em.\"EmpresaID\"=$EmpresaID AND em.\"SucursalID\"=$SucursalID AND em.\"FechaEgreso\" IS NULL");
		$iCant = 0;
		while($row = pg_fetch_array($rs)){
			$ID = $row[0];
			$bExiste = ($row[1] == '' ? false : true);
			if ($bExiste){
				$sql = "
DELETE FROM \"tblNovedades\"
WHERE \"EmpresaID\" = $EmpresaID AND \"SucursalID\" = $SucursalID AND \"Legajo\" = '$ID' AND \"AliasID\" = $CID
AND \"Ajuste\" = $Ajuste";
				pg_exec($db, $sql);
			}
			$iCant++;
		}
		Alerta("Se borraron $iCant novedades con exito");
		$accion = '';
	}
}

if ($accion == ''){
	$rs = pg_query($db, "
SELECT ca.\"AliasID\", ca.\"Descripcion\" FROM \"tblConceptosAlias\" ca
INNER JOIN \"tblConceptos\" co
ON co.\"ConceptoID\"=ca.\"ConceptoID\" AND co.\"Obligatorio\"=false AND co.\"Activo\"=true
WHERE ca.\"EmpresaID\" = $EmpresaID AND ca.\"Liquida\" > 0 ORDER BY ca.\"Descripcion\"");
	if (!$rs){
		exit;
	}
?>
	<b>Se borrar&aacute; la novedad a todos los empleados</b><br><br>
	<table class="datauser" align="left" border="0">
	<TR>
		<TD width="200" class="izquierdo">Seleccione un concepto:</TD>
		<TD class="derecho"><select id=selConcepto>
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
	</select></TD></TR></table><br><br>
	<input type=checkbox id=chkAjuste name=chkAjuste value=1 onclick="javascript:VerAjuste();"> Ajuste
	<div id=dvAjuste style="display:none; position:relative; clear:both;">
	En +<input type=radio name=rdEn id=rdEn value=1> 
	En -<input type=radio name=rdEn id=rdEn value=2><br>
	</div>
	<input type=hidden name=accion id=accion>
	<DIV style="clear:both"><table class="datauser" align="left" border="0">
	<TR>
		<TD width="200" class="izquierdo" >&nbsp;</TD><td class=derecho><a href="javascript:Aceptar(); void(0);" class="tecla"> 
	<img src="images/icon24_grabar.gif" alt="Aceptar" width="24" height="23" border="0" align="absmiddle">  Aceptar </a>
	&nbsp;&nbsp;&nbsp;<a href="novedades.php" class="tecla"> 
	<img src="images/icon24_prev.gif" alt="Volver" width="24" height="23" border="0" align="absmiddle">  Volver </a></TD></TR></table></DIV>
	<br />
<?
}
pg_close($db);
?>
</form>
<? include("footer.php"); ?>
