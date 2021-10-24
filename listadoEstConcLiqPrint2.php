<?
require 'funcs.php';
require_once('library/fpdf.php');

$Jur = LimpiarNumero($_GET["Jur"]);
$TR = LimpiarNumero2($_GET["TR"]);
$TP = LimpiarNumero($_GET["TP"]);
$NumeroLiquidacion = LimpiarNumero($_GET["NumeroLiquidacion"]);
$FechaPeriodo = LimpiarNumero2($_GET["FechaPeriodo"]);

class PDFLibro extends FPDF
{
	private $NumeroLiquidacion;
	private $FechaPeriodo;
	
	function __construct($NumeroLiquidacion, $FechaPeriodo)
	{
		parent::__construct("P", "mm", "legal");
		$this->NumeroLiquidacion = $NumeroLiquidacion;
		$this->FechaPeriodo		 = $FechaPeriodo;
	}
	
	function Header()
	{
		$this->SetFont('arial');
		$this->SetFontSize(5);
		$this->Text(10, 10, str_repeat("-", 335));
		$this->Text(10, 13, "Municipalidad de Exaltacion de la Cruz");
		$this->Text(190, 13, "Fecha: ".@date('d-m-Y'));
		$this->Text(10, 15, "Administracion de Personal ");
		$this->Text(190, 15, "Pagina: ".$this->PageNo());
		$this->Text(10, 18, str_repeat("-", 335));
		$this->Text(90, 23, "*** ESTADISTICA DE CONCEPTOS LIQUIDADOS ***");
		$this->Text(10, 30, "Periodo: " . Mes(substr($this->FechaPeriodo, 5, 2)) . " de " . substr($this->FechaPeriodo, 0, 4) . " Numero De Liquidacion: ".$this->NumeroLiquidacion);
	}	
}

$oPdf = new PDFLibro($NumeroLiquidacion, $FechaPeriodo);
$oPdf->AddPage();

$x = 0;
$y = 28;

if (!($db = Conectar()))
	exit;

