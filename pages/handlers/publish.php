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

    function handler_publish ()	
    {
        // carrying RTCPeerConnection offer data and auxiliary parameters: SDP, ice candidates,
        // hangup state (?), avatar thumbnail, etc. All of them should be saved in local DB
        // and resent to recipient.
        $raw_message = esc_attr(trim($_GET['msg']));
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
        // picture insert:
        // PK_REQ(pubsub) = "$_GET(pkey)*$_GET(skey)", numid = "$_GET(numid)", FK_DEST(dest) = "$_GET(c)" , jsonp = "$_GET(jsonp)" , uuid = "$_GET(uuid)" , FK_MSG(message) = [ sub-insert: PK(mid) = ".id", FK_DEST(number) = ".number", FK_PIC(pack) =  [sub-insert: PK(picid) = "HASH(.packet.message.substr(pos(',')))" , type = ".packet.message.substr(pos('/'),pos(':'))"] ]
        // hangup insert:
        // PK_REQ(pubsub) = "$_GET(pkey)*$_GET(skey)", numid = "$_GET(numid)", FK_DEST(dest) = "$_GET(c)" , jsonp = "$_GET(jsonp)" , uuid = "$_GET(uuid)" , FK_MSG(message) = [ sub-insert: PK(mid) = ".id", FK_DEST(number) = ".number", hangup =  ".packet.hangup" ]
        // SDP insert:
        // PK_REQ(pubsub) = "$_GET(pkey)*$_GET(skey)", numid = "$_GET(numid)", FK_DEST(dest) = "$_GET(c)" , jsonp = "$_GET(jsonp)" , uuid = "$_GET(uuid)" , FK_MSG(message) = [ sub-insert: PK(mid) = ".id", FK_DEST(number) = ".number", FK_SDP(sdp) =  [ sub-insert:  PK(sdpid) = "HASH(.apcket.message.sdp.split('\r\n').makedict(':')['a=fingerprint'])" , <.packet.message.sdp fields> , type = ".packet.message.type" ] ]
        // candidate insert:
        // PK_REQ(pubsub) = "$_GET(pkey)*$_GET(skey)", numid = "$_GET(numid)", FK_DEST(dest) = "$_GET(c)" , jsonp = "$_GET(jsonp)" , uuid = "$_GET(uuid)" , FK_MSG(message) = [ sub-insert: PK(mid) = ".id", FK_DEST(number) = ".number", FK(cand) = [sub-insert:  PK(candid) = "HASH(.packet.message.candidate)", sdpmid = ".packet.message.sdpmid", sdpmlineindex = ".packet.message.sdpmlineindex" ] ]  

        $published_response = array(5,4,3,2,1);

        echo json_encode($published_response);
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
