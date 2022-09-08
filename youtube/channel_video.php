<?php
include 'top.php';
/**
 * This sample sets and retrieves localized metadata for a channel by:
 *
 * 1. Updating language of the default metadata and setting localized metadata
 *   for a channel via "channels.update" method.
 * 2. Getting the localized metadata for a channel in a selected language using the
 *   "channels.list" method and setting the "hl" parameter.
 * 3. Listing the localized metadata for a channel using "channels.list" method and
 *   including "localizations" in the "part" parameter.
 *
 * @author Ibrahim Ulukaya
 */

/**
 * Library Requirements
 *
 * 1. Install composer (https://getcomposer.org)
 * 2. On the command line, change to this directory (api-samples/php)
 * 3. Require the google/apiclient library
 *    $ composer require google/apiclient:~2.0
 */

$client = new Google_Client();
$client->setClientId($OAUTH2_CLIENT_ID);
$client->setClientSecret($OAUTH2_CLIENT_SECRET);
$client->setApplicationName("My Post from app");
$client->setAccessType("offline");
$client->addScope('https://www.googleapis.com/auth/youtube.force-ssl');
$client->addScope("https://www.googleapis.com/auth/userinfo.email");
$client->addScope("https://picasaweb.google.com/data/");
$client->addScope("https://www.googleapis.com/auth/blogger");
$client->addScope("https://www.googleapis.com/auth/drive");
$client->addScope("https://www.googleapis.com/auth/plus.me");
$client->addScope("https://www.googleapis.com/auth/plus.login");
$client->addScope("https://www.googleapis.com/auth/plus.media.upload");
$client->addScope("https://www.googleapis.com/auth/plus.stream.write");
$redirect = filter_var(base_url .'channel_video.php',
    FILTER_SANITIZE_URL);
$client->setRedirectUri($redirect);
$youtube = new Google_Service_YouTube($client);

$tokenSessionKey = 'token-' . $client->prepareScopes();
if (isset($_GET['code'])) {
  if (strval($_SESSION['state']) !== strval($_GET['state'])) {
    die('The session state did not match.');
  }

  $client->authenticate($_GET['code']);
  $_SESSION[$tokenSessionKey] = $client->getAccessToken();
  $_SESSION['tokenSessionKey'] = $_SESSION[$tokenSessionKey];

  /*get time for expire*/
  $authObj = json_decode($client->getAccessToken());
  $expiresIn = $authObj->expires_in;
  $expiry = getDate().now + $expiresIn;
  $_SESSION['expires_in'] = $expiresIn;
  /*end get time for expire*/

  header('Location: ' . $redirect);
}

if (isset($_SESSION[$tokenSessionKey])) {
  $client->setAccessToken($_SESSION[$tokenSessionKey]);
}



