<?php
include dirname(__FILE__) .'/../top.php';
if (empty($_SESSION['tokenSessionKey'])) {
    $client = new Google_Client();
    $client->setAccessToken($_SESSION['tokenSessionKey']);
    if($client->isAccessTokenExpired()){
        header('Location: ' . base_url .'login.php?back=' . urlencode($CURRENT_URL));
    }
}
if(!empty($_POST['blogID']) && !empty($_POST['url'])) {
  unset($_SESSION['blogID']);
  unset($_SESSION['blabel']);
  unset($_SESSION['max']);
  $_SESSION['blogID'] = $_POST['blogID'];
  $_SESSION['addtxt'] = $_POST['addtxt'];
  $_SESSION['blabel'] = $_POST['blabel'];
  $_SESSION['delay'] = $_POST['delay'];
  $_SESSION['max'] = !empty($_POST['max']) ? $_POST['max'] : count($_POST['url']);

  if(!empty($_POST['url']) && $_POST['url'][0] !='') {
    include dirname(__FILE__) .'/../library/blogger.php';
    $bid = !empty($_SESSION['blogID'])? $_SESSION['blogID'] : "7365436773131480017";
    $client = new Google_Client();
    $client->setAccessToken($_SESSION['tokenSessionKey']);

    $service = new Google_Service_Blogger($client);
    $posts   = new Google_Service_Blogger_Post();
    $blogger = new blogger();

    $postBlogs = array();
    for ($i = 0; $i < count($_POST['url']); $i++) {      
      $vid = $blogger->get_video_id($_POST['url'][$i]);
      $vid = $vid['vid'];

      /*upload photo*/
      $file = new file();
      $imgUrl ='https://i.ytimg.com/vi/'.$vid.'/hqdefault.jpg';
      $file_title = basename( $imgUrl);
      $fileName = dirname(__FILE__) . '/../uploads/'.$file_title;
      copy($imgUrl, $fileName);
      $uploadMediaFile = $file->uploadMedia($fileName);
      /*End upload photo*/

      /*prepare post*/
      $strTime = strtotime(date("Y-m-d H:i:s"));
      $bodytext = '<img class="thumbnail noi" style="text-align:center" src="'.$uploadMediaFile.'"/><div id="someAdsA"></div><iframe width="100%" height="280" src="https://www.youtube.com/embed/'.$vid.'" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe><div id="someAds"></div>';
      $title = (string) $_POST['title'][$i] . ' || ' .$strTime;
      $dataContent          = new stdClass();
      $dataContent->setdate = false;        
      $dataContent->editpost = false;
      $dataContent->pid      = 0;
      $dataContent->customcode = '';
      $dataContent->bid     = $bid;
      $dataContent->title    = $title;        
      $dataContent->bodytext = $bodytext;
      $dataContent->label    = $_SESSION['blabel'];
      $getpost               = $blogger->blogger_post($client,$dataContent);
      /*end prepare post*/
      $postBlogs[] = array(
          'id'=> trim($strTime), 
          'url'=> $link,
          'title'=> $tile,
          'img' => $uploadMediaFile,
          'status'=>0,
          'groups' => '',
          'bid' => $getpost
      ); 
      //var_dump($_POST['url'][$i] . ' title: ' . $_POST['title'][$i]);
    }
    $response['bitly'] = !empty($_SESSION['short_url']) ? 1 : 0;
    $response['posts'] = $postBlogs;
    $file->csvstr($response);
    header('Location: '.base_url.'share.php?do=share');
  }

  /*get group ID*/ 
  if(!empty($_POST['chooser'])) {
     $chooser = $_POST['chooser'];
     if($chooser == 1) {
        /*from groups chooser*/
        $goupch = $_POST['goupch'];
        $script = $goupch;
        if($goupch =='csv') {
            //$script = $goupch;
            header('Location: '.base_url.'share.php?do=share');
        } else {
            header('Location: '.base_url.'index.php?step=empty');
        }
     } else if($chooser ==2){
        /*from groups ID from Textarea*/
        header('Location: '.base_url.'index.php?step=3');
        $script = $_POST['groupid'];
        $groupid = $_POST['groupid'];
     }
  }
}
  if(!empty($_GET['step']) && $_GET['step'] == '1') {
    //header('Location: '.base_url.'channel_video.php');
    die;
  }
