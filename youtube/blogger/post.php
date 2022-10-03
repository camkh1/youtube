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
if(empty($_GET['do']) && empty($_GET['id'])) {
    $file_name = 'post.json';
    $getPost = $file->getFileContent($upload_path.$file_name);
}
$jsonTxt = dirname(__FILE__) . '/../uploads/files/blogs/blogid.csv';
$getBlogId = $file->getFileContent($jsonTxt);
unset($_SESSION['back']);
$blogger = new blogger();
//$post = $blogger->MoviePost($getBlogId,$getPost);

    if (!empty($_POST['submit'])) {
        $idblog    = @$_POST['idblog'];
        $thumb     = @$_POST['imageid'];
        $uniq_id     = @$_POST['pid'];
        $videotype = @$_POST['videotype'];
        $title     = @$_POST['title'];
        $bodytext     = @$_POST['onyoutbueBody1'];
        $label_add      = @$_POST['labeladd'];
        $label_add      = str_replace("'", '’', $label_add);
        $label_add      = addslashes($label_add);

        $breaks          = array("\r\n", "\n", "\r");
        $bodytext_normal = str_replace($breaks, "", $bodytext);
        $addOnBody       = '<a href="' . $thumb . '" target="_blank"><img border="0" id="noi" src="' . $thumb . '" /></a><!--more--><meta property="og:image" content="' . $thumb . '"/><link href="' . $thumb . '" rel="image_src"/>';
        $bodytext        = str_replace($breaks, "", $bodytext);
        $bodytext_normal = $bodytext;
        $bodytext = $addOnBody . $bodytext;

        $bidArr = [];
        foreach ($idblog as $key => $bids) {
            preg_match_all('!\d+!', $bids, $matches);
            $bidArr[] = array('bid'=> $matches[0][0],'status'=>0); 
        }
        $dataPost = array(
            'blogid' => $bidArr,
            'title' => $title,
            'image' => $thumb,
            'body' => $bodytext,
            'label' => $label_add,
            'uniq_id' => $uniq_id,
        );
        $upload_path = dirname(__FILE__) . '/../uploads/user/';
        $file_name = 'post-action.json';
        $jsonPost = $file->json($upload_path,$file_name, $dataPost);
        header('Location: ' . base_url . 'blogger/post.php?do=post');
    }
    function getPost($id='')
    {
        $file = new file();
        $blogger = new blogger();
        $client = new Google_Client();
        $client->setAccessToken($_SESSION['tokenSessionKey']);
        $upload_path = dirname(__FILE__) . '/../uploads/user/';
        $file_name = 'post-action.json';
        $str = file_get_contents($upload_path.$file_name);
        $json = json_decode($str);
        $response = array();
        $posts = array();
        $g_bid = @$_GET['pid'];
        if(!empty($id)) { 
            /*Update*/
            $bidArr = [];
            $postNext = '';
            $i=0;
            $fileName = '';
            $totalPosts = count($json->blogid);
            $countPosted = array();
            date_default_timezone_set('Asia/Phnom_Penh');
            foreach ($json->blogid as $bids) {
                $i++;           
                if ( $bids->bid == $id ) {
                    /*post to Blog*/
                    $dataContent          = new stdClass();
                    $date = date("c");
                    $dataContent->setdate = $date;      
                    $dataContent->editpost = false;
                    $dataContent->pid      = 0;
                    $dataContent->customcode = '';
                    $dataContent->bid     = $bids->bid;
                    $dataContent->title    = $json->title;        
                    $dataContent->bodytext = $json->body;
                    $dataContent->label    = $json->label;
                    $info = json_decode($_SESSION['tokenSessionKey']);
                    $dataContent->access_token = $info->access_token;
                    //$getpost               = $blogger->blogger_post($client,$dataContent);
                    $postobj = $blogger->postToBlogger($dataContent);
                    $getpost = $postobj->id;
                    /*Create CSV file for update later*/
                    if (!file_exists(dirname(__FILE__) . '/../uploads/blogger')) {
                        mkdir(dirname(__FILE__) . '/../uploads/blogger', 0700);
                    }
                    if (!file_exists(dirname(__FILE__) . '/../uploads/blogger/posts')) {
                        mkdir(dirname(__FILE__) . '/../uploads/blogger/posts', 0700);
                    }
                    $uploadPath = dirname(__FILE__) . '/../uploads/blogger/posts/';
                    if(empty($g_bid)) {
                        if(!empty($vdoInfo->uniq_id)) {
                            $getp_id = $getpost;
                            $_SESSION['post_id'] = $vdoInfo->uniq_id;
                            $handle = fopen($uploadPath.$getpost.'.csv', "w");
                            fputcsv($handle, array($bids->bid,$getpost));
                            fclose($handle);

                            /*update file*/
                            $vdoInfo = $file->getFileContent($_SESSION['postFile'],'json');
                            $post_data = array(
                                'title'     => $vdoInfo->title,
                                'type'     => 'vdolist',
                                'object_id' => $vdoInfo->object_id,
                                'pid' => $vdoInfo->pid,
                                'image' => array(
                                    'url'=>@$vdoInfo->image->url,
                                    'upload_status'=>@$vdoInfo->image->upload_status
                                ),
                                'label'     => $vdoInfo->label,
                                'list'     => $vdoInfo->list,
                                'file_name'     => $vdoInfo->file_name,
                                'link'     => $vdoInfo->link,
                                'bid'     => $vdoInfo->uniq_id,
                            );
                            $upload_path = dirname(__FILE__) . '/../uploads/posts/' .$_SESSION['fsite'].'/';
                            $csv = $file->json($upload_path,$vdoInfo->file_name, $post_data,'update');
                            /*End update file*/
                        }
                    } else {
                        $getp_id = $g_bid;
                        $handle = fopen($uploadPath.$_SESSION['post_id'].'.csv', "a");
                        fputcsv($handle, array($bids->bid,$_SESSION['post_id']));
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
                'uniq_id' => $json->uniq_id,
            );
            $upload_path = dirname(__FILE__) . '/../uploads/user/';
            $file_name = 'post-action.json';
            $jsonPost = $file->json($upload_path,$file_name, $dataPost);
            if(!empty($postNext)) {
                // if(count($countPosted) % 5 == 0 && count($countPosted)>0){

                // } else {

                // }   
                $back = urlencode(base_url . 'blogger/post.php?do=post&id=' . $postNext . '&pid='.$_SESSION['post_id']);
                echo '<script language="javascript" type="text/javascript">window.setTimeout( function(){window.location = "'.base_url . 'login.php?renew=1&back='.$back.'";}, 1000*3 );</script>';
                exit();
                // echo '<script type="text/javascript">window.setTimeout( function(){window.location = "' . base_url . 'blogger/post.php?do=post&id=' . $postNext . '&pid='.$_SESSION['post_id'].'";}, 1000*5 );</script>';
                // exit();
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
                //header('Location: ' . base_url . 'blogger/index.php');
                echo '<script type="text/javascript">window.location = "' . base_url . 'blogger/index.php";</script>';
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
    header('Location: ' . base_url . 'blogger/post.php?do=post&id='.$bid);
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
                                        <input type="hidden" class="form-control" name="pid" id="pid" value="<?php echo @$getPost->pid?>" />
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