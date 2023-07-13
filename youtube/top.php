<?php
$is_connected = is_connected();
if($is_connected) {
	define('is_connected', true);
} else {
	define('is_connected', false);
	echo '<meta http-equiv="refresh" content="10">';
	echo '<div style="text-align:center;color:red;">Internet Disconnect!</div>';
	die;
}
function is_connected()
{
    $connected = @fsockopen("www.google.com", 80); 
                                        //website, port  (try 80 or 443)
    if ($connected){
        $is_conn = true; //action when connected
        fclose($connected);
    }else{
        $is_conn = false; //action in connection failure
    }
    return $is_conn;

}
date_default_timezone_set('Asia/Phnom_Penh');
define("base_url", "http://localhost/youtube/youtube/");
define("default_blog", "7271011833334695575");
//define("get_post_blog", "4548242398775875765");
define("get_post_blog", "4900901770956221335");
define("get_from_feed", "https://www.video4khmer37.com/latest-new-khmer-movies-videos-1.html");
define("thai_movies", "https://www.doomovie-hd.com/?r=movie_filter_by_year&year=2023");

/*this blog are the same in https://movie-khmer.com/ */
define("blogger_feed", "https://www.blogger.com/feeds/2461095789922722435/posts/default?max-results=50");

define("wp_feed", "https://movie-khmer.com/feed/");
define('BITLY_USERNAME', 'o_19ln460ta4');
define('BITLY_API_KEY', 'R_d25cf36a6098484cbdec62de87af48e9');
//for current url
$CURRENT_URL = (!empty($_SERVER['HTTPS'])) ? "https://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] : "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
session_start();
if (!file_exists(__DIR__ . '/Google/autoload.php')) {
  throw new \Exception('please run "composer require google/apiclient:~2.0" in "' . __DIR__ .'"');
}
require_once __DIR__ . '/Google/autoload.php';
$OAUTH2_CLIENT_ID = '814595907237-kqk1qe9uc8iggm3m788k8u79056dipfh.apps.googleusercontent.com';
$OAUTH2_CLIENT_SECRET = 'sBKCkX2261txKtwilMcCSsuI';