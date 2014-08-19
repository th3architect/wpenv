<?php

namespace wpenv;
use Donut\Path as p;

require_once sprintf("%s/lib/config.php", ROOT);

// args
$action   = arguments(2);

// load config
$config   = new Config(sprintf("%s/config", LOCAL));
$dbconf   = $config->env("database");

// parse action
switch ($action) {

  case "dump":
    $cmd = sprintf("mysqldump %s %s 2> /dev/null", mysql_options($dbconf), $dbconf["DB_NAME"]);

    if (arguments(3)) {
      $filename = p\canonicalize(arguments(3), PWD);
      $cmd = sprintf("%s > %s", $cmd, $filename);
    }
    else {
      fwrite(STDERR, "Using stdout...\n");
    }

    fwrite(STDERR, sprintf("%s\n", $cmd));
    passthru($cmd, $status);
    exit(0);
    break;

  case "restore":

    $cmd = sprintf("mysql %s %s 2> /dev/null", mysql_options($dbconf), $dbconf["DB_NAME"]);

    if (arguments(3)) {
      $filename = p\canonicalize(arguments(3), PWD);
      $cmd = sprintf("%s < %s ", $cmd, $filename);
    }
    else {
      fwrite(STDERR, "Using stdin...\n");
    }

    printf("%s\n", $cmd);
    passthru($cmd, $status);
    exit(0);
    break;

  case "backup":
    $filename = sprintf("%s/snapshots/%s-%s.sql", LOCAL, ENV, time());
    $cmd = sprintf("wpenv db dump %s", $filename);
    // printf("%s\n", $cmd);
    passthru($cmd, $status);
    exit(0);
    break;

  case "push":
    // load rconf
    $remote   = arguments(3);
    $remotes  = $config->env("sync");
    $rconf    = ifsetor($remotes[$remote], false);

    // remote config required
    if ($rconf === false) {
      $options = implode(", ", array_keys($remotes));
      trigger_error(sprintf("Invalid remote `%s'.\nDefined remotes: %s\n", $remote, $options), E_USER_ERROR);
      exit(1);
    }

    $cmd = sprintf("wpenv db dump | ssh %s 'cd %s && wpenv db backup && wpenv db restore'", $rconf["host"], $rconf["path"]);
    fwrite(STDERR, sprintf("%s\n", $cmd));
    passthru($cmd, $status);
    exit(0);
    break;

  case "pull":
    // load rconf
    $remote   = arguments(3);
    $remotes  = $config->env("sync");
    $rconf    = ifsetor($remotes[$remote], false);

    // remote config required
    if ($rconf === false) {
      $options = implode(", ", array_keys($remotes));
      trigger_error(sprintf("Invalid remote `%s'.\nDefined remotes: %s\n", $remote, $options), E_USER_ERROR);
      exit(1);
    }

    $cmd = sprintf("wpenv db backup && ssh %s 'cd %s && wpenv db dump' | wpenv db restore", $rconf["host"], $rconf["path"]);
    printf("%s\n", $cmd);
    passthru($cmd, $status);
    exit(0);
    break;

  default:
    trigger_error(sprintf("Invalid action `%s'.\n wpenv db <push|pull> <remote>\n", $action), E_USER_ERROR);
    exit(1);
}

function mysql_options(Array $conf) {
  $params = array();

  if ($_ = ifsetor($conf["DB_HOST"])) {
    array_push($params, sprintf("-h%s", $_));
  }

  if ($_ = ifsetor($conf["DB_USER"])) {
    array_push($params, sprintf("-u%s", $_));
  }

  if ($_ = ifsetor($conf["DB_PASSWORD"])) {
    array_push($params, sprintf("-p%s", $_));
  }

  return implode(" ", $params);
}
