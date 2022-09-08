<?php
include dirname(__FILE__) .'/../top.php';
echo '1111111111';
if(!empty($_POST['posts'])) {
    $Pjson = $_POST['posts'];
    $code = '{"posts":'.$Pjson.'}';
    echo json_encode($code);
}