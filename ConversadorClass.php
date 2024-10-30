<?php
/*
Copyright 2009 Federico Larumbe (federico.larumbe AT gmail.com)

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

### Load WP-Config File If This File Is Called Directly
if (!function_exists('add_action')) {
  $wp_root = '../../..';
  if (file_exists($wp_root.'/wp-load.php')) {
    require_once($wp_root.'/wp-load.php');
  } else {
    require_once($wp_root.'/wp-config.php');
  }
}

class Conversador
{
  var $conversacion, $estado;

  function ejecuta($datos) {
    /* (
     'conversacionActual',
     'habloInterlocutor',
     // personaje1
     'nombre',
     'conversacionQueNutre',
     'mensajeQueNutre',
     // personaje2
     'nombreInterlocutor',
     'textoInterlocutor')
    */
    $idConversacionActual = $datos['conversacionActual'];
    if($idConversacionActual) {
      $this->conversacion =& Conversacion::conId($idConversacionActual);
      $this->conversacion->personaje1->conversacionQueNutre =& Conversacion::conId($datos['conversacionQueNutre']);
      $this->conversacion->personaje1->mensajeActual =& Mensaje::identificado($datos['conversacionQueNutre'],
									      $datos['mensajeQueNutre']);
    }
    else {
      $this->conversacion =& Conversacion::conNombres($datos['nombre'], $datos['nombreInterlocutor']);
    }
    if($datos['habloInterlocutor'] == 'S') {
      $this->conversacion->personaje2->dice($datos['textoInterlocutor']);
      $this->estado = 'escribe';
    }
    else {
      $this->conversacion->personaje1->diceFraseAleatoria();
      $this->estado = 'callado';
    }
  }
  
  function esperaParaEscribir() {
    return $this->conversacion->personaje1->esperaParaEscribir();
  }

  function cantidadDeConversacionesDe($idPersonaje) {
    return InterfazBaseDatos::cantidadDeConversacionesDe($idPersonaje);
  }

  function ultimaFraseNormalizada() {
    return $this->conversacion->ultimaFraseNormalizada();
  }

}

class ConversadorWeb 
{
  var $conversador;
  
  function ConversadorWeb() {
    $this->conversador = new Conversador();
  }
  
  function ejecuta($datos) {
    return $this->conversador->ejecuta($datos);
  }

  function estado() {
    return $this->conversador->estado;
  }
  
  function nombre() {
    return $this->conversador->conversacion->personaje1->nombre;
  }
  
  function nombreInterlocutor() {
    return $this->conversador->conversacion->personaje2->nombre;
  }
  
  function idConversacionActual() {
    return $this->conversador->conversacion->id;
  }
  
  function idConversacionQueNutre() {
    return $this->conversador->conversacion->personaje1->conversacionQueNutre->id;
  }
  
  function numeroMensajeQueNutre() {
    return $this->conversador->conversacion->personaje1->mensajeActual->numero;
  }
  
  function mensajes() {
    return $this->conversador->conversacion->mensajes;
  }
  
  function muestra() {
    return $this->conversador->conversacion->muestra();
  }
  
  function texto() {
    return $this->conversador->conversacion->texto();
  }

  function esperaParaEscribir() {
    return $this->conversador->esperaParaEscribir();
  }

  function idUltimaConversacion() {
    return InterfazBaseDatos::idUltimaConversacion();
  }

  function idUltimaConversacionDe($idPersonaje) {
    return InterfazBaseDatos::idUltimaConversacionDe($idPersonaje);
  }

  function cantidadDeConversacionesDe($idPersonaje) {
    return $this->conversador->cantidadDeConversacionesDe($idPersonaje);
  }

  function audioUltimaFrase() {
    return textoNormalizado($this->nombre()) . '/' . $this->conversador->ultimaFraseNormalizada() . '.mp3';
  }

  function esAdmin() {
    global $user_level;
    return $user_level >= 3;
  }

}

class Conversacion 
{
  var $id, $personaje1, $personaje2, $fecha;
  var $mensajes; // por referencia

