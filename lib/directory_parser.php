<?php

namespace wpenv;

class DirectoryParser {

  private $path;
  private $h;

  public function __construct($path) {
    $this->path = $path;
    $this->h    = opendir($path);

    if ($this->h === false) {
      trigger_error(sprintf("Could not read from %s. Check the permissions of this directory.", $path), E_USER_ERROR);
    }
  }

  public function readdir(\Closure $callback) {
    $ret = array();

    while (($f=readdir($this->h)) !== false) {
      $filename = sprintf("%s/%s", $this->path, $f);
      $val = $callback($filename, $f);
      if (is_null($val) || $val === false) continue;
      array_push($ret, $val);
    }

    return $ret;
  }

  public function close() {
    closedir($this->h);
  }

  public function __destruct() {
    $this->close();
  }
}
