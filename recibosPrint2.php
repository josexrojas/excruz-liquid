<?
require 'funcs.php';
require_once('library/fpdf.php');

$CentroCostos = LimpiarNumero($_GET["CentroCostos"]);
$TipoRelacion = LimpiarVariable($_GET["TipoRelacion"]);
$FechaPeriodo = LimpiarNumero($_GET["FechaPeriodo"]);
$NumeroLiquidacion = LimpiarNumero($_GET["NumeroLiquidacion"]);
$LegDesde = LimpiarNumero($_GET["LegDesde"]);
$LegHasta = LimpiarNumero($_GET["LegHasta"]);
$Jur = LimpiarNumero($_GET["Jur"]);
$LP = LimpiarNumero($_GET["LP"]);
$dAno = substr($FechaPeriodo, 0, 4);
$dMes = substr($FechaPeriodo, 4, 2);

$Per = Mes(intval($dMes)) . " DE $dAno";

class PDFRecibos extends FPDF
{
	private $NumeroLiquidacion;
	private $Per;
	
	function __construct($NumeroLiquidacion, $Per)
	{
		parent::__construct("P", "mm", "legal");
		$this->NumeroLiquidacion 	= $NumeroLiquidacion;
		$this->Per		 			= $Per;
	}
	
	function Text($x, $y, $txt)
	{		
		if ($y > 335)
		{
			global $y;
			parent::AddPage();
			$y = 55;
		}
			
		parent::Text($x, $y, $txt);		
	}
	
	function Header()
	{
		$this->SetFont('arial');
		$this->Text(10, 10, str_repeat("-", 137));
		$this->Text(10, 15, "Municipalidad de Exaltacion de la Cruz");
		$this->Text(165, 15, "Fecha: ".@date('d-m-Y'));
		$this->Text(10, 20, "Administracion de Personal ");
		$this->Text(165, 20, "Pagina: ".$this->PageNo());
		$this->Text(10, 25, str_repeat("-", 137));
		$this->Text(70, 35, "*** LIBRO DE SUELDOS Y JORNALES ***");
		$this->Text(10, 45, "Periodo: ".$this->Per."    Numero De Liquidacion: ".$this->NumeroLiquidacion);
	}	
}

$oPdf = new PDFRecibos($NumeroLiquidacion, $Per);
$oPdf->AddPage();

$x = 0;
$y = 55;


if (!($db = Conectar()))
	exit;

$EmpresaID = 1;
$SucursalID = 1;
$LegajoNumerico = '1';
if ($LegajoNumerico == '1'){
	$sqlLegajo = "to_number(re.\"Legajo\", '999999') AS \"Legajo\"";
}else{
	$sqlLegajo = "re.\"Legajo\"";
}

$sJoin = "INNER JOIN \"tblEmpleados\" em ON em.\"EmpresaID\" = re.\"EmpresaID\" AND em.\"SucursalID\" = re.\"SucursalID\"
AND em.\"Legajo\" = re.\"Legajo\" AND ";
if ($CentroCostos > 0)
	$sJoin .= "em.\"CentroCostos\" = $CentroCostos AND ";
if ($TipoRelacion > 0)
	$sJoin .= "em.\"TipoRelacion\" in ($TipoRelacion) AND ";
if ($LegajoNumerico == '1'){
	if ($LegDesde != '')
		$sJoin .= "to_number(em.\"Legajo\", '999999') >= $LegDesde AND ";
	if ($LegHasta != '')
		$sJoin .= "to_number(em.\"Legajo\", '999999') <= $LegHasta AND ";
}
$sJoin = substr($sJoin, 0, -5);

