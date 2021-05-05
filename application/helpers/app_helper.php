<?php

function dd($data,$exit=true)
{
  echo '<pre>';
  print_r($data);
  echo '</pre>';
  exit();
}

?>