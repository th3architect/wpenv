<?php

namespace wpenv;

require_once sprintf("%s/lib/config.php", ROOT);

// args
$action   = arguments(2);
$remote   = arguments(3);

// load config
$config   = new Config(sprintf("%s/config", LOCAL));
$remotes  = $config->env("sync");
$rconf    = ifsetor($remotes[$remote], false);

// remote config required
if ($rconf === false) {
  $options = implode(", ", array_keys($remotes));
  trigger_error(sprintf("Invalid remote `%s'.\nDefined remotes: %s\n", $remote, $options), E_USER_ERROR);
  exit(1);
}

// paths
$local_path  = sprintf("%s/plugins/", LOCAL);
$remote_path = sprintf("%s:%s/.wpenv/plugins/", $rconf["host"], ifsetor($rconf["path"], "~"));

// parse action
switch ($action) {
  case "push":
    rsync("-rtvhpP", $local_path, $remote_path);
    break;

  case "pull":
    rsync("-rtvhpP", $remote_path, $local_path);
    break;

  default:
    trigger_error(sprintf("Invalid action `%s'.\n wpenv plugin <push|pull> <remote>\n", $action), E_USER_ERROR);
    exit(1);
}

function rsync($options, $src, $dest) {
  $cmd = sprintf("rsync %s %s %s", $options, $src, $dest);
  printf("%s\n", $cmd);
  passthru($cmd, $status);
}