$rs = pg_query($db, "
SELECT count(DISTINCT re.\"Legajo\")
FROM \"tblRecibos\" re
$sJoin
WHERE re.\"EmpresaID\" = $EmpresaID AND re.\"SucursalID\" = $SucursalID
AND re.\"Legajo\" = em.\"Legajo\" AND re.\"Fecha\" = '$FechaPeriodo' AND re.\"NumeroLiquidacion\" = $NumeroLiquidacion");
if (!$rs){
	exit;
}
$row = pg_fetch_array($rs);
$Cantidad = $row[0];
if ($Cantidad > 0){
	$rs = pg_query($db, "
SELECT $sqlLegajo, re.\"Fecha\", re.\"ConceptoID\", re.\"Descripcion\", re.\"Cantidad\", re.\"Haber1\", re.\"Haber2\", 
re.\"Descuento\", re.\"Aporte\", (CASE co.\"ClaseID\" WHEN 0 THEN 9 ELSE co.\"ClaseID\" END) AS \"Orden\",
em.\"Apellido\" || ' ' || em.\"Nombre\" AS \"ApeYNom\", ed.\"FechaIngreso\", re.\"AliasID\", er.jurisdiccion,
ed.\"LugarPago\", lp.\"Descripcion\"
FROM \"tblRecibos\" re
$sJoin
INNER JOIN \"tblConceptos\" co
ON co.\"EmpresaID\" = re.\"EmpresaID\" AND co.\"ConceptoID\" = re.\"ConceptoID\" AND co.\"ImprimeEnRecibo\" = true
INNER JOIN \"tblEmpleadosDatos\" ed
ON ed.\"EmpresaID\" = re.\"EmpresaID\" AND ed.\"SucursalID\" = re.\"SucursalID\" AND ed.\"Legajo\" = re.\"Legajo\"
LEFT JOIN \"tblLugaresDePago\" lp
ON lp.\"EmpresaID\" = re.\"EmpresaID\" AND lp.\"LugarPago\" = ed.\"LugarPago\" AND lp.\"Activo\" = true
INNER JOIN \"tblEmpleadosRafam\" er
ON er.\"EmpresaID\" = re.\"EmpresaID\" AND er.\"SucursalID\" = re.\"SucursalID\" AND er.\"Legajo\" = re.\"Legajo\"
WHERE re.\"EmpresaID\" = $EmpresaID AND re.\"SucursalID\" = $SucursalID
AND re.\"Legajo\" = em.\"Legajo\" AND re.\"Fecha\" = '$FechaPeriodo' AND re.\"NumeroLiquidacion\" = $NumeroLiquidacion
ORDER BY ".($Jur=='1'?"er.jurisdiccion,":($LP == '1'?"ed.\"LugarPago\",":""))."1, \"Orden\", re.\"ConceptoID\", re.\"Descripcion\"");
	if (!$rs){
		exit;
	}
	$Neto = 0;
	$AntLegajo = '';
	$AntJur = '';
	$AntLP = '';
	while($row = pg_fetch_array($rs)){
		$Legajo = $row[0];
		$ApeYNom = $row[10];
		$FechaIng = FechaSQL2WEB($row[11]);
		$Jurisdiccion = $row[13];
		$LugarPago = $row[14];
		$LPDesc = $row[15];
		if ($Legajo != $AntLegajo){
			if ($AntLegajo != ''){
				$y = $y+5;
				$oPdf->Text(150, $y, "NETO A COBRAR: $Neto");
				$y = $y+5;
			}
			if ($Jur == '1'){
				if ($Jurisdiccion != $AntJur){					
					$Count=1;
					
					$y = $y+5;
					$oPdf->Text(10, $y, "Jurisdiccion: " . Jurisdiccion($db, $Jurisdiccion));			
					$y = $y+10;
					$AntJur = $Jurisdiccion;					
				}
			}else if ($LP == '1'){
				if ($LugarPago != $AntLP){
										
					$y = $y+5;
					$oPdf->Text(10, $y, "Lugar De Pago: $LugarPago ($LPDesc)");			
					$y = $y+5;

					$AntLP = $LugarPago;
				}
			}
			$AntLegajo = $Legajo;
			
			$y = $y+5;
			
			$oPdf->Text(10, $y, "Legajo Nro.: $Legajo    $ApeYNom    Fecha Ingreso: $FechaIng");
			
			$y = $y+5;
			$oPdf->Text(10, $y, "Conc.");
			$oPdf->Text(25, $y, "Descripcion");
			$oPdf->Text(115, $y, "Cant");
			$oPdf->Text(130, $y, "Haber c/desc");
			$oPdf->Text(158, $y, "Haber s/desc");
			$oPdf->Text(190, $y, "Desc.");
			
			$y = $y+5;
			$oPdf->Text(10, $y, str_repeat("-", 137));			
		} // END IF
		$Concepto = $row[2];
		$Descripcion = $row[3];
		$Cantidad = $row[4];
		$Haber1 = $row[5];
		$Haber2 = $row[6];
		$Descuento = $row[7];
		$AliasID = $row[12];
		if ($Haber1 != '')
			$Haber1 = round($Haber1, 2);
		if ($Haber2 != '')
			$Haber2 = round($Haber2, 2);
		if ($Descuento != '')
			$Descuento = round($Descuento, 2);
		if ($Concepto == 99){
			$Neto = $Haber1 + $Haber2 - $Descuento;
			
			$y = $y+5;
			$oPdf->Text(125, $y, str_repeat('-', 55));			
		}
		
		$y = $y+5;
		$oPdf->Text(10, $y, $AliasID);
		$oPdf->Text(25, $y, $Descripcion);
		$oPdf->Text(115, $y, $Cantidad);
		
		$oPdf->SetXY(130, $y);
		$oPdf->MultiCell(25, 0, FormatearImporte($Haber1), 0, 'R');
		
		$oPdf->SetXY(158, $y);
		$oPdf->MultiCell(25, 0, FormatearImporte($Haber2), 0, 'R');
		
		$oPdf->SetXY(178, $y);
		$oPdf->MultiCell(25, 0, FormatearImporte($Descuento), 0, 'R');
		
		/*$oPdf->Text(130, $y, FormatearImporte($Haber1));
		$oPdf->Text(158, $y, FormatearImporte($Haber2));
		$oPdf->Text(185, $y, FormatearImporte($Descuento));*/
		
		
	} // END WHILE
	if ($AntLegajo != ''){
		$y = $y+5;
		$oPdf->Text(110, $y, "NETO A COBRAR: $Neto");
		$y = $y+10;
	}else{
		print "No se encontraron recibos para el criterio seleccionado\n";
	}
}else{
	print "No se encontraron recibos para el criterio seleccionado\n";
}

$oPdf->Output();

?>