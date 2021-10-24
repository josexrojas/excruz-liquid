<?
	header('Content-Type: text/xml');
	print "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\" ?>\n";
	print "<root>\n";
	require 'funcs.php';

	if (!($db = Conectar()))
		exit;

	$Jurisdiccion = LimpiarVariable($_GET["Jurisdiccion"]);

	$rs = pg_query($db, "
SELECT agrupamiento, detalle
FROM owner_rafam.agrupamientos
WHERE jurisdiccion = '$Jurisdiccion'
ORDER BY 1");
	if (!$rs){
		exit;
	}
	while($row = pg_fetch_array($rs)){
		print "<agrupamiento id=\"$row[0]\" detalle=\"$row[1]\" />\n";
	}
	print "</root>\n";
?>
