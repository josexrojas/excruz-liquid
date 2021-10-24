<?
	$rs = pg_query($db, "
SELECT pe.\"FechaPeriodo\", pe.\"NumeroLiquidacion\", pe.\"Descripcion\", pe.\"Estado\", (case when pe.\"Estado\"=2 then 10 else 1 end)
FROM \"tblPeriodos\" pe
WHERE pe.\"EmpresaID\" = $EmpresaID AND pe.\"SucursalID\" = $SucursalID
ORDER BY 5, pe.\"FechaPeriodo\" DESC
	");
	if (!$rs){
		exit;
	}
?>
	<table class="datauser" align="left">
	<TR>
		<TD class="izquierdo">Seleccione Liquidaci&oacute;n:</TD><TD class="derecho"><select id=selPeriodo name=selPeriodo>
<?
	while($row = pg_fetch_array($rs)){
		switch($row[3]){
		case 1:
			$Estado = 'Abierta';
			break;
		case 2:
			$Estado = 'Cerrada';
			break;
		case 3:
			$Estado = 'Confirmada';
			break;
		}
		$Fecha = FechaSQL2WEB($row[0]);
		print "<option value=$row[0]|$row[1]";
		if ($_SESSION["NumeroLiquidacion"] == $row[1] && $_SESSION["FechaPeriodo"] == $row[0])
			print " selected";
		print ">$Fecha - $row[2] ($Estado) </option>\n";
	}
?>
	</select></TD></TR>
