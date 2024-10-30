<?php
include_once('ConversadorClass.php');
auth_redirect();
get_header();
?>
<link rel="stylesheet" href="conversador.css" type="text/css" />
<div id="contenido">
  <div id="contenido2">
  
    <div id="post">
    
    <div class="storycontent single listaConversaciones"><?php

$conversador = new ConversadorWeb();
if($conversador->esAdmin()) {
  $datos = array(
		 'lang' => stripslashes($_REQUEST['lang']));
  $personajes = PersonajeFrasesAleatorias::lista();

    echo '<h2>' . __('Conversador', 'conversador') . '</h2>';
    echo '<h3>' . __('Characters', 'conversador') . '</h3><br>';
    echo '<table border="0">
	  <thead>
	     <tr><td width="100">' . __('Name', 'conversador') . '</td>
	     <td width="100">' . __('Conversations', 'conversador') . '</td>
	     <td width="170">' . __('Last conversation with', 'conversador') . '</td>
	     <td width="100">' . __('Messages', 'conversador') . '</td></tr>
	  </thead>';
    foreach($personajes as $personaje) {
      $ultima = Conversacion::ultimaDe($personaje['id']);
      $urlLista = 'listaConversaciones.php?nombre=' . $personaje['nombre'] . '&lang=' . $datos['lang'];
      $urlUltima = 'unaConversacion.php?nombre=' . $personaje['nombre'] . '&lang=' . $datos['lang'] . '&id=' . $ultima['id'];
      echo "<tr><td><a href='$urlLista' class='linklistaConversaciones'>" . $personaje['nombre'] . '</a></td>';
      echo "<td>" . $personaje['cantConversaciones'] . '</td>';
      echo "<td><a href='$urlUltima' class='linklistaConversaciones'>" . $ultima['nombre'] . '</a></td>';
      echo "<td>" . $ultima['cant'] . '</td></tr>';
    }
    echo '</table><br>';
}
else {
     echo "<h3>" . __('Administration priviliges are needed to access this page.', 'conversador') . "</h3>";
} ?>
    </div>
    
    </div>
    </div><?php muestra_sidebar(); ?>
</div>
<div class="clear"></div>
<?php get_footer(); ?>
