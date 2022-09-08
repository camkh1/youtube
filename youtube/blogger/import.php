<?php
include dirname(__FILE__) .'/../top.php';
if (empty($_SESSION['tokenSessionKey'])) {
    $client = new Google_Client();
    
    if(!$client->isAccessTokenExpired()){
        header('Location: ' . base_url .'login.php?back=' . urlencode($CURRENT_URL));
    }
} else {
    $client = new Google_Client();
    $client->setAccessToken($_SESSION['tokenSessionKey']);
    if($client->isAccessTokenExpired()){
        header('Location: ' . base_url .'login.php?back=' . urlencode($CURRENT_URL));
    }
}
include dirname(__FILE__) .'/../library/blogger.php';
$upload_path = dirname(__FILE__) . '/../uploads/user/'.$_SESSION['user_id'] . '/';
$file_name = 'post.json';
$file = new file();
$getPost = $file->getFileContent($upload_path.$file_name);
$jsonTxt = dirname(__FILE__) . '/../uploads/files/blogs/blogid.csv';
$getBlogId = $file->getFileContent($jsonTxt);

$blogger = new blogger();
//$post = $blogger->MoviePost($getBlogId,$getPost);

    if (!empty($_GET['item'])) {
        $_SESSION['get_next'] = $_GET['page'] - 1;
        $upload_path = dirname(__FILE__) . '/../uploads/posts/'.$_SESSION['user_id'] . '/'.$_SESSION['fsite'] .'/';
        $files1 = scandir($upload_path);
        $lists = getFile($files1,$_GET['item']);
        $file_name = $lists;
        $file = new file();
        $getPost = $file->getFileContent($upload_path.$file_name);
//     $b_idArr = getbid($getBlogId);

        if(preg_match('/Continue/', $getPost->label)) {
            $vdoStatus = '';
        } else {
            $vdoStatus = ' End';
        }
        $thumb     = $blogger->resize_image($getPost->image->url, 0);
        $videotype = @$_POST['videotype'];
        $title     = @$getPost->title . ' id ' . $_GET['item'] . ' || ' . count($getPost->list->list) . $vdoStatus;
        $bodytext     = $blogger->getPlaylist($getPost->list->list,$getPost->title, $getPost->image->url);
        $label_add      = @$getPost->label;
        $label_add      = str_replace("'", '’', $label_add);
        $label_add      = addslashes($label_add);

        $breaks          = array("\r\n", "\n", "\r");
        $bodytext_normal = str_replace($breaks, "", $bodytext);
        $addOnBody       = '<a href="' . $thumb . '" target="_blank"><img border="0" id="noi" src="' . $thumb . '" /></a><!--more--><meta property="og:image" content="' . $thumb . '"/><link href="' . $thumb . '" rel="image_src"/>';
        $bodytext        = str_replace($breaks, "", $bodytext);
        $bodytext_normal = $bodytext;
        $bodytext = $addOnBody . $bodytext;

        $bidArr = [];
        foreach ($getBlogId as $key => $bids) {
            preg_match_all('!\d+!', $bids->bid, $matches);
            $bidArr[] = array('bid'=> $matches[0][0],'status'=>0); 
        }
        $dataPost = array(
            'blogid' => $bidArr,
            'title' => $title,
            'image' => $thumb,
            'body' => $bodytext,
            'label' => $label_add,
            'list' => $getPost->list->list,
            'post_id' => $_GET['item'],
            'link' => @$getPost->link,
            'page' => @$_GET['page'],
        );
        $upload_path = dirname(__FILE__) . '/../uploads/posts/'.$_SESSION['user_id'] . '/';
        $jsonPost = $file->json($upload_path,$file_name, $dataPost);
        header('Location: ' . base_url . 'blogger/import.php?do=post&p='.$file_name);
    }
    function getFile($file,$id)
    {
        foreach ($file as $key => $value) {
            $ch = explode('_', $value);
            if($ch[0] == $id) {
                return $value;
                break;
            }
        }
    }
    function getbid($getBlogId)
    {
        $BidArr = [];
        foreach ($getBlogId as $value) {
            $BidArr[] = $value->bid;
        }
        return $BidArr;
    }
    function getPost($id='')
    {
        $file = new file();
        $blogger = new blogger();
        $client = new Google_Client();
        $client->setAccessToken($_SESSION['tokenSessionKey']);
        $upload_path = dirname(__FILE__) . '/../uploads/posts/'.$_SESSION['user_id'] . '/';
        if(!empty($_GET['p'])) {
            $_SESSION['postF'] = $_GET['p'];
        }
        $file_name = $_SESSION['postF'];
        $str = file_get_contents($upload_path.$file_name);
        $json = json_decode($str);
        $response = array();
        $posts = array();
        if(!empty($id)) { 
            /*Update*/
            $bidArr = [];
            $postNext = '';
            $i=0;
            $fileName = '';
            $totalPosts = count($json->blogid);
            $countPosted = array();
            foreach ($json->blogid as $bids) {
                $i++;                
                if ( $bids->bid == $id ) {
                    /*post to Blog*/
                    $dataContent          = new stdClass();
                    $dataContent->setdate = false;        
                    $dataContent->editpost = false;
                    $dataContent->pid      = 0;
                    $dataContent->customcode = '';
                    $dataContent->bid     = $bids->bid;
                    $dataContent->title    = $json->title;        
                    $dataContent->bodytext = $json->body;
                    $dataContent->label    = $json->label;
                    $getpost               = $blogger->blogger_post($client,$dataContent);
                    if(empty($getpost)) {
                        $urls = urlencode(base_url . 'blogger/import.php?do=post&id=' . $postNext);
                        echo '<script language="javascript" type="text/javascript">window.setTimeout( function(){window.location = "' .base_url . 'blogger/index.php&error=1&url='.$urls.'";}, 3000 );</script>';
                        die;
                    }

                    /*Create CSV file for update later*/
                    if (!file_exists(dirname(__FILE__) . '/../uploads/blogger')) {
                        mkdir(dirname(__FILE__) . '/../uploads/blogger', 0700);
                    }
                    if (!file_exists(dirname(__FILE__) . '/../uploads/blogger/posts')) {
                        mkdir(dirname(__FILE__) . '/../uploads/blogger/posts', 0700);
                    }
                    if (!file_exists(dirname(__FILE__) . '/../uploads/blogger/posts/'.$_SESSION['user_id'])) {
                        mkdir(dirname(__FILE__) . '/../uploads/blogger/posts/'.$_SESSION['user_id'], 0700);
                    }
                    $uploadPath = dirname(__FILE__) . '/../uploads/blogger/posts/'.$_SESSION['user_id'] . '/';
                    if($i==1) {
                        $handle = fopen($uploadPath.$_SESSION['postF'].'.csv', "a");
                        fputcsv($handle, array($bids->bid,$getpost));
                        fclose($handle);
                    } else {
                        $handle = fopen($uploadPath.$_SESSION['postF'].'.csv', "a");
                        fputcsv($handle, array($bids->bid,$getpost));
                        fclose($handle);
                    }
                    
                    /*end Create CSV file for update later*/
                    /*End post to Blog*/
                    $bidArr[] = array('bid'=> $bids->bid,'status'=>1); 
                    $posted = array_push($countPosted, $bids->bid);
                } else {
                    $bidArr[] = array('bid'=> $bids->bid,'status'=>$bids->status);
                    if($bids->status == 0) {                        
                        $postNext = $bids->bid;
                        array_push($countPosted, $bids->bid);
                    }                    
                }
                if ($i == $totalPosts) {
                    // $handle = fopen($uploadPath.$_SESSION['post_id'].'.csv', "a");
                    // fputcsv($handle, array('url',$_SESSION['url_id']));
                    // fclose($handle);
                }
            }         
            $dataPost = array(
                'blogid' => $bidArr,
                'title' => $json->title,
                'image' => $json->image,
                'body' => $json->body,
                'label' => $json->label,
                'list' => $json->list,
                'post_id' => $json->post_id,
                'link' => @$json->link,
                'page' => @$json->page,
            );
            $jsonPost = $file->json($upload_path,$file_name, $dataPost);
            if(!empty($postNext)) {
                echo '<script language="javascript" type="text/javascript">window.setTimeout( function(){window.location = "' . base_url . 'blogger/import.php?do=post&id=' . $postNext . '";}, 500 );</script>';
                exit();
            }
            if(1 == count($countPosted)) {
                if(preg_match('/Continue/', $json->label)) {
                    $status = 'Continue';
                } else {
                    $status = 'End';
                }
                $handle = fopen($uploadPath.$_SESSION['post_id'].'.csv', "a");
                fputcsv($handle, array('url',$_SESSION['url_id']));
                fputcsv($handle, array('status',$status));
                fclose($handle);
                if($_SESSION['get_next']>1) {
                    echo 'wait 10 minutes<br/>';
                    echo $json->title.'<br/>';
                    echo $json->post_id.'<br/>';
                    $fsite = $_SESSION['fsite'];
                    $get_next = $_SESSION['get_next'];
                     echo '<script language="javascript" type="text/javascript">window.setTimeout( function(){window.location = "' . base_url . 'blogger/'.$fsite.'.php?page='.$get_next . '";}, 3000 );</script>';
                     exit();
                    //header('Location: ' . base_url . 'blogger/'.$_SESSION['fsite'].'.php?page='.$_SESSION['get_next']);
                } else {
                    header('Location: ' . base_url . 'blogger/index.php');
                }
                
            }           
            exit();
            /*End Update*/
        } else {
            $i=0;
            foreach ($json->blogid as $key => $value) {
                $i++;
                if($value->status ==0) {
                    return $value->bid;
                }
            }
        }
    }
    function doPost() {
        if(!empty($_GET['id'])) {
            $id = $_GET['id'];
            getPost($id);
        } 
        die;
    }
