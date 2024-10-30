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
@header('Content-Type: text/html; charset=UTF-8');

include('ConversadorClass.php');

//conversadorInstall();

class TestConversacion
{
  var $conversacion;

  function testConversacionConNombre() {
    $unaConversacion = Conversacion::conNombres('Kalkotest', 'Pedro');
    assert($unaConversacion->id);
    assert($unaConversacion->personaje1->id);
    assert($unaConversacion->personaje2->id);
    assert(stristr($unaConversacion->personaje1->nombre, 'kalko') == 0);
    assert(stristr($unaConversacion->personaje2->nombre, 'pedro') == 0);

    $otraConversacion = Conversacion::conNombres('Kalkotest', 'Pedro');
    assert($otraConversacion->id != $unaConversacion->id);
    assert($otraConversacion->personaje1->id == $unaConversacion->personaje1->id);
    assert($otraConversacion->personaje2->id == $unaConversacion->personaje2->id);
  }

  function testMensajes() {
    $unaConversacion = Conversacion::conNombres('Kalkotest', 'Pedro');
    $unaConversacion->personaje1->dice('Hola Pedro');
    $unaConversacion->personaje2->dice('Hola Kalkotest');
    $unaConversacion->personaje1->dice('Todo bien?');
    $unaConversacion->personaje2->dice("S\xc3\xad, todo bien.");
    $unaConversacion->personaje1->dice('Seguro?');
    $unaConversacion->personaje2->dice("S\xc3\xad, todo bien.");

    assert($unaConversacion->personaje1->conversacion->id == $unaConversacion->personaje2->conversacion->id);
    assert($unaConversacion->personaje1->conversacion->personaje1->id == $unaConversacion->personaje2->conversacion->personaje1->id);
    assert($unaConversacion->personaje1->conversacion->mensajes == $unaConversacion->personaje2->conversacion->mensajes);
    assert(count($unaConversacion->mensajes) == 6);

    $primero = $unaConversacion->mensajes[0];
    $segundo = $unaConversacion->mensajes[1];
    $cuarta = $unaConversacion->mensajes[3];
    $ultimo = $unaConversacion->ultimoMensaje();

    assert($primero->id_personaje);
    assert($primero->id_personaje == $unaConversacion->personaje1->id);
    assert($segundo->id_personaje == $unaConversacion->personaje2->id);

    assert($ultimo->numero == 6);
    assert($ultimo->frase->id);
    assert($ultimo->frase->texto == "S\xc3\xad, todo bien.");
    assert($ultimo->frase->id == $cuarta->frase->id);
    assert($ultimo->frase->id != $primero->frase->id);
  }

  function testTraduceAcentosUTF8() {
    $cadena1UTF8 = "¥µÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýÿ";
    /*echo "$cadena1UTF8<br>";
    echo "Traducida: " . Frase::sinAcentos($cadena1UTF8) . "<br>";
    echo "Traducir \xc3\xad: " . Frase::sinAcentos("\xc3\xad") . "<br>";
    echo "Traducir í: " . Frase::sinAcentos("í") . "<br>";*/
    $cadena2 = "YuAAAAAAACEEEEIIIIDNOOOOOOUUUUYBaaaaaaaceeeeiiiionoooooouuuuyy";
    assert(Frase::sinAcentos($cadena1UTF8) == $cadena2);
  }

  function testTraduceAcentosRuso() {
    $cadena1UTF8 = "Равсоитсл";
    $cadena2 = "Равсоитсл";
    assert(Frase::sinAcentos($cadena1UTF8) == $cadena2);
  }

  function testCaracteresEspeciales() {
    $this->conversacion =& Conversacion::conNombres("Kalkotest", "Pedro");
    $this->conversacion->personaje1->dice("Te dije: 'Hola, como te va?'");
    $this->conversacion->personaje2->dice('Me dijiste: "Hola, que tal?"');
    $this->conversacion->personaje1->dice("En qué año estamos?");
    $this->conversacion->personaje2->dice("Estamos en el siglo XXI.");

    //$this->conversacion->muestra();

    assert($this->mensajeConTexto(0, "Te dije: 'Hola, como te va?'"));
    assert($this->mensajeConTexto(1, 'Me dijiste: "Hola, que tal?"'));
    assert($this->mensajeConTexto(2, "En qué año estamos?"));
    assert($this->mensajeConTexto(3, "Estamos en el siglo XXI."));
  }

