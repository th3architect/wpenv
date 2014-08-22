<?php

namespace wpenv;

function path() {
  $path = getenv("WP_ENV_PATH");
  if ($path === false) return "/usr/bin/env wpenv";
  return $path;
}

function define($json) {
  foreach (json_decode($json, true) as $key => $value) {
    \define($key, $value);
  }
}

function configure($key) {
  $cmd = sprintf("%s config %s", path(), $key);
  define(shell_exec($cmd));
}