  function &unaAleatoriaDe($id_personaje) {
    $id = InterfazBaseDatos::conversacionAleatoriaDe($id_personaje);
    if($id) {
      $conversacion =& Conversacion::conId($id);
    }
    else {
      $conversacion = null;
    }
    return $conversacion;
  }

  function &numero($numero, $idPersonaje) {
    $id = InterfazBaseDatos::conversacionNumero($numero, $idPersonaje);
    if($id) {
      $conversacion =& Conversacion::conId($id);
    }
    else {
      $conversacion = null;
    }
    return $conversacion;
  }

  function &ultimasDe($idPersonaje, $pag) {
    return InterfazBaseDatos::ultimasConversacionesDe($idPersonaje, $pag);
  }

  function &ultimaDe($idPersonaje) {
    return InterfazBaseDatos::ultimaConversacionDe($idPersonaje);
  }

  function cualNumero() {
    return InterfazBaseDatos::conversacionCualNumero($this->id, $this->personaje1->id);
  }

  function Conversacion($id, &$personaje1, &$personaje2, &$mensajes, $fecha) {
    $this->id = $id;
    $this->personaje1 =& $personaje1;
    $this->personaje2 =& $personaje2;
    $this->mensajes =& $mensajes;
    $this->fecha = $fecha;
  }

  function &conNombres($nombrePersonaje1, $nombrePersonaje2) {
    $personaje1 =& PersonajeFrasesAleatorias::conNombre($nombrePersonaje1);
    $personaje2 =& PersonajeUsuario::conNombre($nombrePersonaje2);
    $fecha = current_time('mysql');

    $id = InterfazBaseDatos::nuevaConversacion($personaje1->id, $personaje2->id, $fecha);
    $mensajes = array();
    $conversacion = new Conversacion($id, $personaje1, $personaje2, $mensajes, $fecha);
    $conversacion->personaje1->conversacion =& $conversacion;
    $conversacion->personaje2->conversacion =& $conversacion;
    return $conversacion;
  }

  function &conId($id) {
    $datos = InterfazBaseDatos::conversacion($id);
    $personaje1 =& PersonajeFrasesAleatorias::conId($datos['id_personaje1']);
    $personaje2 =& PersonajeUsuario::conId($datos['id_personaje2']);
    $mensajes =& Mensaje::deConversacion($id);
    $conversacion = new Conversacion($id, $personaje1, $personaje2, $mensajes, $datos['fecha']);
    $conversacion->personaje1->conversacion =& $conversacion;
    $conversacion->personaje2->conversacion =& $conversacion;
    return $conversacion;
  }

  function &mensajeParaAlmacenar($id_personaje, $cadena) {
    $mensaje =& Mensaje::paraAlmacenar($id_personaje, $cadena, $this);
    $this->mensajes[] =& $mensaje;
    return $mensaje;
  }

  function &ultimoMensaje() {
    if(count($this->mensajes) > 0)
      $mensaje =& $this->mensajes[count($this->mensajes) - 1];
    else
      $mensaje = null;
    return $mensaje;
  }

  function &ultimaFrase() {
    $mensaje =& $this->ultimoMensaje();
    return $mensaje->frase;
  }

  function proximoNumeroDeMensaje() {
    $mensaje =& $this->ultimoMensaje();
    if($mensaje)
      return $mensaje->numero + 1;
    else
      return 1;
  }

  function siguienteDelMismoPersonaje($mensaje) {
    $nuevoMensaje = $this->siguienteDe($mensaje);
    while($nuevoMensaje && $nuevoMensaje->id_personaje != $mensaje->id_personaje) {
      $nuevoMensaje = $this->siguienteDe($nuevoMensaje);
    }
    return $nuevoMensaje;
  }

  function siguienteDe($mensaje) {
    $siguienteNumero = $mensaje->numero + 1;
    if($siguienteNumero <= count($this->mensajes))
      $mensaje = $this->mensajes[$siguienteNumero - 1];
    else
      $mensaje = null;
    return $mensaje;
  }

  function &primeraRespuesta() {
    $indice = 0;
    while($indice < count($this->mensajes) && 
	  $this->mensajes[$indice]->id_personaje != $this->personaje2->id) {
      $indice++;
    }
    if($indice < count($this->mensajes))
      $mensaje = $this->mensajes[$indice];
    else
      $mensaje = null;
    return $mensaje;
  }

