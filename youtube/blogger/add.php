<?php
include dirname(__FILE__) .'/../top.php';
if (empty($_SESSION['tokenSessionKey'])) {
    header('Location: ' . base_url .'login.php?back=' . urlencode($CURRENT_URL));
}
if (!empty($_SESSION['tokenSessionKey'])) {
    $client = new Google_Client();
    $client->setAccessToken($_SESSION['tokenSessionKey']);
    if($client->isAccessTokenExpired()) {
        header('Location: ' . base_url .'login.php?renew=1&back=' . urlencode($CURRENT_URL));
    }
}
function checkDuplicate($bid,$label='',$max=3,$start = 1){
    if(!empty($label)) {
        $link_blog = 'https://www.blogger.com/feeds/'.$bid.'/posts/summary/-/'.$label.'?max-results='.$max .'&start-index='.$start.'&alt=json-in-script';
        $response = file_get_contents($link_blog);
        $response = str_replace('gdata.io.handleScriptLoaded({', '{',$response);
        $response = str_replace('}}]}});', '}}]}}',$response);
        $html = json_decode($response);
    } else {
        $link_blog = 'https://www.blogger.com/feeds/'.$bid.'/posts/summary?max-results='.$max .'&start-index='.$start.'&alt=json-in-script';
        $response = file_get_contents($link_blog);
        $response = str_replace('gdata.io.handleScriptLoaded({', '{',$response);
        $response = str_replace('}}]}});', '}}]}}',$response);
        $html = json_decode($response);        
    } 
    return $html;
}
function getBlogId()
{
   $jsonTxt = dirname(__FILE__) . '/../uploads/files/blogs/blogid.csv';
   $fp = fopen($jsonTxt,'r') or die("can't open file");
   $data = [];
    while($csv_line = fgetcsv($fp,1024)) {
        $data[] = array(
            'bid' => $csv_line['0'],
            'bname' => $csv_line['1']
        );
    }
    return (object) $data;
}
function sitekmobilemovie($param = '', $title = '', $thumb = '', $post_id = '', $videotype = '') {
}
/* form */
include dirname(__FILE__) .'/../library/blogger.php';
$site = new blogger();
if (!empty($_POST['submit'])) {
    $videotype = '';
    $id = !empty($_POST['edit_post_id']) ? $_POST['edit_post_id'] : '';
    $xmlurl    = @$_POST['blogid'];
    $thumb     = @$_POST['imageid'];
    $label = @$_POST['label'];
    $title     = @$_POST['title'];
    if (preg_match('/kmobilemovie/', $xmlurl)) {
        $xmlurl = sitekmobilemovie($xmlurl, $title, $thumb, $id, $label);
    }
    $list = $site->getfromsiteid($xmlurl, $id, $thumb, $title, $label);
    $upload_path = dirname(__FILE__) . '/../uploads/user/';
    $file_name = 'post.json';
    $file = new file();
    $csv = $file->json($upload_path,$file_name, $list);
    
    $_SESSION['url_id'] = $xmlurl;
    //$code = get_from_site_id($xmlurl, $id, $thumb, $title, '', $videotype); 
    if (!empty($_POST['edit_post_id'])) {
        //redirect(base_url() . 'post/getcode/edit/' . $id);
        header('Location: ' . base_url . '/blogger/edit.php?id='.$_POST['edit_post_id']);
    } else {
        header('Location: ' . base_url . '/blogger/post.php?do=add');
    }
}
/* end form */
$image = $site->resize_image(@$_GET['img'],0);
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
                                        <input type="text" class="form-control" name="title" id="title" value="<?php if(!empty($_GET['title'])):
                                        $title = explode('||', $_GET['title']);
                                        echo $title[0]; endif;?>" />
                                        <?php if(!empty($_GET['id'])):?>
                                        <input type="hidden" class="form-control" name="edit_post_id" value="<?php echo $_GET['id'];?>" />
                                        <?php endif;?>
                                    </div>                         
                                </div>                         
                            </div>                         
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="text" id="image-url" class="form-control required" name="imageid" value="<?php echo @$image;?>"/>
                                    <div class="input-group-btn">
                                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#cropModal">
                                         Change
                                        </button>
                                    </div>
                                </div>                        
                            </div>                         
                        </div>
                        <div class="form-group">
                            <div class="col-md-5">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label for="imageid">Type</label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="text" class="form-control" name="label" placeholder="Label here" value="<?php echo (!empty($_GET['l'])) ? $_GET['l'] : '';?>" required />
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-7">
                                <div class="input-group">
                                    <input type="text" class="form-control required" name="blogid" required/>
                                    <div class="input-group-btn">
                                        <button type="submit" class="btn btn-info" name="submit" value="Submit">
                                            Get code                           
                                        </button>
                                    </div>
                                </div>
                            </div>                            
                        </div>
                        <div class="form-group">
                            <div id="image-preview">
                                <img style="width: 100%;max-width: 300px;" src="<?php echo $site->resize_image(@$_GET['img'],300);?>"/>
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
    <script type="text/javascript" src="<?php echo base_url; ?>assets/js/jquery.form.js"></script>


    <script src="<?php echo base_url; ?>assets/plugins/crop-upload/js/jquery.Jcrop.min.js"></script>
    <script src="<?php echo base_url; ?>assets/plugins/crop-upload/js/jquery.color.js"></script>
    <script src="<?php echo base_url; ?>assets/plugins/crop-upload/js/script.js"></script>
    <link href="<?php echo base_url; ?>assets/plugins/crop-upload/css/main.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo base_url; ?>assets/plugins/crop-upload/css/jquery.Jcrop.min.css" rel="stylesheet" type="text/css" />     


