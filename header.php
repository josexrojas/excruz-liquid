<?
require_once "funcs.php";
EstaLogeado();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//ES" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="imagetoolbar" content="false">
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta http-equiv="X-UA-Compatible" content="IE=7" />
<link href="style.css" rel="stylesheet" media="screen" type="text/css" />
<link href="print.css" rel="stylesheet" media="print" type="text/css" />
<title>LIQUID - ICG Sueldos</title>


</head>
<body>
<DIV class="dvCalendar">
<script src="CalendarControl.js" language="javascript"></script>
</div>
<DIV class="content">
<DIV class="header">
<img src="images/logo.jpg" width="162" height="48" hspace="4" vspace="4" align="left" />
<DIV style="float:right"><BR /><BR />
  <a href="main.php"><img src="images/icon16_Home.gif" width="16" height="16" border="0" align="absmiddle" /> Home</a>&nbsp;&nbsp;&nbsp;<a href="logout.php"> <img src="images/icon16_Logout.gif" width="16" height="16" border="0" align="absmiddle" /> Logout</a>&nbsp;&nbsp;&nbsp; <img src="images/icon16_Help.gif" width="16" height="16" border="0" align="absmiddle" /> Ayuda&nbsp;&nbsp;&nbsp; <img src="images/icon16_Mail.gif" width="16" height="16" border="0" align="absmiddle" /> Cont&aacute;ctenos&nbsp;&nbsp;&nbsp;</DIV>
<BR /><DIV style="margin-top:56px; width:100%; background-image:url(images/ruler.jpg); height:10px"></DIV>
</DIV>
<DIV class="subheader" id="barMenu"><? include("sueldos.php"); ?></DIV>
<DIV class="main">
