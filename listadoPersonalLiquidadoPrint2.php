<?php

require 'funcs.php';

if (!($db = Conectar()))
	exit;

$EmpresaID 	= 1;
$SucursalID = 1;

$dAno = $_GET["Ano"];

if ($dAno == ''){
	exit;
}
$Jur 			= false;
$TR 			= false;
$chkTipoRelM 	= true;
$chkTipoRelJ 	= true;
$chkTipoRelC 	= true;
$chkTipoRelL 	= true;
$TipoRelacion 	= '';
if ($chkTipoRelM == true)
		$TipoRelacion .= '1,';
if ($chkTipoRelJ == true)
		$TipoRelacion .= '2,';
if ($chkTipoRelC == true)
		$TipoRelacion .= '3,';
if ($chkTipoRelL == true)
		$TipoRelacion .= '4,';
$TipoRelacion = substr($TipoRelacion, 0, -1);


print "<Body>\n";
print "<Header><!--CancelarModoComprimido-->" . str_repeat('-', 82);
print "\nMunicipalidad de Exaltacion de la Cruz               Fecha: <!--Fecha-->\n";
print "Administracion de Personal                             Pagina:   <!--NumeroPagina-->\n";
print str_repeat('-', 120) . "\n\n";
print str_repeat(' ', 22) . "*** LISTADO DE PERSONAL LIQUIDADO ***\n\n";
print "Periodo: " . Mes($dMes) . " de $dAno\n\n";
print "</Header><Cuerpo>";


