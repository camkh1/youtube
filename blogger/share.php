<?php
include 'top.php';
$postToShare = '';
function checkDuplicate($bid,$label='',$max=3){
    if(!empty($label)) {
        $link_blog = 'https://www.blogger.com/feeds/'.$bid.'/posts/summary/-/'.$label.'?max-results='.$max;
        $html = simplexml_load_file($link_blog);
    } else {
        $link_blog = 'https://www.blogger.com/feeds/'.$bid.'/posts/summary?max-results='.$max;
        $html = simplexml_load_file($link_blog);
    } 
    return $html->entry;
}
/*create CSV file*/
function csvstr($list = array(),$update='')
{
    date_default_timezone_set('Asia/Phnom_Penh');
    if(!empty($_SESSION['blabel'])) {
        $permarklink = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $_SESSION['blabel']);
        $permarklink = str_replace(",", '', $permarklink);
        $cat_slug = preg_replace("/[[:space:]]/", "-", $permarklink);
        $upload_path = "C:\\myImacros/".$_SESSION['blogID'].'/'.$cat_slug.'/';
    } else {
        $upload_path = "C:\\myImacros/".$_SESSION['blogID'].'/';
    }    
    $file_name = date("m-d-Y").'_file.json';
    if (!file_exists($upload_path)) {
        mkdir($upload_path, 0700, true);
    }
    if (!file_exists($upload_path.$file_name)) {
        $f = fopen($upload_path.$file_name, 'w');
        fwrite($f, json_encode($list));
        fclose($f);
    } else {
        $setList = checkIdExist($list['posts'],$upload_path.$file_name);
        $f = fopen($upload_path.$file_name, 'w');
        fwrite($f, json_encode($setList));
        fclose($f);
    }
    if (!empty($update)) {
        $f = fopen($upload_path.$file_name, 'w');
        fwrite($f, json_encode($list));
        fclose($f);
    }
}

function checkIdExist($data = array(),$file)
{
    $str = file_get_contents($file);
    $json = json_decode($str,true);
    $lists = json_decode(json_encode($data), FALSE);
    $result = array_merge($json['posts'], $data);
    $details = unique_multidim_array($result,'id'); 
    $response = array();
    $response['posts'] = $details;
    return $response;
}
function unique_multidim_array($array, $key) { 
    $temp_array = array(); 
    $i = 0; 
    $key_array = array(); 
    
    foreach($array as $val) { 
        if (!in_array($val[$key], $key_array)) { 
            $key_array[$i] = $val[$key]; 
            $temp_array[$i] = $val; 
        } 
        $i++; 
    } 
    return $temp_array; 
} 
function doShare()
{
    date_default_timezone_set('Asia/Phnom_Penh'); 
    if(!empty($_SESSION['blogID'])) {
        $bid = $_SESSION['blogID'];
        $max = !empty($_SESSION['max']) ? $_SESSION['max'] : 50;
        $blabel = !empty($_SESSION['blabel']) ? $_SESSION['blabel'] : '';
        $checkPost = checkDuplicate($bid,$blabel,$max);
        $i=1;
        /*Add to list first*/
        $response = array();
        $posts = array();
        foreach ($checkPost as $value) {
            $tCheck = explode('||', $value->title);
            if(!empty($tCheck[1])) {
                $titles[$i] = $tCheck[1];
                if(!empty($value->link[4]['href'])) {
                    $tile = trim($tCheck[0]);
                    if(!empty($tile)) {
                        $link = (string) $value->link[4]['href'];
                        $posts[] = array('id'=> trim($tCheck[1]), 'url'=> $link,'title'=> $tile,'status'=>0);
                    }
                } else if(!empty($value->link[2]['href'])) {
                    $posts[] = array('id'=> trim($tCheck[1]), 'url'=> (string) $value->link[2]['href'],'title'=> trim($tCheck[0]),'status'=>0);
                }
            }
            $i++;
        }
        $response['posts'] = $posts;
        csvstr($response);
        /*End add to list first*/
        /*Get post to share*/
        return getPost();
        /*End get Post to share*/
    }
} 

