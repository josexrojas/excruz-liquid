<?php

include('funcs.php');

$CategoriaID = $_REQUEST['CategoriaID'];
$CargoID = $_REQUEST['CargoID'];
$Cargo = $_REQUEST['Cargo'];
$Valor = $_REQUEST['Valor'];
$Periodo = $_REQUEST['Periodo'];

if (!($db = Conectar()))
	exit;
	
if (!($db2 = ConectarSimulador()))
	exit;

$sql2 = "SELECT \"LiquidacionCategoria\"($CategoriaID, '$Cargo', '$Periodo', " . (int)$Valor . ")";


pg_query($db2, $sql2);

	$query = "

SELECT 

  c.categoria AS \"CategoriaID\",
  c.cargo AS \"CargoID\",
  c.\"Categoria\",
  c.\"Cargo\",
  COUNT(DISTINCT \"Legajo\") AS \"Empleados\",
  AVG(c.\"Basico\") AS \"Basico\",
  AVG(c.\"Antiguedad\") AS \"Antiguedad\",
  MIN(c.\"Bruto\") AS \"BrutoMin\", 
  AVG(c.\"Bruto\") AS \"BrutoProm\", 
  MAX(c.\"Bruto\") AS \"BrutoMax\",
  c.agrupamiento AS \"AgrupamientoID\"

  FROM (

   SELECT
    rc.categoria, 
    rca.cargo,
    rc.detalle as \"Categoria\",
    rca.detalle as \"Cargo\",
    r.\"Legajo\",
    SUM(CASE WHEN r.\"AliasID\" IN (1, 2, 11, 121, 138, 141) THEN r.\"Haber1\" ELSE 0 END) AS \"Basico\",
    SUM(CASE WHEN r.\"AliasID\" IN (3, 4, 134, 150, 170, 171, 172) THEN r.\"Haber1\" ELSE 0 END) AS \"Antiguedad\",
    SUM(CASE WHEN r.\"AliasID\" IN (15) THEN r.\"Haber1\" + r.\"Haber2\" ELSE 0 END) AS \"Bruto\",
	er.agrupamiento
	
    FROM \"tblEmpleados\" e
    LEFT JOIN \"tblEmpleadosDatos\" ed ON e.\"EmpresaID\" = ed.\"EmpresaID\" AND e.\"SucursalID\" = ed.\"SucursalID\" AND e.\"Legajo\" = ed.\"Legajo\"
    LEFT JOIN \"tblEmpleadosRafam\" er ON e.\"EmpresaID\" = er.\"EmpresaID\" AND e.\"SucursalID\" = er.\"SucursalID\" AND e.\"Legajo\" = er.\"Legajo\"
    LEFT JOIN owner_rafam.categorias rc ON er.agrupamiento = rc.agrupamiento AND er.categoria = rc.categoria AND rc.jurisdiccion = '1110100000'
    LEFT JOIN owner_rafam.cargos rca ON er.agrupamiento = rca.agrupamiento AND er.categoria = rca.categoria AND er.cargo = rca.cargo AND rca.jurisdiccion = '1110100000'
    INNER JOIN \"tblRecibos\" r ON e.\"EmpresaID\" = r.\"EmpresaID\" AND e.\"SucursalID\" = r.\"SucursalID\" AND e.\"Legajo\" = r.\"Legajo\" AND r.\"Fecha\" = '$Periodo' AND r.\"NumeroLiquidacion\" IN (1, 2)

    WHERE (e.\"FechaEgreso\" > '$Periodo' OR e.\"FechaEgreso\" IS NULL)
    AND (ed.\"FechaIngreso\" <= '$Periodo')
    AND r.\"Descripcion\" NOT LIKE 'AJ.%' AND r.\"ConceptoID\" NOT IN (91)
    AND (
       \"AliasID\" IN (1, 2, 11, 121, 138, 141)
    OR \"AliasID\" IN (3, 4, 134, 150, 170, 171, 172)
    OR \"AliasID\" IN (15)
    )

    AND \"AliasID\" NOT IN (27, 19, 22, 35, 29, 30, 31, 7, 36, 32, 174, 25, 143, 144, 37, 8, 34, 74, 23, 26, 23, 75, 81, 156, 78, 156, 69, 70, 78, 84, 73, 146, 80, 5, 6, 13, 90, 169, 163, 118, 66, 79, 130, 149, 166, 9, 56, 10, 82, 67, 165, 50, 51, 137, 86, 155, 145, 168, 14, 126, 24, 139, 140, 164, 124, 125, 129, 132, 131, 12, 158, 142, 114, 122, 123, 46, 47)

    GROUP BY rc.categoria, rc.detalle, rca.cargo, er.agrupamiento, rca.detalle, r.\"Legajo\"

  ) c
  WHERE c.categoria = '$CategoriaID'
	AND c.cargo = '$CargoID'
	AND c.\"Cargo\" = '$Cargo'
  GROUP BY c.categoria, c.cargo, c.\"Categoria\", c.\"Cargo\", c.agrupamiento
  ORDER BY c.categoria, c.\"Cargo\";

";

	$rs = pg_query($db, $query);
	$rs2 = pg_query($db2, $query);

	if (!$rs || !$rs2){
		exit;
	}


	$i = 0;
	while($row = pg_fetch_array($rs))
	{
		$row2 = @pg_fetch_row($rs2, $i);
		
		$color = ($row['BrutoProm'] > $row2[8]) ? "bgcolor=\"#FF9900\"" : "";
		$color2 = ($row['BrutoMax'] > $row2[9]) ? "bgcolor=\"#FFFF00\"" : "";
		?>
		<tr>
        	<td nowrap><a href="simulador2.php?CategoriaID=<?=$row['CategoriaID']?>&CargoID=<?=$row['CargoID']?>&Periodo=<?=$Periodo?>&AgrupamientoID=<?=$row['AgrupamientoID']?>"><?=$row['Categoria']?></a></td>
            <td nowrap><a href="simulador2.php?CategoriaID=<?=$row['CategoriaID']?>&CargoID=<?=$row['CargoID']?>&Periodo=<?=$Periodo?>&AgrupamientoID=<?=$row['AgrupamientoID']?>"><?=$row['Cargo']?></a></td>
            <td nowrap><?=$row['Empleados']?></td>
            <td nowrap>$ <?=number_format($row['Basico'])?></td>
            <td nowrap>$ <?=number_format($row['Antiguedad'])?></td>
            <td nowrap>$ <?=number_format($row['BrutoMin'])?></td>
            <td nowrap><b>$ <?=number_format($row['BrutoProm'])?></b></td>
            <td nowrap>$ <?=number_format($row['BrutoMax'])?></td>
            <td nowrap><input type="text" size="3" id="id<?=$row['CategoriaID'].$row['Cargo']?>" value="<?=$row2[5]?>"><input type="button" class="recalc" data-text="id<?=$row['CategoriaID'].$row['Cargo']?>" data-categoria="<?=$row['CategoriaID']?>" data-cargoid="<?=$row['CargoID']?>" data-cargo="<?=$row['Cargo']?>" data-periodo="<?=$Periodo?>" value="Recalcular" /></td>
            <td nowrap>$ <?=number_format($row2[7])?></td>
            <td nowrap <?=$color?>><b>$ <?=number_format($row2[8])?></b></td>
            <td nowrap <?=$color2?>>$ <?=number_format($row2[9])?></td>
        </tr>
<?
		$i++;
	}
	
	pg_close($db);
?>