<!-- crop Modal -->
<div class="modal fade khmer" id="cropModal" role="dialog" aria-labelledby="cropModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="cropModalLabel">Upload</h4>
      </div>
      <div class="modal-body bbody">
            <form id="upload_form" method="post" enctype="multipart/form-data" action='<?php echo base_url;?>blogger/upload.php' class="form-horizontal row-border" >
                 <!-- hidden crop params -->
                <input type="hidden" id="x1" name="x1" />
                <input type="hidden" id="y1" name="y1" />
                <input type="hidden" id="x2" name="x2" />
                <input type="hidden" id="y2" name="y2" />
                <div class="row" id="uploadFile">
                    <div class="col-md-6">
                        <label>
                          <select name="watermark" class="form-control" style="width: 200px">
                                <option value="" selected="selected" required>
                                    Select Watermark
                                </option>
                                <option value="sruol9">
                                    sruol9
                                </option>
                                <option value="khmermove">
                                    khmermove
                                </option>
                                <option value="50">
                                    50
                                </option>
                                <option value="-1">
                                    All
                                </option>
                            </select>
                        </label>
                        <label>
                          <select name="vdotype" class="form-control" style="width: 200px" required>
                                <option value="">
                                    Select video type
                                </option>
                                <option value="th">
                                    ថៃ
                                </option>
                                <option value="th-s">
                                    ថៃដាច់
                                </option>
                                <option value="ch">
                                    ចិន
                                </option>
                                <option value="ch-s">
                                    ចិនដាច់
                                </option>
                                <option value="ko">
                                    កូរ៉េ
                                </option>
                                <option value="ko-s">
                                    កូរ៉េ
                                </option>
                                <option value="kh">
                                    ខ្មែរ
                                </option>
                                <option value="funny">
                                    សំណើច
                                </option>
                                <option value="streng">
                                    ប្លែកៗ
                                </option>
                                <option value="news">
                                    ព័ត៌មាន
                                </option>
                                <option value="sports">
                                    កីឡា
                                </option>
                                <option value="comedy">
                                    កំប្លែង
                                </option>
                                <option value="enter">
                                    កំសាន្ត
                                </option>
                            </select>
                        </label>
                    </div>
                    <div class="col-md-6">
                        <fieldset>
                            <div class="form-group">
                                <input type="file" name="image_file" id="image_file" onchange="fileSelectHandler()" data-style="fileinput" accept="image/*"/>
                                <div class="error"></div>
                                <?php if(!empty($_GET['id'])):?>
                                <input type="hidden" class="form-control" name="edit_post_id" value="<?php echo $_GET['id'];?>" />
                                <?php endif;?>
                            </div>
                        </fieldset>
                    </div>

                    <div class="step2">
                        <h2>Step2: Please select a crop region</h2>
                        <img id="preview" />

                        <div class="info">
                            <label>File size</label> <input type="text" id="filesize" name="filesize" class="form-control input-width-small" style="display: inline-block;" />
                            <label>Type</label> <input type="text" id="filetype" name="filetype" class="form-control input-width-small" style="display: inline-block;" />
                            <label>Image dimension</label> <input type="text" id="filedim" name="filedim" class="form-control input-width-small" style="display: inline-block;" />
                            <label>W</label> <input type="text" id="w" name="w" class="form-control input-width-small" style="display: inline-block;" />
                            <label>H</label> <input type="text" id="h" name="h" class="form-control input-width-small" style="display: inline-block;" />
                        </div>
                        <div class="form-group fixed">
                            <div class="col-md-12">
                                <input type="submit" value="Upload" class="btn btn-primary pull-right" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row" id="wrap-loading">
                    <div class="col-md-12">
                        <div id="loading"></div>
                    </div>
                </div>
            </form>
      </div>
    </div>
  </div>
