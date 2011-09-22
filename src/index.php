<?php

/*
    TODO:
    * Clean up ext.js (tons of redundant code :S)
    * FR: Loading indication while loading filesystem (put load filesystem in an ajax script?)

    * Create a filter for list of playlist contents (to only show invalid for instance)
    * FR: Printout permissions of playlists?
    * Bug: cannot save songs containing '&' in the filename to a playlist
*/

require_once('config.php');

echo "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN'\n'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>";
echo "\n<html>";
echo "\n    <head>";
echo "\n        <title>".APPLICATION_NAME." v.".APPLICATION_VERSION."</title>";
echo "\n        <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />";
echo "\n        <meta http-equiv='Content-Language' content='en' />";
echo "\n        <link rel=\"stylesheet\" type=\"text/css\" href=\"".EXTJS_PATH."/resources/css/ext-all-notheme.css\">";
echo "\n        <link rel=\"stylesheet\" type=\"text/css\" title=\"access\" href=\"".EXTJS_PATH."/resources/css/xtheme-".EXTJS_THEME.".css\" />";
echo "\n        <link rel='stylesheet' href='./style.css' type='text/css' />";
echo "\n        <script type=\"text/javascript\" src=\"".EXTJS_PATH."/adapter/ext/ext-base.js\"></script>";
echo "\n        <script type=\"text/javascript\" src=\"".EXTJS_PATH."/ext-all-debug.js\"></script>";
echo "\n        <script type=\"text/javascript\" src=\"common.js\"></script>";
echo "\n        <script type=\"text/javascript\" src=\"ext".EXTJS_VERSION.".js\"></script>";
echo "\n    </head>";
echo "\n    <body>";
echo "\n        <div id='loading-mask'></div>";
echo "\n        <div id='loading'>";
echo "\n            <span id='loading-message'>n/a</span>";
echo "\n        </div>";
echo "\n        <div id='container'>";
echo "\n            <div class='content' id='header'>".APPLICATION_NAME." v.".APPLICATION_VERSION."</div>";
echo "\n            <div class='content' id='messages'></div>";
echo "\n            <div class='content' id='tree'></div>";
//echo "\n            <div class='content' id='footer'><a href=\"unit_tests.php\">Unit tests</a></div>";

// Load filesystem
echo "\n            <script type='text/javascript'>";
echo "\n                document.getElementById('loading-message').innerHTML = 'Loading filesystem...';";
echo "\n            </script>";

// Start rendering
echo "\n            <script type='text/javascript'>";
echo "\n                render(".(isset($_GET['reload']) ? "true" : "false").", '".ROOT_DIRECTORY."', '".((empty($_GET['playlist'])) ? '' : $_GET['playlist'])."');";
echo "\n            </script>";

echo "\n        </div>";
echo "\n    </body>";
echo "\n</html>";

?>