  function &respuestaA($numeroMensaje) {
    //    echo "respuestaA($numeroMensaje): " . $this->mensajes[$numeroMensaje - 1]->frase->texto . '<br>';
    $id_personaje = $this->mensajes[$numeroMensaje - 1]->id_personaje;
    $indice = $numeroMensaje;
    while($indice < count($this->mensajes) && 
	  $this->mensajes[$indice]->id_personaje == $id_personaje) {
      $indice++;
    }
    if($indice < count($this->mensajes))
      $mensaje = $this->mensajes[$indice];
    else
      $mensaje = null;
    return $mensaje;
  }

  function texto() {
    $texto = '';
    foreach($this->mensajes as $mensaje) {
      if($mensaje->id_personaje == $this->personaje1->id)
	$nombre = $this->personaje1->nombre;
      else
	$nombre = $this->personaje2->nombre;
      $texto .= "<b>$nombre</b>: " . $mensaje->textoMostradoEn($this) . "<br><br>";
    }
    return $texto;
  }

  function muestra() {
    echo $this->texto();
  }

  function ultimosMensajesDe(&$personaje) {
    $indice = count($this->mensajes) - 1;
    $cant = 0;
    while($indice >= 0 && 
	  $this->mensajes[$indice]->id_personaje == $personaje->id) {
      $indice--;
      $cant++;
    }
    return $cant;
  }

  function ultimaFraseNormalizada() {
    $mensaje = $this->ultimoMensaje();
    return $mensaje->textoNormalizadoEn($this);
  }
}

class Personaje
{
  var $id, $nombre, $protegido;
  var $conversacion;

  function dice($cadena) {
    if($this->hablo()) {
      $mensaje =& $this->conversacion->mensajeParaAlmacenar($this->id, $cadena);
    }
  }

  function hablo() {
    return true;
  }

  function &conDatos(&$datos) {
    $personaje = new Personaje();
    $personaje->id = $datos['id'];
    $personaje->nombre = $datos['nombre'];
    $personaje->protegido = $datos['protegido'];
    return $personaje;
  }

  function &conNombre($nombre, $protegido) {
    $nombre = Personaje::nombreAdecuado($nombre);
    $personaje =& InterfazBaseDatos::personajeConNombre($nombre);
    if($personaje) {
      if($personaje->protegido != $protegido) {
	$personaje = Personaje::conNombre($nombre . '-bis', $protegido); // se asegura que el valor de protegido sea correcto (sino el nombre es con '-bis')
      }
    }
    else {
      $datos = array('id' => null, 'nombre' => $nombre, 'protegido' => $protegido);
      $personaje =& Personaje::conDatos($datos);
      $personaje->id = InterfazBaseDatos::nuevoPersonaje($personaje);
    }
    return $personaje;
  }

  function nombreAdecuado($nombre) {
    if(stripos('personaje', $nombre) !== FALSE) {
      $nombre .= '-bis'; // para que no haya problemas cuando se reemplaza por la variable
    }
    return $nombre;
  }

  function &conId($id) {
    return InterfazBaseDatos::personaje($id);
  }

  function copiaDe(&$otroPersonaje) {
    $this->id = $otroPersonaje->id;
    $this->nombre = $otroPersonaje->nombre;
    $this->protegido = $otroPersonaje->protegido;
  }
}

class PersonajeUsuario extends Personaje
{
  function PersonajeUsuario(&$otroPersonaje) {
    $this->copiaDe($otroPersonaje);
  }

  function &conNombre($nombre) {
    $personajeGuardado =& Personaje::conNombre($nombre, false);
    $personaje =& new PersonajeUsuario($personajeGuardado);
    return $personaje;
  }

  function &conId($id) {
    $personajeGuardado =& Personaje::conId($id);
    $personaje =& new PersonajeUsuario($personajeGuardado);
    return $personaje;
  }
}

class PersonajeFrasesAleatorias extends Personaje 
{
  var $conversacionQueNutre;
  var $mensajeActual;        // mensaje actual de la conversación que nutre

