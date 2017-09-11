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



    define('SDPTABLE', 'sdp_sdp_data');
    define('HANGUPTABLE', 'sdp_hangup_data');
    define('ICECANDIDATETABLE', 'sdp_icecand_data');
    define('REQTABLE', 'sdp_req_data');
    define('THUMBNAILTABLE', 'sdp_pack_data');
    define('DESTINATIONTABLE', 'sdp_dest_data');


    $db_connection = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD);

    if( !isset($tables_initialised) )
        $tables_initialised = False;

    try {
        // Check if there are initialised tables in DB:
        if( !$tables_initialised ) {
            mysqli_select_db($db_connection, "information_schema");
            $stmt_list = $db_connection->prepare("SELECT table_name FROM tables" .
                " WHERE table_name LIKE ?"
            );
            $stmt_list->bind_param( "s", "%".SDPTABLE."%" );

            if( $stmt_list->execute() ) {
                $result_tables = $stmt_list->get_result();

                if( $result_tables != NULL ) {
                    $queries = array();
                    mysqli_select_db($db_connection, DB_NAME);

                    $queries[HANGUPTABLE] = $db_connection->prepare("CREATE TABLE " .
                        HANGUPTABLE .
                        " ( PRIMARY KEY (cand_id) INT(16) UNSIGNED AUTO_INCREMENT," .
                        "   candidate VARCHAR(50) NOT NULL," .
                        "   sdpmlineindex VARCHAR(50) NOT NULL" .
                        " )"
                    );
                    $queries[SDPTABLE] = $db_connection->prepare("CREATE TABLE " .
                        SDPTABLE .
                        " ( PRIMARY KEY (sdp_id) INT(16) UNSIGNED AUTO_INCREMENT," .
                        "   sdp_fingerpriint VARCHAR(50) NOT NULL UNIQUE," .
                        "   message_typ VARCHAR(50)," .
                        "   sdp_content LONGBLOB NOT NULL" .
                        " )"
                    );
                    $queries[ICECANDIDATETABLE] = $db_connection->prepare("CREATE TABLE " .
                        ICECANDIDATETABLE .
                        " ( PRIMARY KEY (cand_id) INT(16) UNSIGNED AUTO_INCREMENT," .
                        "   candidate VARCHAR(50) NOT NULL," .
                        "   sdpmlineindex VARCHAR(50) NOT NULL" .
                        " )"
                    );
                    $queries[DESTINATIONTABLE] = $db_connection->prepare("CREATE TABLE " .
                        DESTINATIONTABLE .
                        " ( PRIMARY KEY (dest_id) INT(16) UNSIGNED AUTO_INCREMENT," .
                        "   dest_key VARCHAR(50) NOT NULL," .
                        "   dest_number INT(16)" .
                        " )"
                    );
                    $queries[THUMBNAILTABLE] = $db_connection->prepare("CREATE TABLE " .
                        THUMBNAILTABLE .
                        " ( PRIMARY KEY (pkg_id) INT(16) UNSIGNED AUTO_INCREMENT," .
                        "   data TEXT NOT NULL" .
                        " )"
                    );
                    /*
                    $queries[reqtable] = $db_connection->prepare("CREATE TABLE " .
                        REQTABLE .
                        " ( PRIMARY KEY (picreq_id) INT(16) UNSIGNED AUTO_INCREMENT," .
                        "   pubsub VARCHAR(50) NOT NULL," .
                        "   numid INT(16) UNSIGNED NOT NULL," .
                        "   FOREIGN KEY (dest) REFERENCES " .
                        DESTINATIONTABLE .
                        "(dest_id)," .
                        "   jsonp VARCHAR(50)," .
                        "   uuid VARCHAR(50) NOT NULL," .
                        "   mid VARCHAR(50) NOT NULL," .
                        "   CONSTRAINT UNIQUE (candidate, package, sdp, hangup)," .
                        "   CONSTRAINT CHECK (candidate is not null or package is not null)," .
                        "   FOREIGN KEY (candidate) REFERENCES "+ICECANDIDATETABLE+"(cand_id)," .
                        "   FOREIGN KEY (package) REFERENCES "+PACKAGETABLE+"(pkg_id)," .
                        "   FOREIGN KEY (sdp) REFERENCES "+SDPTABLE+"(sdp_id)," .
                        "   FOREIGN KEY (sdp) REFERENCES "+SDPTABLE+"(sdp_id)" .
                        " )"
                    );
                    */
                    $queries[reqtable] = $db_connection->prepare("CREATE TABLE " .
                        REQTABLE .
                        " ( PRIMARY KEY (picreq_id) INT(16) UNSIGNED AUTO_INCREMENT," .
                        "   pubsub VARCHAR(50) NOT NULL UNIQUE," .
                        "   numid INT(16) UNSIGNED NOT NULL," .
                        "   foreign key (dest) REFERENCES " .
                        DESTINATIONTABLE .
                        "(dest_id)," .
                        "   jsonp VARCHAR(50)," .
                        "   uuid VRCHAR(50) NOT NULL," .
                        "   mid VARCHAR(50) NOT NULL," .
                        "   CONSTRAINT UNIQUE (candidate, thumbnail, sdp, hangup)," .
                        "   CONSTRAINT CHECK (candidate IS NOT NULL" .
                        "                     OR thumbnail IS NOT NULL" .
                        "                     OR sdp IS NOT NULL" .
                        "                     OR hangup IS NOT NULL" .
                        "                    )," .
                        "   candidate LONGBLOB," .
                        "   thumbnail LONGBLOB," .
                        "   sdp LONGBLOB," .
                        "   hangup LONGBLOB" .
                        " )"
                      );

                    foreach( $queries as $q_key => $q_value )
                        if( !$query->execute() )
                            throw new Exception('Failed to create table for %s.' . $q_key);
                }
                $tables_initialised = True;
            }
            else
               throw new Exception('Failed to check SDP tables.');
        }
    } catch (Exception $e) {
        echo json_encode(array('Caught exception: ' + $e->getMessage() + "\n"));
    }

?>
