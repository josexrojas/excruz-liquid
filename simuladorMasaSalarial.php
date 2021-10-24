<?php

include('funcs.php');

$Periodo = $_REQUEST['Periodo'];

if (!($db = Conectar()))
	exit;
	
if (!($db2 = ConectarSimulador()))
	exit;

	$query = "

SELECT
SUM(CASE WHEN r.\"ConceptoID\" <> 99 AND \"Haber1\" IS NOT NULL THEN \"Haber1\" ELSE 0 END) +
SUM(CASE WHEN r.\"ConceptoID\" <> 99 AND \"Haber2\" IS NOT NULL THEN \"Haber2\" ELSE 0 END) +
SUM(CASE WHEN r.\"ConceptoID\" <> 99 AND \"Aporte\" IS NOT NULL THEN \"Aporte\" ELSE 0 END) +
SUM(CASE WHEN r.\"AliasID\" NOT IN (29, 30) AND \"Aporte\" IS NOT NULL THEN - \"Aporte\" ELSE 0 END) AS \"Importe\"
FROM \"tblEmpleadosRafam\" er
INNER JOIN \"tblEmpleados\" e ON er.\"EmpresaID\" = e.\"EmpresaID\" AND er.\"SucursalID\" = e.\"SucursalID\" AND er.\"Legajo\" = e.\"Legajo\"
INNER JOIN \"tblRecibos\" r ON e.\"EmpresaID\" = r.\"EmpresaID\" AND e.\"SucursalID\" = r.\"SucursalID\" AND e.\"Legajo\" = r.\"Legajo\"

WHERE r.\"Fecha\" = '$Periodo'
HAVING SUM(CASE WHEN r.\"ConceptoID\" <> 99 AND \"Haber1\" IS NOT NULL THEN \"Haber1\" ELSE 0 END) +
SUM(CASE WHEN r.\"ConceptoID\" <> 99 AND \"Haber2\" IS NOT NULL THEN \"Haber2\" ELSE 0 END) +
SUM(CASE WHEN r.\"ConceptoID\" <> 99 AND \"Aporte\" IS NOT NULL THEN \"Aporte\" ELSE 0 END) +
SUM(CASE WHEN r.\"AliasID\" NOT IN (29, 30) AND \"Aporte\" IS NOT NULL THEN - \"Aporte\" ELSE 0 END) <> 0


";

	$rs = pg_query($db, $query);
	$rs2 = pg_query($db2, $query);

	if (!$rs || !$rs2){
		exit;
	}

	$rowAnterior = pg_fetch_row($rs);
	$rowNuevo = pg_fetch_row($rs2);
?>
	<table border="0" cellpadding="1" cellspacing="1" class="datagrid">
        <tr>
            <td style="font-size:19px!important;"><strong>Masa salarial actual: </strong></td>
            <td style="font-size:19px!important;"><strong>$ <?=number_format($rowAnterior[0])?></strong></td>
        </tr>
        <tr>
            <td style="font-size:19px!important;"><strong>Masa salarial recalculada: </strong></td>
            <td style="font-size:19px!important;"><strong>$ <?=number_format($rowNuevo[0])?></strong></td>
        </tr>
        <tr>
            <td style="font-size:19px!important;"><strong>Diferencia: </strong></td>
            <td style="font-size:19px!important;"><strong>$ <?=number_format($rowNuevo[0] - $rowAnterior[0])?></strong></td>
        </tr>
   </table>
<?
	pg_close($db);
?>
