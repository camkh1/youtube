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
unset($_SESSION['goback']);
libxml_set_streams_context($context);
$arrContextOptions=array(
    "ssl"=>array(
        "verify_peer"=>false,
        "verify_peer_name"=>false,
    ),
);

// $headers = @get_headers(get_from_feed);
// var_dump($headers);
// if(strpos($headers[0],'404') === false)
// {
//  echo get_from_feed;
// }
// die;
$context = stream_context_create(
    array(
        "http" => array(
            "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36"
        ),
            "ssl"=>array(
            "verify_peer"=>false,
            "verify_peer_name"=>false,
        )
    )
);
$html = file_get_html(get_from_feed, false, $context);
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
    $videoInfoArr = explode('ភាគ ', $videoInfo);
    if(!empty($videoInfoArr[1])) {
        $part = $videoInfoArr[1];
        $part = trim($part);
        if (preg_match ( '/ចប់/', $part )) {
            $isEnd = true;
            $LabelStatus = 'End';
        }
        $part = str_replace('ចប់', '', $part);
    }

    $link = $e->find('.video-info a', 0)->href;
    //$link = 'https://www.video4khmer37.com/title/khmer-korean-drama-om-nach-toek-prak-album-10934-page-1.html';
    // $link = 'https://www.video4khmer36.com/watch/khmer-chinese-drama/chub-pheak-jum-peak-sneah-hd-part-50end-video-1553686.html';
    // $part = 50;
    

    /*get english title*/
    preg_match("/khmer-lakhon-(.*?)(?:embed|-album-|-part-)|khmer-chinese-drama-(.*?)(?:embed|-album-|-part-)|khmer-thai-lakhorn-drama\/(.*?)(?:embed|-album-|-part-)|thai-lakhon-(.*?)(?:embed|-album-|-part-)|khmer-korean-drama-(.*?)(?:embed|-album-|-part-)/",$link,$matches);
    if(!empty($matches[1])) {
        $title = ucwords(str_replace('-', ' ', $matches[1]));
        $setFile = $matches[1];
    }
    if(!empty($matches[2])) {
        $title = ucwords(str_replace('-', ' ', $matches[2]));
        $setFile = $matches[2];
    }
    if(!empty($matches[4])) {
        $title = ucwords(str_replace('-', ' ', $matches[4]));
        $setFile = $matches[4];
    }
    if(!empty($matches[5])) {
        $title = ucwords(str_replace('-', ' ', $matches[5]));
        $setFile = $matches[5];
    }
    if(!empty($matches[6])) {
        $title = ucwords(str_replace('-', ' ', $matches[6]));
        $setFile = $matches[6];
    }
    echo '<a href="'.$link.'">'.$videoInfoArr[0].' - '.$part.' '.$c.'</a><br/>';
    echo $title.'<br/>';
    /*ENd get english title*/

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
    if(preg_match('/India/', $c)) {
        $setLabel = 'India';
    }


    if(preg_match('/រឿងភាគ/', $c)) {
        $LabelType = 'Drama';
    }
    if(preg_match('/Thai Lakhon/', $c)) {
        $LabelType = 'Drama';
    }
    if(preg_match('/រឿងភាគថៃ/', $c)) {
        $setLabel = 'thai';
    }
    if(preg_match('/រឿងភាគខ្មែរ/', $c)) {
        $setLabel = 'khmer';
    }
    if(preg_match('/រឿងភាគកូរ៉េ/', $c)) {
        $setLabel = 'Korea';
    }
    if(preg_match('/រឿងភាគចិន/', $c)) {
        $setLabel = 'china';
    }
    echo $c . ' = '. $setLabel.' - '.$LabelType.'<br/>';
    $Cates = $site->getLabelBySpec($setLabel ,$LabelType, $LabelStatus);
    /*End set Label*/
    /*for test*/
    /*End for test*/
    if(!empty($link)) {
        echo '<br/><br/><br/>==========================<br/>';
        // $checkForDup = preg_replace("/[^a-zA-Z0-9]+/", " ", $title);
        // $checkForDup = preg_replace('/\s+/', '-', $checkForDup);
        // $checkForDup = strtolower( $checkForDup );
        $checkForDup = $setLabel .'-'. trim($setFile).'.json';
        $parse = parse_url($link);
        $_SESSION['fsite'] = $host = explode('.', $parse['host'])[1];
        $_SESSION['url_id'] = $link;

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
        preg_match("/-album-/",$link,$chpart);
        if(!empty($chpart[0])) {
            if($chpart[0] == '-album-') {
                $plink = $link;
            }
        }
        $vdoList = array();
        if(empty($plink)) {        
            $p = file_get_html($link, false, $context);
            $plink = $p->find('.content .card-text .text-uppercase', 0)->href;
            if($part>1 && empty($plink)) {
                $p = file_get_html($link, false, $context);
                $plink = $p->find('.content .card-text .text-uppercase', 0)->href;
            }
            $text = $p->find('.content .card-text .text-uppercase', 0)->plaintext;
        }
        $sps = array();
        $sp = getnext($plink,$sps);
        echo 'count video list: ' . count($sp).'<br/>';
        /*save file to local*/
        $file_name = $checkForDup;
        if (!file_exists(dirname(__FILE__) . '/../uploads/posts/'.$_SESSION['fsite'])) {
            mkdir(dirname(__FILE__) . '/../uploads/posts/'.$_SESSION['fsite'], 0700);
        }
        $upload_path = dirname(__FILE__) . '/../uploads/posts/'.$_SESSION['fsite'].'/';
        echo $checkForDup .' '. $link .'<br/>';
        $_SESSION['postFile'] = $upload_path.$checkForDup;
        echo dirname(__FILE__).'<br/>';
        echo $upload_path.$checkForDup;
        if(file_exists($upload_path.$checkForDup)) {
            echo '<br/>file_exists';
            $current = date("Y-m-d");
            $date = date ("Y-m-d", filemtime($upload_path.$checkForDup));
            /*End get all video from link*/ 
            $str = file_get_contents($upload_path.$checkForDup);
            $jsonFile = json_decode($str);
            if(count($sp) == count($jsonFile->list)) {
                echo '<br/>list == list';
            }
            if($current == $date) {
                echo '<br/>$current == $date';
            } else {
                echo '<br/>not $current == $date';
            }
        } else {
            echo '<br/>not file_exists';
        }
        echo '<br/>';
        if(file_exists($upload_path.$checkForDup)) {
            echo 'exist<br/>';
            $current = date("Y-m-d");
            $date = date ("Y-m-d", filemtime($upload_path.$checkForDup));
            /*End get all video from link*/
            $str = file_get_contents($upload_path.$checkForDup);
            $jsonFile = json_decode($str); 
            if(count($sp) == count($jsonFile->list)) {
                continue;
            }    
            if($current == $date) {
                continue;
            } else {
                $vdoInfo = $file->getFileContent($upload_path.$checkForDup,'json');
                if(count($vdoInfo->list) == $part) {
                    continue;
                }             
                $uniq_id = $vdoInfo->pid;
                $bid = $vdoInfo->bid;
                $_SESSION['id_edit'] = $vdoInfo->bid;
                /*get all video from link*/
                if(!empty($part)) {
                    $arrContextOptions=array(
                        "ssl"=>array(
                            "verify_peer"=>false,
                            "verify_peer_name"=>false,
                        ),
                    ); 
                    for ($n=0; $n < count($sp); $n++) {
                        $setNum = $n+1; 
                        $glink = @$sp[$n];
                        if(!empty($sp[$n])) {
                            if(intval($setNum) <=count(@$vdoInfo->list)) {
                                if($vdoInfo->list[$n]->part == $setNum) {
                                    if(!empty($vdoInfo->list[$n]->vid)) {
                                        $vdoList[$setNum] = array(
                                            'vid'  => $vdoInfo->list[$n]->vid,
                                            'vtype' => $vdoInfo->list[$n]->vtype
                                        );
                                    } else {
                                        //echo 'not found <br/>';
                                        $con = file_get_html($glink, false, $context);
                                        $code = $con->find('.embed-responsive-item iframe', 0)->src;
                                        $data_list = $site->get_video_id($code);
                                        $vdoList[$setNum] = $data_list;
                                    }
                                }
                            } else {
                                //echo $setNum .' get mew <br/>';
                                echo $glink.'<br/>';
                                $code = file_get_html($glink, false, $context);
                                $data_list = $site->get_video_id($code);
                                $vdoList[$setNum] = $data_list;
                            }
                        } else {
                            continue;
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
            if(!empty($part) && !empty($sp)) {
                for ($n=0; $n < count($sp); $n++) {
                    $setNum = $n+1; 
                    $glink = @$sp[$n];
                    echo @$sp[$n]. ' - ' .$n.' <br/>';
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
                    
                    if(!empty($glink)) {
                        $con = file_get_html($sp[$n], false, $context);
                        $code = $con->find('section.content iframe', 0)->src;
                        $data_list = $site->get_video_id($code);
                        $vdoList[$setNum] = $data_list;
                    } else {
                        continue;
                    }
                    
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
        $file_name = $checkForDup;

        if (!file_exists(dirname(__FILE__) . '/../uploads/posts/'.$host)) {
            mkdir(dirname(__FILE__) . '/../uploads/posts/'.$host, 0700);
        }
        $upload_path = dirname(__FILE__) . '/../uploads/posts/'.$host.'/';
        $file_post = $file_name = trim($file_name);
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
        $_SESSION['goback'] = base_url . 'close.php';
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
    $i++;
}
echo '<script language="javascript" type="text/javascript">window.setTimeout( function(){window.location = "'.base_url . 'close.php";}, 1000 );</script>';
exit();
function getnext($plink,$sp)
{
    $context = stream_context_create(
        array(
            "http" => array(
                "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36"
            ),
                "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            )
        )
    );
    $gl = file_get_html($plink, false, $context);
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
        if($nt =='ទំព័របន្ទាប់') {
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