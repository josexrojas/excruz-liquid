<?
require_once "funcs.php";
EstaLogeado();

if (!($db = Conectar()))
	exit;

$EmpresaID = $_SESSION["EmpresaID"];
$SucursalID = $_SESSION["SucursalID"];

$AID = LimpiarNumero($_GET["AID"]);
$CID = LimpiarNumero($_GET["CID"]);
$ID = LimpiarVariable($_GET["ID"]);
$Valor = LimpiarNumero2($_GET["Valor"]);

$rs = pg_query($db, "
SELECT \"Valores\" FROM \"tblNovedades\"
WHERE \"EmpresaID\" = $EmpresaID AND \"SucursalID\" = $SucursalID AND \"Legajo\" = '$ID' AND \"AliasID\" = $AID
AND \"Ajuste\" = 0 LIMIT 1");
if (!$rs){
	print "E";
	exit;
}
if (pg_numrows($rs) > 0){
	$bActualizar = true;
	$row = pg_fetch_array($rs);
	if ($row[0] == '') {
		$bValoresNull = true;
	}else{
		$bValoresNull = false;
	}
}else{
	$bActualizar = false;
}

if ($Valor == ''){
	// Borrar la novedad
	if ($bActualizar){
		pg_exec($db, "
DELETE FROM \"tblNovedades\"
WHERE \"EmpresaID\" = $EmpresaID AND \"SucursalID\" = $SucursalID AND \"Legajo\" = '$ID' AND \"AliasID\" = $AID
AND \"Ajuste\" = 0
		");
	}
	$Texto = '';
}else{
	$rs = pg_query($db, "
SELECT cp.\"ParametroID\", cpv.\"Valor\", cp.\"ConceptoID\", cp.\"Descripcion\"
FROM \"tblConceptosAlias\" ca
LEFT JOIN \"tblConceptosParametros\" cp
ON ca.\"ConceptoID\" = cp.\"ConceptoID\" AND ca.\"EmpresaID\" = cp.\"EmpresaID\"
LEFT JOIN \"tblConceptosParametrosValores\" cpv
ON cpv.\"EmpresaID\" = ca.\"EmpresaID\" AND cpv.\"AliasID\" = ca.\"AliasID\" and cpv.\"ParametroID\" = cp.\"ParametroID\"
WHERE ca.\"EmpresaID\" = $EmpresaID AND ca.\"AliasID\" = $AID AND ca.\"Activo\" = true 
ORDER BY cp.\"ParametroID\"");
	if (!$rs){
		exit;
	}
	$Vals = '';
	while($row = pg_fetch_array($rs)){
		$CID = $row[2];
		if ($row[1] == ''){
			if ($Vals != '')
				$Vals .= ',';
			$Vals .= $Valor;
			$Num = $row[0];
			$Texto .= $row[3] . ':' . $Valor;
		}else{
			if ($Vals != '')
				$Vals .= ',';
			$Vals .= $row[1];
		}
	}
	if ($bActualizar){
		// Actualizar
		if ($bValoresNull) {
			pg_exec($db, "
UPDATE \"tblNovedades\" SET \"Valores\" = '\{$Valor}'
WHERE \"EmpresaID\" = $EmpresaID AND \"SucursalID\" = $SucursalID AND \"Legajo\" = '$ID' AND \"AliasID\" = $AID
AND \"Ajuste\" = 0
			");
		}else{
			pg_exec($db, "
UPDATE \"tblNovedades\" SET \"Valores\"[$Num] = $Valor
WHERE \"EmpresaID\" = $EmpresaID AND \"SucursalID\" = $SucursalID AND \"Legajo\" = '$ID' AND \"AliasID\" = $AID
AND \"Ajuste\" = 0
			");
		}
	}else{
		// Insertar
		pg_exec($db, "
INSERT INTO \"tblNovedades\" 
(\"ConceptoID\", \"EmpresaID\", \"SucursalID\", \"Legajo\", \"AliasID\", \"Valores\", \"Ajuste\", \"ValidoLiquidacion\")
VALUES ($CID, $EmpresaID, $SucursalID, '$ID', $AID, '\{$Vals}', 0, true)");
	}
}
if ($Texto != ''){
?>
<img src="images/icon24_valor.gif" align="absmiddle" border="0" width="24" height="24" alt="<?=$Texto?>">
<?
exit;
?>
<ilayer><layer onmouseover="return escape('<?=$Texto?>');"><img src="images/icon24_valor.gif" align="absmiddle" border="0" width="24" height="24" onmouseover="return escape('<?=$Texto?>');"></layer></ilayer>
<? } ?>
