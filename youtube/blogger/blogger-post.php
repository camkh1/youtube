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

// $context = stream_context_create(
//     array(
//         "http" => array(
//             "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36"
//         ),
//             "ssl"=>array(
//             "verify_peer"=>false,
//             "verify_peer_name"=>false,
//         )
//     )
// );
// $html = file_get_html(blogger_feed, false, $context);

$context = stream_context_create(array('ssl'=>array(
    'verify_peer' => false, 
    "verify_peer_name"=>false
    )));

libxml_set_streams_context($context);
$id1 = simplexml_load_file(blogger_feed);
$sectionA = $id1->entry;
$i = 0;
$creat_arr = array();
$label = '';
foreach ($sectionA as $value) {
    $xmlns = $value->children('http://www.w3.org/2005/Atom');
    $title = (string) $value->title;
    $content = (string) $value->content;
    $category = $value->category;
    $LabelType = 'Short';
    $isEnd = true;
    $LabelStatus = 'End';
    foreach ($category as $glabel) {
        $c = $glabel["term"];
        /*set Label*/
        if(preg_match('/Drama/', $c)) {
            $LabelType = 'Drama';
        }
        if(preg_match('/Thai Drama/', $c)) {
            $setLabel = 'thai';
        }
        if(preg_match('/Khmer Drama/', $c)) {
            $setLabel = 'khmer';
        }
        if(preg_match('/Korean/', $c)) {
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
        if(preg_match('/On Air/', $c)) {
            $isEnd = false;
            $LabelStatus = 'Continue';
        }
        //echo $c . ' = '. $setLabel.' - '.$LabelType.'<br/>';
        /*End set Label*/
    }
    $Cates = $site->getLabelBySpec($setLabel ,$LabelType, $LabelStatus);

    $image = $value->children('http://search.yahoo.com/mrss/')->thumbnail->attributes();
    $thumb = (string) $image['url'];
    $regex = '/< *img[^>]*src *= *["\']?([^"\']*)/';
    foreach ($value->link as $links) {
        //var_dump($links);
        if($links['rel'] == 'alternate' ) {
            $link = (string) $links['href'];
        }
    }
    $linkc = $link;
    preg_match_all( $regex, $content, $matches );
    $ImgSrc = array_pop($matches);
    if(!empty($ImgSrc)) {
        foreach ($ImgSrc as $image) {
            $imagedd = strtok($image, "?");
        }
    }

    /*create data for post*/
    // $checkForDup = preg_replace("/[^a-zA-Z0-9]+/", " ", $title);
    // $checkForDup = preg_replace('/\s+/', '-', $checkForDup);
    // $checkForDup = strtolower( $checkForDup );
    $tinfo = explode(' [',$title);
    if(!empty($tinfo[0])) {
        $title = $tinfo[0];
        if(!empty($tinfo[1])) {
            $part = (int) filter_var($tinfo[1], FILTER_SANITIZE_NUMBER_INT);
        }
    }
    
    $checkForDup = slugify($title);
    $checkForDup = strtolower($setLabel) .'-'. trim($checkForDup).'.json';
    $parse = parse_url($link);
    $_SESSION['fsite'] = $host = explode('.', $parse['host'])[1];
    $_SESSION['url_id'] = $link;

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
        if(count($part) == count($jsonFile->list)) {
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
        if(count($part) <= count($jsonFile->list)) {
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
        }
    } else {
        
    }
    $str = str_replace(' ', '', $content);
    preg_match_all('{"file":(.*)}', $str, $matches);
    $prcot = @$matches[0][0];
    $prcot = str_replace(' ', '', $prcot);
    $prcot = str_replace('"file":"', '<a href="', $prcot);
    $prcot = str_replace("file':'", '<a href="', $prcot);
    $prcot = str_replace('","', '">link</a>', $prcot);
    $prcot = str_replace("','", '">link</a>', $prcot);
    $str = <<<HTML
'.$prcot.'
HTML;
    $part    = 0;
    $vdoList = array();
    $html    = str_get_html($str);

    foreach ($html->find('a') as $e) {
        $part++;
        $code = $e->href;
        if (!empty($videotype)) {
            $data_list = $site->get_video_id($code, $videotype);
            $v_id      = $data_list['vid'];
            $v_type    = $data_list['vtype'];
        } else {
            $data_list = $site->get_video_id($code);
            $v_id      = $data_list['vid'];
            $v_type    = $data_list['vtype'];
        }
        $vdoList[$part] = array(
            'vid'  => $v_id,
            'vtype' => $v_type,
        );
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
        continue;
    }
    $thumbIn = $site->resize_image($thumb,0);
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
    $_SESSION['goback'] = base_url . 'post_to_telegram.php';
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
    echo $thumbIn.'<br/><br/><br/>';
    break;
    die;
    /*End create data for post*/

    $i++;
}
echo '<script type="text/javascript">window.location = "' . base_url . 'close.php";</script>';
die;

$i = 0;
$creat_arr = array();
$label = '';
foreach ($html->find('.video-item') as $e) {


    if(!empty($link)) {
        echo '<br/><br/><br/>==========================<br/>';
        // $checkForDup = preg_replace("/[^a-zA-Z0-9]+/", " ", $title);
        // $checkForDup = preg_replace('/\s+/', '-', $checkForDup);
        // $checkForDup = strtolower( $checkForDup );
        $checkForDup = $setLabel .'-'. trim($setFile).'.json';
        $parse = parse_url($link);
        $_SESSION['fsite'] = $host = explode('.', $parse['host'])[1];
        $_SESSION['url_id'] = $link;

        preg_match("/-album-/",$link,$chpart);
        if(!empty($chpart[0])) {
            if($chpart[0] == '-album-') {
                $plink = $link;
            }
        }
        $vdoList = array();
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
                        // $con = file_get_html($sp[$n], false, $context);
                        // $code = $con->find('section.content iframe', 0)->src;
                        // $data_list = $site->get_video_id($code);
                        $code = file_get_html($glink, false, $context);
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
function slugify($text, string $divider = '-')
{
  // replace non letter or digits by divider
  $text = preg_replace('~[^\pL\d]+~u', $divider, $text);

  // transliterate
  $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

  // remove unwanted characters
  $text = preg_replace('~[^-\w]+~', '', $text);

  // trim
  $text = trim($text, $divider);

  // remove duplicate divider
  $text = preg_replace('~-+~', $divider, $text);

  // lowercase
  $text = strtolower($text);

  if (empty($text)) {
    return 'n-a';
  }

  return $text;
}