function getPost($id='')
{
    if(!empty($_SESSION['blogID'])) {
        $bid = $_SESSION['blogID'];
        //$upload_path = "C:\\myImacros/".$_SESSION['blogID'].'/';
        if(!empty($_SESSION['blabel'])) {
            $permarklink = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $_SESSION['blabel']);
            $permarklink = str_replace(",", '', $permarklink);
            $cat_slug = preg_replace("/[[:space:]]/", "-", $permarklink);
            $upload_path = "C:\\myImacros/".$_SESSION['blogID'].'/'.$cat_slug.'/';
        } else {
            $upload_path = "C:\\myImacros/".$_SESSION['blogID'].'/';
        }

        $file_name = date("m-d-Y").'_file.json';
        $str = file_get_contents($upload_path.$file_name);
        $json = json_decode($str);
        $response = array();
        $posts = array();
        if(!empty($id)) {
            /*Update*/
            foreach ($json->posts as $key => $value) {
                if ( $value->id == $id ) {
                    $posts[] = array('id'=> trim($value->id), 'url'=> (string) $value->url,'title'=> trim($value->title),'status'=>1);
                } else {
                    $posts[] = array('id'=> trim($value->id), 'url'=> (string) $value->url,'title'=> trim($value->title),'status'=>$value->status);
                }
            }
            $response['posts'] = $posts;
            csvstr($response,1);
            header('Location: '.base_url.'share.php?do=wait');
            exit();
            /*End Update*/
        } else {
            $i=0;
            foreach ($json->posts as $key => $value) {
                $i++;
                if($value->status ==0) {
                    $dataShare['url'] = $value->url;
                    $dataShare['title'] = $value->title;
                    $dataShare['id'] = $value->id;
                    return $dataShare;
                }
            }
        }
    }
}

function getPostShow()
{
    $dataShare = array();
    if(!empty($_SESSION['blogID'])) {
        $bid = $_SESSION['blogID'];
        //$upload_path = "C:\\myImacros/".$_SESSION['blogID'].'/';
        if(!empty($_SESSION['blabel'])) {
            $permarklink = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $_SESSION['blabel']);
            $permarklink = str_replace(",", '', $permarklink);
            $cat_slug = preg_replace("/[[:space:]]/", "-", $permarklink);
            $upload_path = "C:\\myImacros/".$_SESSION['blogID'].'/'.$cat_slug.'/';
        } else {
            $upload_path = "C:\\myImacros/".$_SESSION['blogID'].'/';
        }

        $file_name = date("m-d-Y").'_file.json';
        $str = file_get_contents($upload_path.$file_name);
        $json = json_decode($str);
        $i=0;
        $object = new stdClass();
        foreach ($json->posts as $key => $value) {
            $i++;
                $dataShare[] = array(
                    'id' => $value->id,
                    'url' => $value->url,
                    'title' => $value->title,
                    'status' => $value->status,
                );
                // $dataShare[]['url'] = $value->url;
                // $dataShare[]['title'] = $value->title;
                // $dataShare[]['id'] = $value->id;
        }
    }
    return (object) $dataShare;
}