if(!empty($_GET['do']) && $_GET['do'] == 'post' && empty($_GET['id'])) {
    $bid = getPost();
    header('Location: ' . base_url . 'blogger/import.php?do=post&id='.$bid);
} else if(!empty($_GET['id']) && $_GET['do'] == 'post') {
    $postToShare = doPost();
}
?>
<!doctype html>
<html>
<head>
  <?php include __DIR__.'/../head.php';?>
<title>Set and retrieve localized metadata for a channel</title>
<script type="text/javascript" src="<?php echo base_url;?>assets/plugins/datatables/jquery.dataTables.min.js"></script>
</head>
<body>
  <?php include __DIR__.'/../header.php';?>
      <div id="container">
        <div id="content">
            <div class="container">
                <?php include __DIR__.'/../leftside.php';?>
                <div class="page-header">
                    <div class="page-title">
                        <h3>Auto Post to Blogger and Facebook
                        </h3>
                    </div>
                </div>
                

                <!-- data -->
                <div class="col-md-12">
            <div class="widget box">
                <div class="widget-header">
                    <h4>
                        <i class="icon-reorder">
                        </i>
                        Add New Post
                    </h4>                     
                    <div class="toolbar no-padding">
                    </div>
                </div>
                <div class="widget-content">
                    <form method="post" id="validate" class="form-horizontal row-border">
                        <div class="form-group">
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-2">
                                        <label for="imageid">Title</label>
                                    </div>
                                    <div class="col-md-10">
                                        <input type="text" class="form-control" name="title" id="title" value="<?php echo @$getPost->title?>" />
                                    </div>                                                            
                                </div>                         
                            </div>                         
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-2">
                                        <label for="imageid">Image</label>
                                    </div>
                                    <div class="col-md-5">
                                        <input type="text" class="form-control" name="imageid" id="imageid" value="<?php echo @$getPost->image?>" />
                                    </div>
                                    <div class="col-md-5">
                                        <img src="<?php echo @$getPost->image?>" />                                     
                                    </div>                         
                                </div>                         
                            </div>                         
                        </div>
                        <div class="form-group">
                            <div class="col-md-4">
                                <div id="bloglist">
                                    <?php
                                    if (!empty($editaction)) :
                                        if (!empty($bloglist_edit)):
                                            $i = 0;
                                            foreach ($bloglist_edit as $value) :
                                                $i++;
                                                ?>
                                                <label class="checkbox"><input type="checkbox" value="<?php echo $value[Tbl_title::object_id]; ?>" name="idblog[]" checked/>( <?php echo $i; ?> ) <?php echo $value[Tbl_title::title]; ?></label>
                                                <?php
                                            endforeach;
                                        else:
                                            $i = 0;
                                            foreach ($bloglist as $value) :
                                                $i++;
                                                ?>
                                                <label class="checkbox"><input type="checkbox" value="<?php echo $value->{Tbl_title::object_id}; ?>" name="idblog[]"/><?php echo $value->{Tbl_title::title}; ?></label>
                                                <?php
                                            endforeach;
                                        endif;
                                    else:
                                        /* edit action */
                                        $i = 0;
                                        foreach ($getBlogId as $value) :
                                            $i++;
                                            ?>
                                            <label class="checkbox"><input type="checkbox" value="<?php echo trim($value->bid);?>" name="idblog[]" checked/><?php echo $value->bname; ?></label>
                                            <?php
                                        endforeach;
                                    endif;
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="row">
                                    <div class="col-md-2">
                                        <label for="imageid">Type</label>
                                    </div>
                                    <div class="col-md-10">
                                        <textarea onclick="this.focus(); this.select()" class="form-control" name="onyoutbueBody1" cols="5" rows="3"><?php
                                        echo $blogger->getPlaylist($getPost->list,$getPost->title, $getPost->image);
                                        ?></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <input id="input22" class="select2-select-02 col-md-12 full-width-fix" multiple="" data-placeholder="Type to add a Tag" type="hidden" name="labeladd" value="<?php
                                        if (!empty($getPost->label)): echo $getPost->label;
                                        endif;
                                        ?>" />
                            </div>                           
                        </div>
                        <div class="form-group fixed">
                            <div class="col-md-12">
                                <input name="submit" type="submit" value="Public" class="btn btn-primary pull-right" />
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
                <!-- End data -->
            </div>
        </div>
    </div> 
    <script type="text/javascript" src="<?php echo base_url; ?>assets/js/libs/jquery.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url; ?>assets/plugins/jquery-ui/jquery-ui-1.10.2.custom.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url; ?>bootstrap/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url; ?>assets/js/libs/lodash.compat.min.js"></script> 
    <script type="text/javascript" src="<?php echo base_url; ?>assets/plugins/typeahead/typeahead.min.js"></script>            
    <script type="text/javascript" src="<?php echo base_url; ?>assets/plugins/tagsinput/jquery.tagsinput.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url; ?>assets/plugins/select2/select2.min.js"></script>


    <script type="text/javascript">
        $( document ).ready(function() {
            $(".select2-select-02").select2({tags:[]});
        });
    </script>
</body>
</html>