  function testAlmacenarVariables() {
    $this->conversacion =& Conversacion::conNombres("Kalkotest", "Pedro");
    $this->conversacion->personaje1->dice("Hola Pedro");
    $this->conversacion->personaje2->dice("Hola Kalkotest");
    $this->conversacion->personaje1->dice("Sí, yo me llamo Kalkotest.");
    $this->conversacion->personaje2->dice("Claro y yo me llamo Pedro.");
    $this->conversacion->personaje1->dice("Yo soy Kalkotest y vos sos Pedro.");
    $this->conversacion->personaje2->dice("Kalkotest es Kalkotest es Kalkotest.");
    $this->conversacion->personaje1->dice("kalkotest es lo mismo que KALKOTEST.");
    $this->conversacion->personaje2->dice("kalkotest es lo mismo que kálkotest que kaLkötest.");

    //$this->conversacion->muestra();

    assert($this->mensajeConTexto(0, 'Hola $personaje2'));
    assert($this->mensajeConTexto(1, 'Hola $personaje1'));
    assert($this->mensajeConTexto(2, "S\xc3\xad, yo me llamo \$personaje1."));
    assert($this->mensajeConTexto(3, 'Claro y yo me llamo $personaje2.'));
    assert($this->mensajeConTexto(4, 'Yo soy $personaje1 y vos sos $personaje2.'));
    assert($this->mensajeConTexto(5, '$personaje1 es $personaje1 es $personaje1.'));
    assert($this->mensajeConTexto(6, '$personaje1 es lo mismo que $personaje1.'));
    assert($this->mensajeConTexto(7, '$personaje1 es lo mismo que $personaje1 que $personaje1.'));
  }

  function testFrasesAleatorias() {
    $this->conversacion =& Conversacion::conNombres('Kalkotest', 'Pedro');
    $this->conversacion->personaje1->diceFraseAleatoria();
    $this->conversacion->personaje2->dice("S\xed, yo me llamo Pedro."); // igual a la frase que de testAlmacenarVariables
    $this->conversacion->personaje1->diceFraseAleatoria();    // responderá la frase siguiente de la misma conversación
                                                              // y seguirá con esa conversación.
    $this->conversacion->personaje2->dice('Digo otra cosa.');
    $this->conversacion->personaje1->diceFraseAleatoria();
    $this->conversacion->personaje2->dice('Digo otra cosa mas.');
    $this->conversacion->personaje1->diceFraseAleatoria();

    //$this->conversacion->muestra();

    assert($this->textoDeMensaje(0) != '');
    assert($this->mensajeConTexto(2, 'Claro y yo me llamo $personaje1.'));
    assert($this->mensajeConTexto(4, '$personaje2 es $personaje2 es $personaje2.'));
    assert($this->mensajeConTexto(6, '$personaje2 es lo mismo que $personaje2 que $personaje2.'));
  }

  function testSimilitud() {
    $this->conversacion =& Conversacion::conNombres('Kalkotest', 'Pedro');
    $this->conversacion->personaje1->diceFraseAleatoria();
    $this->conversacion->personaje2->dice('Claro y yo me llamo $personaje2.');
    $this->conversacion->personaje1->diceFraseAleatoria();
    $this->conversacion->personaje2->dice("Cl\xe1ro y yo me llamo \$personaje2.");
    $this->conversacion->personaje1->diceFraseAleatoria();
    $this->conversacion->personaje2->dice('ClarO y yo me llamo $personaje2.');
    $this->conversacion->personaje1->diceFraseAleatoria();
    $this->conversacion->personaje2->dice('Claro y              yo me llamo $personaje2.');
    $this->conversacion->personaje1->diceFraseAleatoria();
    $this->conversacion->personaje2->dice('Claro y yo me llamo $personaje1.');
    $this->conversacion->personaje1->diceFraseAleatoria();
    $this->conversacion->personaje2->dice('Claro y yo me llamo $personaje2?????????');
    $this->conversacion->personaje1->diceFraseAleatoria();
    $this->conversacion->personaje2->dice('Claro ee yo me llamo $personaje2.');
    $this->conversacion->personaje1->diceFraseAleatoria();
    $this->conversacion->personaje2->dice('Claro: y yo me llamo $personaje2.');
    $this->conversacion->personaje1->diceFraseAleatoria();
    $this->conversacion->personaje2->dice('Claru y yo me llamo $personaje2.');
    $this->conversacion->personaje1->diceFraseAleatoria();
    $this->conversacion->personaje2->dice('y yo me llamo $personaje2.');
    $this->conversacion->personaje1->diceFraseAleatoria();

    //    $this->conversacion->muestra();

    assert($this->mensajeConTexto(2, 'Digo otra cosa.'));
    assert($this->mensajeConTexto(4, 'Digo otra cosa.'));
    assert($this->mensajeConTexto(6, 'Digo otra cosa.'));
    assert($this->mensajeConTexto(8, 'Digo otra cosa.'));
    assert($this->mensajeConTexto(10, 'Digo otra cosa.'));
    assert($this->mensajeConTexto(12, 'Digo otra cosa.'));
    assert($this->mensajeConTexto(14, 'Digo otra cosa.'));
    assert($this->mensajeConTexto(16, 'Digo otra cosa.'));
    assert($this->mensajeConTexto(18, 'Digo otra cosa.'));
    assert($this->mensajeConTexto(20, 'Digo otra cosa mas.'));
  }