</div> 

<script type="text/javascript">
$(document).ready(function() { 
    var options = { 
        beforeSubmit:  showRequest,  // pre-submit callback 
        success:       showResponse  // post-submit callback 
        // other available options: 
        //url:       url         // override for form's 'action' attribute 
        //type:      type        // 'get' or 'post', override for form's 'method' attribute 
        //dataType:  null        // 'xml', 'script', or 'json' (expected server response type) 
        //clearForm: true        // clear all form fields after successful submit 
        //resetForm: true        // reset the form after successful submit 
 
        // $.ajax options can be used here too, for example: 
        //timeout:   3000 
    }; 
 
    // bind to the form's submit event 
    $('#upload_form').submit(function() { 
        $(this).ajaxSubmit(options); 
        return false; 
    }); 
});     


// pre-submit callback 
function showRequest(formData, jqForm, options) { 
    $('#uploadFile').hide();
    $('#wrap-loading').show();
    $("#loading").html('<center><img src="<?php echo base_url; ?>assets/img/upload_progress.gif" alt="Uploading...."/></center>');
    return true; 
} 
 
// post-submit callback 
function showResponse(responseText, statusText, xhr, $form)  { 
    var obj = JSON.parse(responseText);
    if (!obj.error) {
        $("#image-url").val(obj.image);
        $("#image-preview").html('<img src="' + obj.image + '" alt="Success...."/>').show();
        $("#photoCrop").attr("src",obj.image);
        $('#cropModal').modal('hide');
        $("#imagepost").val(obj.image);
        $('#uploadFile').show();
        $('#wrap-loading').hide();
    }
}                    
</script>
<style type="text/css">
.modal-dialog {
    z-index: 1050;
    width: auto!important;
    padding: 10px;
    margin-right: auto;
    margin-left: auto;
}
.ui-widget-overlay {
  opacity: 0.80;
  filter: alpha(opacity=70);
}
.jc-dialog {
  padding-top: 1em;
}
.ui-dialog p tt {
  color: yellow;
}
.jcrop-light .jcrop-selection {
  -moz-box-shadow: 0px 0px 15px #999;
  /* Firefox */

  -webkit-box-shadow: 0px 0px 15px #999;
  /* Safari, Chrome */

  box-shadow: 0px 0px 15px #999;
  /* CSS3 */

}
.jcrop-dark .jcrop-selection {
  -moz-box-shadow: 0px 0px 15px #000;
  /* Firefox */

  -webkit-box-shadow: 0px 0px 15px #000;
  /* Safari, Chrome */

  box-shadow: 0px 0px 15px #000;
  /* CSS3 */

}
.jcrop-fancy .jcrop-handle.ord-e {
  -webkit-border-top-left-radius: 0px;
  -webkit-border-bottom-left-radius: 0px;
}
.jcrop-fancy .jcrop-handle.ord-w {
  -webkit-border-top-right-radius: 0px;
  -webkit-border-bottom-right-radius: 0px;
}
.jcrop-fancy .jcrop-handle.ord-nw {
  -webkit-border-bottom-right-radius: 0px;
}
.jcrop-fancy .jcrop-handle.ord-ne {
  -webkit-border-bottom-left-radius: 0px;
}
.jcrop-fancy .jcrop-handle.ord-sw {
  -webkit-border-top-right-radius: 0px;
}
.jcrop-fancy .jcrop-handle.ord-se {
  -webkit-border-top-left-radius: 0px;
}
.jcrop-fancy .jcrop-handle.ord-s {
  -webkit-border-top-left-radius: 0px;
  -webkit-border-top-right-radius: 0px;
}
.jcrop-fancy .jcrop-handle.ord-n {
  -webkit-border-bottom-left-radius: 0px;
  -webkit-border-bottom-right-radius: 0px;
}
.description {
  margin: 16px 0;
}
.jcrop-droptarget canvas {
  background-color: #f0f0f0;
}
</style>   
</body>
</html>