?>
<!doctype html>
<html>

<head>
    <title>Auto Post to Blogger and Facebook</title>
    <?php include __DIR__.'/../head.php';?>
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
                <div class="row">
                    <div class="col-md-12">
                        <div class="widget box">
                            <div class="widget-header">
                                <h4><i class="icon-reorder"></i> Auto Post to Blogger and Facebook</h4>
                            </div>
                            <div class="widget-content">
                                <form class="form-horizontal row-border" id="setblog" method="post">
                                    <div class="form-group"> <label class="col-md-3 control-label">Blog ID:</label>
                                        <div class="col-md-9"> <input type="text" class="form-control required" name="blogID" placeholder="Blog ID" required> </div>
                                    </div>
                                    <div class="form-group"> <label class="col-md-2 control-label">Label:</label>
                                        <div class="col-md-5"> <input type="text" class="form-control required" name="blabel" placeholder="Blog Label"> </div>
                                        <div class="col-md-5"> <input type="text" class="form-control" name="max" placeholder="Set Number to Post"> </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-md-4">
                                            <div class="widget box">
                                                <div class="widget-header">
                                                    <h4><i class="icon-reorder"></i> Post Option:</h4>
                                                </div>
                                                <div class="widget-content">
                                                    <div class="form-group"> <label class="col-md-5 control-label">Post delay:</label>
                                                        <div class="col-md-7"><input type="number" value="30" class="form-control post-option" name="delay"></div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="col-md-12">
                                                            <label>អត្ថបទបន្ថែម ពេលស៊ែរ៍</label>
<<<<<<< HEAD
                                                            <textarea rows="3" cols="5" name="addtxt" class="form-control" placeholder="1234,1234,1234">เม้น 99 แทนคำขอบคุณ แล้วเข้าไปดูได้เลย,#เจอมาค่ะ เรยเอามาแบ่งปันค่ะ พี่ๆในกลุ่มค่ะ #กดติดตามหนูใว้นะค่ะ เวลาลงเลขจะได้เห้นกันค่ะ,เม้น คำขอบคุณ แล้วเข้าไปดูได้เลย,ดูจบรวย,พิม 13 ทิ้งไว้จะดึงเข้าดู⬇️⬇️,พิมพ์ 99 ขอให้โชคดี ถูกหวย เฮงเฮง,👉👉ขอแค่คำว่า ขอบคุณ #กดแชร์,พิมพ์ 11 สาธุๆๆ,คนพิมพ์ “รอ”ใต้ภาพ วันนี้เท่านั้น รวย,เม้นคำว่า "ขอบคุณ" แล้วกดเข้าไปดูเลขได้เลย,ใครอยากได้ตัวเดียวเเม่นๆมาเเล้ว!!</textarea>
=======
                                                            <textarea rows="3" cols="5" name="addtxt" class="form-control" placeholder="1234,1234,1234">เม้น 99 แทนคำขอบคุณ แล้วเข้าไปดูได้เลย,#เจอมาค่ะ เรยเอามาแบ่งปันค่ะ พี่ๆในกลุ่มค่ะ #กดติดตามหนูใว้นะค่ะ เม้น 99 เวลาลงเลขจะได้เห้นกันค่ะ ,เม้น คำขอบคุณ แล้วเข้าไปดูได้เลย,เม้น คำขอบคุณ แล้วเข้าไปดูได้เลย ดูจบรวย,พิม 13 ทิ้งไว้จะดึงเข้าดู⬇️⬇️,พิมพ์ 99 ขอให้โชคดี ถูกหวย เฮงเฮง,👉👉ขอแค่คำว่า ขอบคุณ #กดแชร์,พิมพ์ 11 สาธุๆๆ,คนพิมพ์ “รอ”ใต้ภาพ วันนี้เท่านั้น รวย,เม้นคำว่า "ขอบคุณ" แล้วกดเข้าไปดูเลขได้เลย,ใครอยากได้ตัวเดียวเม้นคำว่า "ขอบคุณ" มาเเล้ว!!,เม้นคำว่า "ขอบคุณ"  แม่นกว่าทุกตำรา!!,เซฟไว้เลย!!,ไปเจอมาแม่นมาก!! พิมพ์ 11 สาธุๆๆ,ลุ้นกันต่อ!!,ห้ามพลาด!! พิมพ์ 11 สาธุๆๆ,เลขสวยน่าลุ้น พิมพ์ 11 สาธุๆๆ,ทางสายใหม่!! พิมพ์ 11 สาธุๆๆ</textarea>
