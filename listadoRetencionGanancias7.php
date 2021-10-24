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

	<H1>Listado de Retenciones de Ganancias</H1>
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

  SUM(
    CASE WHEN ca."ConceptoID" NOT IN (83, 84) AND ca."AliasID" NOT IN (18,   19, 22, 35, 73,   17, 30, 145,     114,   24)
      THEN CASE WHEN "Haber1" IS NULL THEN 0 ELSE "Haber1" END + CASE WHEN "Haber2" IS NULL THEN 0 ELSE "Haber2" END
      ELSE CASE WHEN "Descuento" IS NULL THEN 0 ELSE - "Descuento" END
    END) AS "Total",

  SUM(
    CASE WHEN ca."ConceptoID" NOT IN (83, 84) AND ca."AliasID" NOT IN (18,   19, 22, 35, 73,   17, 30, 145,     114,   24)
      THEN CASE WHEN "Haber1" IS NULL THEN 0 ELSE "Haber1" END + CASE WHEN "Haber2" IS NULL THEN 0 ELSE "Haber2" END
      ELSE CASE WHEN "Descuento" IS NULL THEN 0 ELSE - "Descuento" END
    END) -
  CASE
    WHEN COUNT(DISTINCT r."Fecha") = 1 THEN 7154.08 * 1
    WHEN COUNT(DISTINCT r."Fecha") = 2 THEN 7154.08 * 2
    WHEN COUNT(DISTINCT r."Fecha") = 3 THEN 7154.08 * 3
    WHEN COUNT(DISTINCT r."Fecha") = 4 THEN 7154.08 * 4
    WHEN COUNT(DISTINCT r."Fecha") = 5 THEN 7154.08 * 5
    WHEN COUNT(DISTINCT r."Fecha") = 6 THEN 7154.08 * 6
    WHEN COUNT(DISTINCT r."Fecha") = 7 THEN 7154.08 * 7
    WHEN COUNT(DISTINCT r."Fecha") = 8 THEN 7154.08 * 8
    WHEN COUNT(DISTINCT r."Fecha") = 9 THEN 7154.08 * 9
    WHEN COUNT(DISTINCT r."Fecha") = 10 THEN 7154.08 * 10
    WHEN COUNT(DISTINCT r."Fecha") = 11 THEN 7154.08 * 11
    WHEN COUNT(DISTINCT r."Fecha") = 12 THEN 7154.08 * 12
    WHEN COUNT(DISTINCT r."Fecha") = 13 THEN 7154.08 * 12
  ELSE 0 END -
  CASE
    WHEN COUNT(DISTINCT r."Fecha") = 1 THEN 34339.60 * 1
    WHEN COUNT(DISTINCT r."Fecha") = 2 THEN 34339.60 * 2
    WHEN COUNT(DISTINCT r."Fecha") = 3 THEN 34339.60 * 3
    WHEN COUNT(DISTINCT r."Fecha") = 4 THEN 34339.60 * 4
    WHEN COUNT(DISTINCT r."Fecha") = 5 THEN 34339.60 * 5
    WHEN COUNT(DISTINCT r."Fecha") = 6 THEN 34339.60 * 6
    WHEN COUNT(DISTINCT r."Fecha") = 7 THEN 34339.60 * 7
    WHEN COUNT(DISTINCT r."Fecha") = 8 THEN 34339.60 * 8
    WHEN COUNT(DISTINCT r."Fecha") = 9 THEN 34339.60 * 9
    WHEN COUNT(DISTINCT r."Fecha") = 10 THEN 34339.60 * 10
    WHEN COUNT(DISTINCT r."Fecha") = 11 THEN 34339.60 * 11
    WHEN COUNT(DISTINCT r."Fecha") = 12 THEN 34339.60 * 12
    WHEN COUNT(DISTINCT r."Fecha") = 13 THEN 34339.60 * 12
  ELSE 0 END AS "GananciaNetaSujeta",

  COUNT(DISTINCT r."Fecha") AS "Periodos"

  FROM "tblRecibos" r
  INNER JOIN "tblConceptosAlias" ca ON r."AliasID" = ca."AliasID"
  INNER JOIN "tblEmpleados" e ON r."Legajo" = e."Legajo" and r."EmpresaID"=e."EmpresaID" and r."SucursalID"=e."SucursalID"

  WHERE r."ConceptoID" NOT IN (99) AND "Fecha" >= '2019-01-01' AND "Fecha" < '2020-01-01'
  AND ca."TipoConceptoIPS" != 'AFA'


  GROUP BY e."Legajo",
  e."Nombre" || ' ' || e."Apellido"

  HAVING 
  SUM(
    CASE WHEN ca."ConceptoID" NOT IN (83, 84) AND ca."AliasID" NOT IN (18,   19, 22, 35, 73,   17, 30, 145,     114,   24)
      THEN CASE WHEN "Haber1" IS NULL THEN 0 ELSE "Haber1" END + CASE WHEN "Haber2" IS NULL THEN 0 ELSE "Haber2" END
      ELSE CASE WHEN "Descuento" IS NULL THEN 0 ELSE - "Descuento" END
    END) -
  CASE
    WHEN COUNT(DISTINCT r."Fecha") = 1 THEN 7154.08 * 1
    WHEN COUNT(DISTINCT r."Fecha") = 2 THEN 7154.08 * 2
    WHEN COUNT(DISTINCT r."Fecha") = 3 THEN 7154.08 * 3
    WHEN COUNT(DISTINCT r."Fecha") = 4 THEN 7154.08 * 4
    WHEN COUNT(DISTINCT r."Fecha") = 5 THEN 7154.08 * 5
    WHEN COUNT(DISTINCT r."Fecha") = 6 THEN 7154.08 * 6
    WHEN COUNT(DISTINCT r."Fecha") = 7 THEN 7154.08 * 7
    WHEN COUNT(DISTINCT r."Fecha") = 8 THEN 7154.08 * 8
    WHEN COUNT(DISTINCT r."Fecha") = 9 THEN 7154.08 * 9
    WHEN COUNT(DISTINCT r."Fecha") = 10 THEN 7154.08 * 10
    WHEN COUNT(DISTINCT r."Fecha") = 11 THEN 7154.08 * 11
    WHEN COUNT(DISTINCT r."Fecha") = 12 THEN 7154.08 * 12
    WHEN COUNT(DISTINCT r."Fecha") = 13 THEN 7154.08 * 12
  ELSE 0 END -
  CASE
    WHEN COUNT(DISTINCT r."Fecha") = 1 THEN 34339.60 * 1
    WHEN COUNT(DISTINCT r."Fecha") = 2 THEN 34339.60 * 2
    WHEN COUNT(DISTINCT r."Fecha") = 3 THEN 34339.60 * 3
    WHEN COUNT(DISTINCT r."Fecha") = 4 THEN 34339.60 * 4
    WHEN COUNT(DISTINCT r."Fecha") = 5 THEN 34339.60 * 5
    WHEN COUNT(DISTINCT r."Fecha") = 6 THEN 34339.60 * 6
    WHEN COUNT(DISTINCT r."Fecha") = 7 THEN 34339.60 * 7
    WHEN COUNT(DISTINCT r."Fecha") = 8 THEN 34339.60 * 8
    WHEN COUNT(DISTINCT r."Fecha") = 9 THEN 34339.60 * 9
    WHEN COUNT(DISTINCT r."Fecha") = 10 THEN 34339.60 * 10
    WHEN COUNT(DISTINCT r."Fecha") = 11 THEN 34339.60 * 11
    WHEN COUNT(DISTINCT r."Fecha") = 12 THEN 34339.60 * 12
    WHEN COUNT(DISTINCT r."Fecha") = 13 THEN 34339.60 * 12
  ELSE 0 END > 0

  ORDER BY to_number(e."Legajo", '999999') ASC;
  
FIN;

	$rs = pg_query($db, $sql);

	if (!$rs) exit;

?>
	<table width="100%" border="0" cellpadding="5" cellspacing="1" class="datagrid">
	<tr><th>Legajo</th><th>Apellido y Nombre</th><th>Remuneraci&oacute;n NETA</th><th>Ganancia NETA sujeta</th><th>Periodos</th></tr>

<?
	while($row = pg_fetch_array($rs))
	{
?>
		<tr><td><?=$row[0]?></td><td><?=$row[1]?></td><td>$ <?=number_format($row[2], 2)?></td><td>$ <?=number_format($row[3], 2)?></td><td><?=$row[4]?></td></tr>
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
