<?
require 'funcs.php';
EstaLogeado();

$Servidor = Servidor();
$CentroCostos = LimpiarNumero($_GET["CentroCostos"]);
$TipoRelacion = LimpiarVariable($_GET["TipoRelacion"]);
$FechaPeriodo = LimpiarNumero2($_GET["FechaPeriodo"]);
$NumeroLiquidacion = LimpiarNumero($_GET["NumeroLiquidacion"]);
$LegDesde = LimpiarNumero($_GET["LegDesde"]);
$LegHasta = LimpiarNumero($_GET["LegHasta"]);
$Jur = LimpiarNumero($_GET["Jur"]);
$LP = LimpiarNumero($_GET["LP"]);

?>

<HTML>
<HEAD>
<TITLE>Recibos</TITLE>
</HEAD>
<BODY leftmargin="0" topmargin="0" marginheight="0" marginwidth="0">

<OBJECT CLASSID="clsid:5220cb21-c88d-11cf-b347-00aa00a28331" VIEWASTEXT>
	<PARAM NAME="LPKPath" VALUE="http://<?=$Servidor?>/Reporte/reporte.lpk">
</OBJECT>

<OBJECT ID="PrnReporte"
CLASSID="CLSID:623CB803-12DB-4065-832F-F8A188B220A7"
CODEBASE="http://<?=$Servidor?>/Reporte/reporte.cab#version=<?=VersionReporte();?>" HEIGHT=749 WIDTH=870 VIEWASTEXT>
	<PARAM NAME="Servidor" VALUE="<?=$Servidor?>">
	<PARAM NAME="ImprimirDirectoImpresora" VALUE="SI">
	<PARAM NAME="Pagina" VALUE="/recibosPrint2.php?CentroCostos=<?=$CentroCostos?>&TipoRelacion=<?=$TipoRelacion?>&FechaPeriodo=<?=$FechaPeriodo?>&NumeroLiquidacion=<?=$NumeroLiquidacion?>&LegDesde=<?=$LegDesde?>&LegHasta=<?=$LegHasta?>&Jur=<?=$Jur?>&LP=<?=$LP?>">
</OBJECT>
</BODY>
</HTML>

