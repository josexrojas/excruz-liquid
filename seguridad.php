<?
	$UID = $_SESSION["ID"];
	$rs = pg_query("
SELECT uim.\"ItemID\"
FROM \"tblUsuariosItemModulo\" uim
WHERE uim.\"EmpresaID\" = $EmpresaID AND uim.\"SucursalID\" = $SucursalID AND uim.\"UsuarioID\" = $UID
AND uim.\"ModuloID\" = $SEGURIDAD_MODULO_ID
");
	$js = "var Items = new Array(\"\"";
	$Items = array();
	if (pg_numrows($rs)>0){
		while($row = pg_fetch_array($rs)){
			$js .= ',';
			$js .= $row[0];
			array_push($Items, $row[0]);
		}
	}
	$js .= ");\n\n";

function ChequearSeguridad($ItemID){
	global $Items;
	for($i=0;$i<count($Items);$i++){
		if ($Items[$i] == $ItemID)
			return true;
	}
	return false;
}
?>
<script>
	<?=$js?>
	function ChequearSeguridad(ItemID){
		var i;
		for(i=1;i<Items.length;i++){
			if (Items[i] == ItemID)
				return true;
		}
		alert('Usted no tiene permisos para efectuar esa operacion');
		return false;
	}
</script>