function refresh()
{
    /**
     * Library Requirements
     *
     * 1. Install composer (https://getcomposer.org)
     * 2. On the command line, change to this directory (api-samples/php)
     * 3. Require the google/apiclient library
     *    $ composer require google/apiclient:~2.0
     */
    if (!file_exists(__DIR__ . '/Google/autoload.php')) {
      throw new \Exception('please run "composer require google/apiclient:~2.0" in "' . __DIR__ .'"');
    }
    require_once __DIR__ . '/Google/autoload.php';
    $OAUTH2_CLIENT_ID = '814595907237-kqk1qe9uc8iggm3m788k8u79056dipfh.apps.googleusercontent.com';
    $OAUTH2_CLIENT_SECRET = 'sBKCkX2261txKtwilMcCSsuI';

    $client = new Google_Client();
    $client->setClientId($OAUTH2_CLIENT_ID);
    $client->setClientSecret($OAUTH2_CLIENT_SECRET);
    $client->setApplicationName("My Post from app");
    $client->setAccessType("offline");
    $client->addScope('https://www.googleapis.com/auth/youtube.force-ssl');
    $client->addScope("https://www.googleapis.com/auth/userinfo.email");
    $client->addScope("https://picasaweb.google.com/data/");
    $client->addScope("https://www.googleapis.com/auth/blogger");
    $client->addScope("https://www.googleapis.com/auth/drive");
    $client->addScope("https://www.googleapis.com/auth/plus.me");
    $client->addScope("https://www.googleapis.com/auth/plus.login");
    $client->addScope("https://www.googleapis.com/auth/plus.media.upload");
    $client->addScope("https://www.googleapis.com/auth/plus.stream.write");
    $redirect = filter_var(base_url .'channel_video.php',
        FILTER_SANITIZE_URL);
    $client->setRedirectUri($redirect);
    $youtube = new Google_Service_YouTube($client);

    $tokenSessionKey = 'token-' . $client->prepareScopes();
    if (isset($_GET['code'])) {
      if (strval($_SESSION['state']) !== strval($_GET['state'])) {
        die('The session state did not match.');
      }

      $client->authenticate($_GET['code']);
      $_SESSION[$tokenSessionKey] = $client->getAccessToken();
      $_SESSION['tokenSessionKey'] = $_SESSION[$tokenSessionKey];
      header('Location: ' . $redirect);
    }

    if (isset($_SESSION[$tokenSessionKey])) {
      $client->setAccessToken($_SESSION[$tokenSessionKey]);
    }    
}

$pid = [];
if(!empty($_GET['id'])) {
    $pid = explode(',', $_GET['id']);
}
if(!empty($_GET['do']) && $_GET['do'] == 'blank') {
    $runcode = 1;
} else if(!empty($_GET['do']) && $_GET['do'] == 'share') {
    $postToShare = doShare();
} else if(!empty($_GET['do']) && $_GET['do'] == 'wait') {
    refresh();
}

if(!empty($_GET['id']) && $_GET['do'] == 'complete') {
    getPost($_GET['id']);
    die;
}
if(!empty($_POST['blogID'])) {
    $_SESSION['blogID'] = $_POST['blogID'];
    $_SESSION['addtxt'] = $_POST['addtxt'];
    $_SESSION['delay'] = $_POST['delay'];
}
?>
<!doctype html>
<html>

