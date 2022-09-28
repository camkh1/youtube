<?php
include dirname(__FILE__) .'/../top.php';
include dirname(__FILE__) .'/../library/simple_html_dom.php';
include dirname(__FILE__) .'/../library/blogger.php';
$log_id = $_SESSION['user_id'];
$site = new blogger();
$context = stream_context_create(array('ssl'=>array(
    'verify_peer' => false, 
    "verify_peer_name"=>false
    )));

libxml_set_streams_context($context);
$html = file_get_html(get_from_feed);
$i = 0;
$creat_arr = array();
$label = '';
$isEnd = false;
$LabelStatus = 'Continue';
foreach ($html->find('.video-item') as $e) {
    $isEnd = false;
    $thumb = $e->find('.video-thumb img', 0)->src;
    $videoInfo = $e->find('.video-info a', 0)->innertext;
    $c = $e->find('.video-info .text-muted', 0)->plaintext;
    $videoInfoArr = explode(' - ', $videoInfo);
    if(!empty($videoInfoArr[0])) {
        $title = $videoInfoArr[0];
    }
    if(!empty($videoInfoArr[1])) {
        $part = $videoInfoArr[1];
        $part = str_replace('part ', '', $part);
        if (preg_match ( '/End/', $part )) {
            $isEnd = true;
            $LabelStatus = 'End';
        }
        $part = str_replace('End', '', $part);
    }

    $link = $e->find('.video-info a', 0)->href;
    // $link = 'https://www.video4khmer36.com/watch/khmer-chinese-drama/chub-pheak-jum-peak-sneah-hd-part-50end-video-1553686.html';
    // $part = 50;
    echo '<a href="'.$link.'">'.$title.' - '.$part.' '.$c.'</a><br/>';

    /*set Label*/
    $LabelType = 'Short';
    if(preg_match('/Drama/', $c)) {
        $LabelType = 'Drama';
    }
    if(preg_match('/Thai Lakhon/', $c)) {
        $LabelType = 'Drama';
    }
    if(preg_match('/Thai Lakhon/', $c)) {
        $setLabel = 'thai';
    }
    if(preg_match('/Khmer Drama Lakhon/', $c)) {
        $setLabel = 'khmer';
    }
    if(preg_match('/Khmer Korean Drama/', $c)) {
        $setLabel = 'Korea';
    }
    if(preg_match('/Chinese Drama/', $c)) {
        $setLabel = 'china';
    }
    if(preg_match('/India/', $c)) {
        $setLabel = 'India';
    }
    $Cates = $site->getLabelBySpec($setLabel ,$LabelType, $LabelStatus);
    /*End set Label*/

    if(!empty($link)) {  
        $vdoList = array();      
        $numid = explode('-video-', $link);        
        $p = file_get_html($link);
        $plink = $p->find('.content .card-text .text-uppercase', 0)->href;
        $text = $p->find('.content .card-text .text-uppercase', 0)->plaintext;
        $sps = array();
        $sp = getnext($plink,$sps);
        /*save file to local*/
        $file_name = preg_replace("/[^a-zA-Z0-9]+/", " ", trim($title));
        $file_name = preg_replace('/\s+/', '-', $file_name);
        $file_name = strtolower( $file_name );
        $parse = parse_url($link);
        $_SESSION['fsite'] = $host = explode('.', $parse['host'])[1];
        if (!file_exists(dirname(__FILE__) . '/../uploads/posts/'.$_SESSION['user_id'] . '/'.$host)) {
            mkdir(dirname(__FILE__) . '/../uploads/posts/'.$_SESSION['user_id'] . '/'.$host, 0700);
        }
        $upload_path = dirname(__FILE__) . '/../uploads/posts/'.$_SESSION['user_id'] . '/'.$host.'/';
        $file_name = trim($file_name).'.json';
        if(file_exists($upload_path.$file_name)) {
            echo 'exist<br/>';
        } else {
            /*search list from google*/
            $url = "https://www.google.com/search?q=".preg_replace('/\s+/', '+', trim($title));
            $options = array(
              'http'=>array(
                'method'=>"GET",
                'header'=>"Accept-language: en\r\n" .
                          "Cookie: foo=bar\r\n" .  // check function.stream-context-create on php.net
                          "User-Agent: Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.102011-10-16 20:23:10\r\n" // i.e. An iPad 
              )
            );
            $context = stream_context_create($options);
            $s = file_get_html($url, false, $context);
            foreach ($s->find('a') as $re) {
                if(preg_match('/\/url\?q=/', $re->href)) {
                    parse_str( parse_url( $re->href, PHP_URL_QUERY ), $my_array_of_vars );
                    $srel = $my_array_of_vars['q'];
                    echo $re->href.'<br/>';
                    parse_str( parse_url( $srel, PHP_URL_QUERY ), $my_vars );
                    $rel = $my_vars['u'];
                    if(!preg_match('/'.$host.'/', $rel) && !preg_match('/google/', $rel) && !preg_match('/youtube/', $rel) ) {
                        echo $rel.'<br/>';
                        $arrContextOptions=array(
                            "ssl"=>array(
                                "verify_peer"=>false,
                                "verify_peer_name"=>false,
                            ),
                        ); 
                        $listA = file_get_html($rel, false, stream_context_create($arrContextOptions));
                        if (preg_match('/og:image/', $listA)) {
                            $thumbIn = $listA->find('meta[property=og:image]', 0)->content;
                        }
                        $list = $site->getsitecontent($listA);
                        //echo $rel.' thumb: '.$thumbIn.'<br/>';
                        break;
                    }
                    
                    //echo $re->href.'<br/>';
                }
            }
            /*End search list from google*/

            /*get all video from link*/
            if(!empty($numid[1])) {
                for ($n=0; $n < intval($part); $n++) {
                    $setNum = $n+1; 
                    $glink = $sp[$n];
                    if(intval($part) <=count(@$list) && end($sp) != $sp[$n]) {
                        $vdoList[$setNum] = array(
                            'list'  => $list[($n+1)]['list'],
                            'vtype' => $list[($n+1)]['vtype']
                        );
                    }
                    if(intval($part)>count(@$list)) {
                        $arrContextOptions=array(
                            "ssl"=>array(
                                "verify_peer"=>false,
                                "verify_peer_name"=>false,
                            ),
                        ); 
                        $con = file_get_html($glink, false, stream_context_create($arrContextOptions));
                        $code = $con->find('.embed-responsive-item iframe', 0)->src;
                        $data_list = $site->get_video_id($code);
                        $vdoList[$setNum] = $data_list;
                    }
                }
            }
            if (!empty($vdoList)) {
                $i = 0;
                $viddata=[];
                foreach ($vdoList as $value) {
                    $i++;
                    if (!empty($videotype)) {
                        $v_type = $videotype;
                    } else {
                        $v_type = $value['vtype'];
                    }
                    $viddata[] = array(
                        'vid' => $value['vid'],
                        'part' => $i,
                        'vtype' => $v_type
                    );
                }
            }
            $thumbIn = (!empty($thumbIn) ? $thumbIn : $thumb);
            $str = time();
            $str = md5($str);
            $uniq_id = substr($str, 0, 9);
            $title = trim($title) . ' id ' . $uniq_id;
            $data_vdo = array(
                'title'     => trim($title) . ' || part ' . '[ '.@count($viddata).' ]',
                'type'     => 'vdolist',
                'object_id' => $log_id,
                'image'     => $thumbIn,
                'label'     => @$New_label,
                'list'     => @$viddata,
                'pid'     => @$uniq_id,
            );
            var_dump($data_vdo);die;
            // $post_data = array(
            //     'title'     => trim($title),
            //     'type'     => 'vdolist',
            //     'object_id' => $log_id,
            //     'pid' => $list["pid"],
            //     'image' => array(
            //         'url'=>@$img,
            //         'upload_status'=>false
            //     ),
            //     'label'     => @$Cates,
            //     'list'     => $list,
            //     'file_name'     => $_SESSION['file_name'],
            //     'link'     => $link,
            //     'page'     => $_GET['page'],
            // );
            // $file = new file();
            // $csv = $file->json($upload_path,$file_name, $post_data);
            // $file = new file();
            // $csv = $file->json($upload_path,$file_name, $post_data);
        }
    }
    if($i==0) {
        break;
    }
    $i++;
}
function getnext($plink,$sp)
{
    $gl = file_get_html($plink);
    foreach ($gl->find('.content .cover-item .cover-desc') as $k => $l) {
        $llink = $l->find('a', 0)->href;
        preg_match('/-part-(.*?)-video-/', $llink, $match);
        if(!empty($match[1])) {
            $partl = $match[1];
            $sp[(intval($match[1])-1)] = $llink;
        } else {
            $sp[] = $llink;
        }
        //echo $llink.'<br/>';
    }
    $nextpage = false;
    foreach ($gl->find('.page-item a') as $n) {
        $nl = $n->href;
        $nt = $n->title;
        if($nt =='Next Page') {
            $nextpage = true;
            $sp = getnext($nl,$sp);
            break;
        }
    }
    return $sp;
}