  function testDividirEnSubfrases() {
    $frase = Frase::conTexto('Todo tranqui? Como va todo?');
    $subfrases = $frase->subfrases();
    assert($subfrases[0] == 'Todo tranqui');
    assert($subfrases[1] == 'Como va todo');

    $frase = Frase::conTexto('Todo tranqui! Como va todo?');
    $subfrases = $frase->subfrases();
    assert($subfrases[0] == 'Todo tranqui');
    assert($subfrases[1] == 'Como va todo');

    $frase = Frase::conTexto('Todo tranqui. Como va todo?');
    $subfrases = $frase->subfrases();
    assert($subfrases[0] == 'Todo tranqui');
    assert($subfrases[1] == 'Como va todo');

    $frase = Frase::conTexto("Todo tranqui \xbfComo va todo?");
    $subfrases = $frase->subfrases();
    assert($subfrases[0] == 'Todo tranqui');
    assert($subfrases[1] == 'Como va todo');

    $frase = Frase::conTexto('Todo tranqui????? Como va todo????');
    $subfrases = $frase->subfrases();
    assert($subfrases[0] == 'Todo tranqui');
    assert($subfrases[1] == 'Como va todo');

    $frase = Frase::conTexto('Todo tranqui;  ; como va todo?');
    $subfrases = $frase->subfrases();
    assert($subfrases[0] == 'Todo tranqui');
    assert($subfrases[1] == 'como va todo');

    $frase = Frase::conTexto('Todo tranqui  ; como va todo?');
    $subfrases = $frase->subfrases();
    assert($subfrases[0] == 'Todo tranqui');
    assert($subfrases[1] == 'como va todo');

    $frase = Frase::conTexto('; Como va todo?');
    $subfrases = $frase->subfrases();
    assert($subfrases[0] == 'Como va todo');
  }

  function testSimilitudSubfrase() {
    $this->conversacion =& Conversacion::conNombres('Kalkotest', 'Pedro');
    $this->conversacion->personaje1->dice('Similitud por subfrase.');
    $this->conversacion->personaje2->dice('Bien');
    $this->conversacion->personaje1->dice('Como va todo?');
    $this->conversacion->personaje2->dice('Si, similitud por subfrase.');
    $this->conversacion->personaje1->diceFraseAleatoria();
    assert($this->ultimoMensajeEs('Bien'));

    $this->conversacion->personaje2->dice('Todo tranqui? Como va todo?');
    $this->conversacion->personaje1->diceFraseAleatoria();
    assert($this->ultimoMensajeEs('Si, similitud por subfrase.'));

    $this->conversacion->personaje2->dice('Todo tranqui????? Como va todo????');
    $this->conversacion->personaje1->diceFraseAleatoria();
    assert($this->ultimoMensajeEs('Si, similitud por subfrase.'));

    $this->conversacion->personaje2->dice('Todo tranqui? Como va todo?   ');
    $this->conversacion->personaje1->diceFraseAleatoria();
    assert($this->ultimoMensajeEs('Si, similitud por subfrase.'));

    $this->conversacion->personaje2->dice('Todo tranqui. Como va todo?');
    $this->conversacion->personaje1->diceFraseAleatoria();
    assert($this->ultimoMensajeEs('Si, similitud por subfrase.'));

    $this->conversacion->personaje2->dice('Todo tranqui, como va todo?');
    $this->conversacion->personaje1->diceFraseAleatoria();
    assert($this->ultimoMensajeEs('Si, similitud por subfrase.'));

    $this->conversacion->personaje2->dice('Todo tranqui: como va todo?');
    $this->conversacion->personaje1->diceFraseAleatoria();
    assert($this->ultimoMensajeEs('Si, similitud por subfrase.'));

    $this->conversacion->personaje2->dice('Todo tranqui; como va todo?');
    $this->conversacion->personaje1->diceFraseAleatoria();
    assert($this->ultimoMensajeEs('Si, similitud por subfrase.'));

    $this->conversacion->personaje2->dice('Todo tranqui;.; como va todo?');
    $this->conversacion->personaje1->diceFraseAleatoria();
    assert($this->ultimoMensajeEs('Si, similitud por subfrase.'));

    $this->conversacion->personaje2->dice('Todo tranqui;  ; como va todo?');
    $this->conversacion->personaje1->diceFraseAleatoria();
    assert($this->ultimoMensajeEs('Si, similitud por subfrase.'));

    $this->conversacion->personaje2->dice('Todo tranqui; como va todo');
    $this->conversacion->personaje1->diceFraseAleatoria();
    assert($this->ultimoMensajeEs('Si, similitud por subfrase.'));

    $this->conversacion->personaje2->dice('; como va todo?');
    $this->conversacion->personaje1->diceFraseAleatoria();
    assert($this->ultimoMensajeEs('Si, similitud por subfrase.'));

    $this->conversacion->personaje2->dice('Todo tranqui como va todo?');
    $this->conversacion->personaje1->diceFraseAleatoria();
    assert(!$this->ultimoMensajeEs('Si, similitud por subfrase.'));

    //$this->conversacion->muestra();
  }

