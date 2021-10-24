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
\'\' AS "NumeroOficina",
CASE WHEN e."TipoRelacion" IN (3,4) THEN \'C/EST\' WHEN e."TipoRelacion" = 2 THEN \'JORNA\' WHEN e."TipoRelacion" = 1 THEN \'MENSU\' END AS "TipoRelacionLaboral",
\'F\' AS "Dedicacion",
CASE WHEN ed."LugarPago" IN (0,1,6,7,8,11,13,14,99) THEN \'2\' WHEN ed."LugarPago" IN (2,3,4,5) THEN \'3\' END AS "FormaPago",
\'2\' AS "LugarEntregaCodigo",
\'\' AS "LugarEntregaDescripcion",
CASE WHEN e."TipoRelacion" IN (1,3,4) THEN \'1\' WHEN e."TipoRelacion" = 2 THEN \'3\' END AS "FrecuenciaPago",
\'\' AS "Multiproposito1",
\'\' AS "Multiproposito2",
\'\' AS "Multiproposito3",
\'\' AS "Multiproposito4",
\'\' AS "Multiproposito5",
\'\' AS "Multiproposito6",
CASE WHEN er."agrupamiento" = 2 AND er."categoria" = 99 THEN \'6\' ELSE \'1\' END AS "RelacionLaboral",
\'0\' AS "ProveedorInterno",
\'IPS\' AS "CajaAfjp",
\'\' AS "CajaDescripcion",
CASE WHEN er."agrupamiento" = 2 AND er."categoria" = 99 THEN \'HCD\' WHEN e."TipoRelacion" IN (3,4) THEN \'C\' ELSE \'P\' END AS "EncuadrePrevisionalCodigo",
\'\' AS "EncuadrePrevisionalDescripcion"
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
	echo parse('str', 5, $o["NumeroOficina"]);
	echo parse('str', 5, $o["TipoRelacionLaboral"]);
	echo parse('str', 1, $o["Dedicacion"]);
	echo parse('num', 1, $o["FormaPago"]);
	echo parse('str', 5, $o["LugarEntregaCodigo"]);
	echo parse('str', 30, $o["LugarEntregaDescripcion"]);
	echo parse('str', 5, $o["FrecuenciaPago"]);
	echo parse('str', 9, $o["Multiproposito1"]);
	echo parse('str', 9, $o["Multiproposito2"]);
	echo parse('str', 9, $o["Multiproposito3"]);
	echo parse('str', 9, $o["Multiproposito4"]);
	echo parse('str', 9, $o["Multiproposito5"]);
	echo parse('str', 9, $o["Multiproposito6"]);
	echo parse('num', 2, $o["RelacionLaboral"]);
	echo parse('num', 5, $o["ProveedorInterno"]);
	echo parse('str', 4, $o["CajaAfjp"]);
	echo parse('str', 30, $o["CajaDescripcion"]);
	echo parse('str', 5, $o["EncuadrePrevisionalCodigo"]);
	echo parse('str', 30, $o["EncuadrePrevisionalDescripcion"]);

	echo "\r";
}

?>