$sql = "
SELECT DISTINCT re.\"Legajo\", em.\"Nombre\", em.\"Apellido\",
em.\"TipoRelacion\", er.categoria, cpe.\"HorasDiarias\"".($Jur=='1'?",er.jurisdiccion":"").", ca.detalle AS \"Cargo\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 1 THEN re.\"Haber1\" ELSE 0 END ) as \"EneroRemunerativo\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 1 THEN re.\"Haber2\" ELSE 0 END ) as \"EneroNoRemunerativo\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 1 THEN re.\"Descuento\" ELSE 0 END ) as \"EneroRetenciones\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 1 THEN (re.\"Haber1\" + re.\"Haber2\") - re.\"Descuento\" ELSE 0 END ) as \"EneroLiquido\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 2 THEN re.\"Haber1\" ELSE 0 END ) as \"FebreroRemunerativo\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 2 THEN re.\"Haber2\" ELSE 0 END ) as \"FebreroNoRemunerativo\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 2 THEN re.\"Descuento\" ELSE 0 END ) as \"FebreroRetenciones\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 2 THEN (re.\"Haber1\" + re.\"Haber2\") - re.\"Descuento\" ELSE 0 END ) as \"FebreroLiquido\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 3 THEN re.\"Haber1\" ELSE 0 END ) as \"MarzoRemunerativo\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 3 THEN re.\"Haber2\" ELSE 0 END ) as \"MarzoNoRemunerativo\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 3 THEN re.\"Descuento\" ELSE 0 END ) as \"MarzoRetenciones\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 3 THEN (re.\"Haber1\" + re.\"Haber2\") - re.\"Descuento\" ELSE 0 END ) as \"MarzoLiquido\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 4 THEN re.\"Haber1\" ELSE 0 END ) as \"AbrilRemunerativo\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 4 THEN re.\"Haber2\" ELSE 0 END ) as \"AbrilNoRemunerativo\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 4 THEN re.\"Descuento\" ELSE 0 END ) as \"AbrilRetenciones\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 4 THEN (re.\"Haber1\" + re.\"Haber2\") - re.\"Descuento\" ELSE 0 END ) as \"AbrilLiquido\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 5 THEN re.\"Haber1\" ELSE 0 END ) as \"MayoRemunerativo\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 5 THEN re.\"Haber2\" ELSE 0 END ) as \"MayoNoRemunerativo\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 5 THEN re.\"Descuento\" ELSE 0 END ) as \"MayoRetenciones\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 5 THEN (re.\"Haber1\" + re.\"Haber2\") - re.\"Descuento\" ELSE 0 END ) as \"MayoLiquido\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 6 THEN re.\"Haber1\" ELSE 0 END ) as \"JunioRemunerativo\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 6 THEN re.\"Haber2\" ELSE 0 END ) as \"JunioNoRemunerativo\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 6 THEN re.\"Descuento\" ELSE 0 END ) as \"JunioRetenciones\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 6 THEN (re.\"Haber1\" + re.\"Haber2\") - re.\"Descuento\" ELSE 0 END ) as \"JunioLiquido\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 7 THEN re.\"Haber1\" ELSE 0 END ) as \"JulioRemunerativo\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 7 THEN re.\"Haber2\" ELSE 0 END ) as \"JulioNoRemunerativo\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 7 THEN re.\"Descuento\" ELSE 0 END ) as \"JulioRetenciones\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 7 THEN (re.\"Haber1\" + re.\"Haber2\") - re.\"Descuento\" ELSE 0 END ) as \"JulioLiquido\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 8 THEN re.\"Haber1\" ELSE 0 END ) as \"AgostoRemunerativo\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 8 THEN re.\"Haber2\" ELSE 0 END ) as \"AgostoNoRemunerativo\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 8 THEN re.\"Descuento\" ELSE 0 END ) as \"AgostoRetenciones\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 8 THEN (re.\"Haber1\" + re.\"Haber2\") - re.\"Descuento\" ELSE 0 END ) as \"AgostoLiquido\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 9 THEN re.\"Haber1\" ELSE 0 END ) as \"SeptiembreRemunerativo\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 9 THEN re.\"Haber2\" ELSE 0 END ) as \"SeptiembreNoRemunerativo\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 9 THEN re.\"Descuento\" ELSE 0 END ) as \"SeptiembreRetenciones\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 9 THEN (re.\"Haber1\" + re.\"Haber2\") - re.\"Descuento\" ELSE 0 END ) as \"SeptiembreLiquido\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 10 THEN re.\"Haber1\" ELSE 0 END ) as \"OctubreRemunerativo\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 10 THEN re.\"Haber2\" ELSE 0 END ) as \"OctubreNoRemunerativo\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 10 THEN re.\"Descuento\" ELSE 0 END ) as \"OctubreRetenciones\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 10 THEN (re.\"Haber1\" + re.\"Haber2\") - re.\"Descuento\" ELSE 0 END ) as \"OctubreLiquido\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 11 THEN re.\"Haber1\" ELSE 0 END ) as \"NoviembreRemunerativo\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 11 THEN re.\"Haber2\" ELSE 0 END ) as \"NoviembreNoRemunerativo\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 11 THEN re.\"Descuento\" ELSE 0 END ) as \"NoviembreRetenciones\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 11 THEN (re.\"Haber1\" + re.\"Haber2\") - re.\"Descuento\" ELSE 0 END ) as \"NoviembreLiquido\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 12 THEN re.\"Haber1\" ELSE 0 END ) as \"DiciembreRemunerativo\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 12 THEN re.\"Haber2\" ELSE 0 END ) as \"DiciembreNoRemunerativo\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 12 THEN re.\"Descuento\" ELSE 0 END ) as \"DiciembreRetenciones\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 12 THEN (re.\"Haber1\" + re.\"Haber2\") - re.\"Descuento\" ELSE 0 END ) as \"DiciembreLiquido\",
emd.\"Sexo\" as \"Sexo\", CASE WHEN emd.\"EstadoCivil\" = 1 THEN 'Soltero/a' WHEN emd.\"EstadoCivil\" = 2 THEN 'Casado/a' WHEN emd.\"EstadoCivil\" = 3 THEN 'Viudo/a' WHEN emd.\"EstadoCivil\" = 4 THEN 'Divorciado/a' ELSE '' END AS \"EstadoCivil\", emd.\"NumeroDocumento\", CASE WHEN emd.\"TipoDocumento\" = 1 THEN 'DNI' WHEN emd.\"TipoDocumento\" = 2 THEN 'CI' WHEN emd.\"TipoDocumento\" = 3 THEN 'PASAPORTE' WHEN emd.\"TipoDocumento\" = 4 THEN 'LE' WHEN emd.\"TipoDocumento\" = 5 THEN 'LC' ELSE '' END AS \"TipoDocumento\", emd.\"FechaNacimiento\", emd.\"CUIT\", emd.\"FechaIngreso\"
FROM \"tblRecibos\" re
INNER JOIN  \"tblEmpleados\" em
ON em.\"EmpresaID\" = re.\"EmpresaID\" AND em.\"SucursalID\" = re.\"SucursalID\" AND em.\"Legajo\" = re.\"Legajo\"".
($TipoRelacion != '' ? " AND em.\"TipoRelacion\" in ($TipoRelacion)" : '')."
INNER JOIN \"tblEmpleadosRafam\" er
ON er.\"EmpresaID\" = re.\"EmpresaID\" AND er.\"SucursalID\" = re.\"SucursalID\" AND er.\"Legajo\" = re.\"Legajo\"
INNER JOIN \"tblEmpleadosDatos\" emd
ON emd.\"EmpresaID\" = re.\"EmpresaID\" AND emd.\"SucursalID\" = re.\"SucursalID\" AND emd.\"Legajo\" = re.\"Legajo\"
LEFT JOIN \"tblCategoriasPorEmpresa\" cpe
ON cpe.\"EmpresaID\" = re.\"EmpresaID\" AND cpe.\"Agrupamiento\" = er.agrupamiento
AND cpe.\"Categoria\" = er.categoria AND cpe.\"Cargo\" = er.cargo
LEFT JOIN owner_rafam.cargos ca
ON substr(er.jurisdiccion, 1, 5) = substr(ca.jurisdiccion, 1, 5) AND er.agrupamiento = ca.agrupamiento AND
er.categoria = ca.categoria AND er.cargo = ca.cargo
WHERE re.\"EmpresaID\" = $EmpresaID AND re.\"SucursalID\" = $SucursalID AND re.\"ConceptoID\" = 99 AND
re.\"Fecha\" >= '$dAno-01-01' AND re.\"Fecha\" < '$dAno-12-01'::timestamp + interval '1 month'
GROUP BY re.\"Legajo\", em.\"Nombre\", em.\"Apellido\", em.\"TipoRelacion\", er.categoria, cpe.\"HorasDiarias\", ca.detalle,emd.\"Sexo\" , emd.\"EstadoCivil\", emd.\"NumeroDocumento\", emd.\"TipoDocumento\", emd.\"FechaNacimiento\", emd.\"CUIT\", emd.\"FechaIngreso\"
ORDER BY ".($Jur=='1'?"7,":"")."4,3
";

 $rs = pg_query($db, $sql);

        if (!$rs){
                exit;
        }
        $Jurisdiccion = '';
 $AntJur = '';
        $TipoRel = '';
        $AntRel = '';
        $Abrir = 1;
        $CantEmp = 0;
        $CantGEmp = 0;
         print "<b>Per&iacute;odo A&ntilde;o ". $dAno ." </b>";

