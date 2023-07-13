<?php
include dirname(__FILE__) .'/../top.php';
include dirname(__FILE__) .'/../library/simple_html_dom.php';
include dirname(__FILE__) .'/../library/blogger.php';
$page = !empty($_GET['page'])? '&page=' .$_GET['page'] :'';
$step = !empty($_GET['step'])? $_GET['step'] :1;
$log_id = $_SESSION['user_id'];
getByStep($step);
function getByStep($step='')
{
    $page = !empty($_GET['page'])? '&page=' .$_GET['page'] :'';
    $cate = !empty($_GET['cate'])? $_GET['cate'] :'';
    $step = !empty($_GET['step'])? $_GET['step'] :1;
    switch ($step) {
        case '1':
            $dataOne = getOne($cate,$page);
            if($dataOne) {
                echo '<script language="javascript" type="text/javascript">window.setTimeout( function(){window.location = "'.base_url.'blogger/thai-movies-doomovie-hd.php?step=2'.$page.'";}, 1000 );</script>';
                die;
            }
            break;
        case '2':
            $dataTwo = getTwo($page);
            if($dataTwo) {
                echo '<script language="javascript" type="text/javascript">window.setTimeout( function(){window.location = "'.base_url.'blogger/thai-movies-doomovie-hd.php?step=3";}, 1000 );</script>';
                die;
            }
            break;
        case '3':
            getThree();
            break;    
        default:
            // code...
            break;
    }
}
function getByCat($cate='',$page = '')
{
    if(!empty($cate)) {
        switch ($cate) {
            case '1':
                // code...
                break;
            
            default:
                // code...
                break;
        }
    }
    
    return getOne($url, $page);
}
function getOne($cate='',$page='')
{
    $url = 'https://www.doomovie-hd.com/?r=movie&cate='.$cate.$page;
    $_SESSION['page_url'] = $url;
    $context = stream_context_create(
        array(
            "http" => array(
                "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36",
                //"header" => "Cookie: lang=en",
            ),
                "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            )
        )
    );
    if(!empty($url)) {
        $html = file_get_html($url, false, $context);
    } else {
        $html = file_get_html(thai_movies . $page, false, $context);
    }
    $post = new stdClass();
    $defaultCat = array();
    switch ($cate) {
        case '2':
            $defaultCat[] = 'Movies';
            $defaultCat[] = 'American - Europe';
            break;
        case '13':
            $defaultCat[] = 'Movies';
            $defaultCat[] = 'American - Europe';
            $defaultCat[] = 'American - Europe Series';
            $defaultCat[] = 'Series Movies';
            break; 
        case '3':
            $defaultCat[] = 'Movies';
            $defaultCat[] = 'Netflix';
            break;
        case '8':
            $defaultCat[] = 'Movies';
            $defaultCat[] = 'Chinese Movies';
            $defaultCat[] = 'Series Movies';
            $defaultCat[] = 'Chinese Series';
            break;
        case '14':
            $defaultCat[] = 'Movies';
            $defaultCat[] = 'Chinese Movies';
            break;
        case '34':
            $defaultCat[] = 'Movies';
            $defaultCat[] = 'Korean Movies';
            break;
        case '12':
            $defaultCat[] = 'Movies';
            $defaultCat[] = 'Korean Movies';
            $defaultCat[] = 'Korean Series';
            $defaultCat[] = 'Series Movies';
            break; 
        case '6':
            $defaultCat[] = 'Movies';
            $defaultCat[] = 'Thai Movies';
            break; 
        case '11':
            $defaultCat[] = 'Movies';
            $defaultCat[] = 'Thai Movies';
            $defaultCat[] = 'Thai Series';
            $defaultCat[] = 'Series Movies';
            break; 
        case '35':
            $defaultCat[] = 'Movies';
            $defaultCat[] = 'Japanese Movies';
            break; 
        case '15':
            $defaultCat[] = 'Movies';
            $defaultCat[] = 'Japanese Movies';
            $defaultCat[] = 'Japanese Series';
            $defaultCat[] = 'Series Movies';
            break;
        case '37':
            $defaultCat[] = 'Movies';
            $defaultCat[] = 'Indian Movies';
            break; 
        case '16':
            $defaultCat[] = 'Movies';
            $defaultCat[] = 'Indian Movies';
            $defaultCat[] = 'Indian Series';
            $defaultCat[] = 'Series Movies';
            break;
        case '9':
            $defaultCat[] = 'Movies';
            $defaultCat[] = 'Cartoon Movies';
            break;
        case '16':
            $defaultCat[] = 'Movies';
            $defaultCat[] = 'Cartoon Movies';
            $defaultCat[] = 'Cartoon Series';
            $defaultCat[] = 'Series Movies';
            break;
        default:
            // code...
            break;
    }
    foreach ($html->find('section .movie-card') as $m) {
        $thumb = $m->find('img',0)->src;
        $views = $m->find('.movie-card-a .movie-card-views',0)->plaintext;
        $card_a = $m->find('.movie-card-a',0);
        $card_b = $m->find('.movie-card-b',0);
        if(preg_match('/container-4K/', $card_a)) {
            $q = '4K quality';
        } else if(preg_match('/container-FULL-HD/', $card_a)) {
            $q = 'FHD quality';
        } else if(preg_match('/container-HD/', $card_a)) {
            $q = 'HD quality';
        } else if(preg_match('/container-ZOOM/', $card_a)) {
            $q = 'ZOOM quality';
        }
        
        $linkinfo = $card_b->find('a',0);
        $link = $linkinfo->href;

        $title = $linkinfo->plaintext;

        $sound = $card_b->find('.movie-card-info-section-container div',0)->plaintext;
        $star = $card_b->find('.movie-card-info-section-container div',1)->plaintext;
        $duration = $card_b->find('.movie-card-info-section-container div',2)->plaintext;

        echo $thumb.'<br/>';
        echo 'views '.$views.'<br/>';
        
        echo 'quality '.$q.'<br/>';
        echo 'link '.$link.'<br/>';
        echo 'title '.$title.'<br/>';
        echo 'sound '.$sound.'<br/>';
        echo 'star :'.(int) $star.' = '.$star.'<br/>';
        echo 'duration '.$duration.'<br/>';
        $cat = array();
        $cat[] = 'in '.$sound;
        $cat[] = 'Star '.(int) $star;
        $cat[] = 'Star '.$star;

        $post->quality = $q;
        $post->link = $link;
        $post->title = $title;
        $post->title = $title;
        $post->thumb = $thumb;
        $post->views = trim($views);
        $post->duration = $duration;
        $post->sound = $sound;

        // $html_post = file_get_html($link, false, $context);
        // $labels = $html_post->find('article',0);
        // echo ($html_post);
        //echo 'Type '.$labels .'<br/>';
            //echo $m.'<br/>';
        $SeriesMovies =false;
        if(preg_match('/updated to/', $title) || preg_match('/Season/', $title) || preg_match('/ 1-/', $title)) {
            $cat[] = 'Series Movies';
            $SeriesMovies = true;
        } else {
            $checkCat = array_search('Series Movies',$defaultCat,true);
            if(empty($checkCat)) {
                $cat[] = 'Short Movies';
            }
            $SeriesMovies = false;
        }
        // echo '<pre>';
        // print_r($cat);
        // echo '</pre>';
        // echo '<br/>';
        $url_components = parse_url($link);
        parse_str($url_components['query'], $params);
        echo ' ID: '.$params['amp;id'].'<br/>';
        $post->id = $params['amp;id'];
        

        // $url = 'https://www.doomovie-hd.com/dmhd_v2/';
        // $data_url = array('r' => 'ajax','module'=>'movie','do'=>'get_play_link','id'=>$params['amp;id'],'disc'=>1);

        // // use key 'http' even if you send the request to https://...
        // $options = array('http' => array(
        //         'header' => "Content-type: application/x-www-form-urlencoded\r\n",
        //         'method' => 'POST',
        //         'content' => http_build_query($data_url),
        //     ),);
        // $context = stream_context_create($options);
        // $html = file_get_html($url, false, $context);



        $checkForDup = $params['amp;id'].'.json';
        $file_name = $checkForDup;
        if (!file_exists(dirname(__FILE__) . '/../uploads/posts/thaimovie')) {
            mkdir(dirname(__FILE__) . '/../uploads/posts/thaimovie', 0700);
        }
        $upload_path = dirname(__FILE__) . '/../uploads/posts/thaimovie/';
        $_SESSION['postFile'] = $upload_path.$checkForDup;
        if(file_exists($upload_path.$checkForDup)) {
            $current = date("Y-m-d");
            $date = date ("Y-m-d", filemtime($upload_path.$checkForDup));
            /*End get all video from link*/
            $str = file_get_contents($upload_path.$checkForDup);
            $jsonFile = json_decode($str);
            if(!empty($SeriesMovies)) {
                if($part <= count($jsonFile->list)) {
                    echo 'part '.count($part).' <= list '.count($jsonFile->list).'<br/>';
                    continue;
                }
            }
            if($current == $date) {
                echo '$current == $date<br/>';
                continue;
            } else {
                $vdoInfo = $file->getFileContent($upload_path.$checkForDup,'json');
                if(count($vdoInfo->list) >= $part) {
                    continue;
                }             
                $uniq_id = $vdoInfo->pid;
                $bid = $vdoInfo->bid;
                $_SESSION['id_edit'] = $vdoInfo->bid;
            }
        } 

        /*for serries movies*/
        if(!empty($SeriesMovies)) {

        }
        $post->SeriesMovies = $SeriesMovies;
        $cats = array_merge($defaultCat, $cat);
        $post->label = $cats;
        $file = new file();
        $jsonPost = $file->json($upload_path,$checkForDup, $post);
        break;
        /*End for serries movies*/
    }
    return $post;
}
function getTwo($page='')
{
    if(file_exists($_SESSION['postFile'])) {
        $str = file_get_contents($_SESSION['postFile']);
        $jsonFile = json_decode($str);
        //echo $jsonFile->label;
        $checkCat = array_search('Series Movies',$jsonFile->label,true);
        $url = 'https://www.doomovie-hd.com/dmhd_v2/';
        if(!empty($checkCat)) {
            $data_url = array('r' => 'ajax','module' => 'movie','do' => 'get_play_link','id' => $jsonFile->id,'disc' => 1);
        } else {
            $data_url = array('r' => 'ajax','module' => 'movie','do' => 'get_play_link','id' => $jsonFile->id);
        }
        // use key 'http' even if you send the request to https://...
        $options = array('http' => array(
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data_url),
            ),);
        $context = stream_context_create($options);
        $htmlList = file_get_html($url, false, $context);
        $jsonFiles = json_decode($htmlList);
        $vListArr = array();
        $vdoList = array_unique($jsonFiles->links);
        foreach ($vdoList as $vlist) {
            if(!empty($vlist)) {
                $vListArr[] = $vlist;
            }
        }

        $post = new stdClass();
        $post->quality = @$jsonFile->quality;
        $post->link = @$jsonFile->link;
        $post->title = @$jsonFile->title;
        $post->title = @$jsonFile->title;
        $post->thumb = @$jsonFile->thumb;
        $post->views = @$jsonFile->views;
        $post->duration = @$jsonFile->duration;
        $post->sound = @$jsonFile->sound;
        $post->label = @$jsonFile->label;
        $post->id = @$jsonFile->id;
        $post->SeriesMovies = @$jsonFile->SeriesMovies;
        $post->playlist = $vListArr;
        
        $checkForDup = $jsonFile->id.'.json';
        $upload_path = dirname(__FILE__) . '/../uploads/posts/thaimovie/';
        $file = new file();
        $jsonPost = $file->json($upload_path,$checkForDup, $post);
        return $post;
    } else {
        return false;
    }
}
function getThree($page='')
{
    if(file_exists($_SESSION['postFile'])) {
        $str = file_get_contents($_SESSION['postFile']);
        $jsonFile = json_decode($str);
        $checkCat = array_search('Series Movies',$jsonFile->label,true);
        if(empty($checkCat)) {
            $context = stream_context_create(array('ssl'=>array(
                'verify_peer' => false, 
                "verify_peer_name"=>false
                )));
            libxml_set_streams_context($context);
            $arrContextOptions=array(
                "ssl"=>array(
                    "verify_peer"=>false,
                    "verify_peer_name"=>false,
                ),
            );
            $contextEng = stream_context_create(
                array(
                    "http" => array(
                        "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36",
                        "header" => "Cookie: lang=en",
                    ),
                        "ssl"=>array(
                        "verify_peer"=>false,
                        "verify_peer_name"=>false,
                    )
                )
            );
            $htmlEng = file_get_html($_SESSION['page_url'], false, $contextEng);
            foreach ($htmlEng->find('section .movie-card') as $mE) {
                $card_b = $mE->find('.movie-card-b',0);
                $linkinfo = $card_b->find('a',0);
                $linkEn = $linkinfo->href;
                $titleEn = $linkinfo->plaintext;
                // echo $linkEn.'<br/>';
                // echo $titleEn.'<br/>';
                if($linkEn == $jsonFile->link) {
                    $post = new stdClass();
                    $post->quality = @$jsonFile->quality;
                    $post->link = @$jsonFile->link;
                    $post->title = @$jsonFile->title;
                    $post->title = @$jsonFile->title;
                    $post->titleEn = @$titleEn;
                    $post->thumb = @$jsonFile->thumb;
                    $post->views = @$jsonFile->views;
                    $post->duration = @$jsonFile->duration;
                    $post->sound = @$jsonFile->sound;
                    $post->label = @$jsonFile->label;
                    $post->id = @$jsonFile->id;
                    $post->SeriesMovies = @$jsonFile->SeriesMovies;
                    $vdoList = @$jsonFile->playlist;

                    echo $linkEn.'<br/>';
                    echo $titleEn.'<br/>';
                    $backupLink = file_get_html('https://www.037hdmovie.com/?s='.urlencode($titleEn), false, $context);
                    $alinkSearch = $backupLink->find('.moviefilm a',0);
                    if(!empty($alinkSearch)) {
                        $backupVdo = file_get_html($alinkSearch->href, false, $context);
                        $VdoA = $backupVdo->find('.filmicerik iframe',0);
                        if(!empty($VdoA)){
                            $vdoList[] = $VdoA->src;  
                        } 
                    } 
                    $playList = array();
                    $playList[] = $vdoList; 
                    $post->playlist = @$playList;
                    $checkForDup = $jsonFile->id.'.json';
                    $upload_path = dirname(__FILE__) . '/../uploads/posts/thaimovie/';
                    $file = new file();
                    $jsonPost = $file->json($upload_path,$checkForDup, $post);
                    // $country = file_get_html('https://www.google.com/search?q='.urlencode($titleEn.', Countries of origin site:imdb.com'), false, $context);
                    // //echo $country;
                    // if(preg_match('/korea/i', $country) || preg_match('/korean/i', $country)) {
                    //     $type = 'korea';
                    // } else if(preg_match('/china/i', $country) || preg_match('/chinese/i', $country)) {
                    //     $type = 'china';
                    // }
                    // echo @$type;
                    break;
                }
            }
        }
    }
    return true;
}

//echo $html;





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