<?
	header('Content-Type: text/xml');
	print "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\" ?>\n";
	print "<root>\n";
	require 'funcs.php';

	if (!($db = Conectar()))
		exit;

	$Jurisdiccion = LimpiarVariable($_GET["Jurisdiccion"]);
	$Agrupamiento = LimpiarNumero($_GET["Agrupamiento"]);
	$Categoria = LimpiarNumero($_GET["Categoria"]);

	$rs = pg_query($db, "
SELECT cargo, detalle
FROM owner_rafam.cargos
WHERE jurisdiccion = '$Jurisdiccion' AND agrupamiento = $Agrupamiento AND categoria = $Categoria
ORDER BY 1");
	if (!$rs){
		exit;
	}
	while($row = pg_fetch_array($rs)){
		print "<categoria id=\"$row[0]\" detalle=\"$row[1]\" />\n";
	}
	print "</root>\n";
?>
