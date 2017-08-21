<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/html">
<head>
    <!--
         This file is part of Encorrientador Unido.

         Encorrientador Unido is a collection of web-based software plugins for
         popular open-source content management systems. It has a purpose of providing
         secure browser-based video chat with database management features and
         account registration independent from third-party content delivery networks.
         This software is based on PubNub WebRTC video chat source code.
         Copyright (C) 2017  Andrei Shishkin <QfpbC7u3V13qJUop@i2pmail.org>

         Encorrientador Unido is free software: you can redistribute it and/or modify
         it under the terms of the GNU General Public License as published by
         the Free Software Foundation, either version 3 of the License, or
         (at your option) any later version.

         Encorrientador Unido is distributed in the hope that it will be useful,
         but WITHOUT ANY WARRANTY; without even the implied warranty of
         MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
         GNU General Public License for more details.

         You should have received a copy of the GNU General Public License
         along with this program.  If not, see <http://www.gnu.org/licenses/>.
    -->
    <!--
        The MIT License (MIT)

        Copyright (c) 2015 Kevin Gleason

        Permission is hereby granted, free of charge, to any person obtaining a copy
        of this software and associated documentation files (the "Software"), to deal
        in the Software without restriction, including without limitation the rights
        to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
        copies of the Software, and to permit persons to whom the Software is
        furnished to do so, subject to the following conditions:

        The above copyright notice and this permission notice shall be included in all
        copies or substantial portions of the Software.

        THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
        IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
        FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
        AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
        LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
        OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
        SOFTWARE.
    -->
    <title>WebRTC Video Chat</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    
    <link rel="stylesheet" type="text/css" href="stylesheets/style.css" />
    <link rel="stylesheet" type="text/css" href="fonts/font-awesome-4.3.0/css/font-awesome.min.css" />
    <script src="js/modernizr.custom.js"></script>
	<link rel="stylesheet" type="text/css" href="css/normalize.css" />


    
</head>
<body>
<div class = "bodyDiv">
    
    <div class="md-modal md-effect-13" id="modal-13">
			<div class="md-content">
				<div>
	                 <div id="vid-box"></div>
                    <button class="md-close">
                       <i class="md-close fa fa-times-circle-o"></i>
                    </button>
                    
				</div>
			</div>
    </div>
    
    
        <form name="loginForm" id="login" action="#" onsubmit="return errWrap(login,this);">
            <span class="input input--nao">
                <input class="input__field input__field--nao" type="text" name="username" id="username" placeholder="Enter A Username"/>
                        <label class="input__label input__label--nao" for="username">
                            <span class="input__label-content input__label-content--nao">                                          
                            </span>
                        </label>
                    <svg class="graphic graphic--nao" width="300%" height="100%" viewBox="0 0 1200 60" preserveAspectRatio="none">
                        <path d="M0,56.5c0,0,298.666,0,399.333,0C448.336,56.5,513.994,46,597,46c77.327,0,135,10.5,200.999,10.5c95.996,0,402.001,0,402.001,0"/>
                    </svg>
            </span>
            
               <button class="cbutton cbutton--effect-radomir" type="submit" name="login_submit" value="Log In" style="margin-top: 40px; margin-left:-10px">
						<i class="cbutton__icon fa fa-fw fa fa-sign-in"></i>
				</button>
        </form>

	<form name="callForm" id="call" action="#" onsubmit="return errWrap(makeCall,this);">
        <span class="input input--nao">
            <input class="input__field input__field--nao" type="text" name="number" id="call" placeholder="Enter User To Call!"/>
					<label class="input__label input__label--nao" for="number">
						<span class="input__label-content input__label-content--nao">                                          
                        </span>
					</label>
				<svg class="graphic graphic--nao" width="300%" height="100%" viewBox="0 0 1200 60" preserveAspectRatio="none">
				    <path d="M0,56.5c0,0,298.666,0,399.333,0C448.336,56.5,513.994,46,597,46c77.327,0,135,10.5,200.999,10.5c95.996,0,402.001,0,402.001,0"/>
				</svg>
        </span>
        
        <button class="cbutton cbutton--effect-radomir md-trigger" type="submit" value="Call" style="margin-top: 40px; margin-left:-10px" data-modal="modal-13">
            <i class="cbutton__icon fa fa-fw fa fa fa-phone-square"></i>
        </button>

        
	</form>
    <button class="cbutton cbutton--effect-radomir md-trigger" value="Call" hidden="true" data-modal="modal-13" id="showModal"></button>
	<div class="ptext">
        <p>To Use:</p>
        <p>Enter a username and click Log in button. If input turns green you are ready to receive/place a call.</p>
        <p>In a separate browser window, log in with a different username, and place a call to the first.</p>
    </div>

     
</div>
</body>

<!--
     jQuery dependency disabled temporarily until this source file become
     a part of full-featured plugin to make page convinient with WordPress themes
  -->
<!--<script src="js/jquery-2.1.3.min.js"></script>-->
<script src="js/webrtccore.js"></script>
<script src="js/webrtc.js"></script>
<script type="text/javascript">

var video_out = document.getElementById("vid-box");

function readycb(){
  form.username.style.background="#55ff5b";
  form.login_submit.hidden="true";
}

function login(form) {
	var phone = window.phone = PHONE({
	    number        : form.username.value || "Anonymous", // listen on username line else Anonymous
	    publish_key   : 'pub', // Your Pub Key
	    subscribe_key : 'sub', // Your Sub Key
	});	
	phone.ready(readycb);
	phone.receive(function(session){
	    session.connected(function(session) { video_out.appendChild(session.video); showModal();});
	    session.ended(function(session) { video_out.innerHTML=''; });
	});
	return false;
}
    
function makeCall(form){
	if (!window.phone) alert("Login First!");
	else phone.dial(form.number.value);
	return false;
}
    
function showModal(){
    $("#showModal").click();
}

function errWrap(fxn, form){
	try {
		return fxn(form);
	} catch(err) {
		alert("WebRTC is currently only supported by Chrome, Opera, and Firefox");
		return false;
	}
}

</script>
<script src="js/modalEffects.js"></script>
<script src="js/classie.js"></script>

</html>
