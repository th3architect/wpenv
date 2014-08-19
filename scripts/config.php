<?php

namespace wpenv;

require_once sprintf("%s/lib/config.php", ROOT);

// requires .wpenv
local_required();

// args
$key = arguments(2);

// load config
$c = new Config(sprintf("%s/config", LOCAL));

// get JSON
$conf = call_user_func_array(array($c, "env"), array_slice(arguments(), 2));

// output
if (defined('\JSON_PRETTY_PRINT')) {
  echo json_encode($conf, \JSON_PRETTY_PRINT);
}

else {
  echo json_encode($conf);
}