  function testInteraccion() {
	// Inicializacion: hay un mensaje de kalko
	$conversador = $this->ConversadorQueEjecuta(array(
							  'nombreInterlocutor' => 'Pedro',
							  'textoInterlocutor' => null,
							  'habloInterlocutor' => null,
							  'conversacionActual' => null,
							  'conversacionQueNutre' => null,
							  'mensajeQueNutre' => null,
							  'estado' => null,
							  'nombre' => 'Kalkotest'));
	$idConversacionActual = $conversador->idConversacionActual();
	assert($idConversacionActual);
	assert($conversador->nombre() == 'Kalkotest');
	assert($conversador->nombreInterlocutor() == 'Pedro');
	$mensajes = $conversador->mensajes();
	assert(count($mensajes) == 1);
	assert($mensajes[0]->id_personaje == $conversador->conversador->conversacion->personaje1->id);
	assert($mensajes[0]->frase->texto != "");
	$idConversacionQueNutre = $conversador->idConversacionQueNutre();
	$numeroMensajeQueNutre = $conversador->numeroMensajeQueNutre();
	$conversacionQueNutre = $conversador->conversador->conversacion->personaje1->conversacionQueNutre;
	$siguienteMensaje = $conversacionQueNutre->mensajes[3];
	$textoSiguienteMensaje = $siguienteMensaje->textoPersonajesInvertidosEn($conversacionQueNutre);
	assert($idConversacionActual);
	assert($idConversacionQueNutre);
	assert($textoSiguienteMensaje != "");

	// Usuario dice cualquier cosa que no esté en la base. Se mantiene la conversacion anterior y el mensaje del personaje aleatorio.
	// Kalko se mantiene escribiendo...
	$conversador = $this->ConversadorQueEjecuta(array(
							 'textoInterlocutor' => 'Hola kalkotest. Como estas? Que loco che.',
							 'habloInterlocutor' => 'S',
							 'conversacionActual' => $idConversacionActual,
							 'conversacionQueNutre' => $idConversacionQueNutre,
							 'mensajeQueNutre' => $numeroMensajeQueNutre,
							 'estado' => 'escribiendo'));
	assert($conversador->nombre() == 'Kalkotest');
	assert($conversador->nombreInterlocutor() == 'Pedro');
	$mensajes = $conversador->mensajes();
	assert(count($mensajes) == 2);
	assert($mensajes[0]->id_personaje == $conversador->conversador->conversacion->personaje1->id);
	assert($mensajes[1]->id_personaje == $conversador->conversador->conversacion->personaje2->id);
	assert($idConversacionActual == $conversador->idConversacionActual());
	assert($idConversacionQueNutre == $conversador->idConversacionQueNutre());
	assert($numeroMensajeQueNutre == $conversador->numeroMensajeQueNutre());


	// Tiempo de escritura de Kalko agotado, dice una frase de la misma conversación que nutre.
	$conversador = $this->ConversadorQueEjecuta(array(
							  'textoInterlocutor' => null,
							  'habloInterlocutor' => null,
							  'conversacionActual' => $idConversacionActual,
							  'conversacionQueNutre' => $idConversacionQueNutre,
							  'mensajeQueNutre' => $numeroMensajeQueNutre));
	$mensajes = $conversador->mensajes();
	assert(count($mensajes) == 3);
	assert($mensajes[2]->id_personaje == $conversador->conversador->conversacion->personaje1->id);
	assert($idConversacionActual == $conversador->idConversacionActual());
	assert($idConversacionQueNutre == $conversador->idConversacionQueNutre());
	assert($numeroMensajeQueNutre + 2 == $conversador->numeroMensajeQueNutre());

	//$conversador->conversador->conversacion->muestra();

  }