  function PersonajeFrasesAleatorias(&$otroPersonaje) {
    $this->copiaDe($otroPersonaje);
  }

  function &conNombre($nombre) {
    $personajeGuardado =& Personaje::conNombre($nombre, true);
    $personaje =& new PersonajeFrasesAleatorias($personajeGuardado);
    return $personaje;
  }

  function &conId($id) {
    $personajeGuardado =& Personaje::conId($id);
    $personaje =& new PersonajeFrasesAleatorias($personajeGuardado);
    return $personaje;
  }

  function &lista() {
    return InterfazBaseDatos::personajesInteligentes();
  }

  function diceFraseAleatoria() {
    $this->dice($this->proximaFrase());
  }

  function proximaFrase() {
    //echo 'respuestaAMensajeSimilar<br>';
    $mensaje = $this->respuestaAMensajeSimilar();
    if(!$mensaje && $this->conversacionQueNutre) {
      //echo 'proximoMensaje<br>';
      $mensaje = $this->proximoMensaje();
    }
    if(!$mensaje) {
      //echo 'mensajeAleatorio<br>';
      $mensaje = $this->mensajeAleatorio();
    }
    if(!$mensaje) {
      //echo 'mensajeFijo<br><br>';
      $mensaje = $this->mensajeFijo();
    }
    //echo "conversacionQueNutre: " . $this->conversacionQueNutre->id . '<br>';
    $this->mensajeActual = $mensaje;
    return $mensaje->textoPersonajesInvertidosEn($this->conversacion);
  }

  function proximoMensaje() {
    return $this->conversacionQueNutre->siguienteDelMismoPersonaje($this->mensajeActual);
  }

  function mensajeAleatorio() {
    $this->conversacionQueNutre = Conversacion::unaAleatoriaDe($this->conversacion->personaje1->id);
    if($this->conversacionQueNutre)
      $mensaje =& $this->conversacionQueNutre->primeraRespuesta();
    else
      $mensaje = null;
    return $mensaje;
  }

  function respuestaAMensajeSimilar() {
    $ultimoMensaje = $this->conversacion->ultimoMensaje();
    //echo "<br>ultimoMensaje->texto: " . $ultimoMensaje->frase->texto;
    //echo "<br>ultimoMensaje->id_personaje: " . $ultimoMensaje->id_personaje;
    if($ultimoMensaje->id_personaje == $this->conversacion->personaje2->id) {
      list($idConversacion, $numeroMensaje) = $this->mensajeSimilarA($ultimoMensaje->frase);
      //echo "<br>similar idConversacion: $idConversacion<br>";
      //echo "numeroMensaje: $numeroMensaje<br>";
      if($idConversacion) {
	$this->conversacionQueNutre = Conversacion::conId($idConversacion);
	$mensaje = $this->conversacionQueNutre->respuestaA($numeroMensaje);
      }
      else {
	$mensaje = null;
      }
    }
    else {
      $mensaje = null;
    }
    return $mensaje;
  }

  function mensajeSimilarA(&$unaFrase) {
    $res = InterfazBaseDatos::conversacionMensajeSimilar($this->id, $unaFrase->texto);
    if(!$res) {
      $subfrases = $unaFrase->subfrases();
      if(count($subfrases) > 0) {
	$res = InterfazBaseDatos::conversacionMensajeSimilar($this->id, $subfrases[count($subfrases) - 1]);
      }
    }
    return $res;
  }

  function mensajeFijo() {
    return MensajeVolatil::conTexto(__('How is it going $personaje1?', 'conversador'));
  }

  function esperaParaEscribir() {
    $ultimosMensajesPropios = $this->conversacion->ultimosMensajesDe($this);
    return pow(2, $ultimosMensajesPropios) * 18000;
  }

}

class Mensaje
{
  var $id_conversacion, $numero, $id_personaje, $frase;

  function &identificado($idConversacion, $numero) {
    $mensaje = Mensaje::conDatos(InterfazBaseDatos::mensaje($idConversacion, $numero));
    return $mensaje;
  }

