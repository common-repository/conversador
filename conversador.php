<?php
/*
Plugin Name: Conversador
Plugin URI: http://hamartia.com.ar/2009/10/19/conversador-english/
Description: Chat with a fictitious character that learns from you. The bot records all the conversations and uses the users answers to write.
Version: 2.70
Author: Federico Larumbe
Author URI: http://hamartia.com.ar/2009/10/19/conversador-english/
Text Domain: conversador

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
include_once('ConversadorClass.php');

function conversadorInstall() {
  InterfazBaseDatos::nuevasTablas();
}

class ConversadorPlugin {
  
  function init($lang) {
    global $locale;
    //add_action('init', 'conversadorInit');
    $plugin_dir = basename(dirname(__FILE__));

    if($lang) {
      $locale = 'en_US';
    }
    load_plugin_textdomain('conversador', 'wp-content/plugins/' . $plugin_dir, $plugin_dir);

    add_action("sm_buildmap", "conversadorInGoogleSiteMap");

    register_activation_hook(__FILE__, 'conversadorInstall');
  }

}

function conversadorInGoogleSiteMap() {
  if ( class_exists('GoogleSitemapGenerator') ) {
    $generatorObject = &GoogleSitemapGenerator::GetInstance();
    if ($generatorObject != null) {
      $chat_link = get_option( 'siteurl' ) . '/wp-content/plugins/' . basename(dirname(__FILE__)) . '/chat.php';
      //            echo "chat_link: $chat_link<br>";
      $generatorObject->AddUrl($chat_link, time(), "monthly", 0.5);
    }
  }
}

ConversadorPlugin::init($_REQUEST['lang']);
?>