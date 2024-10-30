=== Plugin Name ===
Contributors: federico.larumbe
Tags: bot, chat, intelligence, ai, conversation
Requires at least: 2.7.0
Tested up to: 2.7.1
Stable tag: 2.61

Chat with a fictitious character that learns from you. The bot records all the conversations and uses the users answers to write.

== Description ==

Chat with a fictitious character that learns from you. The bot records all the conversations and uses the users answers to write. When you answer a question it memorize the relation between both phrases, so if someone do the same question, it will write a related answer.

You can use different characters and each one keeps independent conversations, so each character has a "personality".

It is i18n customizable and all the visual customizations could be done at `conversador.css` (for instance each character avatar). Translations to different languages are welcomed.

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload `conversador` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Place a link to open a new chat window:

       `<a href="#" onclick="window.open('/wp-content/plugins/conversador/chat.php?nombre=Kalko', '_blank', 'width=300,height=600,scrollbars=no,status=no'); return false;">Chat with Kalko</a>`

   You can change the character name with the `nombre` parameter. If you enter a different name, a new character is created and its conversations are stored independent from the others characters ones.

== Changelog ==

= 2.70 =
* It finds mp3 audio that matchs the phrases said in folder 'audio/characterName/'.
  PENDING: update chat.php
* listaPersonajes.php list the intelligent characters and each one's last conversation.
* listaConversaciones.php list one character last conversations.

= 2.61 =
* UTF8 compatible.
* If Google Sitemap Generator is installed, it add "chat.php" page to the index.

= 2.60 =
* If it does not find a phrase similar to the one the user entered, it divides the user phrase by the punctuation characters and searchs for the last subphrase.
* It shows conversations timestamps at `unaConversacion.php`.

= 2.51 =
* Show conversations count at `unaConversacion.php`.
* Filter `enter` keys in chat.

= 2.5 =
* Change language with `lang` parameter.
* Read a random previous conversation.
* Page meta tags: author, description and keywords.
* Plugin page in english.

= 2.0 =
* New `nombre` parameter added at `chat.php` in order to create different characters.
* Focus at text box.
* Each character has a related css class in conversador.css.
* ISO-8859-1 encoding specified.

= 1.0 =
* First version.