  function &paraAlmacenar($id_personaje, $texto, &$conversacion) {
    return Mensaje::conFrase($id_personaje,
			     Frase::paraAlmacenar($texto, $conversacion->personaje1, $conversacion->personaje2),
			     $conversacion);
  }

  function &conFrase($id_personaje, &$frase, &$conversacion) {
    $mensaje = new Mensaje();
    $mensaje->id_conversacion = $conversacion->id;
    $mensaje->numero = $conversacion->proximoNumeroDeMensaje();
    $mensaje->id_personaje = $id_personaje;
    $mensaje->frase =& $frase;
    $mensaje->agrega();
    return $mensaje;
  }

  function agrega() {
    InterfazBaseDatos::nuevoMensaje($this);
  }

  function &deConversacion($idConversacion) {
    $mensajes = InterfazBaseDatos::mensajesDe($idConversacion);
    return Mensaje::mensajesConDatos($mensajes);
  }

  function &mensajesConDatos(&$datosMensajes) {
    if($datosMensajes)
      $mensajes = array_map("MensajeConDatos", $datosMensajes);
    else
      $mensajes = array();
    return $mensajes;
  }

  function &conDatos(&$datos) {
    $mensaje = new Mensaje(); 
    $mensaje->id_conversacion = $datos['id_conversacion'];
    $mensaje->numero = $datos['numero'];
    $mensaje->id_personaje = $datos['id_personaje'];
    $mensaje->frase =& Frase::conId($datos['id_frase']);
    return $mensaje;
  }

  function textoMostradoEn(&$conversacion) {
    return $this->frase->textoMostrado($conversacion->personaje1, $conversacion->personaje2);
  }

  function textoPersonajesInvertidosEn(&$conversacion) {
    return $this->frase->textoMostrado($conversacion->personaje2, $conversacion->personaje1);
  }

  function textoNormalizadoEn(&$conversacion) {
    return textoNormalizado($this->textoMostradoEn($conversacion));
  }
  
}

function &MensajeConDatos(&$datos) {
  $mensaje =& Mensaje::conDatos($datos);
  return $mensaje;
}

class MensajeVolatil extends Mensaje
{

  function &conTexto($texto) {
    $mensaje = new MensajeVolatil();
    $datos = array('id' => -1, 'texto' => $texto);
    $mensaje->frase = Frase::conDatos($datos);
    return $mensaje;
  }

}

class Frase
{
  var $id, $texto;

  function &conId($id) {
    $datos = InterfazBaseDatos::frase($id);
    $frase = new Frase();
    $frase->id = $id;
    $frase->texto = $datos['texto'];
    return $frase;
  }

  function &conTexto($texto) {
    $datos = InterfazBaseDatos::fraseConTexto($texto);
    if(!$datos) {
      $datos['id'] = InterfazBaseDatos::nuevaFrase($texto);
      $datos['texto'] = $texto;
    }
    return Frase::conDatos($datos);
  }

  function &conDatos(&$datos) {
    $frase = new Frase();
    $frase->id = $datos['id'];
    $frase->texto = $datos['texto'];
    return $frase;
  }

  function &paraAlmacenar($texto, &$personaje1, &$personaje2) {
    $texto = Frase::reemplazaNombre($texto, $personaje1->nombre, '$personaje1');
    $texto = Frase::reemplazaNombre($texto, $personaje2->nombre, '$personaje2');
    return Frase::conTexto($texto);
  }

  function textoMostrado(&$personaje1, &$personaje2) {
    $texto = Frase::reemplazaNombre($this->texto, '$personaje1', $personaje1->nombre);
    $texto = Frase::reemplazaNombre($texto, '$personaje2', $personaje2->nombre);
    return $texto;
  }

  /*
   * Devuelve el resultado de reemplazar $unTexto por $otroTexto en $texto sin considerar los acentos, ni mayúsculas, ni minúsuculas al momento de buscar.
   */
  function reemplazaNombre($texto, $unTexto, $otroTexto) {
    //    echo "<br>reemplazaNombre($texto, $unTexto, $otroTexto)";
    $unTextoS = Frase::sinAcentos($unTexto);
    $encontro = true;
    $longi = mb_strlen($unTexto);
    $pos = 0;
    do {
      $textoS = Frase::sinAcentos($texto);
      $pos = stripos($textoS, $unTextoS, $pos);
      if($pos !== FALSE) {
	$texto = mb_substr($texto, 0, $pos) . 
	            $otroTexto . 
	            mb_substr($texto, $pos+$longi);
	$pos += mb_strlen($otroTexto);
      }
    } while($pos !== FALSE);
    return $texto;
  }

