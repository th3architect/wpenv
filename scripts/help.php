<?php

namespace wpenv;

require_once sprintf("%s/lib/directory_parser.php", ROOT);

function get_scripts($path) {
  $dp  = new DirectoryParser($path);

  return $dp->readdir(function($filename) {
    if (substr($filename, -4) !== ".php") return;
    return basename($filename, ".php");
  });
}

$scripts = array_unique(array_merge(
  get_scripts(sprintf("%s/scripts", ROOT)),
  get_scripts(sprintf("%s/scripts", LOCAL))
));

sort($scripts);

printf("Available scripts\n");

foreach ($scripts as $s) {
  printf("%2s%s\n", "", $s);
}