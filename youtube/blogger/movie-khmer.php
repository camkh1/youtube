<?php
include dirname(__FILE__) .'/../top.php';
include dirname(__FILE__) .'/../library/simple_html_dom.php';
include dirname(__FILE__) .'/../library/blogger.php';
$log_id = $_SESSION['user_id'];
$site = new blogger();
$file = new file();
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
        $title = trim($videoInfoArr[0]);
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
    /*for test*/
    // $link = 'https://www.video4khmer36.com/watch/khmer-chinese-drama/sneah-mun-thngai-licht-hd-part-13-video-1639897.html';
    // $title = trim('Sneah Mun Thngai Licht [HD]');
    // $setLabel = 'china';
    // $part = 13;
    /*End for test*/
    if(!empty($link)) {
        $checkForDup = preg_replace("/[^a-zA-Z0-9]+/", " ", $title);
        $checkForDup = preg_replace('/\s+/', '-', $checkForDup);
        $checkForDup = strtolower( $checkForDup );
        $checkForDup = $setLabel .'-'. trim($checkForDup).'.json';
        $parse = parse_url($link);
        $_SESSION['fsite'] = $host = explode('.', $parse['host'])[1];

        /*Check for post exist*/
        // if(!empty($title)) {
            
        //     $upload_path = dirname(__FILE__) . '/../uploads/posts/'.$_SESSION['user_id'] . '/'.$host.'/';
        //     $files1 = array_diff(scandir($upload_path), array('..', '.'));
        //     foreach ($files1 as $key => $fd) {
        //         $ch = explode('_', $fd);
        //         if($ch[1] == $checkForDup) {
        //             echo 'found';
        //             die;
        //             $current = date("Y-m-d");
        //             $date = date ("Y-m-d", filemtime($upload_path.$fd));
        //             if($current == $date) {
        //                 continue;
        //             } else {
        //                 break;
        //             }
        //         }
        //     }
        // }
        /*End Check for post exist*/
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
        if (!file_exists(dirname(__FILE__) . '/../uploads/posts/'.$_SESSION['fsite'])) {
            mkdir(dirname(__FILE__) . '/../uploads/posts/'.$_SESSION['fsite'], 0700);
        }
        $upload_path = dirname(__FILE__) . '/../uploads/posts/'.$_SESSION['fsite'].'/';
        echo $checkForDup .' '. $link .'<br/>';
        $_SESSION['postFile'] = $upload_path.$checkForDup;
        if(file_exists($upload_path.$checkForDup)) {
            echo 'exist<br/>';
            $current = date("Y-m-d");
            $date = date ("Y-m-d", filemtime($upload_path.$checkForDup));
            /*End get all video from link*/
            if($current == $date) {
                continue;
            } else {
                $vdoInfo = $file->getFileContent($upload_path.$checkForDup,'json');
                $uniq_id = $vdoInfo->pid;
                $bid = $vdoInfo->bid;
                $_SESSION['id_edit'] = $vdoInfo->bid;
                /*get all video from link*/
                if(!empty($numid[1])) {
                    $arrContextOptions=array(
                        "ssl"=>array(
                            "verify_peer"=>false,
                            "verify_peer_name"=>false,
                        ),
                    ); 
                    for ($n=0; $n < intval($part); $n++) {
                        $setNum = $n+1; 
                        $glink = $sp[$n];
                        if(intval($setNum) <=count(@$vdoInfo->list)) {
                            if($vdoInfo->list[$n]->part == $setNum) {
                                if(!empty($vdoInfo->list[$n]->vid)) {
                                    $vdoList[$setNum] = array(
                                        'vid'  => $vdoInfo->list[$n]->vid,
                                        'vtype' => $vdoInfo->list[$n]->vtype
                                    );
                                } else {
                                    //echo 'not found <br/>';
                                    $con = file_get_html($glink, false, stream_context_create($arrContextOptions));
                                    $code = $con->find('.embed-responsive-item iframe', 0)->src;
                                    $data_list = $site->get_video_id($code);
                                    $vdoList[$setNum] = $data_list;
                                }
                            }
                        } else {
                            //echo $setNum .' get mew <br/>';
                            $con = file_get_html($glink, false, stream_context_create($arrContextOptions));
                            $code = $con->find('.embed-responsive-item iframe', 0)->src;
                            $data_list = $site->get_video_id($code);
                            $vdoList[$setNum] = $data_list;
                        }
                    }
                }
                // /*End get all video from link*/
            }
        } else {
            /*search list from google*/
            // $url = "https://www.google.com/search?q=".preg_replace('/\s+/', '+', trim($title));
            // $options = array(
            //   'http'=>array(
            //     'method'=>"GET",
            //     'header'=>"Accept-language: en\r\n" .
            //               "Cookie: foo=bar\r\n" .  // check function.stream-context-create on php.net
            //               "User-Agent: Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.102011-10-16 20:23:10\r\n" // i.e. An iPad 
            //   )
            // );
            // $context = stream_context_create($options);
            // $s = file_get_html($url, false, $context);
            // foreach ($s->find('a') as $re) {
            //     if(preg_match('/\/url\?q=/', $re->href)) {
            //         parse_str( parse_url( $re->href, PHP_URL_QUERY ), $my_array_of_vars );
            //         $srel = $my_array_of_vars['q'];
            //         echo $re->href.'<br/>';
            //         parse_str( parse_url( $srel, PHP_URL_QUERY ), $my_vars );
            //         $rel = @$my_vars['u'];
            //         if(!preg_match('/'.$host.'/', $rel) && !preg_match('/google/', $rel) && !preg_match('/youtube/', $rel) ) {
            //             $arrContextOptions=array(
            //                 "ssl"=>array(
            //                     "verify_peer"=>false,
            //                     "verify_peer_name"=>false,
            //                 ),
            //             ); 
            //             $listA = file_get_html($rel, false, stream_context_create($arrContextOptions));
            //             if (preg_match('/og:image/', $listA)) {
            //                 $thumbIn = $listA->find('meta[property=og:image]', 0)->content;
            //             }
            //             $listv = $site->getsitecontent($listA);
            //             //echo $rel.' thumb: '.$thumbIn.'<br/>';
            //             break;
            //         }
                    
            //         //echo $re->href.'<br/>';
            //     }
            // }
            /*End search list from google*/
            /*get all video from link*/
            if(!empty($numid[1]) && !empty($sp)) {
                for ($n=0; $n < intval($part); $n++) {
                    $setNum = $n+1; 
                    $glink = $sp[$n];
                    // if(intval($part) <=count(@$listv) && end($sp) != $sp[$n]) {
                    //     $vdoList[$setNum] = array(
                    //         'vid'  => $listv[($n+1)]['vid'],
                    //         'vtype' => $listv[($n+1)]['vtype']
                    //     );
                    // }
                    // if(intval($part)>count(@$listv)) {
                    //     $arrContextOptions=array(
                    //         "ssl"=>array(
                    //             "verify_peer"=>false,
                    //             "verify_peer_name"=>false,
                    //         ),
                    //     ); 
                    //     $con = file_get_html($glink, false, stream_context_create($arrContextOptions));
                    //     $code = $con->find('.embed-responsive-item iframe', 0)->src;
                    //     $data_list = $site->get_video_id($code);
                    //     $vdoList[$setNum] = $data_list;
                    // }
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
            /*End create file to post*/
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
                if(empty($value['vid'])) {
                    echo '<script language="javascript" type="text/javascript">window.setTimeout( function(){location.reload()}, 5000 );</script>';
                    exit();
                    break;
                }
                $viddata[] = array(
                    'vid' => $value['vid'],
                    'part' => $i,
                    'vtype' => $v_type
                );
            }
        }
        if (empty($viddata)) {
            echo 'no vdoList';
            die;
        }
        $thumbIn = (!empty($thumbIn) ? $thumbIn : $thumb);
        $thumbIn = $site->resize_image($thumbIn,0);
        if(empty($uniq_id)) {
            $str = time();
            $str = md5($str);
            $uniq_id = substr($str, 0, 9);   
        }
        $jsonTxt = dirname(__FILE__) . '/../uploads/files/blogs/blogid.csv';
        $getBlogId = $file->getFileContent($jsonTxt);

        $arrSearch = array(); 
        if(!empty($_SESSION['id_edit'])) {
            $blogEdit = dirname(__FILE__) . '/../uploads/blogger/posts/' . $_SESSION['id_edit'].'.csv';
            $getEditBlogId = $file->getFileContent($blogEdit);
            foreach ($getEditBlogId as $values) {
                $gpid = @$values->bname;
                $arrSearch[] = array(
                    'bid' =>@$values->bid,
                    'pid'=> @$values->bname
                );
            }
        }
        $bidArr = [];
        foreach ($getBlogId as $value){
            $i++;
            $bidArr[] = array(
                'bid'=> trim(str_replace('﻿', '', $value->bid)),
                'pid'=> searchForId($value->bid, $arrSearch),
                'status'=>0); 
        }
        $label_add      = addslashes(@$New_label);

        /*save file to local*/
        $file_name = preg_replace("/[^a-zA-Z0-9]+/", " ", $title);
        $file_name = preg_replace('/\s+/', '-', $file_name);
        $file_name = strtolower( $file_name );

        if (!file_exists(dirname(__FILE__) . '/../uploads/posts/'.$host)) {
            mkdir(dirname(__FILE__) . '/../uploads/posts/'.$host, 0700);
        }
        $upload_path = dirname(__FILE__) . '/../uploads/posts/'.$host.'/';
        $file_post = $file_name = $setLabel .'-'. trim($file_name).'.json';
        $_SESSION['file_name'] = trim($file_name);
        $title = trim($title) . ' id ' . $uniq_id;
        $title = trim($title) . ' || part ' . '[ '.@count($viddata).' ]';
        if(!empty($vdoInfo)) {
            $post_data = array(
                'title'     => $vdoInfo->title,
                'type'     => 'vdolist',
                'object_id' => $vdoInfo->object_id,
                'pid' => $vdoInfo->pid,
                'image' => array(
                    'url'=>@$vdoInfo->image->url,
                    'upload_status'=>$vdoInfo->image->upload_status
                ),
                'label'     => @$Cates,
                'list'     => @$viddata,
                'file_name'     => $vdoInfo->file_name,
                'link'     => @$vdoInfo->link,
                'bid'     => @$vdoInfo->bid,
            );
        } else {
            $post_data = array(
                'title'     => trim($title),
                'type'     => 'vdolist',
                'object_id' => $log_id,
                'pid' => $uniq_id,
                'image' => array(
                    'url'=>@$thumbIn,
                    'upload_status'=>false
                ),
                'label'     => @$Cates,
                'list'     => @$viddata,
                'file_name'     => $_SESSION['file_name'],
                'link'     => $link,
            );
        }
           
        /*End save file to local*/

        /*create file to post*/
        $bodytext = $site->Playlist($viddata,$title, $thumbIn);
        $breaks          = array("\r\n", "\n", "\r");
        $bodytext_normal = str_replace($breaks, "", $bodytext);
        $addOnBody       = '<a href="' . $thumb . '" target="_blank"><img border="0" id="noi" src="' . $thumb . '" /></a><!--more--><meta property="og:image" content="' . $thumb . '"/><link href="' . $thumb . '" rel="image_src"/>';
        $bodytext        = str_replace($breaks, "", $bodytext);
        $bodytext_normal = $bodytext;
        $bodytext = $addOnBody . $bodytext;
        $dataPost = array(
            'blogid' => $bidArr,
            'title' => $title,
            'image' => $thumbIn,
            'body' => $bodytext,
            'label' => $Cates,
            'uniq_id' => $uniq_id,
        );
        $upload_path = dirname(__FILE__) . '/../uploads/user/';
        $file_name = 'post-action.json';
        $jsonPost = $file->json($upload_path,$file_name, $dataPost);
        $upload_path = dirname(__FILE__) . '/../uploads/posts/'.$_SESSION['fsite'].'/';
        if(file_exists($upload_path.$checkForDup)) {
            unlink($upload_path.$checkForDup);
            echo $checkForDup;
            $csv = $file->json($upload_path,$file_post, $post_data);
            if($csv) {
                echo $vdoInfo->bid;
                if(!empty($_SESSION['id_edit'])) {
                    // header('Location: ' . base_url . '/blogger/edit.php?id='.$vdoInfo->bid); 
                    // die;
                    $back = urlencode(base_url . '/blogger/edit.php?do=post&id='.$vdoInfo->bid);
                    echo '<script language="javascript" type="text/javascript">window.setTimeout( function(){window.location = "'.base_url . 'login.php?renew=1&back='.$back.'";}, 30 );</script>';
                    exit();
                } else {
                    echo 222;
                    // header('Location: ' . base_url . 'login.php?renew=1&back='.urlencode(base_url . 'blogger/post.php?do=post'));
                    // die;
                    $back = urlencode(base_url . 'blogger/post.php?do=post');
                    echo '<script language="javascript" type="text/javascript">window.setTimeout( function(){window.location = "'.base_url . 'login.php?renew=1&back='.$back.'";}, 30 );</script>';
                    exit();
                }   
            }
        } else {
            echo 'not ';
            echo $checkForDup;
            $csv = $file->json($upload_path,$file_post, $post_data);
            if($jsonPost) {
                //header('Location: ' . base_url . 'login.php?renew=1&back='.urlencode(base_url . 'blogger/post.php?do=post'));
                $back = urlencode(base_url . 'blogger/post.php?do=post');
                echo '<script language="javascript" type="text/javascript">window.setTimeout( function(){window.location = "'.base_url . 'login.php?renew=1&back='.$back.'";}, 30 );</script>';
                exit();
                //header('Location: ' . base_url . 'blogger/post.php?do=post');
            }
        }
        break;
        die;
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
function searchForId($id, $array) {
   foreach ($array as $key => $val) {
    $pos = strpos($val['bid'], $id);
    if ($pos === false) {
    } else {
        return @$val['pid'];
    }
   }
   return null;
} 