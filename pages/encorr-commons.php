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



    define("SDPTABLE", "sdp_sdp_data");
    define("HANGUPTABLE", "sdp_hangup_data");
    define("ICECANDIDATETABLE", "sdp_icecand_data");
    define("REQTABLE", "sdp_req_data");
    define("THUMBNAILTABLE", "sdp_thumbnail_data");
    define("DESTINATIONTABLE", "sdp_dest_data");


    $db_connection = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD);

    if( !isset($tables_initialised) )
        $tables_initialised = False;

    try {
        // Check if there are initialised tables in DB:
        if( !$tables_initialised ) {
            mysqli_select_db($db_connection, "information_schema");
            $stmt_list = $db_connection->prepare("SELECT table_name FROM tables" .
                " WHERE table_name = ?"
            );
            $param_value = SDPTABLE;
            $stmt_list->bind_param( "s", $param_value );

            if( $stmt_list->execute() ) {
                $result_tables = $stmt_list->get_result();

                if( $result_tables != NULL ) {
                    $queries = array();
                    mysqli_select_db($db_connection, DB_NAME);

                    $queries[HANGUPTABLE] = $db_connection->prepare("CREATE TABLE " .
                        HANGUPTABLE .
                        " ( cand_id INT(16) UNSIGNED AUTO_INCREMENT PRIMARY KEY," .
                        "   candidate VARCHAR(50) NOT NULL," .
                        "   sdpmlineindex VARCHAR(50) NOT NULL" .
                        " )"
                    );
                    $queries[SDPTABLE] = $db_connection->prepare("CREATE TABLE " .
                        SDPTABLE .
                        " ( sdp_id INT(16) UNSIGNED AUTO_INCREMENT PRIMARY KEY," .
                        "   sdp_fingerpriint VARCHAR(50) NOT NULL UNIQUE," .
                        "   message_typ VARCHAR(50)," .
                        "   sdp_content LONGBLOB NOT NULL" .
                        " )"
                    );
                    $queries[ICECANDIDATETABLE] = $db_connection->prepare("CREATE TABLE " .
                        ICECANDIDATETABLE .
                        " ( cand_id INT(16) UNSIGNED AUTO_INCREMENT PRIMARY KEY," .
                        "   candidate VARCHAR(50) NOT NULL," .
                        "   sdpmlineindex VARCHAR(50) NOT NULL" .
                        " )"
                    );
                    $queries[DESTINATIONTABLE] = $db_connection->prepare("CREATE TABLE " .
                        DESTINATIONTABLE .
                        " ( dest_id INT(16) UNSIGNED AUTO_INCREMENT PRIMARY KEY," .
                        "   dest_key VARCHAR(50) NOT NULL," .
                        "   dest_number INT(16)" .
                        " )"
                    );
                    $queries[THUMBNAILTABLE] = $db_connection->prepare("CREATE TABLE " .
                        THUMBNAILTABLE .
                        " ( pkg_id INT(16) UNSIGNED AUTO_INCREMENT PRIMARY KEY," .
                        "   data TEXT NOT NULL" .
                        " )"
                    );
                    /*
                    $queries[reqtable] = $db_connection->prepare("CREATE TABLE " .
                        REQTABLE .
                        " ( picreq_id INT(16) UNSIGNED AUTO_INCREMENT PRIMARY KEY," .
                        "   pubsub VARCHAR(50) NOT NULL," .
                        "   numid INT(16) UNSIGNED NOT NULL," .
                        "   dest FOREIGN KEY REFERENCES " .
                        DESTINATIONTABLE .
                        "(dest_id) ON DELETE CASCADE," .
                        "   jsonp VARCHAR(50)," .
                        "   uuid VARCHAR(50) NOT NULL," .
                        "   mid VARCHAR(50) NOT NULL," .
                        "   CONSTRAINT UNIQUE (candidate, package, sdp, hangup)," .
                        "   CONSTRAINT CHECK (candidate is not null or package is not null)," .
                        "   candidate FOREIGN KEY REFERENCES "+ICECANDIDATETABLE+"(cand_id) ON DELETE CASCADE," .
                        "   package FOREIGN KEY REFERENCES "+PACKAGETABLE+"(pkg_id) ON DELETE CASCADE," .
                        "   sdp FOREIGN KEY REFERENCES "+SDPTABLE+"(sdp_id) ON DELETE CASCADE," .
                        "   sdp FOREIGN KEY REFERENCES "+SDPTABLE+"(sdp_id) ON DELETE CASCADE" .
                        " )"
                    );
                     */
                    $queries[REQTABLE] = $db_connection->prepare("CREATE TABLE " .
                        REQTABLE .
                        " ( picreq_id INT UNSIGNED " .
                        "             AUTO_INCREMENT NOT NULL " .
                        "             PRIMARY KEY," .
                        "   pubsub VARCHAR(50) NOT NULL UNIQUE," .
                        "   numid SMALLINT UNSIGNED NOT NULL," .
                        "   dest INT(16) UNSIGNED," .
                        "   FOREIGN KEY (dest) REFERENCES " .
                        DESTINATIONTABLE .
                        "(dest_id) ON DELETE CASCADE," .
                        "   jsonp VARCHAR(50)," .
                        "   uuid VARCHAR(50) NOT NULL," .
                        "   mid VARCHAR(50) NOT NULL," .
                        "   CONSTRAINT UNIQUE (candidate_idx," .
                        "                      thumbnail_idx," .
                        "                      sdp_idx," .
                        "                      hangup_idx" .
                        "                     )," .
                        "   CONSTRAINT CHECK (candidate_idx IS NOT NULL" .
                        "                     OR thumbnail_idx IS NOT NULL" .
                        "                     OR sdp_idx IS NOT NULL" .
                        "                     OR hangup_idx IS NOT NULL" .
                        "                    )," .
                        "   candidate LONGBLOB," .
                        "   thumbnail LONGBLOB," .
                        "   sdp LONGBLOB," .
                        "   hangup LONGBLOB," .
                        "   candidate_idx SMALLINT UNSIGNED DEFAULT '0'," .
                        "   sdp_idx SMALLINT UNSIGNED DEFAULT '0'," .
                        "   hangup_idx SMALLINT UNSIGNED DEFAULT '0'," .
                        "   thumbnail_idx SMALLINT UNSIGNED DEFAULT '0'," .
                        "   INDEX IDXcandidate (candidate_idx, candidate(255))," .
                        "   INDEX IDXhangup (hangup_idx, hangup(255))," .
                        "   INDEX IDXsdp (sdp_idx, sdp(255))," .
                        "   INDEX IDXthumbnail (thumbnail_idx, thumbnail(255))" .
                        " )"
                      );

                    foreach( $queries as $q_key => $q_value )
                        if( !$q_value->execute() ) {
                            $last_db_error = $db_connection->error;
                            throw new Exception("Failed to create table for " .
                                                $q_key .
                                                ". Reason: " .
                                                $las_db_error
                                            );
                        }
                }
                $tables_initialised = True;
            }
            else {
                $last_db_error = $db_connection->error;
                throw new Exception("Failed to check SDP tables. Reason: " .
                                    $last_db_error
                                );
            }
        }
    } catch (Exception $e) {
        echo json_encode(array("Caught exception: " + $e->getMessage() + "\n"));
    }

?>
