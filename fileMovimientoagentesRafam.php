<?php

require_once "funcs.php";
EstaLogeado();

if (!($db = Conectar()))
	exit;

function CambiarFecha($Fecha)
{
	if (sizeof($Fecha) == 0)
		return $Fecha;

	if (strpos($Fecha, '-') === false && strpos($Fecha, '/') === false)
	{
		$Anio 	= substr($Fecha, 0, 4);
		$Mes 	= substr($Fecha, 4, 2);
		$Dia 	= substr($Fecha, 6, 2);
		
		$Fecha = $Dia . '/' . $Mes . '/' . $Anio;
	}
	elseif (strpos($Fecha, '-'))
	{
		$Fecha = implode('-', array_reverse(explode('-', substr($Fecha, 0, 10))));
	}
	elseif (strpos($Fecha, '/'))
	{
		$Fecha = implode('/', array_reverse(explode('/', substr($Fecha, 0, 10))));
	}
	
	return $Fecha;
}

function parse($type, $long, $str)
{
	$diflen = $long - strlen($str);
	
	switch ($type)
	{
		case 'str':
				if ($diflen > 0)
				{
					for ($i=0; $i<$diflen; $i++) 
						$str.= " ";
				}
			break;
			
		case 'num':
				if ($diflen > 0)
				{
					for ($i=0; $i<$diflen; $i++) 
						$str2.= "0";
						
					$str = $str2 . $str;
				}
			break;	
			
		case 'date':
				$str = str_replace("/", "", str_replace("-", "", CambiarFecha($str)));
			break;
			
		case 'cuit':
				$Cuit = str_replace("-", "", trim($str));

				if ( strlen ( $Cuit ) == 11 ) 
					$Cuit = substr ( $Cuit, 0, 2 ) . '-' . substr ( $Cuit, 2, - 1 ) . '-' . substr ( $Cuit, - 1 );

				$str = $Cuit;
				
			break;
	}
	
	return substr(utf8_encode($str), 0, $long);
	//forzar corte a maxlen
}

$query = 'SELECT e."Legajo",
er.cargo AS "NumeroCargo",
CURRENT_DATE AS "FechaMovimiento",
\'2005\' AS "Ejercicio",
er.jurisdiccion AS "Jurisdiccion",
er.agrupamiento AS "Agrupamiento",
\'\' AS "AgrupamientoDescripcion",
er.categoria AS "Categoria",
\'\' AS "CategoriaDescripcion",
er.cargo AS "Cargo",
\'\' AS "CargoDescripcion",
CASE WHEN ed."HorasDiarias" = 6 THEN \'1\' WHEN ed."HorasDiarias" = 9 THEN \'2\' WHEN ed."HorasDiarias" = 8 THEN \'3\' END AS "ModuloHorario",
CASE WHEN ed."HorasDiarias" = 6 THEN \'6 Hs\' WHEN ed."HorasDiarias" = \'9\' THEN \'9 hs\' WHEN ed."HorasDiarias" = 8 THEN \'8 hs\' END AS "ModuloHorarioDescripcion",
(ed."HorasDiarias" * 5) AS "ModuloHorarioHoras",
er.agrupamiento AS "AgrupamientoFuncion",
\'\' AS "AgrupamientoDescripcionFuncion",
er.categoria AS "CategoriaFuncion",
\'\' AS "CategoriaDescripcionFuncion",
er.activ_proy AS "ActividadProyecto",
er.activ_obra AS "ActividadObra",
er.codigo_ff AS "FuenteFinanciamiento",
er."TipoDePlanta" AS "TIpoPlanta",
(SELECT codigo_ue FROM owner_rafam.estruc_prog WHERE anio_presup = \'2014\' AND jurisdiccion = er.jurisdiccion AND programa = er.programa AND activ_proy = er.activ_proy) AS "DependenciaLogica",
(SELECT codigo_ue FROM owner_rafam.estruc_prog WHERE anio_presup = \'2014\' AND jurisdiccion = er.jurisdiccion AND programa = er.programa AND activ_proy = er.activ_proy) AS "DependenciaFisica",
CURRENT_DATE AS "FechaDesde",
\'OT\' AS "FormaAcceso",
\'\' AS "Numero",
CASE WHEN e."TipoRelacion" = 2 THEN \'J\' ELSE \'B\' END AS "TipoRemuneracion",
(SELECT "Haber1" FROM "tblRecibos" WHERE "Legajo" = e."Legajo" ORDER BY "Fecha" DESC LIMIT 1) AS "ImporteRemuneracion",
\'\' AS "Suplemento1",
\'\' AS "Suplemento2"
FROM "tblEmpleados" e
INNER JOIN "tblEmpleadosRafam" er ON e."Legajo" = er."Legajo"
INNER JOIN "tblEmpleadosDatos" ed ON e."Legajo" = ed."Legajo"
WHERE e."FechaEgreso" IS NULL';

$rs = pg_query($db, $query);
if (!$rs)
	exit;

header("Content-disposition: attachment; filename=\"LEGAJOS.txt\"");
header("Content-Type: application/force-download");
header("Content-Transfer-Encoding: binary");
header("Content-Length: ".strlen($filecontent));
header("Pragma: no-cache");
header("Expires: 0");

while ($o = pg_fetch_array($rs))
{
	echo parse('num', 12, $o["Legajo"]);
	echo parse('num', 1, $o["NumeroCargo"]);
	echo parse('date', 8, $o["FechaMovimiento"]);
	echo parse('num', 4, $o["Ejercicio"]);
	echo parse('num', 10, $o["Jurisdiccion"]);
	echo parse('num', 2, $o["Agrupamiento"]);
	echo parse('str', 20, $o["AgrupamientoDescripcion"]);
	echo parse('num', 2, $o["Categoria"]);
	echo parse('str', 20, $o["CategoriaDescripcion"]);
	echo parse('num', 2, $o["Cargo"]);
	echo parse('str', 20, $o["CargoDescripcion"]);
	echo parse('num', 2, $o["ModuloHorario"]);
	echo parse('str', 50, $o["ModuloHorarioDescripcion"]);
	echo parse('num', 2, $o["ModuloHorarioHoras"]);
	echo parse('num', 2, $o["AgrupamientoFuncion"]);
	echo parse('str', 20, $o["AgrupamientoDescripcionFuncion"]);
	echo parse('num', 2, $o["CategoriaFuncion"]);
	echo parse('str', 20, $o["CategoriaDescripcionFuncion"]);
	echo parse('num', 2, $o["ActividadProyecto"]);
	echo parse('num', 2, $o["ActividadObra"]);
	echo parse('num', 3, $o["FuenteFinanciamiento"]);
	echo parse('num', 1, $o["TIpoPlanta"]);
	echo parse('str', 6, $o["DependenciaLogica"]);
	echo parse('str', 6, $o["DependenciaFisica"]);
	echo parse('date', 10, $o["FechaDesde"]);
	echo parse('str', 2, $o["FormaAcceso"]);
	echo parse('str', 20, $o["Numero"]);
	echo parse('str', 1, $o["TipoRemuneracion"]);
	echo parse('num', 10, $o["ImporteRemuneracion"]);
	echo parse('num', 10, $o["Suplemento1"]);
	echo parse('num', 10, $o["Suplemento2"]);

	echo "\r";
}

?>