?>
</br>
</br>
<?php
while($row = pg_fetch_array($rs))
        {;
                $CantEmp++;
                $CantGEmp++;
                $Legajo = $row[0];
                $ApeYNom = trim($row[2] . ', ' . $row[1]);
                $Cat = $row[4];
                $Horas = $row[5];

                $EneroRemunerativo = $row[7];
                $EneroNoremunerativo = $row[8];
                $EneroRetenciones = $row[9];
                $EneroLiquido = $row[10];

                $FebreroRemunerativo = $row[11];
                $FebreroNoremunerativo = $row[12];
                $FebreroRetenciones = $row[13];
                $FebreroLiquido = $row[14];

                $MarzoRemunerativo = $row[15];
                $MarzoNoremunerativo = $row[16];
                $MarzoRetenciones = $row[17];
                $MarzoLiquido = $row[18];

                $AbrilRemunerativo = $row[19];
                $AbrilNoremunerativo = $row[20];
                $AbrilRetenciones = $row[21];
 $AbrilLiquido = $row[22];

                $MayoRemunerativo = $row[23];
                $MayoNoremunerativo = $row[24];
                $MayoRetenciones = $row[25];
                $MayoLiquido = $row[26];

                $JunioRemunerativo = $row[27];
                $JunioNoremunerativo = $row[28];
                $JunioRetenciones = $row[29];
                $JunioLiquido = $row[30];


                $JulioRemunerativo = $row[31];
                $JulioNoremunerativo = $row[32];
                $JulioRetenciones = $row[33];
                $JulioLiquido = $row[34];

                $AgostoRemunerativo = $row[35];
                $AgostoNoremunerativo = $row[36];
                $AgostoRetenciones = $row[37];
                $AgostoLiquido = $row[38];

                $SeptiembreRemunerativo = $row[39];
                $SeptiembreNoremunerativo = $row[40];
                $SeptiembreRetenciones = $row[41];
                $SeptiembreLiquido = $row[42];

                $OctubreRemunerativo = $row[43];
                $OctubreNoremunerativo = $row[44];
                $OctubreRetenciones = $row[45];
                $OctubreLiquido = $row[46];

                $NoviembreRemunerativo = $row[47];
                $NoviembreNoremunerativo = $row[48];
                $NoviembreRetenciones = $row[49];
                $NoviembreLiquido = $row[50];

                $DiciembreRemunerativo = $row[51];
                $DiciembreNoremunerativo = $row[52];
                $DiciembreRetenciones = $row[53];
                $DiciembreLiquido = $row[54];

 $Sexo = $row[55];
                $EstadoCivil = $row[56];



                $Documento = $row[58]." - ".$row[57];
                $FechaNacimiento = $row[59];
                $Cuil = $row[60];
                $FechaIngreso = $row[61];

                switch($row[3]){
                case 1:
                $TipoRel = 'Mensualizado';
                        break;
                case 2:
                        $TipoRel = 'Jornalizado';
                        break;
                case 3:
                        $TipoRel = 'Contratado';
                        break;
                }
                $i = 6;
                if ($Jur == '1')
                        $Jurisdiccion = $row[$i++];
                $Car = $row[$i++];

                if ($Jurisdiccion != '' && $AntJur != $Jurisdiccion){
                        if ($AntJur != '')
                                $Cerrar = 1;
                }
                if ($TR == '1' && $TipoRel != '' && $AntRel != $TipoRel){
                        if ($AntRel != '')
                                $Cerrar = 1;
                }
                if ($Cerrar == 1){
                        $Cerrar = 0;
                        $CantEmp--;
                        print "</table><br>\n";
                        print "<b>Cantidad de empleados activos: $CantEmp</b><br><br>\n";
                        $CantEmp = 1;
                }
                if ($Jurisdiccion != '' && $AntJur != $Jurisdiccion){
 print "<b>Jurisdicci&oacute;n: " . Jurisdiccion($db, $Jurisdiccion) . "</b><br><br>";
                        $AntJur = $Jurisdiccion;
                        $AntTP = '0';
                        $Abrir = 1;
                }
                if ($TR == '1' && $TipoRel != '' && $AntRel != $TipoRel){
                        print "<b>Tipo De Relaci&oacute;n: $TipoRel</b><br><br>";
                        $AntRel = $TipoRel;
                        $Abrir = 1;
                }
                if ($Abrir == 1){
                        $Abrir = 0;
						print str_pad("", 224, ' ', STR_PAD_RIGHT);
						print str_pad("Enero", 50, ' ', STR_PAD_RIGHT);
						print str_pad("Febrero", 50, ' ', STR_PAD_RIGHT);
						print str_pad("Marzo", 50, ' ', STR_PAD_RIGHT);
						print str_pad("Abril", 50, ' ', STR_PAD_RIGHT);
						print str_pad("Mayo", 50, ' ', STR_PAD_RIGHT);
						print str_pad("Junio",50, ' ', STR_PAD_RIGHT);
						print str_pad("Julio", 50, ' ', STR_PAD_RIGHT);
						print str_pad("Agosto", 50, ' ', STR_PAD_RIGHT);
						print str_pad("Septiembre", 50, ' ', STR_PAD_RIGHT);
						print str_pad("Octubre", 50, ' ', STR_PAD_RIGHT);
						print str_pad("Noviembre", 50, ' ', STR_PAD_RIGHT);
						print str_pad("Diciembre", 50, ' ', STR_PAD_RIGHT);
						print "\n";
						print str_pad("Leg", 5, ' ', STR_PAD_RIGHT);
						print str_pad("Apellido y Nombre", 35, ' ', STR_PAD_RIGHT);
						print str_pad("Cat", 4, ' ', STR_PAD_RIGHT);
						print str_pad("Cargo", 26, ' ', STR_PAD_RIGHT);
						print str_pad("Hs", 4, ' ', STR_PAD_RIGHT);
						print str_pad("Planta", 18, ' ', STR_PAD_RIGHT);
						print str_pad("Sexo", 6, ' ', STR_PAD_RIGHT);
						print str_pad("Estado Civil", 15, ' ', STR_PAD_RIGHT);
						print str_pad("Tipo y Numero Documento", 30, ' ', STR_PAD_RIGHT);
						print str_pad("Fecha Nacimiento", 20, ' ', STR_PAD_RIGHT);
						print str_pad("Cuil", 16, ' ', STR_PAD_RIGHT);
						print str_pad("Fecha Antiguedad", 20, ' ', STR_PAD_RIGHT);
						print str_pad("Remunerativo", 13, ' ', STR_PAD_RIGHT);
						print str_pad("No Remunerativo", 16, ' ', STR_PAD_RIGHT);
						print str_pad("Retenciones", 13, ' ', STR_PAD_RIGHT);
						print str_pad("Liquido", 14, ' ', STR_PAD_RIGHT);
						print str_pad("Remunerativo", 13, ' ', STR_PAD_RIGHT);
						print str_pad("No Remunerativo", 16, ' ', STR_PAD_RIGHT);
						print str_pad("Retenciones", 13, ' ', STR_PAD_RIGHT);
						print str_pad("Liquido", 14, ' ', STR_PAD_RIGHT);
						print str_pad("Remunerativo", 13, ' ', STR_PAD_RIGHT);
						print str_pad("No Remunerativo", 16, ' ', STR_PAD_RIGHT);
						print str_pad("Retenciones", 13, ' ', STR_PAD_RIGHT);
						print str_pad("Liquido", 14, ' ', STR_PAD_RIGHT);
						print str_pad("Remunerativo", 13, ' ', STR_PAD_RIGHT);
						print str_pad("No Remunerativo", 16, ' ', STR_PAD_RIGHT);
						print str_pad("Retenciones", 13, ' ', STR_PAD_RIGHT);
						print str_pad("Liquido", 14, ' ', STR_PAD_RIGHT);
						print str_pad("Remunerativo", 13, ' ', STR_PAD_RIGHT);
						print str_pad("No Remunerativo", 16, ' ', STR_PAD_RIGHT);
						print str_pad("Retenciones", 13, ' ', STR_PAD_RIGHT);
						print str_pad("Liquido", 14, ' ', STR_PAD_RIGHT);
						print str_pad("Remunerativo", 13, ' ', STR_PAD_RIGHT);
						print str_pad("No Remunerativo", 16, ' ', STR_PAD_RIGHT);
						print str_pad("Retenciones", 13, ' ', STR_PAD_RIGHT);
						print str_pad("Liquido", 14, ' ', STR_PAD_RIGHT);
						print str_pad("Remunerativo", 13, ' ', STR_PAD_RIGHT);
						print str_pad("No Remunerativo", 16, ' ', STR_PAD_RIGHT);
						print str_pad("Retenciones", 13, ' ', STR_PAD_RIGHT);
						print str_pad("Liquido", 14, ' ', STR_PAD_RIGHT);
						print str_pad("Remunerativo", 13, ' ', STR_PAD_RIGHT);
						print str_pad("No Remunerativo", 16, ' ', STR_PAD_RIGHT);
						print str_pad("Retenciones", 13, ' ', STR_PAD_RIGHT);
						print str_pad("Liquido", 14, ' ', STR_PAD_RIGHT);
						print str_pad("Remunerativo", 13, ' ', STR_PAD_RIGHT);
						print str_pad("No Remunerativo", 16, ' ', STR_PAD_RIGHT);
						print str_pad("Retenciones", 13, ' ', STR_PAD_RIGHT);
						print str_pad("Liquido", 14, ' ', STR_PAD_RIGHT);
						print str_pad("Remunerativo", 13, ' ', STR_PAD_RIGHT);
						print str_pad("No Remunerativo", 16, ' ', STR_PAD_RIGHT);
						print str_pad("Retenciones", 13, ' ', STR_PAD_RIGHT);
						print str_pad("Liquido", 14, ' ', STR_PAD_RIGHT);
						print str_pad("Remunerativo", 13, ' ', STR_PAD_RIGHT);
						print str_pad("No Remunerativo", 16, ' ', STR_PAD_RIGHT);
						print str_pad("Retenciones", 13, ' ', STR_PAD_RIGHT);
						print str_pad("Liquido", 14, ' ', STR_PAD_RIGHT);
						print str_pad("Remunerativo", 13, ' ', STR_PAD_RIGHT);
						print str_pad("No Remunerativo", 16, ' ', STR_PAD_RIGHT);
						print str_pad("Retenciones", 13, ' ', STR_PAD_RIGHT);
						print str_pad("Liquido", 14, ' ', STR_PAD_RIGHT);
						print "\n";
						print str_repeat('-', 450) . "\n";

                }
				
					print str_pad($Legajo, 5, ' ', STR_PAD_RIGHT);
					print str_pad($ApeYNom, 35, ' ', STR_PAD_RIGHT);
					print str_pad($Cat, 4, ' ', STR_PAD_RIGHT);
					print str_pad(substr($Car, 0, 26), 26, ' ', STR_PAD_RIGHT);
					print str_pad($Horas, 4, ' ', STR_PAD_BOTH);
					print str_pad($TipoRel, 20, ' ', STR_PAD_RIGHT);
					print str_pad($Sexo, 6, ' ', STR_PAD_RIGHT);
					print str_pad($EstadoCivil, 15, ' ', STR_PAD_RIGHT);
					print str_pad($Documento, 30, ' ', STR_PAD_RIGHT);
					print str_pad($FechaNacimiento, 20, ' ', STR_PAD_RIGHT);
					print str_pad($Cuil, 16, ' ', STR_PAD_RIGHT);
					print str_pad($FechaIngreso, 20, ' ', STR_PAD_RIGHT);
					print str_pad($EneroRemunerativo, 13, ' ', STR_PAD_RIGHT);
					print str_pad($EneroNoremunerativo, 15, ' ', STR_PAD_RIGHT);
					print str_pad($EneroRetenciones, 13, ' ', STR_PAD_RIGHT);
					print str_pad($EneroLiquido, 13, ' ', STR_PAD_RIGHT);
					print str_pad($FebreroRemunerativo, 13, ' ', STR_PAD_RIGHT);
					print str_pad($FebreroNoremunerativo, 15, ' ', STR_PAD_RIGHT);
					print str_pad($FebreroRetenciones, 13, ' ', STR_PAD_RIGHT);
					print str_pad($FebreroLiquido, 13, ' ', STR_PAD_RIGHT);
					print str_pad($MarzoRemunerativo, 13, ' ', STR_PAD_RIGHT);
                                        print str_pad($MarzoNoremunerativo, 15, ' ', STR_PAD_RIGHT);
                                        print str_pad($MarzoRetenciones, 13, ' ', STR_PAD_RIGHT);
                                        print str_pad($MarzoLiquido, 13, ' ', STR_PAD_RIGHT);
                                        print str_pad($AbrilRemunerativo, 13, ' ', STR_PAD_RIGHT);
                                        print str_pad($AbrilNoremunerativo, 15, ' ', STR_PAD_RIGHT);
                                        print str_pad($AbrilRetenciones, 13, ' ', STR_PAD_RIGHT);
                                        print str_pad($AbrilLiquido, 13, ' ', STR_PAD_RIGHT);
                                        print str_pad($MayoRemunerativo, 13, ' ', STR_PAD_RIGHT);
                                        print str_pad($MayoNoremunerativo, 15, ' ', STR_PAD_RIGHT);
                                        print str_pad($MayoRetenciones, 13, ' ', STR_PAD_RIGHT);
                                        print str_pad($MayoLiquido, 13, ' ', STR_PAD_RIGHT);
                                        print str_pad($JunioRemunerativo, 13, ' ', STR_PAD_RIGHT);
                                        print str_pad($JunioNoremunerativo, 15, ' ', STR_PAD_RIGHT);
                                        print str_pad($JunioRetenciones, 13, ' ', STR_PAD_RIGHT);
                                        print str_pad($JunioLiquido, 13, ' ', STR_PAD_RIGHT);
                                        print str_pad($JulioRemunerativo, 13, ' ', STR_PAD_RIGHT);
                                        print str_pad($JulioNoremunerativo, 15, ' ', STR_PAD_RIGHT);
                                        print str_pad($JulioRetenciones, 13, ' ', STR_PAD_RIGHT);
                                        print str_pad($JulioLiquido, 13, ' ', STR_PAD_RIGHT);
                                        print str_pad($AgostoRemunerativo, 13, ' ', STR_PAD_RIGHT);
                                        print str_pad($AgostoNoremunerativo, 15, ' ', STR_PAD_RIGHT);
                                        print str_pad($AgostoRetenciones, 13, ' ', STR_PAD_RIGHT);
                                        print str_pad($AgostoLiquido, 13, ' ', STR_PAD_RIGHT);
                                        print str_pad($SeptiembreRemunerativo, 13, ' ', STR_PAD_RIGHT);
                                        print str_pad($SeptiembreNoremunerativo, 15, ' ', STR_PAD_RIGHT);
                                        print str_pad($SeptiembreRetenciones, 13, ' ', STR_PAD_RIGHT);
                                        print str_pad($SeptiembreLiquido, 13, ' ', STR_PAD_RIGHT);
                                        print str_pad($OctubreRemunerativo, 13, ' ', STR_PAD_RIGHT);
                                        print str_pad($OctubreNoremunerativo, 15, ' ', STR_PAD_RIGHT);
                                        print str_pad($OctubreRetenciones, 13, ' ', STR_PAD_RIGHT);
                                        print str_pad($OctubreLiquido, 13, ' ', STR_PAD_RIGHT);
                                        print str_pad($NoviembreRemunerativo, 13, ' ', STR_PAD_RIGHT);
                                        print str_pad($NoviembreNoremunerativo, 15, ' ', STR_PAD_RIGHT);
                                        print str_pad($NoviembreRetenciones, 13, ' ', STR_PAD_RIGHT);
                                        print str_pad($NoviembreLiquido, 13, ' ', STR_PAD_RIGHT);
                                        print str_pad($DiciembreRemunerativo, 13, ' ', STR_PAD_RIGHT);
                                        print str_pad($DiciembreNoremunerativo, 15, ' ', STR_PAD_RIGHT);
                                        print str_pad($DiciembreRetenciones, 13, ' ', STR_PAD_RIGHT);
                                        print str_pad($DiciembreLiquido, 13, ' ', STR_PAD_RIGHT);
					
					print "\n";
              }
				print "\nCantidad de empleados activos: $CantEmp\n";
				print "\nCantidad Total de empleado activos: $CantGEmp\n";

pg_close($db);
?>
</Cuerpo>
</Body>
