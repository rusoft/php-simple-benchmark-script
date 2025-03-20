<?php
/**
 * php safe options - mimic htaccess for cgi/fpm
 * Php 4.4+
 */

/** ---------------------------------- Tests functions -------------------------------------------- */


ini_set("display_errors", 0);
ini_set("implicit_flush", 1);

ini_set("opcache.enable", 0);
ini_set("xpcache.cacher", 0);
ini_set("apc.enable", 0);

ini_set("xdebug.default_enable", 0);
ini_set("xdebug.mode", "off");