  function sinAcentos($cadena) { //UTF8 compatible
    $acentos = "¥µÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýÿ";
    $sinAcentos = "YuAAAAAAACEEEEIIIIDNOOOOOOUUUUYBaaaaaaaceeeeiiiionoooooouuuuyy";
    $length = mb_strlen($cadena);
    $traducida = '';
    for($i=0; $i<$length; $i++) {
      $char = mb_substr($cadena, $i, 1);
      $pos = mb_strpos($acentos, $char);
      if($pos !== FALSE) {
	$traducida .= mb_substr($sinAcentos, $pos, 1);
      }
      else {
	$traducida .= $char;
      }
    }
    return $traducida;
  }

  function subfrases() {
    return preg_split('/( *[?,;:.!\xbf\xa1] *)+/',         // con espacios intermedios
		      $this->texto,
		      -1,
		      PREG_SPLIT_NO_EMPTY);
  }

}

class InterfazBaseDatos {
  function &personaje($id) {
    global $wpdb;
    $p = rowUTF8Decoded($wpdb->get_row("SELECT * FROM ".$wpdb->prefix."conversador_personaje WHERE id = $id", ARRAY_A));
    if ($p) {
      $personaje =& Personaje::conDatos($p);
    }
    else {
      $personaje = null;
    }
    return $personaje;
  }

