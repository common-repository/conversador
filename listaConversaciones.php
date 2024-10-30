<?php
include_once('ConversadorClass.php');
get_header();
?>
<link rel="stylesheet" href="conversador.css" type="text/css" />
<div id="contenido">
  <div id="contenido2">
  
    <div id="post">
    
    <div class="storycontent single listaConversaciones"><?php

$conversador = new ConversadorWeb();
$datos = array(
	       'lang' => stripslashes($_REQUEST['lang']),
	       'pag' => $_REQUEST['pag'],
	       'nombre' => stripslashes($_REQUEST['nombre']));
if(!$datos['nombre']) {
  $datos['nombre'] = 'Kalko';
}
if(!$datos['pag']) {
  $datos['pag'] = '1';
}
$personaje = PersonajeFrasesAleatorias::conNombre($datos['nombre']);
$conversaciones = Conversacion::ultimasDe($personaje->id, $datos['pag']);

  echo '<h2>' . __('Last conversations', 'conversador') . '</h2>';
  echo '<h3>' . $datos['nombre'] . '</h3><br>';
  echo '<table border="0">
       	<thead>
	   <tr><td width="100">ID</td>
	   <td width="100">' . __('With', 'conversador') . '</td>
	   <td width="120">' . __('Messages', 'conversador') . '</td></tr>
	</thead>';
  $aConversacionUrl = 'unaConversacion.php?nombre=' . $personaje->nombre . '&lang=' . $datos['lang'];
  foreach($conversaciones as $con) {
    $url = $aConversacionUrl . '&id=' . $con->id;
    echo "<tr><td><a href='$url' class='linklistaConversaciones'>" . $con->id . '</a></td>';
    echo "<td><a href='$url'>" . $con->nombre . '</a></td>';
    echo "<td><a href='$url'>" . $con->cant . '</a></td></tr>';
  }
  echo '</table><br>';
  $base_url = 'listaConversaciones.php?lang=' . $datos['lang'] . '&nombre=' . $datos['nombre'];
  if($datos['pag'] != '1') {
    echo "<a href='$base_url&pag=" . ($datos['pag'] - 1) . "'>" . __('Newer', 'conversador') . '</a> - ';
  }
  echo "<a href='$base_url&pag=" . ($datos['pag'] + 1) . "'>" . __('Older', 'conversador') . '</a> - ';
  echo "<a href='$aConversacionUrl'>" . __('Random conversation', 'conversador') . '</a>';
?>
    </div>
    
    </div>
    </div>
</div>
<div class="clear"></div>
<?php get_footer(); ?>