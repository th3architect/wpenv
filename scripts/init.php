<?php

namespace wpenv;

require_once sprintf("%s/lib/directory_parser.php", ROOT);

if (LOCAL) {
  trigger_error(sprintf("wpenv is already installed at %s\n", LOCAL), E_USER_ERROR);
}

function copyr($src, $dest) {

  // directory?
  if (is_dir($src)) {

    // create dest directory
    if (mkdir($dest)) {
      chmod($dest, 0775);
      printf("%5s %s\n", "mkdir", $dest);
    }
    else {
      trigger_error(sprintf("Could not create %s. Please check permissions.\n", $dest), E_USER_ERROR);
    }

    // recursively copy
    $dp = new DirectoryParser($src);
    $dp->readdir(function($filename, $f) use($src, $dest) {
      if ($f === "." || $f === "..") return;
      copyr(
        sprintf("%s/%s", $src,  $f),
        sprintf("%s/%s", $dest, $f)
      );
    });
  }

  // file?
  else {
    printf("%5s %s\n", "cp", $dest);
    copy($src, $dest);
    chmod($dest, 0664);
  }
}

copyr(
  sprintf("%s/local", ROOT),
  sprintf("%s/.wpenv", PWD)
);
