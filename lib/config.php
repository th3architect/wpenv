<?php

namespace wpenv;
use Symfony\Component\Yaml as Yaml;
use Donut\Util as u;

require sprintf("%s/lib/directory_parser.php", ROOT);

class Config {

  private $config = array();
  private $parser;

  public function __construct($path, $env=ENV) {
    $this->env      = $env;
    $this->parser   = new Yaml\Parser();

    // build
    $dp       = new DirectoryParser($path);
    $defaults = array("wordpress"=> array("WP_ENV" => $env));

    // map each conf
    // [[production => [foo => conf], [development => [foo => conf]]], [...]
    $confs = $dp->readdir(function($filename) {
      if (substr($filename, -4) !== ".yml") return;
      return $filename;
    });

    // PHP 5.4; this block can be moved inside the function above; remove the foreach wrapper
    foreach ($confs as $key => $filename) {
      // $confs[$key] becomes return in PHP 5.4
      $confs[$key] = u\array_kmap(function($conf, $env) use($filename) {
        return array($env => array(basename($filename, ".yml") => $conf));
      }, $this->parse($filename));
    }

    // flatten and merge
    $this->config = call_user_func_array('\array_replace_recursive',
      call_user_func_array('\array_merge', $confs)
    );

    // merge defaults
    foreach ($this->config as $env => $conf) {
      $this->config[$env] = array_replace_recursive($defaults, $conf);
    }
  }

  private function parse($filename) {
    $yaml = u\capture_buffer($filename);

    if ($yaml === false) {
      trigger_error(sprintf("Could not read %s. Check the permissions of this file.", $filename), E_USER_ERROR);
    }

    return $this->parser->parse($yaml);
  }

  public function get() {
    return \Donut\Util\array_dig($this->config, func_get_args());
  }

  public function env() {
    return call_user_func_array(
      array($this, "get"),
      array_merge(
        array($this->env),
        func_get_args()
      )
    );
    // return $this->get($this->env, $key);
  }
}