<head>
    <?php include 'head.php';?>
    <title>Auto Post to Blogger and Facebook</title>
    <link href="<?php echo base_url; ?>assets/css/plugins/nestable.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo base_url; ?>assets/css/plugins/bootstrap-switch.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="<?php echo base_url; ?>assets/plugins/jquery-ui/jquery.countdown.css">
    <script type="text/javascript" src="<?php echo base_url; ?>assets/js/libs/jquery.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url; ?>plugins/jquery-ui/jquery-ui-1.10.2.custom.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url; ?>bootstrap/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url; ?>assets/js/libs/lodash.compat.min.js"></script>
    <script src="<?php echo base_url; ?>assets/plugins/jquery-ui/jquery.plugin.min.js"></script>
    <script src="<?php echo base_url; ?>assets/plugins/jquery-ui/jquery.countdown.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url; ?>assets/plugins/nestable/jquery.nestable.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url; ?>assets/plugins/bootstrap-switch/bootstrap-switch.min.js"></script>
    <?php if(!empty($_GET['do']) && $_GET['do'] == 'wait'):?>
        <script type="text/javascript">
        //var myVar = setInterval(myTimer, 100000);

        function myTimer() {
            //window.location = "<?php echo base_url;?>/share.php?do=share";
        }
        function counter($el, n) {
            (function loop() {
               $el.html(n);
                $el.progressbar("value", n);
               if (n--) {
                   setTimeout(loop, 1000);
               }
            })();
        }
        
        $( document ).ready(function() { 
            $('#defaultCountdown').countdown({until: '+0h +10m +0s', format: 'HMS'}); 

            setInterval(function() {            
                closeOnLoad("<?php echo base_url;?>login.php");
              }, 120000);   
          //1000 = 1 second      


            var elem = document.getElementById("timer");   
              var width = 1;
              //600 = 1minute
              var id = setInterval(frame, 6000);
              function frame() {
                if (width >= 100) {
                  clearInterval(id);
                  //complete here
                  window.location = "<?php echo base_url;?>share.php?do=share";
                } else {
                  width++; 
                  elem.style.width = width + '%'; 
                  elem.innerHTML = width + '%'; 
                }
              };


            var list = $('#nestable_list_1').nestable('serialize');
            var updateOutput = function(list)
            {
                
                var dataj = window.JSON.stringify(list.nestable('serialize'));
                $('#nestable_list_1_output').val(dataj);
                // var list   = e.length ? e : $(e.target),
                //     output = list.data('output');
                // if (window.JSON) {
                //     output.val(window.JSON.stringify(list.nestable('serialize')));//, null, 2));
                //     var dataj = window.JSON.stringify(list.nestable('serialize'));
                //     /*save to file*/
                //     $.ajax
                //     ({
                //         type: "POST",
                //         //the url where you want to sent the userName and password to
                //         url: '<?php base_url;?>blogger/save_csv.php',
                //         dataType: 'json',
                //         async: false,
                //         //json object to sent to the authentication url
                //         data: {'posts': dataj},
                //         success: function () {

                //         alert("Thanks!"); 
                //         }
                //     })
                //     // $.post("<?php base_url;?>blogger/save_csv.php",
                //     // {
                //     //     name: dataj
                //     // },
                //     // function(data, status){
                //     //     alert("Data: " + data + "\nStatus: " + status);
                //     // });
                //     /*End save to file*/
                // } else {
                //     output.val('JSON browser support required for this demo.');
                // }
            };
            updateOutput(list);
            //$('#nestable_list_1').on('change', updateOutput);
        });


function closeOnLoad(myLink)
{
  var newWindow = window.open(myLink, "connectWindow", "width=600,height=400,scrollbars=yes");
  setTimeout(
             function()
             {
               newWindow.close();
             },
            2000
            );
  return false;
}      
        </script>
              
    <?php endif;?>  
