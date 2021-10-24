<?php 
require_once('funcs.php');


if (!($db = Conectar()))
	exit;
	
$accion = LimpiarVariable($_POST["accion"]);

EstaLogeado();

include ('header.php');

$EmpresaID = $_SESSION["EmpresaID"];
$SucursalID = $_SESSION["SucursalID"];

?>

<form name=frmlistado_altas_bajas action=listado_altas_bajas.php method=post>
<input type=hidden name=accion id=accion>
<input type=hidden name=listado id=listado>

<?php


if ($accion == 'Generar Listado'){
	
	$FechaDesde = FechaWEB2SQL(LimpiarNumero($_POST["FechaDesde"]));
	
	$FechaHasta = FechaWEB2SQL(LimpiarNumero($_POST["FechaHasta"]));
	
	
	
	$strParams  = '?FechaDesde=' 							. $FechaDesde;	
	$strParams .= '&FechaHasta='							. $FechaHasta;
	$strParams .= '&EmpresaID='							    . $EmpresaID;
	$strParams .= '&SucursalID='							. $SucursalID;
	
	
	
?>
<div id=divLoading style="display:block">
<table height=100% align=center valign=center>
<tr><td><img src="images/icon32_process.gif" align=absmiddle width=32 height=32 border=0> Generando Listado</td></tr>
</table>
</div>
<?php



	$rs = pg_query($db, "
SELECT DISTINCT em.\"Legajo\", em.\"Nombre\", em.\"Apellido\", ed.\"NumeroDocumento\", ed.\"FechaIngreso\",ed.\"AltaDecreto\",er.\"cargo\" , em.\"FechaEgreso\",ed.\"BajaDecreto\",ed.\"BajaMotivo\"
FROM \"tblEmpleados\" em
INNER JOIN \"tblEmpleadosDatos\" ed
ON ed.\"EmpresaID\" = em.\"EmpresaID\" AND ed.\"SucursalID\" = em.\"SucursalID\" AND ed.\"Legajo\" = em.\"Legajo\"
LEFT JOIN \"tblEmpleadosRafam\" er
ON er.\"EmpresaID\" = em.\"EmpresaID\" AND er.\"SucursalID\" = em.\"SucursalID\" AND er.\"Legajo\" = em.\"Legajo\"
WHERE em.\"EmpresaID\" = $EmpresaID AND em.\"SucursalID\" = $SucursalID AND
((ed.\"FechaIngreso\" >= '$FechaDesde' AND ed.\"FechaIngreso\" <= '$FechaHasta') OR (em.\"FechaEgreso\" >= '$FechaDesde' AND em.\"FechaEgreso\" <= '$FechaHasta'))
GROUP BY em.\"Legajo\", em.\"Nombre\", em.\"Apellido\", ed.\"NumeroDocumento\", ed.\"FechaIngreso\", em.\"FechaEgreso\", ed.\"AltaDecreto\",er.\"cargo\"	,ed.\"BajaDecreto\",ed.\"BajaMotivo\"
ORDER BY ed.\"FechaIngreso\" ASC
");

	if (!$rs){
		exit;
	}
	
if (pg_numrows($rs)>0){
	


?>
<h1 align="center">Registro de Personal</h1>

<div><a href="listado_altas_bajas_pdf.php<?= $strParams ?>"><img src="images/pdf.png"   border="0" align="absmiddle"><a  href="listado_altas_bajas_pdf.php<?= $strParams ?>" >  Generar PDF </a></div>
<div><br/></div>

            <table width="100%" border="0" cellpadding="5" cellspacing="1" class="datagrid" >
						 
                         <tr colspan="2"> 
                         <th colspan="3"></th>
                         
                         <th colspan="3" >Alta</th>
                         <th colspan="3" >Baja</th>
                         <th></th>
                         </tr>
                         <tr>
                        <th width="40" >Legajo</th>
                        <th width="130" align="center">Apellido y Nombre</th>
                        <th width="60" align="center">DNI</th>
                        <th width="100" align="center">Fecha de Ingreso</th>
                        <th width="80" align="center">Decreto de Alta</th>
                        <th width="100" align="center">Cargo</th>
                        <th width="100" align="center">Fecha de Egreso</th>
                        <th width="40" align="center">Decreto de Baja</th>
                        <th width="100" align="center">Causa</th>
                        <th width="80" align="center" >Firma</th> 
              </tr>
             
<?php 

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
		?>
		<tr>
        <td width="84"  height="30" align="center" style="font-size:9px" ><?=$Legajo?></td>
        <td width="144" height="30" align="center" style="font-size:9px"><?=$ApeyNom?></td>
        <td width="111" height="30" align="center" style="font-size:9px"><?=$NroDoc?></td>
        <td width="96"  height="30" align="center" nowrap="nowrap" style="font-size:9px"><?=$FechaIng?></td>
        <td width="106" height="30" align="center" style="font-size:9px"><?=$Dalta?></td>
        <td width="76"  height="30" align="center" style="font-size:9px"><?=$row2[0]?></td>
        <td width="89"  height="30" align="center" nowrap="nowrap" style="font-size:9px"><?=$FechaEg?></td>
        <td width="105" height="30"  align="center" style="font-size:9x"><?=$Dbaja?></td>
        <td width="80"  height="30" align="center" style="font-size:9px"><?=$Caus?></td>
        <td width="81" ></td>
        </tr>
        
<?php	} ?>
</table>
<?php	}else{?>

<div class=alerta>No figuran empleados dados de alta</div>
			<?php	
            }}
            ?>

<script>
		document.getElementById('divLoading').style.display = 'none';
</script>

<?php
if ($accion == ''){
	$rs = pg_query($db, "
SELECT DISTINCT extract('year' from \"FechaPeriodo\"), extract('month' from \"FechaPeriodo\")
FROM \"tblPeriodos\"
ORDER BY 1 DESC, 2 DESC
	");
	if (!$rs){
		exit;
	}
?>

<script>

function validar()
{
	var FechaD = document.getElementById('FechaDesde').value;
	var FechaH = document.getElementById('FechaHasta').value;
	
	if (Date.parse(FechaD) > Date.parse(FechaH))
		{
			alert("Rango de Fechas incorrecto!!");
			return false;
			
			}else{
				document.getElementById('accion').value= "Generar Listado";
				document.frmlistado_altas_bajas.submit();
				}
	}

</script>
<script>

function mayor(fecha, fecha2){
	
	var xMes=fecha.substring(3, 5);
	var xDia=fecha.substring(0, 2);
	var xAnio=fecha.substring(6,10);
	var yMes=fecha2.substring(3, 5);
	var yDia=fecha2.substring(0, 2);
	var yAnio=fecha2.substring(6,10);
	if (xAnio > yAnio){
		return(true);
	}else{
		if (xAnio == yAnio){
			if (xMes > yMes){
				return(true);
			}
			if (xMes == yMes){
				if (xDia > yDia){
					return(true);
				}else{
					return(false);
				}
			}else{
				return(false);
			}
		}else{
			return(false);
		}
} 
</script>
<table class="datauser" align="left">

	<TR>
		<TD class="izquierdo">Fecha Desde:</TD><TD class="derecho">
		<input type=text name=FechaDesde id=FechaDesde onfocus="showCalendarControl(this);" readonly size=11 value="<?=$FecDesde?>"></TD>
	</TR>
	<TR>
		<TD class="izquierdo">Fecha Hasta:</TD><TD class="derecho">
		<input type=text name=FechaHasta id=FechaHasta onfocus="showCalendarControl(this);" readonly size=11 value="<?=$FecHasta?>"></TD>
	</TR>
	<TR>
		<TD class="izquierdo"></TD><TD class="derecho"><input type=button onclick="return validar()" id=accion name=accion value="Generar Listado"></TD></TR>
    </table>
	
<?
}
pg_close($db);
?>

</form>

<?php include("footer.php"); ?>