  function &personajesInteligentes() {
    global $wpdb;
    return $wpdb->get_results("
    	   SELECT p.id, p.nombre, Count(*) as cantConversaciones
	   FROM ".$wpdb->prefix."conversador_personaje p INNER JOIN ".$wpdb->prefix."conversador_conversacion c
	   	ON p.id = c.id_personaje1
	   WHERE protegido = 1
	   GROUP BY p.id, p.nombre
	   ORDER BY nombre", ARRAY_A);
  }

  function conversacion($id) {
    global $wpdb;
    return rowUTF8Decoded($wpdb->get_row("SELECT * FROM ".$wpdb->prefix."conversador_conversacion WHERE id = " . $wpdb->escape($id), ARRAY_A));
  }

  /*
   * Una conversación entre las del personaje que tenga por lo menos una respuesta.
   */
  function &conversacionAleatoriaDe($id_personaje1) {
    global $wpdb;
    $conversaciones = $wpdb->get_col("
              SELECT DISTINCT id_conversacion 
              FROM ".$wpdb->prefix."conversador_conversacion c 
                        INNER JOIN ".$wpdb->prefix."conversador_mensaje m ON c.id = m.id_conversacion
              WHERE c.id_personaje1 = $id_personaje1 AND m.id_personaje <> $id_personaje1");
    if($conversaciones)
      $conv =& $conversaciones[array_rand($conversaciones)];
    else
      $conv = null;
    return $conv;
  }

  function &conversacionNumero($numero, $idPersonaje) {
    global $wpdb;
    $conversaciones = $wpdb->get_col("
              SELECT id
              FROM ".$wpdb->prefix."conversador_conversacion
              WHERE id_personaje1 = $idPersonaje
              ORDER BY id");
    if($conversaciones && 1 <= $numero && $numero <= count($conversaciones))
      $id = $conversaciones[$numero - 1];
    else
      $id = null;
    return $id;
  }

  function &ultimasConversacionesDe($idPersonaje, $pag) {
    global $wpdb;
    $conversaciones = $wpdb->get_results("
              SELECT c.id, p.nombre, Count(c.id) as cant
              FROM ".$wpdb->prefix."conversador_conversacion c
	      	        INNER JOIN ".$wpdb->prefix."conversador_personaje p
			   ON c.id_personaje2 = p.id
			INNER JOIN ".$wpdb->prefix."conversador_mensaje m
			   ON c.id = m.id_conversacion
              WHERE id_personaje1 = $idPersonaje
	      GROUP BY c.id, p.nombre
              ORDER BY c.id DESC
	      LIMIT " . ($pag - 1) * 10 . ", 10");
    return $conversaciones;
  }

  function &ultimaConversacionDe($idPersonaje) {
    global $wpdb;
    $conversacion = $wpdb->get_row("
              SELECT c.id, p.nombre, Count(c.id) as cant
              FROM ".$wpdb->prefix."conversador_conversacion c
	      	        INNER JOIN ".$wpdb->prefix."conversador_personaje p
			   ON c.id_personaje2 = p.id
			INNER JOIN ".$wpdb->prefix."conversador_mensaje m
			   ON c.id = m.id_conversacion
              WHERE id_personaje1 = $idPersonaje
	      GROUP BY c.id, p.nombre
              ORDER BY c.id DESC
	      LIMIT 0, 1", ARRAY_A);
    return $conversacion;
  }

  function &conversacionCualNumero($idConversacion, $idPersonaje) {
    global $wpdb;
    $conversaciones = $wpdb->get_col("
              SELECT id
              FROM ".$wpdb->prefix."conversador_conversacion
              WHERE id_personaje1 = $idPersonaje
              ORDER BY id");
    if($conversaciones) {
      $numero = array_search($idConversacion, $conversaciones);
      $numero = $numero !== FALSE ? $numero + 1 : FALSE;
    }
    else
      $numero = FALSE;
    return $numero;
  }

  function conversacionMensajeSimilar($id_personaje, $texto) {
    global $wpdb;
    $res = resultsUTF8Decoded($wpdb->get_results($wpdb->prepare(
			  "SELECT id_conversacion, numero 
                           FROM ".$wpdb->prefix."conversador_mensaje m INNER JOIN ".$wpdb->prefix."conversador_frase f ON m.id_frase = f.id
                           WHERE id_personaje = %d AND
                                   texto SOUNDS LIKE %s", $id_personaje, utf8_encode($texto)), ARRAY_N));
    if($res)
      $res = $res[array_rand($res)];
    else
      $res = null;
    return $res;
  }

  function mensajesDe($idConversacion) {
    global $wpdb;
    $mensajes = resultsUTF8Decoded($wpdb->get_results($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."conversador_mensaje WHERE id_conversacion = %d ORDER BY numero", $idConversacion), ARRAY_A));
    return $mensajes;
  }

  function mensaje($idConversacion, $numero) {
    global $wpdb;
    return rowUTF8Decoded($wpdb->get_row($wpdb->prepare("
                                          SELECT * 
                                          FROM ".$wpdb->prefix."conversador_mensaje 
                                          WHERE id_conversacion = %d AND numero = %d",
							$idConversacion, 
							$numero), ARRAY_A));
  }

  function frase($id) {
    global $wpdb;
    return rowUTF8Decoded($wpdb->get_row("SELECT * FROM ".$wpdb->prefix."conversador_frase WHERE id = $id", ARRAY_A));
  }

  function &personajeConNombre($nombre) {
    global $wpdb;
    $p = rowUTF8Decoded($wpdb->get_row($wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'conversador_personaje WHERE nombre = %s', codedToDatabase($nombre)), ARRAY_A));
    if ($p) {
      $personaje =& Personaje::conDatos($p);
    }
    else {
      $personaje = null;
    }
    return $personaje;
  }

  function fraseConTexto($texto) {
    global $wpdb;
    $frase = rowUTF8Decoded($wpdb->get_row($wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'conversador_frase WHERE texto = %s', codedToDatabase($texto)), ARRAY_A));
    return $frase;
  }

  function nuevoPersonaje(&$personaje) {
    global $wpdb;
    $wpdb->insert($wpdb->prefix.'conversador_personaje', array('nombre' => codedToDatabase($personaje->nombre), 
						    'protegido' => $personaje->protegido ));
    return $wpdb->insert_id;
  }

  function nuevaConversacion($idPersonaje1, $idPersonaje2, $fecha) {
    global $wpdb;
    $wpdb->insert($wpdb->prefix.'conversador_conversacion', array('fecha' => $fecha,
								  'id_personaje1' => $idPersonaje1, 
								  'id_personaje2' => $idPersonaje2));
    return $wpdb->insert_id;
  }

  function nuevoMensaje(&$mensaje) {
    global $wpdb;
    $wpdb->insert($wpdb->prefix.'conversador_mensaje', array('id_conversacion' => $mensaje->id_conversacion,
						  'numero' => $mensaje->numero,
						  'id_personaje' => $mensaje->id_personaje,
						  'id_frase' => $mensaje->frase->id));
    return $wpdb->insert_id;
  }

  function nuevaFrase($texto) {
    global $wpdb;
    $wpdb->insert($wpdb->prefix.'conversador_frase', array('texto' => codedToDatabase($texto)));
    return $wpdb->insert_id;
  }

  function nuevasTablas() {
    global $wpdb;
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
    $sql = "CREATE TABLE " . $wpdb->prefix . "conversador_conversacion (
              id int(11) NOT NULL auto_increment,
              fecha datetime default NULL,
              id_personaje1 int(11) NOT NULL,
              id_personaje2 int(11) NOT NULL,
              PRIMARY KEY  (id)
            )";
    dbDelta($sql);

    $sql = "CREATE TABLE " . $wpdb->prefix . "conversador_frase (
              id int(11) NOT NULL auto_increment,
              texto text,
              PRIMARY KEY  (id)
            )";
    dbDelta($sql);

    $sql = "CREATE TABLE " . $wpdb->prefix . "conversador_mensaje (
              id int(11) NOT NULL auto_increment,
              id_conversacion int(11) NOT NULL,
              numero int(11) NOT NULL,
              id_personaje int(11) default NULL,
              id_frase int(11) NOT NULL,
              PRIMARY KEY  (id),
              UNIQUE KEY mensajeIdentificado (id_conversacion,numero)
            )";
    dbDelta($sql);

    $sql = "CREATE TABLE " . $wpdb->prefix . "conversador_personaje (
              id int(11) NOT NULL auto_increment,
              nombre varchar(50) default NULL,
              protegido tinyint(4) NOT NULL,
              PRIMARY KEY  (id)
            )";
    dbDelta($sql);

    add_option("conversador_db_version", "1.0");
  }

  function existeTabla($nombre) {
    global $wpdb;
    return $wpdb->get_var("SHOW TABLES LIKE '$nombre'") == $nombre;
  }

  function idUltimaConversacion() {
    global $wpdb;
    return $wpdb->get_var('SELECT Max(id) FROM wp_conversador_conversacion');
  }

  function idUltimaConversacionDe($idPersonaje) {
    global $wpdb;
    return $wpdb->get_var($wpdb->prepare('SELECT Max(id) FROM wp_conversador_conversacion WHERE id_personaje1 = %d', $idPersonaje));
  }

  function cantidadDeConversacionesDe($idPersonaje) {
    global $wpdb;
    return $wpdb->get_var($wpdb->prepare('SELECT Count(*) FROM wp_conversador_conversacion WHERE id_personaje1 = %d', $idPersonaje));
  }

}

function fechaMysql($fecha) {
  return gmdate('Y-m-d H:i:s', $fecha);
}

function codedToDatabase($texto) {
  //utf8_encode
  return $texto;
}

function valueUTF8Decoded($value) {
  return $value;
  if(is_string($value))
    return utf8_decode($value);
  else
    return $value;
}

function rowUTF8Decoded($row) {
  if($row)
    return array_map("valueUTF8Decoded", $row);
  else
    return $row;
}

function resultsUTF8Decoded($results) {
  if($results)
    return array_map("rowUTF8Decoded", $results);
  else
    return $results;
}

function maybeUTF8Decode($string) {
  return mb_detect_encoding($string." ",'UTF-8,ISO-8859-1') == 'UTF-8' ? utf8_decode($string) : $string;
}

function textoNormalizado($texto) {
  $texto = Frase::sinAcentos($texto);
  return str_replace(' ', '-', preg_replace('/[?,;:.!\xbf\xa1]/', '', strtolower($texto)));
}
?>