<style type="text/css">
#defaultCountdown { width: 340px; height: 100px; font-size: 20pt;margin-bottom: 20px}
.dd3-handle:before{
    top: 9px;
    font-size: 30px
}
.dd3-content{padding-left: 50px}
</style>
</head>   
<body class="">
    <?php include 'header.php';?>
    <div id="container">
        <div id="content">
            <div class="container">
                <?php include 'leftside.php';?>
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
                                <h4><i class="icon-reorder"></i> 
                                    <?php if(!empty($_GET['do']) && $_GET['do'] == 'wait'):?>
                                        រង់ចាំ ប៉ុស្តិ៍បន្ទាប់
                                    <?php else:?>
                                    តើអ្នកចង់ Share ប្លុកគ៍ណាដែរ?
                                    <?php endif;?>
                                </h4>
                            </div>
                            <div class="widget-content">
                                <!-- waiting time -->
                                <?php if(!empty($_GET['do']) && $_GET['do'] == 'wait'):?>
                                    <center>
                                        <div>Blog ID: <?php echo @$_SESSION['blogID'];?> || Label: <?php echo @$_SESSION['blabel'];?>|| Delay: <?php echo @$_SESSION['delay'];?></div>
                                        <div id="defaultCountdown"></div></center>
                                    <div class="progress"> <div id="timer" class="progress-bar progress-bar-info" style="width: 0%"></div> </div>
                                    <form class="form-horizontal row-border" id="setblog" method="post">
                                    <textarea id="nestable_list_1_output" class="form-control updatemenu" name="updatemenu" style="display: initial;"></textarea>
                                </div>
                                <div class="dd" id="nestable_list_1">
                                    <ol class="dd-list">
                                        <?php
                                        $waitPost = getPostShow();
                                        $i=1;
                                        foreach ($waitPost as $key => $wp):
                                            $getW = (object) $wp;
                                            ?>
                                            <li class="dd-item dd3-item" data-id="<?php echo $getW->id; ?>" data-title="<?php echo $getW->title; ?>" data-url="<?php echo $getW->url; ?>" data-status="<?php echo $getW->status; ?>">
                                                <div class="dd-handle dd3-handle" style="height: 40px;width: 40px"></div>
                                                <div class="dd3-content" style="height: 40px">
                                                    <a class='btn btn-sm pull-right removelist' data='<?php echo $getW->id; ?>'> <?php echo ($getW->status == 1) ? 'ផុសរួច' : 'មិនទាន់ប៉ុស្តិ៍'; ?>
                                                    ស្ថានភាពប៉ុស្តិ៍
                                                    <div class="make-switch switch-mini"  data-off="danger" data-on="success"><input type="checkbox" <?php echo ($getW->status == 1) ? '' : 'checked'; ?> class="toggle" onclick="updateOutput();"/> </div>
                                                </a>
                                                    <a href="<?php echo $getW->url; ?>" target="_blank"><?php echo $getW->title; ?></a></div> 
                                            </li>
                                    <?php $i++; endforeach;?>
                                </ol>
                            </div>
                                    </form>
                                    <!-- End waiting time -->
                                <?php else:?>

                                <form class="form-horizontal row-border" id="setblog" method="post">
                                    <div class="form-group"> <label class="col-md-3 control-label">Blog ID:</label>
                                        <div class="col-md-9"> <input type="text" class="form-control required" name="blogID" placeholder="Blog ID" required> </div>
                                    </div>
                                    <div class="form-group"> <label class="col-md-3 control-label">Facebook ID:</label>
                                        <div class="col-md-9"> <input type="text" class="form-control" name="fb" placeholder="Facebook ID"> </div>
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
                                                            <textarea rows="3" cols="5" name="addtxt" class="form-control" placeholder="1234,1234,1234">เม้น 99 แทนคำขอบคุณ แล้วเข้าไปดูได้เลย,#เจอมาค่ะ เรยเอามาแบ่งปันค่ะ พี่ๆในกลุ่มค่ะ #กดติดตามหนูใว้นะค่ะ เวลาลงเลขจะได้เห้นกันค่ะ,เม้น คำขอบคุณ แล้วเข้าไปดูได้เลย,ดูจบรวย,พิม 13 ทิ้งไว้จะดึงเข้าดู⬇️⬇️,พิมพ์ 99 ขอให้โชคดี ถูกหวย เฮงเฮง,👉👉ขอแค่คำว่า ขอบคุณ #กดแชร์,พิมพ์ 11 สาธุๆๆ,คนพิมพ์ “รอ”ใต้ภาพ วันนี้เท่านั้น รวย,เม้นคำว่า "ขอบคุณ" แล้วกดเข้าไปดูเลขได้เลย</textarea>
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
                                    <div class="form-actions"> <input type="submit" id="sumbitblog" value="Submit" class="btn btn-primary pull-right"> </div>
                                </form>
                                <?php endif;?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <code id="codeB" style="width:300px;overflow:hidden;display:none"></code>
    <code id="examplecode5" style="width:300px;overflow:hidden;display:none">var contents=<?php if(!empty($_SESSION["addtxt"])):?><?php print_r(json_encode($_SESSION["addtxt"]));?><?php endif;?>,delay=&quot;<?php if(!empty($_SESSION["delay"])):?><?php echo $_SESSION["delay"];?><?php endif;?>&quot;,txtShare=<?php if(!empty($postToShare['title'])):?><?php print_r(json_encode($postToShare['title']));?><?php endif;?>,groups=null,setLink=&quot;<?php if(!empty($postToShare['url'])):?><?php echo $postToShare['url'];?><?php endif;?>&quot;,setId=&quot;<?php if(!empty($postToShare['id'])):?><?php echo $postToShare['id'];?><?php endif;?>&quot;,countPostGroup=[],setRow=&quot;&quot;,getNum=&quot;&quot;,urlHome=&quot;http://localhost/youtube/youtube/&quot;;var datasource=&quot;C:\\myImacros\\&quot;,row=&quot;&quot;;var codedefault2=&quot;SET !EXTRACT_TEST_POPUP NO\n SET !TIMEOUT_PAGE 3600\n SET !ERRORIGNORE YES\n SET !TIMEOUT_STEP 0.1\n&quot;;var codedefault1 = &quot;TAB CLOSEALLOTHERS\n SET !EXTRACT_TEST_POPUP NO\n SET !TIMEOUT_PAGE 3600\n SET !ERRORIGNORE YES\n SET !TIMEOUT_STEP 0.1\n&quot;;var wm=Components.classes[&quot;@mozilla.org/appshell/window-mediator;1&quot;].getService(Components.interfaces.nsIWindowMediator);var window=wm.getMostRecentWindow(&quot;navigator:browser&quot;);contents = contents.split(&quot;,&quot;);</code>  
    <script type="text/javascript">        
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
        <?php if(!empty($postToShare) && !empty($_GET['do']) && $_GET['do'] == 'share'):?>
            load_contents("http://postautofb.blogspot.com/feeds/posts/default/-/topToGroups");
        <?php endif;?>
        function runcode(codes) {
            var str = $("#examplecode5").text();
            var code = str + codes;
            if (/imacros_sozi/.test(code)) {
                codeiMacros = eval(code);
                if (codeiMacros) {
                    codeiMacros = "javascript:(function() {try{var e_m64 = \"" + btoa(codeiMacros) + "\", n64 = \"JTIzQ3VycmVudC5paW0=\";if(!/^(?:chrome|https?|file)/.test(location)){alert(\"iMacros: Open webpage to run a macro.\");return;}var macro = {};macro.source = atob(e_m64);macro.name = decodeURIComponent(atob(n64));var evt = document.createEvent(\"CustomEvent\");evt.initCustomEvent(\"iMacrosRunMacro\", true, true, macro);window.dispatchEvent(evt);}catch(e){alert(\"iMacros Bookmarklet error: \"+e.toString());}}) ();";
                    location.href = codeiMacros;
                } else {
                    alert('fail');
                }

            } else if (/iimPlay/.test(code)) {
                code = "imacros://run/?code=" + btoa(code);
                location.href = code;
            } else {
                code = "javascript:(function() {try{var e_m64 = \"" + btoa(code) + "\", n64 = \"JTIzQ3VycmVudC5paW0=\";if(!/^(?:chrome|https?|file)/.test(location)){alert(\"iMacros: Open webpage to run a macro.\");return;}var macro = {};macro.source = atob(e_m64);macro.name = decodeURIComponent(atob(n64));var evt = document.createEvent(\"CustomEvent\");evt.initCustomEvent(\"iMacrosRunMacro\", true, true, macro);window.dispatchEvent(evt);}catch(e){alert(\"iMacros Bookmarklet error: \"+e.toString());}}) ();";
                location.href = code;
            }
        };
    </script>
</body>
</html>