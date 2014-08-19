<?php

namespace wpenv;

use Symfony\Component\Yaml as Yaml;

class Environment {

  static private $env;

  static public function load() {

    // cannot load more than once
    if (is_array(self::$env)) {
      trigger_error("Environment is already loaded", E_USER_ERROR);
    }

    // env.yml is present and readable?
    $filename = sprintf("%s/env.yml", LOCAL);

    if (! is_readable($filename)) {
      trigger_error(sprintf("Cannot read environment from %s\n", $filename), E_USER_ERROR);
    }

    // parse env.yml
    $parser     = new Yaml\Parser();
    $yaml       = file_get_contents($filename);
    self::$env  = $parser->parse($yaml);

    // inject into $_ENV
    foreach (self::$env as $key => $value) {
      if (! array_key_exists($key, $_ENV)) {
        $_ENV[$key] = $value;
        putenv(sprintf("%s=%s", $key, $value));
      }
    }

    // WP_ENV is required
    self::required("WP_ENV");
  }

  static public function required($key) {
    if (! array_key_exists($key, $_ENV) || strlen($_ENV[$key]) === 0) {
      trigger_error(sprintf("Environment variable required: `%s',", $key), E_USER_ERROR);
    }

    return $_ENV[$key];
  }

}
