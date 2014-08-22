<?php

namespace wpenv;
use Donut\Path as p;

// define runtime constants
define('wpenv\PWD',       getcwd());
define('wpenv\ROOT',      dirname(dirname(__FILE__)));
define('wpenv\LOCAL',     resolve_local_root(PWD));
define('wpenv\ARGUMENTS', implode(',', $argv));
define('wpenv\COMMAND',   arguments(1));

// functions
function ifsetor(&$value, $default=null) {
  return isset($value) ? $value : $default;
}

function env($key, $default=null, $suffix="\n") {
  echo json_encode(ifsetor($_ENV[$key], $default)), $suffix;
}

function resolve_local_root($path) {
  $local_root = sprintf("%s/.wpenv", $path);

  if (file_exists($local_root)) {
    return $local_root;
  }

  if ($path !== "/") {
    return resolve_local_root(dirname($path));
  }

  return false;
}

function run_script($filename) {
  if (file_exists($filename)) {
    require_once $filename;
    exit(0);
  }
}

function load_text($name, $path=LOCAL) {
  require_once sprintf("%s/txt/%s.php", $path, $name);
}

function command_required() {
  if (strlen(COMMAND) === 0) {
    load_text("usage", ROOT);
    exit(1);
  }
}

function local_required() {
  if (LOCAL === false) {
    load_text("local_required", ROOT);
    exit(1);
  }
}

function no_command_found() {
  load_text("no_command_found", ROOT);
}

function arguments($idx=null) {
  $arguments = explode(",", ARGUMENTS);
  return is_null($idx) ? $arguments : ifsetor($arguments[$idx]);
}

// init
command_required();

// include env
if (! in_array(COMMAND, array("init", "source"))) {
  require_once sprintf("%s/lib/environment.php", ROOT);
  Environment::load();
  define('wpenv\ENV', Environment::required("WP_ENV"));
}

run_script(sprintf("%s/scripts/%s.php",  LOCAL,  arguments(1)));
run_script(sprintf("%s/scripts/%s.php",  ROOT,   arguments(1)));

no_command_found();
