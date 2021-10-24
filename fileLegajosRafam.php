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

$query = 'SELECT 
e."Legajo", 
e."Apellido", 
e."Nombre", 
CASE WHEN ed."TipoDocumento" = 1 THEN \'DNI\' 	WHEN ed."TipoDocumento" = 2 THEN \'CI\' 	WHEN ed."TipoDocumento" = 3 THEN \'PAS\'	WHEN ed."TipoDocumento" = 4 THEN \'LE\'	WHEN ed."TipoDocumento" = 5 THEN \'LC\'	ELSE \'\'	END AS "TipoDocumento",
ed."NumeroDocumento",
ed."Sexo",
\'CUIL\' AS "CuitCuil",
"CUIT" AS "NumeroCuitCuil",
"NumeroCuenta" AS "NumeroCuentaBeneficiaria",
\'1\' AS "BancoCodigo",
\'Banco Provincia\' AS "BancoDescripcion",
p."SucursalBanco" AS "SucursalBancariaCodigo",
p."Descripcion" AS "Sucursal",
CASE WHEN "EstadoCivil" = 1 THEN \'SOL\' WHEN "EstadoCivil" = 2 THEN \'CAS\' WHEN "EstadoCivil" = 3 THEN \'VIUD\' WHEN "EstadoCivil" = 4 THEN \'DIV\' ELSE \'\' END AS "EstadoCivil",
\'1\' AS "ProfesionCodigo",
\'NO ESPECIFICADA\' AS "ProfesionDescripcion",
ed."FechaNacimiento",
ed."PaisNac",
\'BSAS\' AS "CodigoProvinciaNacimiento",
\'\' AS "ProvinciaNac",
\'CAPSR\' AS "CodigoLocalidadNacimiento",
\'\' AS "LocalidadNac",
\'1\' AS "NacionalidadCodigo",
\'\' AS "Nacionalidad",
edo."Calle",
edo."Numero",
\'\' AS "NumeroMedio",
edo."Piso",
edo."Departamento",
CASE WHEN "Localidad" = \'CAPILLA DEL SEÑOR\' THEN \'CAPSR\' WHEN "Localidad" = \'LOS CARDALES\' THEN \'LCARD\'	WHEN "Localidad" = \'PARADA ROBLES\' THEN \'PROBL\'	ELSE \'CAPSR\'	END AS "LocalidadCodigo",
\'\' AS "LocalidadDescripcion",
\'BSAS\' AS "ProvinciaCodigo",
\'\' AS "ProvinciaDescripcion",
edo."CodigoPostal",
edo."Telefono",
edo."Celular",
edo."Email",
(SELECT "Apellido" || \' \' || "Nombres" FROM "tblEmpleadosFamiliares" WHERE "TipoDeVinculo" = 3 AND "Sexo" = \'M\' AND "Legajo" = e."Legajo") AS "ApellidoNombrePadre",
\' \' AS "VivePadre",
(SELECT "Apellido" || \' \' || "Nombres" FROM "tblEmpleadosFamiliares" WHERE "TipoDeVinculo" = 3 AND "Sexo" = \'F\' AND "Legajo" = e."Legajo") AS "ApellidoNombreMadre",
\' \' AS "ViveMadre",
ed."FechaIngreso"
FROM "tblEmpleados" e
INNER JOIN "tblEmpleadosDatos" ed ON e."Legajo" = ed."Legajo"
INNER JOIN "tblLugaresDePago" p ON ed."LugarPago" = p."LugarPago"
INNER JOIN "tblEmpleadosDomicilio" edo ON e."Legajo" = edo."Legajo"
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
	echo parse('str', 20, $o["Apellido"]);
	echo parse('str', 20, $o["Nombre"]);
	echo parse('str', 5, $o["TipoDocumento"]);
	echo parse('num', 10, $o["NumeroDocumento"]);
	echo parse('str', 1, $o["Sexo"]);
	echo parse('str', 5, $o["CuitCuil"]);
	echo parse('cuit', 13, $o["NumeroCuitCuil"]);
	echo parse('str', 12, $o["NumeroCuentaBeneficiaria"]);
	echo parse('str', 12, $o["NumeroCuentaBeneficiaria"]);
	echo parse('str', 4, $o["BancoCodigo"]);
	echo parse('str', 30, $o["BancoDescripcion"]);
	echo parse('str', 5, $o["SucursalBancariaCodigo"]);
	echo parse('str', 30, $o["Sucursal"]);
	echo parse('str', 5, $o["EstadoCivil"]);
	echo parse('str', 5, $o["ProfesionCodigo"]);
	echo parse('str', 30, $o["ProfesionDescripcion"]);
	echo parse('date', 8, $o["FechaNacimiento"]);
	echo parse('str', 40, $o["PaisNac"]);
	echo parse('str', 5, $o["CodigoProvinciaNacimiento"]);
	echo parse('str', 30, $o["ProvinciaNac"]);
	echo parse('str', 5, $o["CodigoLocalidadNacimiento"]);
	echo parse('str', 30, $o["LocalidadNac"]);
	echo parse('str', 5, $o["NacionalidadCodigo"]);
	echo parse('str', 40, $o["Nacionalidad"]);
	echo parse('str', 40, $o["Calle"]);
	echo parse('str', 5, $o["Numero"]);
	echo parse('str', 3, $o["NumeroMedio"]);
	echo parse('str', 2, $o["Piso"]);
	echo parse('str', 4, $o["Departamento"]);
	echo parse('str', 5, $o["LocalidadCodigo"]);
	echo parse('str', 30, $o["LocalidadDescripcion"]);
	echo parse('str', 5, $o["ProvinciaCodigo"]);
	echo parse('str', 30, $o["ProvinciaDescripion"]);
	echo parse('str', 8, $o["CodigoPostal"]);
	echo parse('str', 18, $o["Telefono"]);
	echo parse('str', 18, $o["Celular"]);
	echo parse('str', 50, $o["Email"]);
	echo parse('str', 40, $o["ApellidoNombrePadre"]);
	echo parse('str', 1, $o["VivePadre"]);
	echo parse('str', 40, $o["ApellidoNombreMadre"]);
	echo parse('str', 1, $o["ViveMadre"]);
	echo parse('str', 40, $o["ApellidoNombrePadre"]);
	echo parse('date', 8, $o["FechaIngreso"]);
		
	echo "\r";
}

?>