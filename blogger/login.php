<?php
include dirname(__FILE__) .'/top.php';
$client = new Google_Client();
$client->setClientId($OAUTH2_CLIENT_ID);
$client->setClientSecret($OAUTH2_CLIENT_SECRET);
$client->setApplicationName("web apllication");
$client->setAccessType("offline");
$client->setIncludeGrantedScopes(true);
$client->addScope('https://www.googleapis.com/auth/youtube.force-ssl');
$client->addScope("https://www.googleapis.com/auth/userinfo.email");
$client->addScope("https://www.googleapis.com/auth/userinfo.profile");
$client->addScope("https://picasaweb.google.com/data/");
$client->addScope("https://www.googleapis.com/auth/blogger");
$client->addScope("https://www.googleapis.com/auth/drive");
$client->addScope("https://www.googleapis.com/auth/plus.me");
$client->addScope("https://www.googleapis.com/auth/plus.login");
$client->addScope("https://www.googleapis.com/auth/plus.media.upload");
$client->addScope("https://www.googleapis.com/auth/plus.stream.write");
$redirect = filter_var(base_url .'login.php',
    FILTER_SANITIZE_URL);
$client->setRedirectUri($redirect);

$tokenSessionKey = 'token-' . $client->prepareScopes();
if (isset($_GET['code'])) {
  // if (strval($_SESSION['state']) !== strval($_GET['state'])) {
  //   die('The session state did not match.');
  // }

  $client->authenticate($_GET['code']);
  $_SESSION[$tokenSessionKey] = $client->getAccessToken();
  $_SESSION['tokenSessionKey'] = $_SESSION[$tokenSessionKey];

  /*get time for expire*/
  $authObj = json_decode($client->getAccessToken());
  $expiresIn = $authObj->expires_in;
  $expiry = strtotime("now") + $expiresIn;
  $_SESSION['expires_in'] = $expiresIn;
  /*end get time for expire*/

  if (isset($_SESSION[$tokenSessionKey])) {
    $client->setAccessToken($_SESSION[$tokenSessionKey]);
    $ticket = $client->verifyIdToken();
    if ($ticket) {
      $data = $ticket->getAttributes();
      $_SESSION['user_id'] = $data['payload']['sub'];
    }
  }
  if(!empty($_GET['back'])) {
    $_SESSION['back'] = $_GET['back'];
  } 

  if(!empty($_SESSION['back'])) {
    $back = $_SESSION['back'];
    header('Location: ' . $back);
  } else {
    header('Location: ' . base_url . 'index.php');
  }
}

if (!empty($_SESSION['tokenSessionKey'])) {
  $client->setAccessToken($_SESSION['tokenSessionKey']);
}
if($client->isAccessTokenExpired()){
    $state = mt_rand();
    $client->setState($state);
    $_SESSION['state'] = $state;
    $authUrl = $client->createAuthUrl();
    header('Location: ' . $authUrl);
} else {
    if(!empty($_GET['back'])) {
      header('Location: ' . $_GET['back']);
    } else {
       header('Location: ' . base_url . 'index.php');
    }
    
}
if(!empty($_GET['renew'])) {
    $state = mt_rand();
    $client->setState($state);
    $_SESSION['state'] = $state;
    $authUrl = $client->createAuthUrl();
    header('Location: ' . $authUrl);
}
?>
<!doctype html>
<html>

<head>
    <title>Auto Post to Blogger and Facebook</title>
    <?php include 'head.php';?>
</head>
<body>
    <?php include 'header.php';?>
    <div id="container">
        <div id="content">
            <div class="container">
                <?php include 'leftside.php';?>
                <div class="page-header">
                    <div class="page-title">
                        <h3>Authorization Required
                        </h3>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="widget box">
                            <div class="widget-header">
                                <h4><i class="icon-reorder"></i> Auto Post to Blogger and Facebook</h4>
                            </div>
                            <div class="widget-content">
                               <?php if (empty($client->getAccessToken())):
                                $state = mt_rand();
                                $client->setState($state);
                                $_SESSION['state'] = $state;
                                $authUrl = $client->createAuthUrl();
                                ?>
                                <p>You need to <a href="<?php echo $authUrl;?>" id="login">authorize access</a> before proceeding.<p>
                               <?php endif;?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <style>
        .error {color: red}
        #blockuis{padding:10px;position:fixed;z-index:99999999;background:rgba(0, 0, 0, 0.73);top:20%;left:50%;transform:translate(-50%,-50%);-webkit-transform:translate(-50%,-50%);-moz-transform:translate(-50%,-50%);-ms-transform:translate(-50%,-50%);-o-transform:translate(-50%,-50%);}
    </style>
    <div class="page-header">
    </div>  
    <div style="display:none;text-align:center;font-size:20px;color:white" id="blockuis">
        <div id="loaderimg" class=""><img align="middle" valign="middle" src="http://2.bp.blogspot.com/-_nbwr74fDyA/VaECRPkJ9HI/AAAAAAAAKdI/LBRKIEwbVUM/s1600/splash-loader.gif"></div>
        Please wait...
    </div> 
    <code id="codeB" style="width:300px;overflow:hidden;display:none"></code>
    <script type="text/javascript" src="<?php echo base_url; ?>assets/js/libs/jquery.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url; ?>plugins/jquery-ui/jquery-ui-1.10.2.custom.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url; ?>bootstrap/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url; ?>assets/js/libs/lodash.compat.min.js"></script>
</body>

</html>