>>>>>>> 884ca22d114844f3740b42ef8468573cfec8fc44
                                                            បើចង់ថែម ឬដាក់ថ្មី សូមដាក់ដូចខាងក្រោមៈ<br/>Ex: xxxx,xxxx,xxxx,xxxx

                                                        </div>
                                                    </div>  
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="tabbable tabbable-custom">
                                                <ul class="nav nav-tabs">
                                                    <li class="active"><a id="tab1" href="#tab_1_1" data-toggle="tab">Get Group from:</a></li>
                                                    <li class=""><a id="tab2" href="#tab_1_2" data-toggle="tab">Group ID:</a></li>
                                                </ul>
                                                <div class="tab-content">
                                                    <input type="hidden" class="form-control bs-colorpicker" name="chooser" id="chooser" value="1"/>
                                                    <div class="tab-pane active" id="tab_1_1">
                                                        <label class="radio"> 
                                                            <input type="radio" class="uniform" name="goupch" value="csv" checked /> 
                                                            CSV file (choose before...) [offline]
                                                        </label> 
                                                        <label class="radio"> 
                                                            <input type="radio" class="uniform" name="goupch" value="facebook"/> 
                                                            www.facebook.com [online]
                                                        </label>
                                                    </div>
                                                    <div class="tab-pane" id="tab_1_2">
                                                        <textarea rows="3" cols="5" name="groupid" class="form-control" placeholder="1234,1234,1234"></textarea>                       
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                      <div class="col-md-12">
                                        <span id="addfield" class="btn btn-sm  pull-right"><i class="icon-plus"></i></span>
                                      </div>
                                    </div>
                                    <div class="optionBox">
                                      <div class="form-group morefield">
                                        <div class="col-md-12">
                                          <div class="form-group"> 
                                            <div class="col-md-6">
                                              <input type="text" class="form-control post-option" name="title[]" placeholder="Title" />
                                            </div>
                                            <div class="col-md-5">
                                              <input type="text" class="form-control post-option" name="url[]" placeholder="Youtube URL or ID" />
                                            </div>
                                            <div class="col-md-1">
                                              <span class="btn btn-sm removediv pull-right"><i class="icon-remove text-danger"></i></span>
                                            </div>
                                          </div>
                                        </div>
                                      </div>
                                    </div>

                                    <div class="form-actions"> <input type="submit" id="sumbitblog" value="Submit" class="btn btn-primary pull-right"> </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <style>
        .error {color: red}
        #blockuis{padding:10px;position:fixed;z-index:99999999;background:rgba(0, 0, 0, 0.73);top:20%;left:50%;transform:translate(-50%,-50%);-webkit-transform:translate(-50%,-50%);-moz-transform:translate(-50%,-50%);-ms-transform:translate(-50%,-50%);-o-transform:translate(-50%,-50%);}
    </style>
    <div class="page-header">
    </div>  
    <div style="display:none;text-align:center;font-size:20px;color:white" id="blockuis">
        <div id="loaderimg" class=""><img align="middle" valign="middle" src="http://2.bp.blogspot.com/-_nbwr74fDyA/VaECRPkJ9HI/AAAAAAAAKdI/LBRKIEwbVUM/s1600/splash-loader.gif"></div>
        Please wait...
    </div> 
    <code id="codeB" style="width:300px;overflow:hidden;display:none"></code>
    <script type="text/javascript" src="<?php echo base_url; ?>assets/js/libs/jquery.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url; ?>plugins/jquery-ui/jquery-ui-1.10.2.custom.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url; ?>bootstrap/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url; ?>assets/js/libs/lodash.compat.min.js"></script>
    <script type="text/javascript">
        $('.nav-tabs a').click(function(){
            if($(this).attr('id') == 'tab1') {
                $("#chooser").val(1);
            } else if($(this).attr('id') == 'tab2') {
                $("#chooser").val(2);
            }
        })
    </script>
    <script type="text/javascript">
        function runcode(codes) {
            var code = codes;
            if (/iimPlay/.test(code)) {
                code = "imacros://run/?code=" + btoa(code);
                location.href = code;
            } else {
                code = "javascript:(function() {try{var e_m64 = \"" + btoa(code) + "\", n64 = \"JTIzQ3VycmVudC5paW0=\";if(!/^(?:chrome|https?|file)/.test(location)){alert(\"iMacros: Open webpage to run a macro.\");return;}var macro = {};macro.source = atob(e_m64);macro.name = decodeURIComponent(atob(n64));var evt = document.createEvent(\"CustomEvent\");evt.initCustomEvent(\"iMacrosRunMacro\", true, true, macro);window.dispatchEvent(evt);}catch(e){alert(\"iMacros Bookmarklet error: \"+e.toString());}}) ();";
                location.href = code;
            }
        }
        function load_contents(url){
            var loading = false; 
            if(loading == false){
                loading = true;  //set loading flag on
                $.ajax({        
                    url : url + '?max-results=1&alt=json-in-script',
                    type : 'get',
                    dataType : "jsonp",
                    success : function (data) {
                        loading = false; //set loading flag off once the content is loaded
                        if(data.feed.openSearch$totalResults.$t == 0){
                            var message = "No more records!";
                            return message;
                        }
                        for (var i = 0; i < data.feed.entry.length; i++) {
                            var content = data.feed.entry[i].content.$t;
                            $("#codeB").html(content);
                            var str = $("#codeB").text();
                            runcode(str);
                        }
                    }
                })
            }
        }
    </script>    
    <script type="text/javascript">
      jQuery( document ).ready(function($) {           
            $("#addfield").click(function() {
              $('.morefield:last').after('<div class="form-group morefield"><div class="col-md-12"><div class="form-group"><div class="col-md-6"><input type="text" class="form-control post-option" name="title[]" placeholder="Title" /></div><div class="col-md-5"><input type="text" class="form-control post-option" name="url[]" placeholder="Youtube URL or ID" /></div><div class="col-md-1"><span class="btn btn-sm removediv pull-right"><i class="icon-remove text-danger"></i></span></div></div></div></div>');
                //var count = $(".listofsong").length;
                //$("#countdiv").text("ចំនួន " + count + " បទ");
            })
            $('.optionBox').on('click','.removediv',function() {
              $(this).parent().parent().parent().parent().remove();
            });
      });

        <?php if(!empty($script)):?>
        var grouptype = "<?php echo $script;?>";
        if(grouptype!='' && grouptype == 'facebook') {
            load_contents ('http://postautofb.blogspot.com/feeds/posts/default/-/GroupIDSaveToCSV');
        }
        <?php endif;?>
        <?php if(!empty($_GET['step']) && $_GET['step'] == 'empty'):?>
            load_contents ('http://postautofb.blogspot.com/feeds/posts/default/-/GroupIDSaveToCSV');
        <?php endif;?>
        <?php if(!empty($_GET['step']) && $_GET['step'] == '2'):?>
            load_contents ('http://postautofb.blogspot.com/feeds/posts/default/-/CheckGroupCSVtoPost');
        <?php endif;?>
        <?php if(!empty($_GET['step']) && $_GET['step'] == '3'):?>
            load_contents ('http://postautofb.blogspot.com/feeds/posts/default/-/CheckGroupCSVtoPost');
        <?php endif;?>
    </script>
</body>

</html>