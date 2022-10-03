<?php
include dirname(__FILE__) .'/../top.php';
if (empty($_SESSION['tokenSessionKey'])) {
    $client = new Google_Client();
    if(!$client->isAccessTokenExpired()){
        header('Location: ' . base_url .'login.php?back=' . urlencode($CURRENT_URL));
    }
}
include dirname(__FILE__) .'/../library/blogger.php';
$file = new file();
$upload_path = dirname(__FILE__) . '/../uploads/user/';
$blogger = new blogger();

$date = date("c");

$dataContent          = new stdClass();
$dataContent->setdate = $date;        
$dataContent->editpost = false;
$dataContent->pid      = 0;
$dataContent->customcode = 'thest customcode';
$dataContent->bid     = '1070473514944208293';
$dataContent->pid     = '2509854298978133742';
$dataContent->title    = 'update post 333';        
$dataContent->images    = 'https://www.video4khmer36.com/images/subcat/2673/10593.jpg';        
$dataContent->bodytext = 'just another test. ssss';
$dataContent->label    = 'news';
$info = json_decode($_SESSION['tokenSessionKey']);
$dataContent->access_token = $info->access_token;
//post($dataContent);
$getpost               = $blogger->postToBlogger($dataContent);
var_dump($getpost->id);
function postToBlogger($dataContent='')
{
    if(!empty($dataContent->pid)) {
        $url = 'https://www.googleapis.com/blogger/v3/blogs/'.$dataContent->bid.'/posts/'.$dataContent->pid;
        $body = ' {
             "kind": "blogger#post",
             "id": "'.$dataContent->pid.'",
             "blog": {
              "id": "'.$dataContent->bid.'"
             },
             "selfLink": "https://www.googleapis.com/blogger/v3/blogs/'.$dataContent->bid.'/posts/'.$dataContent->pid.'",
             "title": "'.$dataContent->title.'",
             "content": "'.$dataContent->bodytext.'",
             "labels": ["'.$dataContent->label.'"],
             "updated": "'.$dataContent->setdate.'",
             "published": "'.$dataContent->setdate.'",
            }
';
    } else {
        $url = 'https://www.googleapis.com/blogger/v3/blogs/'.$dataContent->bid.'/posts/';
        $body = ' { 
            "kind": "blogger#post", 
            "blog": {"id": "'.$dataContent->bid.'"}, 
            "title": "'.$dataContent->title.'", 
            "content": "'.$dataContent->bodytext.'",
            "labels": ["'.$dataContent->label.'"],
        }';
    }
    $headerQuery = array();
    $headerQuery[] = 'Authorization: OAuth '.$dataContent->access_token;
    $headerQuery[] = 'Content-Length: '.strlen($body);
    $headerQuery[] = 'Content-Type: application/json';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headerQuery);
    if(empty($dataContent->pid)) {
        curl_setopt($ch, CURLOPT_POST, TRUE);
        
    } else {
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    }
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($ch, CURLINFO_HEADER_OUT, TRUE);
    $data = curl_exec($ch);
    $response = json_decode($data);
    curl_close($ch);
    if(!empty($response->id)) {
        $data = array(
            'url'=> $response->url,
            'id'=> $response->id,
        );
    } else {
        $data = $response;
    }
    return $data;
}