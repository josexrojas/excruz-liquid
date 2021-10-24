<?
require 'funcs.php';
EstaLogeado();

$Servidor = Servidor();
$FechaPeriodo = LimpiarNumero2($_GET["FechaPeriodo"]);
$NumeroLiquidacion = LimpiarNumero($_GET["NumeroLiquidacion"]);

?>

<HTML>
<HEAD>
<TITLE>Listado ART</TITLE>
</HEAD>
<BODY leftmargin="0" topmargin="0" marginheight="0" marginwidth="0">

<OBJECT CLASSID="clsid:5220cb21-c88d-11cf-b347-00aa00a28331" VIEWASTEXT>
	<PARAM NAME="LPKPath" VALUE="http://<?=$Servidor?>/Reporte/reporte.lpk">
</OBJECT>

<OBJECT ID="PrnReporte"
CLASSID="CLSID:623CB803-12DB-4065-832F-F8A188B220A7"
CODEBASE="http://<?=$Servidor?>/Reporte/reporte.cab#version=<?=VersionReporte();?>" HEIGHT=749 WIDTH=870 VIEWASTEXT>
	<PARAM NAME="Servidor" VALUE="<?=$Servidor?>">
	<PARAM NAME="TamanoLetra" VALUE="8">
	<PARAM NAME="ImprimirDirectoImpresora" VALUE="NO">
	<PARAM NAME="Pagina" VALUE="/listadoARTPrint2.php?FechaPeriodo=<?=$FechaPeriodo?>&NumeroLiquidacion=<?=$NumeroLiquidacion?>">
</OBJECT>
</BODY>
</HTML>

