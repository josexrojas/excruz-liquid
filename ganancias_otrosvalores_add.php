<? include("header.php"); ?><br />
<?php 

if (!($db = Conectar()))
	exit;

if ($_POST)
{
	$Mes					= $_POST['Mes'];
	$Anio					= $_POST['Anio'];
	$PrimaSeguroCasoMuerte	= $_POST['PrimaSeguroCasoMuerte'];
	$GastosSepelio			= $_POST['GastosSepelio'];
	$InteresesHipotecarios	= $_POST['InteresesHipotecarios'];

	$err = 0;

	if ($Mes == '')
		$err+=1;

	if ($Anio == '')
		$err+=2;
	
	if ($PrimaSeguroCasoMuerte== '')
		$err+=4;
	
	if ($GastosSepelio== '')
		$err+=8;
	
	if ($InteresesHipotecarios== '')
		$err+=16;
		
	if ($err == 0)
	{
		$rs = pg_query($db, 'INSERT INTO "tblGananciasOtrosValores" ("Mes", "Anio", "PrimaSeguroCasoMuerte", "GastosSepelio", "InteresesHipotecarios") 
		VALUES ('.$Mes.', '.$Anio.', '.$PrimaSeguroCasoMuerte.', '.$GastosSepelio.', '.$InteresesHipotecarios.')');	
			
		header("Location: ganancias_otrosvalores.php");
		exit;
	}
}
?>

<H1 style="display:inline"><img src="images/icon64_Recibos.gif" width="64" height="64" align="absmiddle" /> Agregar otro valor</H1>
<br><br>
<form method="post">
<table width="100%" border="0" cellpadding="5" cellspacing="1" class="datagrid">
	<tr>
		<th>Periodo</th>
		<th>Prima seguro caso muerte</th>
		<th>Gastos de sepelio</th>
		<th>Intereses Hipotecarios</th>
	</tr>
	<tr>
		<td>
			<select name="Mes">
			<?php for ($i=0;$i<=13;$i++){ ?>
				<option value="<?=$i?>" <?=($Mes == $i) ? "selected=selected" : ""?>><?=$i?></option>
			<?php } ?>				
			</select> / 
			<select name="Anio">
				<option value="2017" <?=($Anio == 2017) ? "selected=selected" : ""?>>2017</option>
				<option value="2018" <?=($Anio == 2018) ? "selected=selected" : ""?>>2018</option>
				<option value="2019" <?=($Anio == 2019) ? "selected=selected" : ""?>>2019</option>
				<option value="2020" <?=($Anio == 2020) ? "selected=selected" : ""?>>2020</option>
			</select>
		</td>		
		<td><input type="text" name="PrimaSeguroCasoMuerte" value="<?=$PrimaSeguroCasoMuerte?>" size="8"> <?=($err & 4) ? "(*)" : ""?></td>
		<td><input type="text" name="GastosSepelio" value="<?=$GastosSepelio?>" size="8"> <?=($err & 8) ? "(*)" : ""?></td>
		<td><input type="text" name="InteresesHipotecarios" value="<?=$InteresesHipotecarios?>" size="8"> <?=($err & 16) ? "(*)" : ""?></td>
	</tr>	
</table>
<br>
<div align="right">
	<input type="submit" value="Guardar">
</div>
</form>
<? include("footer.php"); ?>