if ($client->getAccessToken()) {

$channelId = !empty($_SESSION['ch'])? $_SESSION['ch'] : "UCf5KJK8Kl2BmPGwIATXSqGg";
//get the channel id for the username
try {

    // Call the search.list method to retrieve results matching the specified
    // query term.
    //$channelsResponse = channelsListById($youtube,'snippet,contentDetails', array('id' => 'UCZOADi6O8-iMe8EYadWwh_w'));
    $channelsResponse = $youtube->channels->listChannels('contentDetails', array(
      'id' => $channelId,
    ));
    $htmlBody = '';
    $setIDpost = [];
    foreach ($channelsResponse['items'] as $channel) {
      // Extract the unique playlist ID that identifies the list of videos
      // uploaded to the channel, and then call the playlistItems.list method
      // to retrieve that list.
      $uploadsListId = $channel['contentDetails']['relatedPlaylists']['uploads'];

      $playlistItemsResponse = $youtube->playlistItems->listPlaylistItems('snippet', array(
        'playlistId' => $uploadsListId,
        'maxResults' => 10
      ));


      $htmlBody .= "<h3>Videos in list $uploadsListId</h3><ul>";
      $service = new Google_Service_Blogger($client);
      $posts   = new Google_Service_Blogger_Post();

      $bid = !empty($_SESSION['blogID'])? $_SESSION['blogID'] : "7365436773131480017";
      $checkPost = checkDuplicate($bid);
      foreach ($playlistItemsResponse['items'] as $playlistItem) {      	
      	$vid = $playlistItem['snippet']['resourceId']['videoId'];
      	$vPublishedAt = $playlistItem['snippet']['publishedAt'];
      	$strTime = strtotime($vPublishedAt);
      	$vTitle = $playlistItem['snippet']['title'] . ' || ' .$strTime;
      	$vDescription = $playlistItem['snippet']['description'];
      	
      	array_push($setIDpost, $strTime);

        $checkArr = array_search($strTime, $checkPost);

        if(empty($checkArr)) {
        	/*upload photo*/
			$imgUrl ='https://i.ytimg.com/vi/'.$vid.'/hqdefault.jpg';
			$file_title = basename( $imgUrl);
			$fileName = 'uploads/'.$file_title;
			copy($imgUrl, $fileName);
			$uploadMediaFile = uploadMedia($client,$fileName);
			/*End upload photo*/

	      	$bodytext = '<img class="thumbnail noi" style="text-align:center" src="'.$uploadMediaFile.'"/><div id="someAdsA"></div><iframe width="100%" height="280" src="https://www.youtube.com/embed/'.$vid.'" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe><div id="someAds"></div>'. $vDescription;
	      	$dataContent          = new stdClass();
	        $dataContent->setdate = false;        
	      	$dataContent->editpost = false;
	        $dataContent->pid      = 0;
	        $dataContent->customcode = '';
	        $dataContent->bid     = $bid;
	        $dataContent->title    = $vTitle;        
	        $dataContent->bodytext = $bodytext;
	        $dataContent->label    = 'lotta';
        	$getpost               = blogger_post($client,$dataContent);
        }
        //$getpost               = blogger_post($client,$dataContent);
        //$htmlBody .= sprintf('<li>%s (%s)</li>', $playlistItem['snippet']['title'],$playlistItem['snippet']['resourceId']['videoId'] .' : date:'. $playlistItem['snippet']['publishedAt']);
      }	           
    }
    header('Location: '.base_url.'share.php?id='. implode(",",$setIDpost).'&do=share');
    die();
  } catch (Google_Service_Exception $e) {
  	$htmlBody = '';
    $htmlBody .= sprintf('<p>A service error occurred: <code>%s</code></p>',
      htmlspecialchars($e->getMessage()));
  } catch (Google_Exception $e) {
    // $htmlBody = '';
    // $htmlBody .= sprintf('<p>An client error occurred: <code>%s</code></p>',
    //   htmlspecialchars($e->getMessage()));


      $state = mt_rand();
  $client->setState($state);
  $_SESSION['state'] = $state;

  $authUrl = $client->createAuthUrl();
  $htmlBody = <<<END
  <h3>Authorization Required</h3>
  <p>You need to <a href="$authUrl" id="login">authorize access</a> before proceeding.<p>
END;
  $htmlBody .= sprintf('<p>An client error occurred: <code>%s</code></p>',
  htmlspecialchars($e->getMessage()));  



  }




$htmlBody =$htmlBody;
} else {
  // If the user hasn't authorized the app, initiate the OAuth flow
  $state = mt_rand();
  $client->setState($state);
  $_SESSION['state'] = $state;

  $authUrl = $client->createAuthUrl();
  $htmlBody = <<<END
  <h3>Authorization Required</h3>
  <p>You need to <a href="$authUrl" id="login">authorize access</a> before proceeding.<p>
END;
}


