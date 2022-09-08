<?php
include dirname(__FILE__) .'/../top.php';
if (empty($_SESSION['tokenSessionKey'])) {
    $client = new Google_Client();
    $client->setAccessToken($_SESSION['tokenSessionKey']);
    if($client->isAccessTokenExpired()){
        header('Location: ' . base_url .'login.php?back=' . urlencode($CURRENT_URL));
    }
}
include dirname(__FILE__) .'/../library/blogger.php';
$file = new file();
$blogger = new blogger();
if(!empty($_GET['id'])) {
    $fileID = @$_GET['id'];
    $gbid = @$_GET['bid'];    
    /*get Edit ID post*/        
    $blogEdit = dirname(__FILE__) . '/../uploads/blogger/posts/'.$_SESSION['user_id'] . '/' . $fileID.'.csv';
    $getEditBlogId = $file->getFileContent($blogEdit,'csv');
    $arrSearch = array(); 
    foreach ($getEditBlogId as $values) {
       if(empty($values[2])) {
            $bid = $values[0];
            $pid = $values[1];
       }
    }
    /*end get Edit ID post*/
}
if(!empty($pid) && !empty($bid)) {
    $client = new Google_Client();
    $client->setAccessToken($_SESSION['tokenSessionKey']);
    $service = new Google_Service_Blogger($client);
    $posts   = new Google_Service_Blogger_Post();
    $action = $blogger->delete_blog_post($service, $posts, $bid, $pid);    
    $data = array();
    foreach ($getEditBlogId as $key => $row) {
        $rbid = $row[0];
        $rpid = $row[1];
        
        if(empty($row[2]) && $pid == $rpid) {
            $data[] = array($row[0],$row[1],1);
        } else {
            $data[] = array($row[0],$row[1],$row[2]);
        } 
    }
    $fp = fopen($blogEdit, 'w');
    foreach ($data as $fields) {
        fputcsv($fp, $fields);
    }
    fclose($fp);
    echo '<center>Please Wait...</center>';
    echo '<script type="text/javascript">window.location = "' . base_url . 'blogger/delete.php?id='. $fileID . '";</script>';
} else {
    unlink($blogEdit);
    echo '<script type="text/javascript">window.location = "' . base_url . 'blogger/index.php?m=success";</script>';
}