<?
require_once('funcs.php');

$Hoy = date("d-m-Y");
?>
<html>
<head>
<base target=_self>
<link href="style.css" rel="stylesheet" type="text/css" />
<script src="CalendarControl.js" language="javascript"></script>
<script type="text/javascript">
function Aceptar(){
	
	 var vReturnValue = new Object();
	 	
        vReturnValue.FechaEgr = document.getElementById("FechaEgr").value;
        vReturnValue.BajaDecreto = document.getElementById("BajaDecreto").value;
		vReturnValue.BajaMotivo = document.getElementById("BajaMotivo").value;
		
        window.returnValue = vReturnValue;
        window.close();
	}


</script>
<title>Borrar Empleado</title>
</head>
<body onLoad="javascript:document.all['FechaEgr'].disabled=false;" style="background-image:url(images/background_img.jpg);"> 
<br>
<table>
<tr nowrap align="center">
	
		<b>&#191;Est&aacute; seguro que quiere borrar este empleado?</b>
	
</tr>
<tr> 
	<td colspan="2">
		Fecha De Egreso:
	</td>
    <td>
		<input type=text name=FechaEgr id=FechaEgr onFocus="showCalendarControl(this);" disabled readonly size=11 value="<?=$Hoy?>">
	</td>
 </tr>
<tr>   
    <td colspan="2">
    Decreto Baja: 
    </td>
    <td>
      <input type=text name=BajaDecreto id=BajaDecreto >
    </td>
</tr>
<tr>   
    <td colspan="2">
    Motivo de Baja: 
    </td>
    <td>
         <textarea name=BajaMotivo id=BajaMotivo ></textarea>
    </td>
</tr>
<tr >
    <td colspan="2" align="right">
    <input type=button value="Aceptar" onClick="return Aceptar()"></td>
    <td align="left"><input type=button value="Cancelar" onClick="javascript:window.returnValue=''; window.close();"></td>
</tr>

</table>
</body>
</html>
