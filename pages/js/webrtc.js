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
 * Copyright (c) 2017 Stephen Blum
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */



(function(){


// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
// WebRTC Simple Calling API + Mobile
// -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
var PHONE = window.PHONE = function(config) {
    var PHONE         = function(){};
    var pubnub        = PUBNUB(config);
    //var WSURL         = pubnub.get_ws_url();
    // WebSocket object with overriden prototype methods from PN API
    // var pubnubws      = WS(WSURL, 'WSSKeyExchange');
    // Browser WebSocket unchanged object
    //var encorrws      = WebSocket(WSURL, 'WSSKeyExchange');
    // Symmetric and asymmetric keys
    var cipherkeys    = {};
    var pubkey        = config.publish_key   || 'demo';
    var snapper       = function(){ return ' ' }
    var subkey        = config.subscribe_key || 'demo';
    var sessionid     = PUBNUB.uuid();
    var mystream      = null;
    var myvideo       = document.createElement('video');
    var myconnection  = false;
    var mediaconf     = config.media || { audio : true, video : true };
    var conversations = {};
    var oneway        = config.oneway || false
    var broadcast     = config.broadcast || false;

    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    // RTC Peer Connection Session (one per call)
    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    var PeerConnection =
        window.RTCPeerConnection    ||
        window.mozRTCPeerConnection ||
        window.webkitRTCPeerConnection;

    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    // ICE (many route options per call)
    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    var IceCandidate =
        window.mozRTCIceCandidate ||
        window.RTCIceCandidate;

    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    // Media Session Description (offer and answer per call)
    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    var SessionDescription =
        window.RTCSessionDescription    ||
        window.mozRTCSessionDescription ||
        window.webkitRTCSessionDescription;

    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    // Local Microphone and Camera Media (one per device)
    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    /*
    navigator.getUserMedia = 
        navigator.getUserMedia       ||
        navigator.webkitGetUserMedia ||
        navigator.mozGetUserMedia    ||
        navigator.msGetUserMedia;
    */  // Deprecated

    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    // STUN Server List Configuration (public STUN list)
    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    var rtcconfig = {
        /*constraints: {
            mandatory: {
                OfferToReceiveAudio: true,
                OfferToReceiveVideo: true
            },
            optional: []
        },*/
        certificates : [],
        iceServers : [{"urls" :
            ["stun:stun.services.mozilla.com", "stun:stunserver.org"]
        }],
        /*iceServers : [{ "url" :
                navigator.mozGetUserMedia ? "stun:stun.services.mozilla.com"
                : navigator.webkitGetUserMedia ? "stun:stun.l.google.com:19302"
                : "stun:23.21.150.121"
            },
            {url: "stun:stun.l.google.com:19302"},
            {url: "stun:stun1.l.google.com:19302"},
            {url: "stun:stun2.l.google.com:19302"},
            {url: "stun:stun3.l.google.com:19302"},
            {url: "stun:stun4.l.google.com:19302"},
            {url: "stun:23.21.150.121"},
            {url: "stun:stun01.sipphone.com"},
            {url: "stun:stun.ekiga.net"},
            {url: "stun:stun.fwdnet.net"},
            {url: "stun:stun.ideasip.com"},
            {url: "stun:stun.iptel.org"},
            {url: "stun:stun.rixtelecom.se"},
            {url: "stun:stun.schlund.de"},
            {url: "stun:stunserver.org"},
            {url: "stun:stun.softjoys.com"},
            {url: "stun:stun.voiparound.com"},
            {url: "stun:stun.voipbuster.com"},
            {url: "stun:stun.voipstunt.com"},
            {url: "stun:stun.voxgratia.org"},
            {url: "stun:stun.xten.com"}]*/
    };

    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    // Cipher suites
    // (for reference please read the documentation at
    //  https://www.w3.org/TR/2016/PR-WebCryptoAPI)
    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    ciphersuites = [
        {
            name: 'ECDSA',
            hash: 'SHA-256',
            modulusLength: 256,
            publicExponent: new Uint8Array([1, 0, 1])
        },
        {
            name: 'RSA-OAEP',
            hash: 'SHA-256',
            modulusLength: 4096,
            publicExponent: new Uint8Array([1, 0, 1])
        },
        {
            name: 'RSASSA-PKCS1-v1_5',
            hash: 'SHA-256',
            modulusLength: 4096,
            publicExponent: new Uint8Array([1, 0, 1])
        },
        {
            name: 'RSA-OAEP',
            hash: 'SHA-256',
            modulusLength: 2048,
            publicExponent: new Uint8Array([1, 0, 1])
        },
        {
            name: 'RSASSA-PKCS1-v1_5',
            hash: 'SHA-256',
            modulusLength: 2048,
            publicExponent: new Uint8Array([1, 0, 1])
        },
        {
            name: 'AES-CBC',
            hash: 'SHA-256',
            modulusLength: 256,
            publicExponent: new Uint8Array([1, 0, 1])
        }
    ];

    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    // Custom STUN Options
    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    function add_servers(servers) {
        if (servers.constructor === Array)
            [].unshift.apply(rtcconfig.iceServers, servers);
        else rtcconfig.iceServers.unshift(servers);
    }

    if ('servers' in config) add_servers(config.servers);

    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    // PHONE Events
    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    var messagecb     = function(){};
    var readycb       = function(){};
    var unablecb      = function(){};
    var debugcb       = function(){};
    var connectcb     = function(){};
    var disconnectcb  = function(){};
    var reconnectcb   = function(){};
    var callstatuscb  = function(){};
    var receivercb    = function(){};
		var connectcorecb = function(){
                          onready(true)
                        };

    PHONE.message    = function(cb) { messagecb    = cb };
    PHONE.ready      = function(cb) { readycb      = cb };
    PHONE.unable     = function(cb) { unablecb     = cb };
    PHONE.callstatus = function(cb) { callstatuscb = cb };
    PHONE.debug      = function(cb) { debugcb      = cb };
    PHONE.connect    = function(cb) { connectcb    = cb };
    PHONE.disconnect = function(cb) { disconnectcb = cb };
    PHONE.reconnect  = function(cb) { reconnectcb  = cb };
    PHONE.receive    = function(cb) { receivercb   = cb };

    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    // Request for WebSocket server initial start
    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    function request_ws_server_launch() {
        if (wssrvready) return;

        var srv_status_cb = function (response) {
            if (response[0] != 'OK')
                timeout( request_ws_server_launch, 5*SECOND );
        }

        pubnub.launch_ws_server(srv_status_cb);
    }

    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    // Initialize periodical encryption key exchange
    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    function init_transmission_encryption() {
        var ws_message_cb = function (msg_event) {
            cipherkeys = msg_event.data;
        }

        var array = new Uint32Array(255);
        window.crypto.getRandomValues(array);

        encorrws.onmessage = ws_message_cb;
        encorrws.send('InitKeyExchange');
    }

    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    // Generate certificates
    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    function generate_certificates( certs_ready_cb ) {
        gen_certificates(
            ongencert,
            ciphersuites.slice()
        );

        // Add new certificates for PeerConnection
        function gen_certificates( on_cert_ready, cert_descriptions ) {
            if (cert_descriptions.length > 0)
                PeerConnection.generateCertificate(
                    cert_descriptions.pop()
                ).then(
                    function (cert) {
                        on_cert_ready( cert, cert_descriptions );
                    }
                ).catch(
                    // note - '.else' behavior for certifiacate generation is not entirely documented
                    function (err) {
                        on_cert_ready( null, cert_descriptions );
                    }
                );
            else{
                // todo - implement rigorous error handling
                debugcb(err);
            }
        }

        // Add generated certificate into a list of available certificates
        function ongencert( cert, cert_descriptions ) {
            cert && rtcconfig.certificates.push(cert);
            if (cert_descriptions.length > 0)
                gen_certificates( ongencert, cert_descriptions );
            else
               certs_ready_cb();
        }
    }

    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    // Add/Get Conversation - Creates a new PC or Returns Existing PC
    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    function get_conversation( number, talk_ready_cb, isAnswer ) {
        var talk = conversations[number] || get_new_conversation(number)

        function get_new_conversation(num){
            var talk = {
                number  : num,
                status  : '',
                image   : document.createElement('img'),
                started : +new Date,
                imgset  : false,
                imgsent : 0,
                pc      : new PeerConnection(rtcconfig),
                closed  : false,
                usermsg : function(){},
                thumb   : null,
                connect : function(){},
                end     : function(){}
            };

            // Setup Event Methods
            talk.pc.ontrack/*onaddstream*/    = config.onaddstream || onaddstream;
            talk.pc.onicecandidate = onicecandidate;
            talk.pc.number         = num;

            // Disconnect and Hangup
            talk.hangup = function(signal) {
                if (talk.closed) return;

                talk.closed = true;
                talk.imgset = false;
                clearInterval(talk.snapi);

                if (signal !== false) transmit( num, { hangup : true } );

                talk.end(talk);
                talk.pc.close();
                close_conversation(num);
            };

            // Sending Messages
            talk.send = function(message) {
                transmit( num, { usermsg : message } );
            };

            // Sending Stanpshots
            talk.snap = function() {
                var pic = snapper();
                if (talk.closed) clearInterval(talk.snapi);
                transmit( num, { thumbnail : pic } );
                var img = document.createElement('img');
                img.src = pic;
                return { data : pic, image : img };
            };
            talk.snapi = setInterval( function() {
                if (talk.imgsent++ > 5) return clearInterval(talk.snapi);
                talk.snap();
            }, 1500 );
            talk.snap();

            // Nice Accessor to Update Disconnect & Establis CBs
            talk.thumbnail = function(cb) {talk.thumb   = cb; return talk};
            talk.ended     = function(cb) {talk.end     = cb; return talk};
            talk.connected = function(cb) {talk.connect = cb; return talk};
            talk.message   = function(cb) {talk.usermsg = cb; return talk};

            // Add Local Media Streams Audio Video Mic Camera
            //  If answering and oneway streaming, do not attach stream
            if (!isAnswer || !oneway) talk.pc.addStream(mystream);   // Add null here on the receiving end of streaming to go one-way.

            // Notify of Call Status
            update_conversation( talk, 'connecting' );

            // Return Brand New Talk Reference
            conversations[num] = talk;

            return talk;
        }

        // Return Existing or New Reference to Caller
        return talk;
    }

    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    // Remove Conversation
    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    function close_conversation(number) {
        conversations[number] = null;
        delete conversations[number];
    }

    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    // Notify of Call Status Events
    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    function update_conversation( talk, status ) {
        talk.status = status;
        callstatuscb(talk);
        return talk;
    }

    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    // Get Number
    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    PHONE.number = function() {
        return config.number;
    };

    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    // Get Call History
    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    PHONE.history = function(settings) {
        pubnub.history({
            channel  : settings[number],
            callback : function(call_history) {
                settings['history'](call_history[0]);
            }
        })
    };

    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    // Make Call - Create new PeerConnection
    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    PHONE.dial = function(number, servers, on_call_dialed) {
        if (!!servers) add_servers(servers);

        generate_certificates(certreadycb);

        function certreadycb() {
            var talk = get_conversation(number);
            var pc   = talk.pc;

            // Prevent Repeat Calls
            if (talk.dialed) return false;
            talk.dialed = true;

            // Send SDP Offer (Call)
            pc.createOffer( function(offer) {
                transmit( number, { hangup : true } );
                transmit( number, offer, 2 );
                pc.setLocalDescription( offer, debugcb, debugcb );
            }, debugcb );
            // todo - implement new way to send an offer
            // during RTCPeerConnection.onnegotiationneeded event handling
            //
            // function handleNegotiationNeededEvent() {
            //   myPeerConnection.createOffer().then(function(offer) {
            //     return myPeerConnection.setLocalDescription(offer);
            //   })
            //   .then(function() {
            //     sendToServer({
            //       name: myUsername,
            //       target: targetUsername,
            //       type: "video-offer",
            //       sdp: myPeerConnection.localDescription
            //     });
            //   })
            //   .catch(reportError);
            // }

            // Return Session Reference
            if (on_call_dialed)
                on_call_dialed(talk);
        }
    };

    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    // Send Image Snap - Send Image Snap to All Calls or a Specific Call
    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    PHONE.snap = function( message, number ) {
        if (number) return get_conversation(number).snap(message);
        var pic = {};
        PUBNUB.each( conversations, function( number, talk ) {
            pic = talk.snap();
        } );
        return pic;
    };

    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    // Send Message - Send Message to All Calls or a Specific Call
    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    PHONE.send = function( message, number ) {
        if (number) return get_conversation(number).send(message);
        PUBNUB.each( conversations, function( number, talk ) {
            talk.send(message);
        } );
    };

    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    // End Call - Close All Calls or a Specific Call
    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    PHONE.hangup = function(number) {
        if (number) return get_conversation(number).hangup();
        PUBNUB.each( conversations, function( number, talk ) {
            talk.hangup();
        } );
    };

    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    // Auto-hangup on Leave
    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    PUBNUB.bind( 'unload,beforeunload', window, function() {
        if (PHONE.goodbye) return true;
        PHONE.goodbye = true;

        PUBNUB.each( conversations, function( number, talk ) {
            var mynumber = config.number;
            var packet   = { hangup:true };
            var message  = { packet:packet, id:sessionid, number:mynumber };
            var client   = new XMLHttpRequest();
            var url      = 'handlers/publish'
                           + pubkey + '/'
                           + subkey + '/0/'
                           + number + '/0/'
                           + JSON.stringify(message);

            client.open( 'GET', url, false );
            client.send();
            talk.hangup();
        } );

        return true;
    } );

    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    // Expose local stream and pubnub object
    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    PHONE.mystream = mystream;
    PHONE.pubnub   = pubnub;
    PHONE.oneway   = oneway;

    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    // Grab Local Video Snapshot
    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    function snapshots_setup(stream) {
        var video   = myvideo;
        var canvas  = document.createElement('canvas');
        var context = canvas.getContext("2d");
        var snap    = { width: 240, height: 180 };

        // Video Settings
        video.width  = snap.width;
        video.height = snap.height;
        video.src    = URL.createObjectURL(stream);
        video.volume = 0.0;
        video.play();

        // Canvas Settings
        canvas.width  = snap.width;
        canvas.height = snap.height;

        // Capture Local Pic
        snapper = function() {
            try {
                // todo - Use of mozImageSmoothingEnabled is deprecated. Please use the unprefixed imageSmoothingEnabled property instead.
                context.drawImage( video, 0, 0, snap.width, snap.height );
            } catch(e) {}
            return canvas.toDataURL( 'image/jpeg', 0.30 );
        };

        PHONE.video = video;
    }

    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    // Visually Display New Stream
    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    function onaddstream(obj) {
        var vid    = document.createElement('video');
        var stream = obj.stream;
        var number = (obj.srcElement || obj.target).number;
        var talk   = get_conversation(number);

        vid.setAttribute( 'autoplay', 'autoplay' );
        vid.setAttribute( 'data-number', number );
        vid.src = URL.createObjectURL(stream);

        talk.video = vid;
        talk.connect(talk);
    }

    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    // On ICE Route Candidate Discovery
    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    function onicecandidate(event) {
        if (!event.candidate) return;
        transmit( this.number, event.candidate );
    };

    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    // Listen For New Incoming Calls
    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    function subscribe() {
	    console.log("Subscribed to " + config.number);
        pubnub.subscribe({
            restore    : true,
            channel    : config.number,
            message    : receive,
            disconnect : disconnectcb,
            reconnect  : reconnectcb,
            connect    : connectcorecb
        });
    }

    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    // When Ready to Receive Calls
    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    function onready(subscribed) {
        if (subscribed) myconnection = true;
        if (!((mystream || oneway) && myconnection)) return;

        connectcb();
        readycb();
    }

    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    // Stream and error callbacks for MediaStream
    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
  	function streamcb(stream) {
      if (!stream) return unablecb(stream);
				mystream = stream;
        phone.mystream = stream;
        snapshots_setup(stream);
        onready();
        subscribe();
    };

  	function streamerrcb(err) {
      debugcb(err);
      return unablecb(err);
    };

    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    // Prepare Local Media Camera and Mic
    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    function getusermedia() { //Do something if not requesting any media?
      	// Should oneway or broadcast be set by default?
        if (oneway && !broadcast){
	        if (!PeerConnection){ return unablecb(); }
	        onready();
	        subscribe();
            return;
        }
        navigator.mediaDevices.getUserMedia( mediaconf ).then( streamcb ).catch( streamerrcb );
      	/*
        navigator.getUserMedia( mediaconf, function(stream) {
            if (!stream) return unablecb(stream);
            mystream = stream;
            phone.mystream = stream;
            snapshots_setup(stream);
            onready();
            subscribe();
        }, function(info) {
            debugcb(info);
            return unablecb(info);
        } );
      	*/  // Deprecated
    }

    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    // Send SDP Call Offers/Answers and ICE Candidates to Peer
    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    function transmit( phone, packet, times, time ) {
        if (!packet) return;
        var number  = config.number;
        var message = { packet : packet, id : sessionid, number : number };
        debugcb(message);
        pubnub.publish({ channel : phone, message : message });

        // Recurse if Requested for
        if (!times) return;
        time = time || 1;
        if (time++ >= times) return;
        setTimeout( function(){
            transmit( phone, packet, times, time );
        }, 150 );
    }

    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    // SDP Offers & ICE Candidates Receivable Processing
    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    function receive(message) {
        // Debug Callback of Data to Watch
        debugcb(message);

        // Get Call Reference
        var talk = get_conversation(message.number, true);

        // Ignore if Closed
        if (talk.closed) return;

        // User Message
        if (message.packet.usermsg) {
            messagecb( talk, message.packet.usermsg );
            return talk.usermsg( talk, message.packet.usermsg );
        }

        // Thumbnail Preview Image
        if (message.packet.thumbnail) return create_thumbnail(message);

        // If Hangup Request
        if (message.packet.hangup) return talk.hangup(false);

        // If Peer Calling Inbound (Incoming) - Can determine stream + receive here.
        if ( message.packet.sdp && !talk.received ) {
            talk.received = true;
            receivercb(talk);
        }

        // Update Peer Connection with SDP Offer or ICE Routes
        if (message.packet.sdp) add_sdp_offer(message);
        else                    add_ice_route(message);
    }

    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    // Create Remote Friend Thumbnail
    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    function create_thumbnail(message) {
        var talk       = get_conversation(message.number);
        talk.image.src = message.packet.thumbnail;

        // Call only once
        if (!talk.thumb) return;
        if (!talk.imgset) talk.thumb(talk);
        talk.imgset = true;
    }

    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    // Add SDP Offer/Answers
    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    function add_sdp_offer(message) {
        // Get Call Reference
        var talk = get_conversation(message.number, message.packet.type=='answer');
        var pc   = talk.pc;
        var type = message.packet.type == 'offer' ? 'offer' : 'answer';

        // Deduplicate SDP Offerings/Answers
        if (type in talk) return;
        talk[type]  = true;
        talk.dialed = true;

        // Notify of Call Status
        update_conversation( talk, 'routing' );

        // Add SDP Offer/Answer
        pc.setRemoteDescription(
            new SessionDescription(message.packet), function() {
                // Set Connected Status
                update_conversation( talk, 'connected' );

                // Call Online and Ready
                if (pc.remoteDescription.type != 'offer') return;

                // Create Answer to Call
                pc.createAnswer( function(answer) {
                    pc.setLocalDescription( answer, debugcb, debugcb );
                    transmit( message.number, answer, 2 );
                }, debugcb );
            }, debugcb
        );
    }

    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    // Add ICE Candidate Routes
    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    function add_ice_route(message) {
        // Leave if Non-good ICE Packet
        if (!message.packet)           return;
        if (!message.packet.candidate) return;

        // Get Call Reference
        var talk = get_conversation(message.number);
        var pc   = talk.pc;

        // Add ICE Candidate Routes
        pc.addIceCandidate(
            new IceCandidate(message.packet),
            debugcb,
            debugcb
        );
    }

    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    // Main - Request Camera and Mic
    // -=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
    getusermedia()

    return PHONE;
};


})();
