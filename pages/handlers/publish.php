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
    require_once('../encorr-commons.php');
    $db_connection = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD);
    mysqli_select_db($db_connection, DB_NAME);

    function handler_publish ()	
    {
        global $db_connection;

        // Event handler for packages carrying RTCPeerConnection offer data
        // and auxiliary parameters: SDP, ice candidates, hangup state (?),
        // avatar thumbnail, etc. All of them should be temporarily saved
        // in local DB and resent to recipient later.
        $raw_message = esc_attr(trim($_GET['msg']));

        $query_insert_args = array();
        $query_insert_args["skey"] = _GET['skey'];
        $query_insert_args["pkey"] = _GET['pkey'];
        $query_insert_args["numid"] = _GET['numid'];
        $query_insert_args["dest"] = _GET['c'];
        $query_insert_args["jsonp"] = _GET['jsonp'];
        $query_insert_args["uuid"] = _GET['uuid'];

        $decoded_message = urldecode($raw_message);
        $message = json_decode($decoded_message);
        // XDebug $_GET data:
        // SDP fields:
        //v
        //o
        //s
        //t
        //a=fingerprint
        //a=group
        //a=ice-options
        //a=msid-semantic
        //m=audio FK with subfields:
        //   c
        //   a=sendrecv
        //   a=extmap
        //   a=fmtp  FK
        //   a=fmtp  FK
        //   a=ice-pwd
        //   a=ice-ufrag
        //   a=mid
        //   a=msid
        //   a=rtcp-mux
        //   a=rtpmap
        //   a=rtpmap
        //   a=rtpmap
        //   a=rtpmap
        //   a=rtpmap
        //   a=setup
        //   a=ssrc
        //m=video FK with subfields:
        //   c
        //   a=sendrecv
        //   a=extmap
        //
        //
        // picture insert:
        // PK_REQ(pubsub) = "$_GET(pkey)*$_GET(skey)",
        // numid = "$_GET(numid)",
        // FK_DEST(dest) = "$_GET(c)" ,
        // jsonp = "$_GET(jsonp)" ,
        // uuid = "$_GET(uuid)" ,
        // FK_MSG(message) = [ sub-insert:
        //                     PK(mid) = ".id",
        //                     FK_DEST(number) = ".number",
        //                     FK_PIC(pack) =  [sub-insert:
        //                                      PK(picid) = "HASH(.packet.message.substr(pos(',')))" ,
        //                                      type = ".packet.message.substr(pos('/'),pos(':'))"
        //                                     ]
        //                   ]
        //
        // hangup insert:
        // PK_REQ(pubsub) = "$_GET(pkey)*$_GET(skey)",
        // numid = "$_GET(numid)",
        // FK_DEST(dest) = "$_GET(c)",
        // jsonp = "$_GET(jsonp)",
        // uuid = "$_GET(uuid)",
        // FK_MSG(message) = [ sub-insert:
        //                     PK(mid) = ".id",
        //                     FK_DEST(number) = ".number",
        //                     hangup =  ".packet.hangup"
        //                   ]
        //
        // SDP insert:
        // PK_REQ(pubsub) = "$_GET(pkey)*$_GET(skey)",
        // numid = "$_GET(numid)",
        // FK_DEST(dest) = "$_GET(c)",
        // jsonp = "$_GET(jsonp)",
        // uuid = "$_GET(uuid)",
        // FK_MSG(message) = [ sub-insert:
        //                     PK(mid) = ".id",
        //                     FK_DEST(number) = ".number",
        //                     FK_SDP(sdp) =  [ sub-insert:
        //                                      PK(sdpid) =
        //                                        "HASH(.apcket.message.sdp.split('\r\n')
        //                                              .makedict(':')
        //                                              ['a=fingerprint']
        //                                             )
        //                                        ",
        //                                      <.packet.message.sdp fields>,
        //                                      type = ".packet.message.type"
        //                                    ]
        //                   ]
        //
        // candidate insert:
        // PK_REQ(pubsub) = "$_GET(pkey)*$_GET(skey)",
        // numid = "$_GET(numid)",
        // FK_DEST(dest) = "$_GET(c)" ,
        // jsonp = "$_GET(jsonp)" ,
        // uuid = "$_GET(uuid)" ,
        // FK_MSG(message) = [ sub-insert:
        //                     PK(mid) = ".id",
        //                     FK_DEST(number) = ".number",
        //                     FK(cand) = [ sub-insert:
        //                                  PK(candid) = "HASH(.packet.message.candidate)",
        //                                  sdpmid = ".packet.message.sdpmid",
        //                                  sdpmlineindex = ".packet.message.sdpmlineindex"
        //                                ]
        //                   ]

        $prev_req_check = $db_connection->prepare("SELECT pubsub FROM " . REQTABLE);

        $m_packet = $message["packet"];

        if( array_key_exists( "thumbnail", $m_packet ) ) {
            $query_insert_args["mid"] = $message.id;
            $query_insert_args["number"] = $message.number;
            $query_insert_args["thumbnail"] = $m_packet["thumbnail"];
            $ps_keys = query_insert_args["pkey"] . query_insert_args["skey"];

            if( $prev_req_check->execute() ) {
                $prev_req_check->bind($prev_requests);

                $prev_req_key = array_search( $ps_keys, $prev_requests );

                if( !$prev_req_key ) {
                    $check_destination =
                        $db_connection->prepare("SELECT dest_id, dest_key FROM " .
                            DESTINATIONTABLE
                        );

                    if( $check_destination->execute() ) {
                        $check_destination->bind_result( $dest_id_list, $dest_key_list );

                        $dest_id = array_search( $query_insert_args["dest"],
                            $dest_key_list
                        );

                        if( !$dest_id ) {
                            $new_destination = db_connection->prepare("INSERT INTO " .
                                DESTINATIONTABLE .
                                "(dest_key, dest_number)" .
                                "VALUES" .
                                "(?, ?)"
                            );
                            $new_destination->bind_params( 'si',
                                $query_insert_args["dest"],
                                $query_insert_args["number"]
                            );

                            if( $new_destination->execute() )
                                $dest_id = $new_destination->insert_id();
                            else
                                throw new Exception("Failed to register new destination.");
                        }
                    }
                    else
                        throw new Exception("Failed to search for existing destination.");

                    $new_thumbnail_request = $db_connection->prepare("INSERT INTO " .
                        REQTABLE .
                        "( pubsub, numid, dest, jsonp, uuid, mid, thumbnail)" .
                        "VALUES" .
                        "( ?, ?, ?, ?, ?, ?, ? )"
                    );
                    $new_thumbnail_request->bind_params( 'siissib',
                        $query_insert_args["pubsub"],
                        $query_insert_args["numid"],
                        $dest_id,
                        $query_insert_args["jsonp"],
                        $query_insert_args["uuid"],
                        $query_insert_args["mid"],
                        $query_insert_args["thumbnail"]
                    );

                    if( !$new_thumbnail_request->execute() )
                        throw new Exception("Failed to register new thumbnail request.");
                }
            }
            else
                throw new Exception ("Failed to search for previous peer requests.");
        }
        /*
        if( array_key_exists( "sdp", $message["packet"] ) )
            db_connection->prepare();
        if( array_key_exists( "hangup", $message["packet"] ) )
            db_connection->prepare();
        if( array_key_exists( "candidate", $message["packet"] ) )
            db_connection->prepare();
        */

        $publish_response = array(0);

        echo json_encode($publish_response);
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
        'publish'
    );

    //For logged in users
    add_action('PUBLISH', 'handler_publish');

    //For guests
    add_action('REJECT_publish', 'handler_reject');

    //if(in_array($action, $allowed_actions)) {
    //    if(is_user_logged_in())
            do_action('PUBLISH');
    //    else
    //        do_action('REJECT_'.$action);
    //}
		//else {
    //    die('-1');
    //}
?>
