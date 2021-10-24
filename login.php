<?
require 'funcs.php';

$usuario = $_POST['username'];
$password = $_POST['password'];

if ($usuario=='' && $password==''){
	ImprimirForm(0);
}else{
	if (!($db = Conectar()))
		exit;
	if (ChequearUsuario($db, $usuario, $password)){
		header("Location: /main.php");
	}else{
		ImprimirForm(1);
	}
}

function ImprimirForm($err)
{
?><HTML><HEAD>
<link href="style.css" rel="stylesheet" type="text/css">
<meta http-equiv="imagetoolbar" content="false">
	</HEAD><BODY onload="javascript:document.all['username'].focus();">
	<DIV class="splash_outer"><DIV class="splash_inner">
	  <form name=login action=/login.php method=post>

<table border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td colspan=2 class=alert>
<?
if ($err == 1){
	print "Usuario o Clave incorrecta";
}
?>&nbsp;</td>
  </tr>
  <tr >
    <td width="59" height="30">Usuario:</td>
    <td width="122" height="30"> <input name=username type=text size="20" onkeypress="javascript: if (window.event.keyCode==13) document.all['password'].focus();"></td>
  </tr>
  <tr>
    <td height="30">Password:</td>
    <td height="30"><input name=password type=password size="20" onkeypress="javascript: if (window.event.keyCode==13) document.all['logbutton'].focus();"></td>
  </tr>
  <tr>
    <td height="30">&nbsp;</td>
    <td height="30"><a href="javascript:document.login.submit(); void(0);" id=logbutton onkeypress="javascript: if (window.event.keyCode==13) submit();" class="tecla"> <img src="images/icon24_enter.gif" alt="Entrar" width="24" height="24" border="0" 
		align="absmiddle">&nbsp;&nbsp;&nbsp;Entrar&nbsp;&nbsp;&nbsp;</a>&nbsp;</td>
  </tr>
   <tr>
    <td height="100%" colspan=2 valign="bottom"><br>
      Olvidó su clave?<BR>
      Ayuda<BR><BR><BR>
      <BR>
      <BR><BR><BR><BR><BR><BR>versión 1.0</td>
  </tr>
</table>
		
	</form></DIV></DIV></BODY></HTML>
<?
}
?>