$EmpresaID = 1;
$SucursalID = 1;

	$rs = pg_query($db, "
SELECT
	(CASE co.\"ClaseID\" WHEN 0 THEN 9 ELSE co.\"ClaseID\" END) AS \"Orden\", re.\"AliasID\", re.\"Descripcion\", 
	count(1) AS \"CantidadLegajos\", sum(\"Cantidad\") AS \"CantidadLiquidada\", round(sum(\"Haber1\")::numeric, 2) AS 
	\"Haber1\", round(sum(\"Haber2\")::numeric, 2) AS \"Haber2\", round(sum(\"Descuento\")::numeric, 2) AS \"Descuentos\", 
	round(sum(\"Aporte\")::numeric, 2)".($Jur=='1'?",er.jurisdiccion":"").($TP=='1'?",em.\"TipoRelacion\"":"")."
FROM \"tblRecibos\" re
INNER JOIN \"tblConceptos\" co
ON co.\"EmpresaID\" = re.\"EmpresaID\" AND co.\"ConceptoID\" = re.\"ConceptoID\"
INNER JOIN \"tblEmpleados\" em
ON em.\"EmpresaID\" = re.\"EmpresaID\" AND em.\"SucursalID\" = re.\"SucursalID\" AND em.\"Legajo\" = re.\"Legajo\"".
($TR != '' ? " AND em.\"TipoRelacion\" in ($TR)" : '')." 
INNER JOIN \"tblEmpleadosRafam\" er
ON er.\"EmpresaID\" = re.\"EmpresaID\" AND er.\"SucursalID\" = re.\"SucursalID\" AND er.\"Legajo\" = re.\"Legajo\"
WHERE re.\"ConceptoID\" <> 99 AND re.\"EmpresaID\" = $EmpresaID AND re.\"SucursalID\" = $SucursalID AND
re.\"Fecha\" = '$FechaPeriodo' AND re.\"NumeroLiquidacion\" = $NumeroLiquidacion
GROUP BY 2, 3, 1".($Jur=='1'?",10":"").($Jur=='1'&&$TP=='1'?",11":"").($Jur!='1'&&$TP=='1'?",10":"")."
ORDER BY ".($Jur=='1'?"10,":"").($Jur=='1'&&$TP=='1'?"11,":"").($Jur!='1'&&$TP=='1'?"10,":"")." 1, 3
");

	if (!$rs){
		exit;
	}
	$TotalH1 = 0;
	$TotalH2 = 0;
	$TotalDesc = 0;
	$TotalAporte = 0;
	$TotalGH1 = 0;
	$TotalGH2 = 0;
	$TotalGDesc = 0;
	$TotalGAporte = 0;
	$Jurisdiccion = '';
	$AntJur = '';
	$TipoPlanta = '';
	$AntTP = '';
	$Abrir = 1;
	$Count = 1;

	while($row = pg_fetch_array($rs))
	{
		$y = $y+3;
		$i=1;
		$CID = $row[$i++];
		$Descr = $row[$i++];
		$CantLeg = $row[$i++];
		$CantLiq = $row[$i++];
		$H1 = $row[$i++];
		$H2 = $row[$i++];
		$Desc = $row[$i++];
		$Aporte = $row[$i++];
		if ($Jur == '1')
			$Jurisdiccion = $row[$i++];
		if ($TP == '1')
			$TipoPlanta = $row[$i++];

		if ($Jurisdiccion != '' && $AntJur != $Jurisdiccion){
			if ($AntJur != '')
				$Cerrar = 1;
		}
		if ($TipoPlanta != '' && $AntTP != $TipoPlanta){
			if ($AntTP != '')
				$Cerrar = 1;
		}
		if ($Cerrar == 1){
			$Cerrar = 0;
						
			$y = $y+3;
			$oPdf->Text(25, $y, "Totales");
			$oPdf->Text(130, $y, $TotalH1);
			$oPdf->Text(145, $y, $TotalH2);
			$oPdf->Text(165, $y, $TotalDesc);
			$oPdf->Text(185, $y, $TotalAporte);	
			
			$TotalH1 = 0;
			$TotalH2 = 0;
			$TotalDesc = 0;
			$TotalAporte = 0;
		}
		if ($Jurisdiccion != '' && $AntJur != $Jurisdiccion){
			
			$Count=1;
			
			if ($AntJur != "")
			{
				$oPdf->AddPage();
				$y = 28;
				$Abrir = 1;
			}
			
			$y = $y+3;
			$oPdf->Text(10, $y, "Jurisdiccion: " . Jurisdiccion($db, $Jurisdiccion));			
			$y = $y+3;
			$AntJur = $Jurisdiccion;
			$AntTP = '0';
			$Abrir = 1;
		}
		if ($TipoPlanta != '' && $AntTP != $TipoPlanta){
			
			$y=$y+3;
			if ($TipoPlanta == '1')
				$oPdf->Text(10, $y, "Tipo De Relacion: Mensualizado");
			else if ($TipoPlanta == '2')
				$oPdf->Text(10, $y, "Tipo De Relacion: Jornalizado");
			else if ($TipoPlanta == '3')
				$oPdf->Text(10, $y, "Tipo De Relacion: Contratado");
			
			$y=$y+5;

			$AntTP = $TipoPlanta;
			$Abrir = 1;
		}
		if ($Abrir == 1){
			$Abrir = 0;
			
			$oPdf->Text(10, $y, "Concepto");
			$oPdf->Text(25, $y, "Descripcion");
			$oPdf->Text(100, $y, "Cant.Leg.");
			$oPdf->Text(115, $y, "Cant.Liq.");
			$oPdf->Text(130, $y, "Haber c/desc.");
			$oPdf->Text(145, $y, "Haber s/desc.");
			$oPdf->Text(165, $y, "Descuentos");
			$oPdf->Text(185, $y, "Aportes");
			
			$y = $y+3;
			$oPdf->Text(10, $y, str_repeat("-", 335));
			$y = $y+3;//
		}
		if ($H1 != ''){
			$TotalH1 += $H1;
			$TotalGH1 += $H1;
		}
		if ($H2 != ''){
			$TotalH2 += $H2;
			$TotalGH2 += $H2;
		}
		if ($Desc != ''){
			$TotalDesc += $Desc;
			$TotalGDesc += $Desc;
		}
		if ($Aporte != ''){
			$TotalAporte += $Aporte;
			$TotalGAporte += $Aporte;
		}
				
		
		$oPdf->Text(10, $y, $CID);
		$oPdf->Text(25, $y, $Descr);
		$oPdf->Text(100, $y, $CantLeg);
		$oPdf->Text(115, $y, $CantLiq);

		$oPdf->SetXY(116, $y);
		$oPdf->MultiCell(25, 0, $H1, 0, 'R');
		
		$oPdf->SetXY(130, $y);
		$oPdf->MultiCell(25, 0, $H2, 0, 'R');
		
		$oPdf->SetXY(155, $y);
		$oPdf->MultiCell(20, 0, $Desc, 0, 'R');
		
		$oPdf->SetXY(163, $y);
		$oPdf->MultiCell(30, 0, $Aporte, 0, 'R');

		$y = $y+1;
		
		$Count++;
		
		if ($Count>=70)
		{
			$Count=1;
			$oPdf->AddPage();
			$y = 30;
			$Abrir = 1;
		}
	}

	$y = $y+3;
	
	
	if ($Count>=70)
	{
		$Count=1;
		$oPdf->AddPage();
		$y = 30;
		$Abrir = 1;
	}
	$oPdf->Text(25, $y, "Totales");
	$oPdf->Text(130, $y, $TotalH1);
	$oPdf->Text(145, $y, $TotalH2);
	$oPdf->Text(165, $y, $TotalDesc);
	$oPdf->Text(185, $y, $TotalAporte);	
	
	$y = $y+5;
	
	$TotalNeto = $TotalGH1 + $TotalGH2 - $TotalGDesc;
	$oPdf->Text(10, $y, "Total Neto General: $TotalNeto\n");
pg_close($db);

$oPdf->Output();

?>