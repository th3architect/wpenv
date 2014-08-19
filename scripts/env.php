<?php

namespace wpenv;

// output
if (defined('\JSON_PRETTY_PRINT')) {
  echo json_encode($_ENV, \JSON_PRETTY_PRINT);
}

else {
  echo json_encode($_ENV);
}
