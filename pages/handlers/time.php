<?php 
    //mimic the actuall admin-ajax
    define('DOING_AJAX', true);

    //if (!isset( $_POST['action']))
        //die('-1');

    //make sure you update this line 
    //to the relative location of the wp-load.php
    require_once('../../../../wp-load.php'); 
function handler_fun1 ()	
{
  echo json_encode(array(100, 1, 33));
};
function handler_fun2 ()	
{
  echo json_encode(array('success' => true, 'result' => 'sdf42333'));
};
    //Typical headers
    header('Content-Type: text/html');
    send_nosniff_header();

    //Disable caching
    header('Cache-Control: no-cache');
    header('Pragma: no-cache');

    $action = esc_attr(trim($_POST['action']));

    //A bit of security
    $allowed_actions = array(
        'custom_action1',
        'custom_action2'
    );

    //For logged in users
    add_action('SOMETEXT_custom_action1', 'handler_fun1');
    add_action('SOMETEXT_custom_action2', 'handler_fun1');

    //For guests
    add_action('SOMETEXT_nopriv_custom_action2', 'handler_fun2');
    add_action('SOMETEXT_nopriv_custom_action1', 'handler_fun1');
//handler_fun1 ();
 //if(is_user_logged_in())
            do_action('SOMETEXT_nopriv_custom_action1');
        //else
      //      do_action('SOMETEXT_nopriv_'.$action);
    //if(in_array($action, $allowed_actions)) {
      // 
    //} else {
      //  die('-1');
    //} 

?>