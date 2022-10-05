<?php
include dirname(__FILE__) .'/../top.php';
unset($_SESSION['back']);
if (empty($_SESSION['tokenSessionKey'])) {
    $client = new Google_Client();
    $client->setAccessToken($_SESSION['tokenSessionKey']);
    if($client->isAccessTokenExpired()){
        header('Location: ' . base_url .'login.php?back=' . urlencode($CURRENT_URL));
    }
}
include dirname(__FILE__) .'/../library/blogger.php';
$upload_path = dirname(__FILE__) . '/../uploads/user/';
$file_name = 'post.json';
$file = new file();
if(!empty($_GET['id'])) {
    $getPost = $file->getFileContent($upload_path.$file_name);
}
$getBlogId = $file->getBlogID();

if (empty($_GET['do'])) {
    if(!empty($_GET['id'])) {
        $getEditBlogId = $file->getBlogToEdit();
        if(!empty($getEditBlogId)) {
            $editaction = $_GET['id'];
        } else {
            $editaction = false;
        }
    } else {
        //header('Location: ' . base_url . 'blogger/index.php?m=no_id');
    }
}
$blogger = new blogger();
//$post = $blogger->MoviePost($getBlogId,$getPost);

    if (!empty($_POST['idblog'])) {
        $idblog    = @$_POST['idblog'];
        $_SESSION['id_edit']    = @$_POST['postid'];
        $idpost    = @$_POST['idpost'];
        $thumb     = @$_POST['imageid'];
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
            preg_match_all('!\d+!', $idpost[$key], $pid);
            $bidArr[] = array('bid'=> $matches[0][0],'pid'=>$pid[0][0],'status'=>0); 
        }
        $dataPost = array(
            'blogid' => $bidArr,
            'title' => $title,
            'image' => $thumb,
            'body' => $bodytext,
            'label' => $label_add,
        );
        $upload_path = dirname(__FILE__) . '/../uploads/user/';
        $file_name = 'post-action.json';
        $jsonPost = $file->json($upload_path,$file_name, $dataPost);
        header('Location: ' . base_url . 'blogger/edit.php?do=post');
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

        /*get Edit ID post*/        
        $blogEdit = dirname(__FILE__) . '/../uploads/blogger/posts/' . $_SESSION['id_edit'].'.csv';
        $getEditBlogId = $file->getFileContent($blogEdit);
        $arrSearch = array(); 
        foreach ($getEditBlogId as $values) {
            $gpid = @$values->bname;
            $arrSearch[] = array(
                'bid' =>$values->bid,
                'bname'=> $gpid
            );
        }
        /*end get Edit ID post*/
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
            date_default_timezone_set('Asia/Phnom_Penh');
            foreach ($json->blogid as $bids) {
                $i++;                
                if ( $bids->bid == $id ) {
                    /*post to Blog*/                    
                    $pid = $file->searchForId($bids->bid, $arrSearch);
                    $dataContent          = new stdClass();
                    $dataContent->customcode = '';
                    $dataContent->bid     = $bids->bid;                          
                    $dataContent->bodytext = $json->body;
                    $dataContent->label    = $json->label;
                    if(!empty($pid)) {
                        $dataContent->editpost = 1;
                        $dataContent->pid      = $pid;
                        $dataContent->title    = $json->title;
                        date_default_timezone_set('Asia/Phnom_Penh');
                        $date = date("c");
                        $dataContent->setdate = $date; 
                    } else {
                        $pid1 = $blogger->searchPost($json->uniq_id,$bids->bid);
                        if(!empty($pid1['pid'])) {
                            $dataContent->editpost = 1;
                            $dataContent->pid      = $pid1['pid'];
                            $dataContent->title    = $json->title;
                            date_default_timezone_set('Asia/Phnom_Penh');
                            $date = date("c");
                            $dataContent->setdate = $date; 
                        } else {
                            $str = time();
                            $str = md5($str);
                            $uniq_id = substr($str, 0, 9);
                            $dataContent->title    = $json->title . ' id ' . $uniq_id;  
                            $dataContent->editpost = false;
                            $dataContent->pid      = '';
                            $dataContent->setdate = false; 
                        }
                        
                    }
                    //$getpost = $blogger->blogger_post($client,$dataContent);
                    $info = json_decode($_SESSION['tokenSessionKey']);
                    $dataContent->access_token = $info->access_token;
                    $postobj = $blogger->postToBlogger($dataContent);
                    $getpost = @$postobj->id;
                    /*End post to Blog*/
                    $bidArr[] = array('bid'=> $bids->bid,'pid'=>$getpost,'status'=>1); 
                    $posted = array_push($countPosted, $bids->bid);
                } else {
                    $bidArr[] = array('bid'=> $bids->bid,'pid'=> $bids->pid,'status'=>$bids->status);
                    if($bids->status == 0) {        
                        $postNext = $bids->bid;
                        array_push($countPosted, $bids->bid);
                    }                    
                }
            }         
            $dataPost = array(
                'blogid' => $bidArr,
                'title' => $json->title,
                'image' => $json->image,
                'body' => $json->body,
                'label' => $json->label,
                'uniq_id' => @$json->uniq_id,
            );
            $upload_path = dirname(__FILE__) . '/../uploads/user/';
            $file_name = 'post-action.json';
            $jsonPost = $file->json($upload_path,$file_name, $dataPost);
            if(!empty($postNext)) {               
                echo '<script type="text/javascript">window.location = "' . base_url . 'blogger/edit.php?do=post&id=' . $postNext . '";</script>';
            }
            if(1 == count($countPosted)) {
                if(!empty($_SESSION['back'])) {
                    echo '<script type="text/javascript">window.location = "' . $_SESSION['back'] . '";</script>';
                    die;
                } else {
                    echo '<script type="text/javascript">window.location = "' . base_url . 'blogger/index.php";</script>';
                    die;
                    //header('Location: ' . base_url . 'blogger/index.php');
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
        die;
    }
function searchForId($id, $array) {
   foreach ($array as $key => $val) {
    $pos = strpos($val['bid'], $id);
    if ($pos === false) {
    } else {
        return @$val['bname'];
    }
   }
   return null;
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
    header('Location: ' . base_url . 'blogger/edit.php?do=post&id='.$bid);
} else if(!empty($_GET['id']) && !empty($_GET['do']) && $_GET['do'] == 'post') {
    $postToShare = doPost();
} 


if (!empty($_POST['idpost']) && !empty($_POST['keyword'])) {
    /*creat for default blog*/
    $uploadPath = dirname(__FILE__) . '/../uploads/blogger/posts/';
    $handle = fopen($uploadPath.$_POST['idpost'].'.csv', 'a');
    fputcsv($handle, array(default_blog,$_POST['idpost']));
    fclose($handle);
    /*End creat for default blog*/
    $keyword = urlencode($_POST['keyword']);
    header("Location: " . base_url . "blogger/search.php?start=1&keyword=" . $keyword . "&frompost=".$_POST['idpost']);
    exit();
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
                        <?php if(!empty($editaction)):?>
                        <div class="form-group">
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-2">
                                        <label for="imageid">Title</label>
                                    </div>
                                    <div class="col-md-10">
                                        <input type="hidden" class="form-control" name="postid" id="imageid" value="<?php echo @$_GET['id'];?>" />
                                        <input type="hidden" class="form-control" name="uniq_id" id="uniq_id" value="<?php echo @$_GET['id'];?>" />
                                        <input type="text" class="form-control" name="title" id="title" value="<?php echo @$getPost->title?> || part <?php echo count($getPost->list);?>" />
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
                                        if (!empty($getEditBlogId)):
                                            $i = 0;
                                            $arrSearch = array(); 
                                            foreach ($getBlogId as $values) {
                                                $arrSearch[] = array(
                                                    'bid' =>$values->bid,
                                                    'bname'=>$values->bname)
                                                ;
                                            }
                                            foreach ($getEditBlogId as $value):

                                                if (!empty($value) && empty(($value->bid == 'status' || $value->bid == 'url'))):
                                             ?>
                                                <label class="checkbox"><input type="checkbox" value="<?php echo trim($value->bid);?>" name="idblog[]" checked/> <?php echo searchForId($value->bid, $arrSearch);?></label>
                                                <input type="hidden" value="<?php echo trim($value->bname);?>" name="idpost[]"/>
                                                <?php  
                                                endif;
                                                $i++;
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
                        <?php else:?>

                        <!-- if not found in directory -->
                        <form method="post" id="validate" class="form-horizontal row-border">
                            <div class="form-group">
                                <div class="col-md-2">
                                    <label for="imageid">Fill the keyword</label>
                                </div>
                                <div class="col-md-10">
                                    <input type="text" class="form-control" name="keyword" placeholder="Please file the keyword" required />
                                     <input type="hidden" value="<?php echo $_GET['id'];?>" name="idpost"/>
                                </div>
                            </div>
                            <div class="form-group fixed">
                                <div class="col-md-12">
                                    <input name="submit" type="submit" value="Public" class="btn btn-primary pull-right" />
                                </div>
                            </div>
                        </form>
                        <!-- end if not found in directory -->

                        <?php endif;?>
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