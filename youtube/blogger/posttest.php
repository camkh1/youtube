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
$dataContent->titleLink = 'test titleLink';
$getpost = postToBlogger($dataContent);
//$getpost               = $blogger->postToBlogger($dataContent);
var_dump($getpost);
function postToBlogger($dataContent='')
{
    $str = stripslashes($dataContent->bodytext);
    $str = str_replace("<br />", "\n", $str);
    if(!empty($dataContent->pid)) {
        $url = 'https://www.googleapis.com/blogger/v3/blogs/'.$dataContent->bid.'/posts/'.$dataContent->pid;
    } else {
        $url = 'https://www.googleapis.com/blogger/v3/blogs/'.$dataContent->bid.'/posts/';
    }
    $dataBody         = new stdClass();
    $dataBody->kind = "blogger#post";
    if(!empty($dataContent->pid)) {
        $dataBody->id = $dataContent->pid;
        $dataBody->selfLink = 'https://www.googleapis.com/blogger/v3/blogs/'.$dataContent->bid.'/posts/'.$dataContent->pid;
    }
    $dataBody->blog = array('id'=>$dataContent->bid);
    $dataBody->title = $dataContent->title;
    $dataBody->content = $str;
    if(!empty($dataContent->setdate)) {
        $dataBody->updated = $dataContent->setdate;
        $dataBody->published = $dataContent->setdate;
    }
    if(!empty($dataContent->customcode)) {
        $dataBody->location = array(
            'name'=>$dataContent->customcode,
            'lat'=>'37.16031654673677',
            'lng'=>'-108.984375',
            'span'=>'51.044069,82.617188',
        );
    }
    $dataBody->labels = [$dataContent->label];
    $limon_arr = new stdClass();
    $limon_arr->url = 'http://title.com';
    $limon_arr->rel = 'enclosure';
    $limon_arr->type ='mp3';
    $limon_arr->length = '0';
    $limonArray[] = $limon_arr;
    $dataBody->link = $limonArray;
    $body = json_encode($dataBody);
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