<?php
require_once('funcs.php');

include("library/fpdf.php");

$SucursalID 	= $_GET['SucursalID'];
$EmpresaID		= $_GET['EmpresaID'];
$FechaHasta		= $_GET['FechaHasta'];
$FechaDesde	    = $_GET['FechaDesde'];


if (!($db = Conectar()))
	exit;


$rs = pg_query($db, "
SELECT DISTINCT em.\"Legajo\", em.\"Nombre\", em.\"Apellido\", ed.\"NumeroDocumento\", ed.\"FechaIngreso\",ed.\"AltaDecreto\",er.\"cargo\" , em.\"FechaEgreso\",ed.\"BajaDecreto\",ed.\"BajaMotivo\"
FROM \"tblEmpleados\" em
INNER JOIN \"tblEmpleadosDatos\" ed
ON ed.\"EmpresaID\" = em.\"EmpresaID\" AND ed.\"SucursalID\" = em.\"SucursalID\" AND ed.\"Legajo\" = em.\"Legajo\"
LEFT JOIN \"tblEmpleadosRafam\" er
ON er.\"EmpresaID\" = em.\"EmpresaID\" AND er.\"SucursalID\" = em.\"SucursalID\" AND er.\"Legajo\" = em.\"Legajo\"
WHERE em.\"EmpresaID\" = $EmpresaID AND em.\"SucursalID\" = $SucursalID AND ((ed.\"FechaIngreso\" >= '$FechaDesde' AND ed.\"FechaIngreso\" <= '$FechaHasta') OR (em.\"FechaEgreso\" >= '$FechaDesde' AND em.\"FechaEgreso\" <= '$FechaHasta'))
GROUP BY em.\"Legajo\", em.\"Nombre\", em.\"Apellido\", ed.\"NumeroDocumento\", ed.\"FechaIngreso\", em.\"FechaEgreso\", ed.\"AltaDecreto\",er.\"cargo\"	,ed.\"BajaDecreto\",ed.\"BajaMotivo\"
ORDER BY ed.\"FechaIngreso\" ASC
");

	if (!$rs){
		exit;
	}
	
if (pg_numrows($rs)>0){

$pdf= new FPDF();
//creo documento


$pdf->AddPage("L"); //crear documento 

$pdf->Cell(50); 
$pdf->SetFont('Arial','B',14); 
$pdf->Cell(170,15,"Registro de Personal",0,0,'C'); 
$pdf->Ln(20); 
$pdf->SetFont('Arial','B',10); 

 
 //Cabecera
 $pdf->Cell(95,5,"",1);
$pdf->Cell(81,5,"Altas",1,0,'C');
$pdf->Cell(84,5,"Bajas",1,0,'C');
$pdf->Cell(20,5,"",1);

$pdf->Ln(5);
$pdf->Cell(20,7,"Legajo",1);
$xapellido = $pdf->GetX();						 // obtengo x para dibujar tabla
$pdf->Cell(55,7,"Apellido y Nombre",1);
$xap = $pdf->GetX();						     // obtengo x para dibujar tabla

$pdf->Cell(20,7,"DNI",1,0,"C");
$xdni = $pdf->GetX();						     // obtengo x para dibujar tabla
$pdf->Cell(25,7,"Fecha Ingreso",1);
$xfechaIng = $pdf->GetX();
$pdf->Cell(25,7,"Decreto Alta",1);
$xDalta = $pdf->GetX();							// obtengo x para dibujar tabla
$pdf->Cell(31,7,"Cargo",1);
$xcargo =  $pdf->GetX();						// obtengo x para dibujar tabla
$pdf->Cell(29,7,"Fecha Egreso",1);
$xfechaEgr =  $pdf->GetX();						// obtengo x para dibujar tabla
$pdf->Cell(25,7,"Decreto Baja",1);
$xCausa=  $pdf->GetX();							// obtengo x para dibujar tabla
$pdf->Cell(30,7,"Causa",1);	
$xDbaja =  $pdf->GetX();						// obtengo x para dibujar tabla
$pdf->Cell(20,7,"Firma",1);
$xFirma  =  $pdf->GetX();						 // obtengo x para dibujar tabla
$pdf->Ln(7); 
 $pdf->Text(240, 180, "Pagina: ".$pdf->PageNo()); // numero de pagina
//muestro datos 
$y1=0;
while($row = pg_fetch_array($rs))
	{
		
		$ApeyNom = $row[2] . ' ' . $row[1];
		$NroDoc = $row[3];
		$Legajo = $row[0];
		$FechaIng = FechaSQL2WEB($row[4]);
		$Dalta = $row[5];
		$carg = $row[6];
		$FechaEg= FechaSQL2WEB($row[7]);
		$Dbaja = $row[8];
		$Caus  = $row[9];
		
		if ($carg != '')
		{
			$rh = pg_query($db, "SELECT DISTINCT denominacion FROM owner_rafam.cargos cr WHERE cr.cargo IN(SELECT er.cargo FROM \"tblEmpleadosRafam\" er WHERE er.cargo = $carg ) ");
				
			$row2 = pg_fetch_array($rh);
		}
	 
	  if ($y1 > 160)
		{
			$pdf->AddPage("L"); //crear documento 
			$pdf->Text(240, 180, "Pagina: ".$pdf->PageNo());
			$pdf->Cell(50); 
			$pdf->SetFont('Arial','B',14); 
			$pdf->Cell(170,15,"Registro de Personal",0,0,'C'); 
			$pdf->Ln(20); 
			$pdf->SetFont('Arial','B',10); 
			
			 //Cabecera
			 $pdf->Cell(95,5,"",1);
			$pdf->Cell(81,5,"Altas",1,0,'C');
			$pdf->Cell(84,5,"Bajas",1,0,'C');
			$pdf->Cell(20,5,"",1);
			
			$pdf->Ln(5);
			$pdf->Cell(20,7,"Legajo",1);
			$xapellido = $pdf->GetX();
			$pdf->Cell(55,7,"Apellido y Nombre",1);
			$xap = $pdf->GetX();
			
			$pdf->Cell(20,7,"DNI",1,0,"C");
			$xdni = $pdf->GetX();
			$pdf->Cell(25,7,"Fecha Ingreso",1);
			$xfechaIng = $pdf->GetX();
			$pdf->Cell(25,7,"Decreto Alta",1);
			$xDalta = $pdf->GetX();
			$pdf->Cell(31,7,"Cargo",1);
			$xcargo =  $pdf->GetX();
			$pdf->Cell(29,7,"Fecha Egreso",1);
			$xfechaEgr =  $pdf->GetX();
			$pdf->Cell(25,7,"Decreto Baja",1);
			$xCausa=  $pdf->GetX();	
			$pdf->Cell(30,7,"Causa",1);	
			$xDbaja =  $pdf->GetX();
			$pdf->Cell(20,7,"Firma",1);
			$xFirma  =  $pdf->GetX();
			$pdf->Ln(7); 
		}
	
		$pdf->SetFont('Arial','',10);
		$pdf->Cell(20,5,$Legajo,0,0,"C");
		$pdf->SetFont('Arial','',8); 
	
 		$y = $pdf->GetY();
		
		$pdf->MultiCell(35,4,$ApeyNom,0);
	    $pdf->SetFont('Arial','',10); 
		
		$x = $pdf->GetX();
	    $pdf->SetXY($x + 55 ,$y);
		$pdf->Cell(60,5,$NroDoc,0,0,"C");
		
		$x = $pdf->GetX();
	    $pdf->SetXY($x - 20,$y - 2);
		
		$pdf->Cell(25,10,$FechaIng,0,0,"C");
		$pdf->Cell(25,10,$Dalta,0,0,"C");
		
		$x = $pdf->GetX();
		$pdf->SetXY($x ,$y );
		$pdf->MultiCell(30,5,$row2[0],0);
		
		$x = $pdf->GetX();
	    $pdf->SetXY($x + 176 ,$y );
		$pdf->Cell(29,10,$FechaEg,0,0,"C");
		$pdf->Cell(25,10,$Dbaja,0,0,"C");
		$pdf->MultiCell(30,5,$Caus,0);
		$pdf->Cell(20,10,"    ",0,0,"C");
		
		$x2  =  $pdf->GetX();	
		$pdf->Ln(7);
		
		$y1 = $pdf->GetY();
		
		//armo tabla
		$pdf->Line(10,$y1,290,$y1);
		$pdf->Line(10,40,10,$y1);
		$pdf->Line($xap,40,$xap,$y1);
		$pdf->Line($xapellido,40,$xapellido,$y1);
		$pdf->Line($xdni,40,$xdni,$y1);
		$pdf->Line($xfechaIng,40,$xfechaIng,$y1);
		$pdf->Line($xDalta,40,$xDalta,$y1);
		$pdf->Line($xcargo,40,$xcargo,$y1);
		$pdf->Line($xfechaEgr,40,$xfechaEgr,$y1);
		$pdf->Line($xDbaja,40,$xDbaja,$y1);
		$pdf->Line($xCausa,40,$xCausa,$y1);
		$pdf->Line($xFirma,40,$xFirma,$y1);
		$pdf->Line($x2,40,$x2,$y1);
		
	 
		
	}


$pdf->Output(); //el resto es historia 

}



?>