function channelsListById($service, $part, $params) {
    $params = array_filter($params);
    $response = $service->channels->listChannels(
        $part,
        $params
    );

    print_r($response);
}

    function blogger_post($client,$data)
    {
        date_default_timezone_set('Asia/Phnom_Penh');
        try {
            $service = new Google_Service_Blogger($client);
            $posts   = new Google_Service_Blogger_Post();
            $posts->setTitle($data->title);
            $str = stripslashes($data->bodytext);
            $str = str_replace("<br />", "\n", $str);
            $posts->setContent($str);
            //$label = array('khmer1','test1');
            $posts->setLabels(array($data->label));

            /*set date*/
            if (empty($data->setdate)) {
                $date = date("c");
                $posts->setUpdated($date);
                $posts->setPublished($date);
            } else if ($data->setdate) {
                $dateset = $data->setdate;
                $date    = $dateset . 'T';
                $date .= date("H:i:s");
                $posts->setUpdated($date);
                $posts->getPublished($date);
            }
            /*end set date*/

            /*set customcode*/
            if (!empty($data->customcode)) {
                $Location = new Google_Service_Blogger_PostLocation();
                $Location->setName($data->customcode);
                $Location->setLat('37.16031654673677');
                $Location->setLng('-108.984375');
                $Location->setSpan('51.044069,82.617188');
                $posts->setLocation($Location);
            }
            /*end set customcode*/

            /*add post*/
            if (empty($data->editpost)) {
                $getpost = $service->posts->insert($data->bid, $posts);
                $pid     = $getpost->id;
            } else {
                $setUpdateBlogSms[] = $service->posts->update($data->bid, $data->pid, $posts);
                $pid                = $data->pid;
            }
            return $pid;
            die;
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
            return false;
            //echo $exc->getTraceAsString();
        }
    }

    /* login to google account*/
    function requestLoginGoogleAccount()
    {
        try {
            $access_tokenArr = $_SESSION[$tokenSessionKey];
            if(!empty($access_tokenArr)) {
                $client = new Google_Client();
                if(!$client->isAccessTokenExpired()){
                    //$this->mod_general->getRefresh($client);
                }
                $client->setAccessToken($access_tokenArr);
                return array('error'=>false,'client'=> $client);
            } else {
                return array('error'=>true);
            }
        } catch (Exception $e) {
            return array('error'=>$e);
        }
    }

    function checkDuplicate($bid)
       {           
       	$link_blog = 'https://www.blogger.com/feeds/'.$bid.'/posts/summary?max-results=10';
        $html = simplexml_load_file($link_blog);
        $title = [];
        $i=1;
        foreach ($html->entry as $value) {
        	$tCheck = explode('||', $value->title);
			if(!empty($tCheck[1])) {
				$title[$i] = $tCheck[1];
			}
			$i++;
        }
        return $title;
       }

function uploadMedia($client,$file_path)
   {
   	$imgName = __DIR__ . '/'.$file_path;
   	$authToken = $_SESSION['tokenSessionKey'];
	$client_id = '51d22a7e4b628e4';

	$filetype = mime_content_type($file_path);
	/*resize image*/
	$maxDim = 1200;
	$file_name = $imgName;
	list($width, $height, $type, $attr) = getimagesize( $file_name );
    if ( $width < $maxDim || $height < $maxDim ) {
        $target_filename = $file_name;
        $ratio = $width/$height;
        if( $ratio > 1) {
            $new_width = $maxDim;
            $new_height = $maxDim/$ratio;
        } else {
            $new_width = $maxDim*$ratio;
            $new_height = $maxDim;
        }
        $src = imagecreatefromstring( file_get_contents( $file_name ) );
        $dst = imagecreatetruecolor( $new_width, $new_height );
        imagecopyresampled( $dst, $src, 0, 0, 0, 0, $new_width, $new_height, $width, $height );
        imagedestroy( $src );
        imagejpeg( $dst, $target_filename ); // adjust format as needed
        imagedestroy( $dst );
    }
    /*end resize image*/
    /*upload to imgur.com*/
	$image = file_get_contents($imgName);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, 'https://api.imgur.com/3/image.json');
	curl_setopt($ch, CURLOPT_POST, TRUE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array( "Authorization: Client-ID $client_id" ));
	curl_setopt($ch, CURLOPT_POSTFIELDS, array( 'image' => base64_encode($image) ));
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	$reply = curl_exec($ch);
	curl_close($ch);
	$reply = json_decode($reply);
	if(!empty($reply->data->link)) {
		return $reply->data->link;
	} else {
		return false;
	}
	/*End upload*/

 //   	$service = new Google_Service_PlusDomains($client);
 //   	$file = new Google_Service_PlusDomains_Media();
 //   	$file_mime = mime_content_type($file_path);	
 //   	$file_title = basename( $file_path);
	// $file->setDisplayName($file_title);
	// $service->media->insert('me','cloud',$file, array(
	//   'data' => file_get_contents($file_path),
	//   'mimeType' => $file_mime,
	//   'uploadType' => 'multipart'
	// ));
   }   
?>

<!doctype html>
<html>
<head>
  <?php include 'head.php';?>
<title>Set and retrieve localized metadata for a channel</title>
</head>
<body>
  <?php include 'header.php';?>
      <div id="container">
        <div id="content">
            <div class="container">
                <?php include 'leftside.php';?>
                <div class="page-header">
                    <div class="page-title">
                        <h3>Auto Post to Blogger and Facebook
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
                              <?php echo $htmlBody;?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>  
</body>
</html>