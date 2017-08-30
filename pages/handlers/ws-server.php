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

/*
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://sam.zoy.org/wtfpl/COPYING for more details.
 */



    //mimic the actuall admin-ajax
    define('DOING_AJAX', true);

    //if (!isset( $_POST['action']))
        //die('-1');

    require_once('../../../../wp-load.php'); 

    function handler_launch ()	
    {
        /*
Parts of below code were borrowed from here
https://github.com/nicokaiser/php-websocket/blob/master/examples/server_ssl.php
*/

        
        ini_set('display_errors', 1);
error_reporting(E_ALL);

require(__DIR__ . '/../lib/SplClassLoader.php');

$classLoader = new SplClassLoader('Wrench', __DIR__ . '/../lib');
$classLoader->register();

// Generate PEM file
$pemFile                = dirname(__FILE__) . '/generated.pem';
$pemPassphrase          = null;
$countryName            = "AA";
$stateOrProvinceName    = "none";
$localityName           = "none";
$organizationName       = "none";
$organizationalUnitName = "none";
$commonName             = "yyy.zzz";
$emailAddress           = "xxx@yyy.zzz";

\Wrench\Socket::generatePEMFile(
    $pemFile,
    $pemPassphrase,
    $countryName,
    $stateOrProvinceName,
    $localityName,
    $organizationName,
    $organizationalUnitName,
    $commonName,
    $emailAddress
);

// User can use tls in place of ssl
$server = new \Wrench\Server('127.0.0.1', 8000, 'ssl', $pemFile, $pemPassphrase);

// server settings:
$server->setMaxClients(100);
$server->setCheckOrigin(true);
$server->setAllowedOrigin('yyy.zzz');
$server->setMaxConnectionsPerIp(100);
$server->setMaxRequestsPerMinute(2000);

// Hint: Status application should not be removed as it displays usefull server informations:
$server->registerApplication('status', \Wrench\Application\StatusApplication::getInstance());
$server->registerApplication('demo', \Wrench\Application\DemoApplication::getInstance());

$server->run();
        
        echo json_encode('OK');
    };

    //Typical headers
    header('Content-Type: text/html');
    send_nosniff_header();

    //Disable caching
    header('Cache-Control: no-cache');
    header('Pragma: no-cache');

    //$action = esc_attr(trim($_POST['action']));

    //A bit of security
    //todo - implement secure action lists
    $allowed_actions = array(
        'launch_ws_server'
    );

    //For logged in users
    add_action('LAUNCH', 'handler_launch');

    //For guests
    add_action('REJECT_launch', 'handler_reject');

    //if(in_array($action, $allowed_actions)) {
    //    if(is_user_logged_in())
            do_action('LAUNCH');
    //    else
    //        do_action('REJECT_'.$action);
    //}
		//else {
    //    die('-1');
    //}







