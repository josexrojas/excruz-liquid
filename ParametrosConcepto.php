<?
require_once "funcs.php";
EstaLogeado();

if (!($db = Conectar()))
	exit;

$EmpresaID = $_SESSION["EmpresaID"];
$SucursalID = $_SESSION["SucursalID"];

$CID = LimpiarNumero($_GET["CID"]);
$Agregar = LimpiarNumero($_GET["Agregar"]);
$Ajuste = LimpiarNumero($_GET["Ajuste"]);
$Obligatorio = '0';

$rs = pg_query($db, "
SELECT co.\"Obligatorio\", ca.\"DuracionConcepto\"
FROM \"tblConceptosAlias\" ca
INNER JOIN \"tblConceptos\" co
ON co.\"EmpresaID\" = ca.\"EmpresaID\" AND co.\"ConceptoID\" = ca.\"ConceptoID\"
WHERE ca.\"EmpresaID\" = $EmpresaID AND ca.\"AliasID\" = $CID AND ca.\"Activo\" = true
");
if (!$rs){
	exit;
}
$row = pg_fetch_array($rs);
$Obligatorio = ($row[0] == 't' ? '1' : '0');
$Duracion = $row[1];

if ($Agregar == 1){
	$FechaDesde = '';
	$FechaHasta = '';
	if ($Duracion == '2' || $Duracion == '3'){
		$FechaDesde = 'No valido';
		$FechaHasta = $FechaDesde;
	}
	$rs = pg_query($db, "
SELECT cp.\"ParametroID\", cp.\"Descripcion\", cpv.\"Valor\", cpv.\"Valor\" AS \"ValorDef\" 
FROM \"tblConceptosParametros\" cp
INNER JOIN \"tblConceptosAlias\" ca
ON ca.\"ConceptoID\" = cp.\"ConceptoID\" AND ca.\"EmpresaID\" = cp.\"EmpresaID\"
LEFT JOIN \"tblConceptosParametrosValores\" cpv
ON ca.\"EmpresaID\" = cpv.\"EmpresaID\" AND ca.\"AliasID\" = cpv.\"AliasID\" AND cp.\"ParametroID\" = cpv.\"ParametroID\"
WHERE ca.\"EmpresaID\" = $EmpresaID AND ca.\"AliasID\" = $CID AND ca.\"Activo\" = true ORDER BY cp.\"ParametroID\"");
}else{
	$ID = LimpiarVariable($_GET["ID"]);
	if ($Duracion == '1'){
		$rs = pg_query($db, "
SELECT no.\"FechaDesde\", no.\"FechaHasta\", no.\"Valores\"[1]
FROM \"tblNovedades\" no
WHERE no.\"EmpresaID\" = $EmpresaID AND no.\"SucursalID\" = $SucursalID AND no.\"Legajo\" = '$ID' AND no.\"AliasID\" = $CID AND no.\"Ajuste\" = $Ajuste
");
		if (!$rs){
			exit;
		}
		$row = pg_fetch_array($rs);
		$FechaDesde = FechaSQL2WEB($row[0]);
		$FechaHasta = FechaSQL2WEB($row[1]);
		$Valor = $row[2];
	}else{
		$FechaDesde = 'No valido';
		$FechaHasta = $FechaDesde;
	}
	$rs = pg_query($db, "
SELECT cp.\"ParametroID\", cp.\"Descripcion\", no.\"Valores\"[cp.\"ParametroID\"] AS \"Valor\", cpv.\"Valor\" AS \"ValorDef\"
FROM \"tblConceptosParametros\" cp
INNER JOIN \"tblConceptosAlias\" ca
ON ca.\"ConceptoID\" = cp.\"ConceptoID\" AND ca.\"EmpresaID\" = cp.\"EmpresaID\"
LEFT JOIN \"tblConceptosParametrosValores\" cpv
ON ca.\"EmpresaID\" = cpv.\"EmpresaID\" AND ca.\"AliasID\" = cpv.\"AliasID\" AND cp.\"ParametroID\" = cpv.\"ParametroID\"
INNER JOIN \"tblNovedades\" no
ON cp.\"EmpresaID\" = no.\"EmpresaID\" AND ca.\"AliasID\" = no.\"AliasID\"
WHERE no.\"EmpresaID\" = $EmpresaID AND no.\"SucursalID\" = $SucursalID AND no.\"Legajo\" = '$ID' AND no.\"AliasID\" = $CID AND no.\"Ajuste\" = $Ajuste
ORDER BY cp.\"ParametroID\"");
}

if (!$rs){
	exit;
}
if ($Obligatorio == '1'){
	print "<input type=hidden name=chkAjuste value=1>\n";
}
?>
<table class='datauser' border=0>
<TR><TD class="izquierdo" colspan=2>
<input type=checkbox id=chkAjuste name=chkAjuste <? if ($Ajuste > 0 || $Obligatorio == 1) print "checked"; ?> <? if ($Agregar != 1 || $Obligatorio == 1) print "disabled"; ?> value=1 onclick="javascript:VerAjuste();"> Ajuste
<div id=dvAjuste style="display:<? if ($Ajuste > 0 || $Obligatorio == 1) print "block"; else print "none"; ?>; position:relative; clear:both;">
En +<input type=radio name=rdEn id=rdEn value=1 <? if ($Ajuste == '1' || $Ajuste == '') print "checked"; ?> <? if ($Agregar != 1) print "disabled"; ?> > 
En -<input type=radio name=rdEn id=rdEn value=2 <? if ($Ajuste == '2') print "checked"; ?> <? if ($Agregar != 1) print "disabled"; ?> ><br>
Valor De Ajuste: <input type=text name=AjusteValor id=AjusteValor value="<?=$Valor?>">
</div>
<div id=dvParametros style="display:<? if ($Ajuste > 0) print "none"; else print "block"; ?>; position:relative; clear:both;">
<table class='datauser' border=0>
<?
while($row = pg_fetch_array($rs))
{
	$Descripcion = $row[1];
	$Valor = $row[2];
	$ValorDef = $row[3];
	if ($ValorDef != ""){
		print "<input type=hidden id=Param$row[0] name=Param$row[0] value=\"$Valor\" style=\"display:inline\">\n";
	}else{?>
		<TR>
		<TD class='izquierdo' width=200><?=$Descripcion?>:</TD>
		<TD class='derecho2'><input type=text id=Param<?=$row[0]?> name=Param<?=$row[0]?> value="<?=$Valor?>"></td>
		</tr>
		<?
	}
}
?>
	</table></div>
	</td></tr>
<? if ($Duracion == '1') { ?>
	<TR><TD class="izquierdo" colspan=2>Si no ingresa ninguna fecha, la novedad durar&aacute; por la liquidaci&oacute;n</b></td></tr>
<? } else if ($Duracion == '2') { ?>
	<TR><TD class="izquierdo" colspan=2>La fecha de esta novedad no es v&aacute;lida porque esta durar&aacute; por la liquidaci&oacute;n</b></td></tr>
<? } else if ($Duracion == '3') { ?>
	<TR><TD class="izquierdo" colspan=2>La fecha de esta novedad no es v&aacute;lida porque esta nunca caduca</b></td></tr>
<? } ?>
	<TR><TD class="izquierdo" width=200>V&aacute;lido Desde:</TD><TD class='derecho2'><input type=text id=ParamDesde name=ParamDesde onfocus="showCalendarControl(this);" readonly size=11 value="<?=$FechaDesde?>" <? print ($Duracion != '1' ? 'disabled' : ''); ?>></td></TR>
	<TR><TD class="izquierdo"  width=200>V&aacute;lido Hasta:</TD><TD class='derecho'><input type=text id=ParamHasta name=ParamHasta onfocus="showCalendarControl(this);" readonly size=11 value="<?=$FechaHasta?>" <? print ($Duracion != '1' ? 'disabled' : ''); ?>></TD></tr>
	</table>

