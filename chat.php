<?php
/*
Copyright 2009 Federico Larumbe (email: federico.larumbe AT gmail.com)

This file is part of Conversador Plugin.

Conversador Plugin is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Conversador Plugin is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Conversador Plugin.  If not, see <http://www.gnu.org/licenses/>.

*/
@header('Content-Type: text/html; charset=UTF-8');

include_once('ConversadorClass.php');
include_once('conversador.php');

$datos = array(
	       'lang' => stripslashes($_REQUEST['lang']),
	       'nombre' => stripslashes($_REQUEST['nombre']),
	       'conversacionActual' => $_REQUEST['conversacionActual'],
	       'conversacionQueNutre' => $_REQUEST['conversacionQueNutre'],
	       'mensajeQueNutre' => $_REQUEST['mensajeQueNutre'],
	       'nombreInterlocutor' => stripslashes($_REQUEST['nombreInterlocutor']),
	       'textoInterlocutor' => stripslashes($_REQUEST['textoInterlocutor']),
	       'habloInterlocutor' => $_REQUEST['habloInterlocutor']);
if(!$datos['nombre']) {
  $datos['nombre'] = 'Kalko';
}
$cssPersonaje = 'personaje-' . Frase::sinAcentos(mb_strtolower(str_replace(' ', '', $datos['nombre'])));
?>
<html>
<head>
<title><?=__('Chat with', 'conversador') . ' ' . $datos['nombre']?></title>
<link rel="stylesheet" href="conversador.css" type="text/css" />

<meta name="Author" content="Federico Larumbe">
<meta name="Keywords" content="conversation, AI, intelligence, Chat, bot, Conversador, wordpress, plugin, conversar, conversacion, IA, inteligencia, inteligente, robot">
<meta name="Description" lang="en" content="Chat with a fictitious character that learns from you. The bot records all the conversations and uses the users answers to write.">
<meta name="Description" lang="es" content="Plugin de Wordpress que permite chatear con un personaje ficticio que aprende. El bot almacena todas las conversaciones y usa las respuestas de los usuarios para escribir.">

</head>
<body>
<?

if($datos['nombreInterlocutor'])
{
  $conversador = new ConversadorWeb();
  //echo '<div class="debug">Informacion de desarrollo:<br>';
  $conversador->ejecuta($datos);
  //echo '</div>';
  $datos['estado'] = $conversador->estado();
  $datos['esperaParaEscribir'] = $conversador->esperaParaEscribir(); ?>
    <script type="text/javascript">
  
       function hablaPersonaje() {
         var estado = document.getElementById('estadoPersonaje');
	 estado.innerHTML = '<i><?=$datos['nombre'] . ' ' . __('is writing', 'conversador') ?>...</i>';
       }
       function habloPersonaje() {
         var frm = document.getElementById('frmConversador');
	 frm.habloInterlocutor.value = 'N';
	 frm.submit();
       }
       function chatCargado() {
	 <? if($datos['estado'] == 'escribe') { ?>
	      hablaPersonaje();
	      var timerHablo = setTimeout(habloPersonaje, 1000);
	 <? }
	    else { ?>
	      var timerHabla = setTimeout(hablaPersonaje, <?=$datos['esperaParaEscribir']?>);
	      var timerHablo = setTimeout(habloPersonaje, <?=$datos['esperaParaEscribir'] + 3000?>);
	 <? }?>
	 var area = document.getElementById('areaConversacion');
	 area.scrollTop = area.scrollHeight;

         var frm = document.getElementById('frmConversador');
	 frm.textoInterlocutor.focus();
       }
       
       function teclea(e) {
	 if(getKeyCode(e) == 13) {
	   var frm = document.getElementById('frmConversador');
	   frm.submit();
	   return false;
	 }
       }

       function getKeyCode(e) {
	 e= (window.event)? event : e;
	 intKey = (e.keyCode)? e.keyCode: e.charCode;
	 return intKey;
       }
       window.onload = chatCargado;
    </script>

    <form action="chat.php" method="post" id="frmConversador" class="ventanaConversador <?=$cssPersonaje?>">
       <div class="avatar">&nbsp;</div>
       <div id="areaConversacion" class="areaConversacion">
	  <? $conversador->muestra(); ?>
	  <div id="estadoPersonaje"></div><br>
       </div>
       <INPUT TYPE="hidden" NAME="lang" value="<?=$datos['lang']?>">
       <INPUT TYPE="hidden" NAME="nombre" value="<?=$datos['nombre']?>">
       <INPUT TYPE="hidden" NAME="nombreInterlocutor" value="<?=$datos['nombreInterlocutor']?>">
       <INPUT TYPE="hidden" NAME="conversacionActual" value="<?=$conversador->idConversacionActual()?>">
       <INPUT TYPE="hidden" NAME="conversacionQueNutre" value="<?=$conversador->idConversacionQueNutre()?>">
       <INPUT TYPE="hidden" NAME="mensajeQueNutre" value="<?=$conversador->numeroMensajeQueNutre()?>">
       <INPUT TYPE="hidden" NAME="habloInterlocutor" value="S">
       <TEXTAREA NAME="textoInterlocutor" class="textoInterlocutor" ROWS="5" COLS="35" onkeypress="return teclea(event);"><?=$datos['habloInterlocutor'] == 'S' ? '' : $datos['textoInterlocutor']?></TEXTAREA><br>
       <INPUT TYPE="submit" value="<?=__('Send', 'conversador')?>" id="enviar">
    <br>
    <a href="chat.php?nombre=<?=$datos['nombre']?>&lang=<?=$datos['lang']?>"><?=__('See you later...', 'conversador')?></a><br>
    </form><?
}
else { ?>
    <form action="chat.php" method="post" class="frmLoginConversador ventanaConversador <?=$cssPersonaje?>">
      <div class="encabezadoInicio">&nbsp;</div>
      <INPUT TYPE="hidden" NAME="lang" value="<?=$datos['lang']?>">
      <INPUT TYPE="hidden" NAME="nombre" value="<?=$datos['nombre']?>">
      <?=__('Name', 'conversador')?>: <INPUT TYPE="text" NAME="nombreInterlocutor" class="nombreInterlocutor"><br>
      <INPUT TYPE="submit" value="<?=__('Chat', 'conversador')?>" class="ingresar">
    </form><?
}
?>

<a href="unaConversacion.php?nombre=<?=$datos['nombre']?>&lang=<?=$datos['lang']?>" target="_blank" class="leerConversacion"><?=__('Read a previous conversation...', 'conversador')?></a><br>

<p style="font-size: 12px; line-height: 16px;">
<a href="http://hamartia.com.ar/2009/10/19/conversador-english/" target="_blank">Copyright 2010 Federico Larumbe</a><br>
<img src="images/email-federico.jpg" alt="" style="margin-top: 3px;"><br>
GNU General Public License<br></p>
</body>
</html>