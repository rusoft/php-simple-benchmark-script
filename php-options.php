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
ini_set("xdebug.show_exception_trace", 0);
ini_set("xdebug.remote_autostart", 0);
ini_set("xdebug.remote_enable", 0);
ini_set("xdebug.mode", "off");

ini_set("output_buffering", 0);
ini_set("max_execution_time", 600);
ini_set("memory_limit", "130M");
ini_set("mbstring.internal_encoding", "UTF-8");
ini_set("mbstring.func_overload", 0);

ini_set("date.timezone", "Europe/Moscow");