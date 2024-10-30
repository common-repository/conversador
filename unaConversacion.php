<?php
include_once('ConversadorClass.php');
get_header();
?>
<div id="contenido">
  <div id="contenido2">
  
    <div id="post">
    
    <div class="storycontent single"><?php

$conversador = new ConversadorWeb();
$datos = array(
	       'lang' => stripslashes($_REQUEST['lang']),
	       'id' => $_REQUEST['id'],
	       'numero' => $_REQUEST['numero'],
	       'nombre' => stripslashes($_REQUEST['nombre']));
if(!$datos['nombre']) {
  $datos['nombre'] = 'Kalko';
}
$personaje = PersonajeFrasesAleatorias::conNombre($datos['nombre']);
if($datos['id'] == 'ultima') {
  $conversacion = Conversacion::conId($conversador->idUltimaConversacionDe($personaje->id));
}
else if($datos['id']) {
  $conversacion = Conversacion::conId($datos['id']);
}
else if($datos['numero']) {
  $conversacion = Conversacion::numero($datos['numero'], $personaje->id);
}
else {
  $conversacion = Conversacion::unaAleatoriaDe($personaje->id);
}
if($conversacion) {
  echo '<h2>' . __('Conversation', 'conversador') . ' ' . $conversacion->cualNumero() . 
          ' (de ' . $conversador->cantidadDeConversacionesDe($personaje->id) . ') - ' . 
          $conversacion->fecha . '</h2>';
  echo '<h3>' . $conversacion->personaje1->nombre . '</h3><br>';
  echo $conversacion->texto();
  echo '<br><br><a href="unaConversacion.php?nombre=' . $datos['nombre'] . '&lang=' . $datos['lang'] . '">' . __('Another conversation...', 'conversador') . '</a>';
  echo '<br><br><a href="listaConversaciones.php?nombre=' . $datos['nombre'] . '&lang=' . $datos['lang'] . '">' . __('Last conversations...', 'conversador') . '</a>';
}
?>
    </div>
    
    </div>
    </div><?php muestra_sidebar(); ?>
</div>
<div class="clear"></div>
<?php get_footer(); ?>