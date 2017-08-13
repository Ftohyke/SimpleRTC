<?php
  /*
   * Encorrientador Unido - are collection of web-based software plugins for
   * popular open-source content management systems. It has a purpose of providing
   * secure browser-based video chat with database management features and
   * account registration independent from third-party content delivery networks.
   * This software is based on PubNub WebRTC video chat source code.
   * Copyright (C) 2017  Andrei Shishkin <QfpbC7u3V13qJUop@i2pmail.org>
   * 
   * This program is free software: you can redistribute it and/or modify
   * it under the terms of the GNU General Public License as published by
   * the Free Software Foundation, either version 3 of the License, or
   * (at your option) any later version.
   * 
   * This program is distributed in the hope that it will be useful,
   * but WITHOUT ANY WARRANTY; without even the implied warranty of
   * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   * GNU General Public License for more details.
   * 
   * You should have received a copy of the GNU General Public License
   * along with this program.  If not, see <http://www.gnu.org/licenses/>.
   */
?>



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
