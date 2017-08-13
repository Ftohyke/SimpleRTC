<?php
require_once("../../../../wp-load.php");
//for logged in users
add_action('wp_ajax_my_action', 'my_action_callback');
//for not logged in users
add_action('wp_ajax_nopriv_my_action', 'my_action_callback');

function my_action_callback() {
  //your function here
  echo $_GET["request_name"];
  exit; //always call exit at the end of a WordPress ajax function
}
?>