  function testPersonajeRuso() {
    $this->conversacion = Conversacion::conNombres('С еумаженви', 'Равсоитсл');
    assert(stristr($this->conversacion->personaje1->nombre, 'Сеумаженви') == 0);
    assert(stristr($this->conversacion->personaje2->nombre, 'Равсоитсл') == 0);

    $otraConversacion = Conversacion::conNombres('С еумаженви', 'Равсоитсл');
    assert($otraConversacion->id != $this->conversacion->id);
    assert($otraConversacion->personaje1->id == $this->conversacion->personaje1->id);
    assert($otraConversacion->personaje2->id == $this->conversacion->personaje2->id);

    $this->conversacion->personaje1->dice("Yo soy С еумаженви.");
    $this->conversacion->personaje2->dice("Sí, vos sos С еумаженви.");
    $this->conversacion->personaje1->dice("Y vos sos Равсоитсл.");
    $this->conversacion->personaje2->dice("Oviamente yo soy Равсоитсл.");

    assert($this->mensajeConTexto(0, 'Yo soy $personaje1.'));
    assert($this->mensajeConTexto(1, 'Sí, vos sos $personaje1.'));
    assert($this->mensajeConTexto(2, 'Y vos sos $personaje2.'));
    assert($this->mensajeConTexto(3, 'Oviamente yo soy $personaje2.'));
  }

  function &conversadorQueEjecuta($datos) {
	$conversador = new ConversadorWeb();
	$conversador->ejecuta($datos);
	return $conversador;
  }

  function ultimoMensajeEs($texto) {
    return $this->textoDeMensaje(count($this->conversacion->mensajes) - 1) == $texto;
  }

  function mensajeConTexto($numero, $texto) {
    return $this->textoDeMensaje($numero) == $texto;
  }

  function textoDeMensaje($numero) {
    return $this->conversacion->mensajes[$numero]->frase->texto;
  }

  function borraConversacionesDeTest() {
    global $wpdb;

    $wpdb->query("
        DELETE m.*
        FROM ".$wpdb->prefix."conversador_conversacion c INNER JOIN ".$wpdb->prefix."conversador_personaje p ON c.id_personaje1 = p.id 
                            INNER JOIN ".$wpdb->prefix."conversador_mensaje m ON c.id = m.id_conversacion
        WHERE nombre = 'kalkotest'");

    $wpdb->query("
        DELETE c.* 
        FROM ".$wpdb->prefix."conversador_conversacion c INNER JOIN ".$wpdb->prefix."conversador_personaje p ON c.id_personaje1 = p.id 
        WHERE nombre = 'kalkotest'");
  }

  function run() {
    mt_rand(); // sólo para inicializar la semilla

    $this->borraConversacionesDeTest();

    echo 'testTraduceAcentosRuso -------------------------------------------------------------------<br>';
    $this->testTraduceAcentosRuso();
    echo 'testAlmacenarVariables -------------------------------------------------------------------<br>';
    $this->testAlmacenarVariables();
    echo 'testTraduceAcentosUTF8 -------------------------------------------------------------------<br>';
    $this->testTraduceAcentosUTF8();
    echo 'testCaracteresEspeciales -----------------------------------------------------------------<br>';
    $this->testCaracteresEspeciales();
    echo 'testMensajes -----------------------------------------------------------------------------<br>';
    $this->testMensajes();
    echo 'testConversacionConNombre ----------------------------------------------------------------<br>';
    $this->testConversacionConNombre();
    echo 'testFrasesAleatorias ---------------------------------------------------------------------<br>';
    $this->testFrasesAleatorias();
    echo 'testSimilitud ----------------------------------------------------------------------------<br>';
    $this->testSimilitud();
    echo 'testDividirEnSubfrases -------------------------------------------------------------------<br>';
    $this->testDividirEnSubfrases();
    echo 'testSimilitudSubfrase --------------------------------------------------------------------<br>';
    $this->testSimilitudSubfrase();
    echo 'testInteraccion --------------------------------------------------------------------------<br>';
    $this->testInteraccion();
    echo 'testPersonajeRuso ------------------------------------------------------------------------<br>';
    $this->testPersonajeRuso();
  }
}

error_reporting(E_ALL);
$test = new TestConversacion();
$test->run();
