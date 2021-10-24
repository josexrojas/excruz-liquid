<?
include ('header.php');

if (!($db = Conectar()))
	exit;

$EmpresaID = $_SESSION["EmpresaID"];
$SucursalID = $_SESSION["SucursalID"];

$accion = LimpiarVariable($_POST["accion"]);
?>

<form name=frmListadoPersonalLiq method=post>
<input type=hidden name=accion id=accion>
<input type=hidden name=listado id=listado>
<script type="text/JavaScript">
<!--
function MM_openBrWindow(theURL,winName,features) { //v2.0
  window.open(theURL,winName,features);
}
//-->
</script>

	<H1>Listado de Empleados Próximos a Jubilarse</H1>
<div id=divLoading style="display:block">
<table height=100% align=center valign=center>
<tr><td><img src="images/icon32_process.gif" align=absmiddle width=32 height=32 border=0> Generando Listado</td></tr>
</table>
</div>
<?
$sql = <<<FIN
select

  e."Legajo",
  e."Nombre" || ' ' || e."Apellido" AS "Nombre",
  ca.detalle AS "Categoria",
  cpe."SueldoBasico",
  DATE_PART('year', AGE(ed."FechaNacimiento")) as "Edad",
  ROUND(SUM(CASE WHEN (re."ConceptoID" in (1, 2, 10) OR re."AliasID" IN (137, 149, 150)) AND "Descripcion" NOT LIKE 'AJ.%' THEN CASE WHEN re."Haber1" IS NULL THEN 0 ELSE re."Haber1" END ELSE 0 END)) AS "Remunerativo",
  ROUND(SUM(CASE WHEN re."ConceptoID" in (20, 21) AND "Descripcion" NOT LIKE 'AJ.%' THEN CASE WHEN re."Haber2" IS NULL THEN 0 ELSE re."Haber2" END ELSE 0 END)) AS "HsExtras",
  ROUND(SUM(CASE WHEN re."ConceptoID" = 99 THEN CASE WHEN re."Haber1" IS NULL THEN 0 ELSE re."Haber1" END + CASE WHEN re."Haber2" IS NULL THEN 0 ELSE re."Haber2" END - CASE WHEN re."Descuento" IS NULL THEN 0 ELSE re."Descuento" END ELSE 0 END - CASE WHEN (re."ConceptoID" in (1, 2, 10) OR re."AliasID" IN (137, 149, 150)) AND "Descripcion" NOT LIKE 'AJ.%' THEN CASE WHEN re."Haber1" IS NULL THEN 0 ELSE re."Haber1" END ELSE 0 END - CASE WHEN re."ConceptoID" in (20, 21) AND "Descripcion" NOT LIKE 'AJ.%' THEN CASE WHEN re."Haber2" IS NULL THEN 0 ELSE re."Haber2" END ELSE 0 END)) AS "RestoConceptos",
  ROUND(SUM(CASE WHEN re."ConceptoID" = 99 THEN CASE WHEN re."Haber1" IS NULL THEN 0 ELSE re."Haber1" END + CASE WHEN re."Haber2" IS NULL THEN 0 ELSE re."Haber2" END - CASE WHEN re."Descuento" IS NULL THEN 0 ELSE re."Descuento" END ELSE 0 END)) AS "Neto"
  
  FROM "tblEmpleados" e
  INNER join "tblEmpleadosRafam" er on e."EmpresaID" = er."EmpresaID" AND e."SucursalID" = er."SucursalID" AND e."Legajo" = er."Legajo"
  INNER join "tblEmpleadosDatos" ed on e."EmpresaID" = ed."EmpresaID" AND e."SucursalID" = ed."SucursalID" AND e."Legajo" = ed."Legajo"
  LEFT JOIN "tblCategoriasPorEmpresa" cpe ON cpe."EmpresaID" = er."EmpresaID" AND cpe."Agrupamiento" = er.agrupamiento AND cpe."Categoria" = er.categoria AND cpe."Cargo" = er.cargo
  LEFT JOIN owner_rafam.cargos ca ON substr(er.jurisdiccion, 1, 5) = substr(ca.jurisdiccion, 1, 5) AND er.agrupamiento = ca.agrupamiento AND er.categoria = ca.categoria AND er.cargo = ca.cargo
  LEFT JOIN "tblRecibos" re ON e."EmpresaID" = re."EmpresaID" AND e."SucursalID" = re."SucursalID" AND e."Legajo" = re."Legajo" AND re."Fecha" = date_trunc('month', now())

  WHERE (e."FechaEgreso" IS NULL OR e."FechaEgreso" > re."Fecha")
  AND ((ed."Sexo" = 'M' AND AGE(ed."FechaNacimiento") >= interval '60 year') OR (ed."Sexo" = 'F' AND AGE(ed."FechaNacimiento") >= interval '55 year'))
  AND e."TipoRelacion" <> 4
  
  GROUP BY e."Legajo",
  e."Nombre" || ' ' || e."Apellido",
  ca.detalle,
  cpe."SueldoBasico",
  AGE(ed."FechaNacimiento")
  
  ORDER BY to_number(e."Legajo", '99999')
FIN;


	$rs = pg_query($db, $sql);

	if (!$rs) exit;

?>
	<table width="100%" border="0" cellpadding="5" cellspacing="1" class="datagrid">
	<tr><th>Legajo</th><th>Apellido y Nombre</th><th>Categoria</th><th>S. Basico</th><th>Edad</th><th>Remunerativo</th><th>Hs Extras</th><th>Restos Conceptos</th><th>Neto</th></tr>

<?
	while($row = pg_fetch_array($rs))
	{
?>
		<tr><td><?=$row[0]?></td><td><?=$row[1]?></td><td><?=$row[2]?></td><td><?=$row[3]?></td><td><?=$row[4]?></td><td><?=$row[5]?></td><td><?=$row[6]?></td><td><?=$row[7]?></td><td><?=$row[8]?></td><td><?=$row[9]?></td></tr>
<?
	}
	print "</table><br>\n";
?>
<script>
	document.getElementById('divLoading').style.display = 'none';
</script>
<?
pg_close($db);
?>
</form>
<? include("footer.php"); ?>
