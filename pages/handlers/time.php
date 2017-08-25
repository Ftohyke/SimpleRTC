<?php
    /*
     * This file is part of Encorrientador Unido.
     *
     * Encorrientador Unido is a collection of web-based software plugins for
     * popular free web-server software. It has a purpose of providing
     * secure browser-based video chat with database management features and
     * account registration independent from third-party web-servers.
     * This software is based on PubNub WebRTC video chat source code.
     * Copyright (C) 2017  Andrei Shishkin <QfpbC7u3V13qJUop@i2pmail.org>
     *
     * Encorrientador Unido is free software: you can redistribute it and/or modify
     * it under the terms of the GNU General Public License as published by
     * the Free Software Foundation, either version 3 of the License, or
     * (at your option) any later version.
     *
     * Encorrientador Unido is distributed in the hope that it will be useful,
     * but WITHOUT ANY WARRANTY; without even the implied warranty of
     * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
     * GNU General Public License for more details.
     *
     * You should have received a copy of the GNU General Public License
     * along with this program.  If not, see <http://www.gnu.org/licenses/>.
     */



    //mimic the actuall admin-ajax
    define('DOING_AJAX', true);

    //if (!isset( $_POST['action']))
        //die('-1');

    require_once('../../../../wp-load.php');

    function handler_time ()	
    {
      $current_time = round(microtime(true) * 1000);

      echo json_encode(array($current_time));
    };

    //Typical headers
    header('Content-Type: text/html');
    send_nosniff_header();

    //Disable caching
    header('Cache-Control: no-cache');
    header('Pragma: no-cache');

    //$action = esc_attr(trim($_POST['action']));

    //A bit of security
    $allowed_actions = array(
        'heartbeat'
    );

    //For logged in users
    add_action('HEARTBEAT', 'handler_time');

    //For guests
    add_action('REJECT_heartbeat', 'handler_reject');

    //if(in_array($action, $allowed_actions)) {
    //    if(is_user_logged_in())
            do_action('HEARTBEAT');
    //else
    //    do_action('REJECT_'.$action);
    //}
    //else {
    //    die('-1');
    //}
?>