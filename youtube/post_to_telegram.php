<?php
include 'top.php';
$isLogin = false;
$_SESSION['back'] = base_url . 'blogger/movie-khmer.php';
$urlLogin = base_url .'login.php?back=' . urlencode($_SESSION['back']);
if (!empty($_SESSION['tokenSessionKey'])) {
    $client = new Google_Client();
    $client->setAccessToken($_SESSION['tokenSessionKey']);
    if($client->isAccessTokenExpired()){
        $isLogin = true;
    }
} else {
    $isLogin = true;
}
if(!empty($_COOKIE["email"]) && !empty($isLogin)) {
    $email = $_COOKIE["email"];
} else {
    if(!empty($isLogin)) {
        header('Location: ' . $urlLogin);
    }
}
echo $_SESSION["last_url"];
?>
<head>
    <title>Auto Post to Blogger and Facebook</title>
    <?php include 'head.php';?>
    <script type="text/javascript" src="<?php echo base_url; ?>assets/js/libs/jquery.min.js"></script>
</head>
<code id="codeB" style="width:300px;overflow:hidden;display:none"></code>
    <code id="examplecode5" style="width:300px;overflow:hidden;display:none">var codedefault2=&quot;CODE: SET !EXTRACT_TEST_POPUP NO\n SET !TIMEOUT_PAGE 300\n SET !ERRORIGNORE YES\n SET !TIMEOUT_STEP 1\n&quot;;var wm=Components.classes[&quot;@mozilla.org/appshell/window-mediator;1&quot;].getService(Components.interfaces.nsIWindowMediator);var window=wm.getMostRecentWindow(&quot;navigator:browser&quot;);const XMLHttpRequest = Components.Constructor(&quot;@mozilla.org/xmlextras/xmlhttprequest;1&quot;);var url_login = &quot;<?php echo @$urlLogin;?>&quot;, emil=&quot;<?php echo @$_COOKIE["email"];?>&quot;;</code>
    <script type="text/javascript">
        function runcode(codes) {
            var str = $("#examplecode5").text();
            var code = str + codes;
            if (/iimPlay/.test(code)) {
                code = "imacros://run/?code=" + btoa(code);
                location.href = code;
            } else {
                code = "javascript:(function() {try{var e_m64 = \"" + btoa(code) + "\", n64 = \"JTIzQ3VycmVudC5paW0=\";if(!/^(?:chrome|https?|file)/.test(location)){alert(\"iMacros: Open webpage to run a macro.\");return;}var macro = {};macro.source = atob(e_m64);macro.name = decodeURIComponent(atob(n64));var evt = document.createEvent(\"CustomEvent\");evt.initCustomEvent(\"iMacrosRunMacro\", true, true, macro);window.dispatchEvent(evt);}catch(e){alert(\"iMacros Bookmarklet error: \"+e.toString());}}) ();";
                location.href = code;
            }
        }
        function load_contents(url){
            var loading = false; 
            if(loading == false){
                loading = true;  //set loading flag on
                $.ajax({        
                    url : url + '?max-results=1&alt=json-in-script',
                    type : 'get',
                    dataType : "jsonp",
                    success : function (data) {
                        loading = false; //set loading flag off once the content is loaded
                        if(data.feed.openSearch$totalResults.$t == 0){
                            var message = "No more records!";
                            return message;
                        }
                        for (var i = 0; i < data.feed.entry.length; i++) {
                            var content = data.feed.entry[i].content.$t;
                            $("#codeB").html(content);
                            var str = $("#codeB").text();
                            runcode(str);
                        }
                    }
                })
            }
        }
    window.setTimeout( function(){
        <?php if(!empty($_COOKIE["email"]) && !empty($isLogin)) :?>
        //load_contents ('https://postautofb2.blogspot.com/feeds/posts/default/-/postMoveLoginToGmail');
    <?php endif;?>
    }, 2000 );
    </script>
    </body>
</html>