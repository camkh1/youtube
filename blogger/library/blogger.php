<?php
include dirname(__FILE__) .'/file.php';
class blogger extends file {
	    public function getfromsiteid($site_url = '', $post_id = '', $thumb = '', $New_title = '', $New_label = '', $videotype = '')
    {
        $log_id = $_SESSION['user_id'];
        /* end delete before insert new list */
        include dirname(__FILE__) .'/simple_html_dom.php';
        $html   = file_get_html($site_url);
        $title  = @$html->find('.post-title a', 0)->innertext;
        $title1 = @$html->find('.post-title', 0)->innertext;
        if ($title) {
            $title = $html->find('.post-title a', 0)->innertext;
        } elseif ($title1) {
            $title = $html->find('.post-title', 0)->innertext;
        } else {
            $title = $html->find('title', 0)->innertext;
        }
        $postTitle = $title;

        $list_id = $this->getsitecontent($html, $videotype);
        if (preg_match('/og:image/', $html)) {
        	$thumbIn = $html->find('meta[property=og:image]', 0)->content;
        } else if (preg_match('/[class=noi]/', $html)) {
            foreach ($html->find('.noi img') as $e) {
                $thumbIn = $e->src;
            }

        } else if (preg_match('/[id=noi]/', $html)) {
            foreach ($html->find('#noi img') as $e) {
                $thumbIn = $e->src;
            }

        } else {
            foreach ($html->find('#noi') as $e) {
                $thumbIn = $e->src;
            }

        }

        if(empty($thumb)) {
            /*upload image to sever*/
            $file = new file();
            $file_title = basename($thumbIn);
    		//$fileName = 'uploads/'.$file_title;
    		$fileName = dirname(__FILE__) . '/../uploads/user/'. $_SESSION['user_id'] . '/' .$file_title;
    		copy($thumbIn, $fileName);
            $thumbIn = $file->uploadMedia($fileName);
            /*end upload image to sever*/
        }

        $thumbIn = (!empty($thumbIn) ? $thumbIn : '0');
        /* insert new list */
        $title = (!empty($title) ? $title : $postTitle);
        if (preg_match('/]/', $title)) {
            $title = explode('[', $title);
            $title = $title[0];
        } else {
            $title = $title;
        }
        $title = (!empty($New_title) ? $New_title : strip_tags($title));
        /* check title */
        $thumb = (!empty($thumb) ? $thumb : $thumbIn); 
 		/* check title */
        if (!empty($list_id)) {
            $i = 0;
            $viddata=[];
            foreach ($list_id as $value) {
                $i++;
                if (!empty($videotype)) {
                    $v_type = $videotype;
                } else {
                    $v_type = $value['vtype'];
                }
                $viddata[] = array(
		            'vid' => $value['list'],
		            'part' => $i,
		            'vtype' => $v_type
		        );
            }
        }
        /* end insert new list */
        if (empty($post_id)) {
            $str = time();
            $str = md5($str);
            $uniq_id = substr($str, 0, 9);
            $title = trim($title) . ' id ' . $uniq_id;   
        } else {
            $uniq = explode(' id ', $title);
            $uniq_i = explode(' || ', $uniq[1]);
            $uniq_id = $uniq_i[0];
        }
        $data_vdo = array(
            'title'     => trim($title) . ' || part ' . '[ '.@count($viddata).' ]',
            'type'     => 'vdolist',
            'object_id' => $log_id,
            'image'     => $thumb,
            'label'     => @$New_label,
            'list'     => @$viddata,
            'pid'     => @$uniq_id,
        );    
        return $data_vdo;
    }
    public function getsitecontent($html, $videotype = '')
    {
        if (preg_match('/Blog1/', $html)) {
            foreach ($html->find('#Blog1') as $article) {
                $content = $article;
            }
        } else if (preg_match('/page-main/', $html)) {
            foreach ($html->find('#page-main .entry') as $article) {
                $content = $article;
            }
        } else {
            $content = $html;
        }
        $list_id = array();
        if (preg_match('/videogallery-con/', $content)) {
            $i = 0;
            foreach ($content->find('.videogallery-con iframe') as $e) {
                $i++;
                $data_list = $this->get_video_id($e->src, $videotype);
                if (!empty($data_list['vid'])) {
                    $list_id[$i] = array(
                        'list'  => $data_list['vid'],
                        'vtype' => $data_list['vtype'],
                    );
                }
            }
        } else if (preg_match('/\/p\/player.html/', $content)) {
            $strs = <<<HTML
'.$content.'
HTML;
            $html = str_get_html($strs);
            $i    = 0;
            foreach ($html->find('div[align=center] div a[target=_blank]') as $e) {
                $i++;
                $getid   = explode('/p/player.html?', $e->href);
                $content = $getid = $this->youtubecode($getid[1]);
                if (!empty($videotype)) {
                    $v_id = 'http://www.youtube-nocookie.com/embed/' . $content;
                } else {
                    $v_id = $content;
                }
                $list_id[$i] = array(
                    'list'  => $v_id,
                    'vtype' => 'yt',
                );
            }
        } else if (preg_match('/ytlist/', $content)) {
            preg_match("/ytlist = '([^&]+)/i", $content, $code);
            $content = explode(",';", $code[1]);
            $content = explode(",", $content[0]);
            $count   = count($content);

            for ($i = 0; $i < $count; $i++) {
                if (!empty($videotype)) {
                    $v_id = 'http://www.youtube-nocookie.com/embed/' . $content[$i];
                } else {
                    $v_id = $content[$i];
                }
                $list_id[$i] = array(
                    'list'  => $v_id,
                    'vtype' => 'yt',
                );
            }
        }

        /* GET CODE BY LIST (YTV_movies) */else if (preg_match('/YTV_movies/', $content)) {
            $str = <<<HTML
'.$content.'
HTML;

            $html = str_get_html($str);
            $i    = 0;
            foreach ($html->find('div[class=YTV_movies] a') as $article) {
                $code = $article->rel;
                if (!empty($code)) {
                    $code    = explode("'", $code);
                    $content = $code[3];
                    $title   = $article->innertext;
                    if (!empty($videotype)) {
                        $v_id = 'http://www.youtube-nocookie.com/embed/' . $content;
                    } else {
                        $v_id = $content;
                    }
                    $list_id[$i] = array(
                        'list'  => $v_id,
                        'vtype' => 'yt',
                    );
                }
                $i++;
            }
        } /* GET CODE BY LIST by (JW Player) */else if (preg_match('/file":/', $content) || preg_match("/file':/", $content)) {
            $article = $content;
            $code    = trim(@$article->find('script', 0)->innertext);
            if (preg_match('/file":/', $code) || preg_match("/file':/", $code)) {
                $code1 = trim($article->find('script', 0)->innertext);
            }
            $code = trim(@$article->find('script', 1)->innertext);
            if (preg_match('/file":/', $code) || preg_match("/file':/", $code)) {
                $code1 = trim($article->find('script', 1)->innertext);
            }
            $code = trim(@$article->find('script', 2)->innertext);
            if (preg_match('/file":/', $code) || preg_match("/file':/", $code)) {
                $code1 = trim($article->find('script', 2)->innertext);
            }
            $code = trim(@$article->find('script', 3)->innertext);
            if (preg_match('/file":/', $code) || preg_match("/file':/", $code)) {
                $code1 = trim($article->find('script', 3)->innertext);
            }
            $code = trim(@$article->find('script', 4)->innertext);
            if (preg_match('/file":/', $code) || preg_match("/file':/", $code)) {
                $code1 = trim($article->find('script', 4)->innertext);
            }
            $code = trim(@$article->find('script', 5)->innertext);
            if (preg_match('/file":/', $code) || preg_match("/file':/", $code)) {
                $code1 = trim($article->find('script', 5)->innertext);
            }
            $code = trim(@$article->find('script', 6)->innertext);
            if (preg_match('/file":/', $code) || preg_match("/file':/", $code)) {
                $code1 = trim($article->find('script', 6)->innertext);
            }
            $code = trim(@$article->find('script', 7)->innertext);
            if (preg_match('/file":/', $code) || preg_match("/file':/", $code)) {
                $code1 = trim($article->find('script', 7)->innertext);
            }
            $code = trim(@$article->find('script', 8)->innertext);
            if (preg_match('/file":/', $code) || preg_match("/file':/", $code)) {
                $code1 = trim($article->find('script', 8)->innertext);
            }
            $code = trim(@$article->find('script', 9)->innertext);
            if (preg_match('/file":/', $code) || preg_match("/file':/", $code)) {
                $code1 = trim($article->find('script', 9)->innertext);
            }
            $code = trim(@$article->find('script', 10)->innertext);
            if (preg_match('/file":/', $code) || preg_match("/file':/", $code)) {
                $code1 = trim($article->find('script', 10)->innertext);
            }
            $code = trim(@$article->find('script', 11)->innertext);
            if (preg_match('/file":/', $code) || preg_match("/file':/", $code)) {
                $code1 = trim($article->find('script', 11)->innertext);
            }
            $code = trim(@$article->find('script', 12)->innertext);
            if (preg_match('/file":/', $code) || preg_match("/file':/", $code)) {
                $code1 = trim($article->find('script', 12)->innertext);
            }
            $code = trim(@$article->find('script', 13)->innertext);
            if (preg_match('/file":/', $code) || preg_match("/file':/", $code)) {
                $code1 = trim($article->find('script', 13)->innertext);
            }
            $code = trim(@$article->find('script', 14)->innertext);
            if (preg_match('/file":/', $code) || preg_match("/file':/", $code)) {
                $code1 = trim($article->find('script', 14)->innertext);
            }
            $code = trim(@$article->find('script', 15)->innertext);
            if (preg_match('/file":/', $code) || preg_match("/file':/", $code)) {
                $code1 = trim($article->find('script', 15)->innertext);
            }
            $code = trim(@$article->find('script', 16)->innertext);
            if (preg_match('/file":/', $code) || preg_match("/file':/", $code)) {
                $code1 = trim($article->find('script', 16)->innertext);
            }
            $code = trim(@$article->find('script', 17)->innertext);
            if (preg_match('/file":/', $code) || preg_match("/file':/", $code)) {
                $code1 = trim($article->find('script', 17)->innertext);
            }
            $code = trim(@$article->find('script', 18)->innertext);
            if (preg_match('/file":/', $code) || preg_match("/file':/", $code)) {
                $code1 = trim($article->find('script', 18)->innertext);
            }
            $code = trim(@$article->find('script', 19)->innertext);
            if (preg_match('/file":/', $code) || preg_match("/file':/", $code)) {
                $code1 = trim($article->find('script', 19)->innertext);
            }
            $code = trim(@$article->find('script', 20)->innertext);
            if (preg_match('/file":/', $code) || preg_match("/file':/", $code)) {
                $code1 = trim($article->find('script', 20)->innertext);
            }
            $code = trim(@$article->find('script', 21)->innertext);
            if (preg_match('/file":/', $code) || preg_match("/file':/", $code)) {
                $code1 = trim($article->find('script', 21)->innertext);
            }
            $code = trim(@$article->find('script', 22)->innertext);
            if (preg_match('/file":/', $code) || preg_match("/file':/", $code)) {
                $code1 = trim($article->find('script', 22)->innertext);
            }
            $code = trim(@$article->find('script', 23)->innertext);
            if (preg_match('/file":/', $code) || preg_match("/file':/", $code)) {
                $code1 = trim($article->find('script', 23)->innertext);
            }
            $code = trim(@$article->find('script', 24)->innertext);
            if (preg_match('/file":/', $code) || preg_match("/file':/", $code)) {
                $code1 = trim($article->find('script', 24)->innertext);
            }
            $code = trim(@$article->find('script', 25)->innertext);
            if (preg_match('/file":/', $code) || preg_match("/file':/", $code)) {
                $code1 = trim($article->find('script', 25)->innertext);
            }
            if (!empty($code1)) {
                $prcot = str_replace('<![CDATA[', '', $code1);
                $prcot = str_replace(']]', '', $prcot);
                $code  = explode("[", $prcot);
                $code  = explode("]", $code[1]);
                $prcot = str_replace(' ', '', $code[0]);
                $prcot = str_replace('"file":"', '<a href="', $prcot);
                $prcot = str_replace("file':'", '<a href="', $prcot);
                $prcot = str_replace('","', '">link</a>', $prcot);
                $prcot = str_replace("','", '">link</a>', $prcot);
            } else {
                $str = str_replace(' ', '', $article);
                preg_match_all('{"file":(.*)}', $str, $matches);
                $prcot = $matches[0][0];
                $prcot = str_replace(' ', '', $prcot);
                $prcot = str_replace('"file":"', '<a href="', $prcot);
                $prcot = str_replace("file':'", '<a href="', $prcot);
                $prcot = str_replace('","', '">link</a>', $prcot);
                $prcot = str_replace("','", '">link</a>', $prcot);
            }
            $str = <<<HTML
'.$prcot.'
HTML;
            $part    = 0;
            $list_id = array();
            $html    = str_get_html($str);

            foreach ($html->find('a') as $e) {
                $part++;
                $code = $e->href;
                if (!empty($videotype)) {
                    $data_list = $this->get_video_id($code, $videotype);
                    $v_id      = $data_list['vid'];
                    $v_type    = $data_list['vtype'];
                } else {
                    $data_list = $this->get_video_id($code);
                    $v_id      = $data_list['vid'];
                    $v_type    = $data_list['vtype'];
                }
                $list_id[$part] = array(
                    'list'  => $v_id,
                    'vtype' => $v_type,
                );
            }
        } else if (preg_match('/data-vid/', $content)) {
            $i     = 0;
            $code  = trim(@$content->find('#listVideo li', 0)->innertext);
            $code1 = trim(@$content->find('#vList li', 0)->innertext);
            $code2 = trim(@$content->find('#smart li', 0)->innertext);
            if (!empty($code)) {
                $code_id = @$content->find('#listVideo li');
            } else if ($code1) {
                $code_id = @$content->find('#vList li');
            } else if ($code2) {
                $code_id = @$content->find('#smart li');
            }
            foreach ($code_id as $e) {
                $i++;
                $vid         = $e->attr['data-vid'];
                $vid         = trim($vid);
                $type        = 'docs.google';
                $source_type = '';
                if (empty($source_type)) {
                    if (strlen($vid) >= 6 && strlen($vid) <= 7) {
                        $source_type = 'd';
                    } else if (strlen($vid) == 11) {
                        $source_type = 'y';
                    } else if (strlen($vid) >= 8 && strlen($vid) < 10) {
                        $source_type = 'v';
                    } else if (strlen($vid) == 28) {
                        $source_type = 'g';
                    } else if (strlen($vid) >= 3 && strlen($vid) < 6) {
                        $source_type = 'vid';
                    } else {
                        $source_type = 'f';
                    }
                }

                switch ($source_type) {
                    case 'v':
                        $type = 'vimeo';
                        if (!empty($videotype)) {
                            $v_id = 'http://player.vimeo.com/video/' . $vid;
                        } else {
                            $v_id = $vid;
                        }
                        break;
                    case 'y':
                        $type = 'yt';
                        if (!empty($videotype)) {
                            $v_id = 'http://www.youtube-nocookie.com/embed/' . $vid;
                        } else {
                            $v_id = $vid;
                        }
                        break;
                    case 'g':
                        $type = 'docs.google';
                        if (!empty($videotype)) {
                            $v_id = 'http://docs.google.com/file/d/' . $vid . '/preview';
                        } else {
                            $v_id = $vid;
                        }
                        break;
                    case 'd':
                        $type = 'dailymotion';
                        if (!empty($videotype)) {
                            $v_id = 'http://www.dailymotion.com/embed/video/' . $vid . '?autoPlay=0&hideInfos=0';
                        } else {
                            $v_id = $vid;
                        }
                        break;
                    case 'vid':
                        $type = 'iframe';
                        if (!empty($videotype)) {
                            $v_id = 'https://vid.me/e/' . $vid;
                        } else {
                            $v_id = 'https://vid.me/e/' . $vid;
                        }
                        break;
                    case 'f':
                        $type = 'iframe';
                        $v_id = $vid;
                        break;
                }
                $list_id[$i] = array(
                    'list'  => $v_id,
                    'vtype' => $type,
                );
            }
        } else if (preg_match('/spoiler/', $content)) {
            $article = $content;
            foreach ($html->find('#spoiler .drama-info') as $article)
            ;
            $article = str_replace('Phumi Khmer', '', $article);
            $article = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $article);
            $str     = <<<HTML
'.$article.'
HTML;
            $html = str_get_html($str);
            $part = 0;
            foreach ($html->find('a') as $e) {
                $part++;
                $code = $e->href;
                preg_match("/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&\"'>]+)/", $code, $matches);
                if (!empty($matches[1])) {
                    $content = $matches[1];
                    if (!empty($videotype)) {
                        $v_id = 'http://www.youtube-nocookie.com/embed/' . $content;
                    } else {
                        $v_id = $content;
                    }
                    $list_id[$part] = array(
                        'list'  => $v_id,
                        'vtype' => 'yt',
                    );
                }
            }
        } else if (preg_match('/idGD/', $content)) {
            $content = $article;
            $code    = trim(@$article->find('script', 0)->innertext);
            if (preg_match('/idGD/', $code)) {
                $code1 = trim($article->find('script', 0)->innertext);
            }
            $code = trim(@$article->find('script', 1)->innertext);
            if (preg_match('/idGD/', $code)) {
                $code1 = trim($article->find('script', 1)->innertext);
            }
            $code = trim(@$article->find('script', 2)->innertext);
            if (preg_match('/idGD/', $code)) {
                $code1 = trim($article->find('script', 2)->innertext);
            }
            $code = trim(@$article->find('script', 3)->innertext);

            if (preg_match('/idGD/', $code)) {
                $code1 = trim(@$article->find('script', 3)->innertext);
            }
            $code = trim(@$article->find('script', 4)->innertext);
            if (preg_match('/idGD/', $code)) {
                $code1 = trim($article->find('script', 4)->innertext);
            }
            $code = trim(@$article->find('script', 5)->innertext);
            if (preg_match('/idGD/', $code)) {
                $code1 = trim($article->find('script', 5)->innertext);
            }
            $code = trim(@$article->find('script', 6)->innertext);
            if (preg_match('/idGD/', $code)) {
                $code1 = trim($article->find('script', 6)->innertext);
            }
            $code = trim(@$article->find('script', 7)->innertext);
            if (preg_match('/idGD/', $code)) {
                $code1 = trim($article->find('script', 7)->innertext);
            }
            $code = trim(@$article->find('script', 8)->innertext);
            if (preg_match('/idGD/', $code)) {
                $code1 = trim($article->find('script', 8)->innertext);
            }
            $code = trim(@$article->find('script', 9)->innertext);
            if (preg_match('/idGD/', $code)) {
                $code1 = trim($article->find('script', 9)->innertext);
            }
            $code = trim(@$article->find('script', 10)->innertext);
            if (preg_match('/idGD/', $code)) {
                $code1 = trim($article->find('script', 10)->innertext);
            }
            $code = trim(@$article->find('script', 11)->innertext);
            if (preg_match('/idGD/', $code)) {
                $code1 = trim($article->find('script', 11)->innertext);
            }
            $code = trim(@$article->find('script', 12)->innertext);
            if (preg_match('/idGD/', $code)) {
                $code1 = trim($article->find('script', 12)->innertext);
            }
            $code = trim(@$article->find('script', 13)->innertext);
            if (preg_match('/idGD/', $code)) {
                $code1 = trim($article->find('script', 13)->innertext);
            }
            $code = trim(@$article->find('script', 14)->innertext);
            if (preg_match('/idGD/', $code)) {
                $code1 = trim($article->find('script', 14)->innertext);
            }
            $code = trim(@$article->find('script', 15)->innertext);
            if (preg_match('/idGD/', $code)) {
                $code1 = trim($article->find('script', 15)->innertext);
            }
            $code = trim(@$article->find('script', 16)->innertext);
            if (preg_match('/idGD/', $code)) {
                $code1 = trim($article->find('script', 16)->innertext);
            }
            $code = trim(@$article->find('script', 17)->innertext);
            if (preg_match('/idGD/', $code)) {
                $code1 = trim($article->find('script', 17)->innertext);
            }
            $code = trim(@$article->find('script', 18)->innertext);
            if (preg_match('/idGD/', $code)) {
                $code1 = trim($article->find('script', 18)->innertext);
            }
            $code = trim(@$article->find('script', 19)->innertext);
            if (preg_match('/idGD/', $code)) {
                $code1 = trim($article->find('script', 19)->innertext);
            }
            $code = trim(@$article->find('script', 20)->innertext);
            if (preg_match('/idGD/', $code)) {
                $code1 = trim($article->find('script', 20)->innertext);
            }
            $code = trim(@$article->find('script', 21)->innertext);
            if (preg_match('/idGD/', $code)) {
                $code1 = trim($article->find('script', 21)->innertext);
            }
            $code = trim(@$article->find('script', 22)->innertext);
            if (preg_match('/idGD/', $code)) {
                $code1 = trim($article->find('script', 22)->innertext);
            }
            $code = trim(@$article->find('script', 23)->innertext);
            if (preg_match('/idGD/', $code)) {
                $code1 = trim($article->find('script', 23)->innertext);
            }
            $code = trim(@$article->find('script', 24)->innertext);
            if (preg_match('/idGD/', $code)) {
                $code1 = trim($article->find('script', 24)->innertext);
            }
            $code = trim(@$article->find('script', 25)->innertext);
            if (preg_match('/idGD/', $code)) {
                $code1 = trim($article->find('script', 25)->innertext);
            }
            $code1 = explode('"', $code1);
            if (!empty($code1)) {
                $vtype  = '';
                $vlists = $code1[3];
                $vtye   = $code1[13];
                if ($vtye == "image") {
                    $vtye = $code1[17];
                } else {
                    $vtye = $code1[13];
                }
                switch ($vtye) {
                    case 'GD':
                        $vtype = 'docs.google';
                        break;
                    case 'DG':
                        $vtype = 'docs.google';
                        break;
                    case 'GDoc':
                        $vtype = 'docs.google';
                        break;
                    case 'YT':
                        $vtype = 'yt';
                        break;
                    case 'TY':
                        $vtype = 'yt';
                        break;
                    case 'youtube':
                        $vtype = 'yt';
                        break;
                    case 'onYT':
                        $vtype = 'yt';
                    case 'iframe':
                        $vtype = 'iframe';
                        break;
                    case 'ok':
                        $vtype = 'ok';
                        break;
                    case 'vimeo':
                        $vtype = 'vimeo';
                        break;
                }
                $check_vd = str_replace("0!7x0k!iYAt", 'xxxxxxxx', $vlists);
                $check_vd = str_replace("0!?^0!?A", 'xxxxxxxx', $check_vd);
                $check_vd = str_replace("?^0!AB7Ik!Tx", 'xxxxxxxx', $check_vd);
                $vlis     = explode('xxxxxxxx', $check_vd);
                $i        = 0;
                if ($vtye == 'youtube' || $vtye == 'GDoc') {
                    foreach ($vlis as $value) {
                        $i++;
                        $list_id[$i] = array(
                            'list'  => substr($value, 1, -1),
                            'vtype' => $vtype,
                        );
                    }
                } else {
                    foreach ($vlis as $value) {
                        $i++;

                        $list_id[$i] = array(
                            'list'  => $value,
                            'vtype' => $vtype,
                        );
                    }
                }
            }
        } elseif (preg_match('/vimeowrap/', $content)) {
            $code = str_replace(' ', '', $content);
            $code = explode('urls:[', $code);
            if (!empty($code[1])) {
                $code = $code[1];
                $code = str_replace("'", '', $code);
                $code = explode(',],plugins:', $code);
                $code = explode(",", $code[0]);
                $i    = 0;
                foreach ($code as $value_arr) {
                    $i++;
                    preg_match("/(https?:\/\/)?(www\.)?(player\.)?vimeo\.com\/([a-z]*\/)*([0-9]{6,11})[?]?.*/", $value_arr, $matches);
                    if (!empty($matches[5])) {
                        if (!empty($videotype)) {
                            $v_id = 'http://player.vimeo.com/video/' . $matches[5];
                        } else {
                            $v_id = $matches[5];
                        }
                        $list_id[$i] = array(
                            'list'  => $v_id,
                            'vtype' => 'vimeo',
                        );
                    }
                }
            }
        } elseif (preg_match('/kmobilemovie/', $content)) {

        } elseif (preg_match('/kmobilemovie/', $content)) {

        }
        return @$list_id;
    }

    public function youtubecode($url)
    {
        preg_match("/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&\"'>]+)/", $url, $matches);
        if (!empty($matches[1])) {
            $code = $matches[1];
        } else {
            $code = $url;
        }
        return $code;
    }
    public function get_video_id($param, $videotype = '')
    {
        $v_type = $this->check_v_type($param);
        switch ($v_type) {
            case 'yt':
                preg_match("/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&\"'>]+)/", $param, $matches);
                if (!empty($matches[1])) {
                    $content = (!empty($matches[1]) ? $matches[1] : '');
                    if (!empty($videotype)) {
                        $v_id   = 'https://www.youtube.com/embed/' . $content;
                        $v_type = 'iframe';
                    } else {
                        $v_id = $content;
                    }
                }
                break;
            case 'vimeo':
                preg_match("/(https?:\/\/)?(www\.)?(player\.)?vimeo\.com\/([a-z]*\/)*([0-9]{6,11})[?]?.*/", $param, $matches);
                if (!empty($matches[5])) {
                    if (!empty($videotype)) {
                        if ($videotype != 'vimeoDomain') {
                            $v_id   = 'http://player.vimeo.com/video/' . $matches[5];
                            $v_type = 'iframe';
                        } else {
                            $v_id = $matches[5];
                        }
                    } else {
                        $v_id = $matches[5];
                    }
                }
                break;
            case 'docs.google':
                $g_array = explode('/', $param);
                if (!empty($g_array[5])) {
                    $v_ids = $g_array[5];
                } else {
                    $v_ids = $param;
                }
                if (!empty($videotype)) {
                    $v_id   = 'https://docs.google.com/file/d/' . $v_ids . '/preview';
                    $v_type = 'iframe';
                } else {
                    $v_id = $v_ids;
                }
                break;
            case 'dailymotion':
                preg_match('#dailymotion.com/embed/video/([A-Za-z0-9]+)#s', $param, $matches);
                if (!empty($matches[1])) {
                    $v_ids = $matches[1];
                } else {
                    $v_ids = $param;
                }
                if (!empty($videotype)) {
                    $v_id   = 'http://www.dailymotion.com/embed/video/' . $v_ids . '?autoPlay=0&hideInfos=0';
                    $v_type = 'iframe';
                } else {
                    $v_id = $v_ids;
                }
                break;
            case 'fbvid':
                if (preg_match('/photo/', $param)) {
                    preg_match("/v=([^&]+)/i", $param, $code);
                    $v_id = $code[1];
                } elseif (preg_match('/embed/', $param)) {
                    preg_match("/video_id=([^&]+)/i", $param, $code);
                    $v_id = $code[1];
                } elseif (preg_match('/video.php/', $param)) {
                    preg_match("/v=([^&]+)/i", $param, $code);
                    $v_id = $code[1];
                } else {
                    $v_id = $valCode;
                }
                if (!empty($videotype)) {
                    $v_id   = 'https://www.facebook.com/video/embed?video_id=' . $v_id;
                    $v_type = 'iframe';
                }
                break;
                case 'ok':
                    if (!empty($videotype)) {
                        $v_id = $param;
                    } else {
                        preg_match("/videoembed\/([^&]+)/i", $param, $code);
                        $v_id = $code[1];
                    }
                    break;
            default:
                $v_id   = $param;
                $v_type = 'iframe';
                break;
        }
        $data = array(
            'vid'   => $v_id,
            'vtype' => $v_type,
        );
        return $data;
    }
    public function check_v_type($param)
    {
        if (preg_match("/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&\"'>]+)/", $param)) {
            $v_type = 'yt';
        } elseif (preg_match('/vimeo/', $param)) {
            $v_type = 'vimeo';
        } elseif (preg_match('/docs.google/', $param)) {
            $v_type = 'docs.google';
        } elseif (preg_match('/drive.google.com/', $param)) {
            $v_type = 'docs.google';
        } elseif (preg_match('/dailymotion/', $param)) {
            $v_type = 'dailymotion';
        } elseif (preg_match('/facebook.com/', $param) || preg_match('/fb.com/', $param)) {
            $v_type = 'fbvid';
        } elseif (preg_match('/ok.ru/', $param)) {
            $v_type = 'ok';
        } else {
            $v_type = '';
        }
        return $v_type;
    }

    public function blogger_post($client,$data)
    {
        date_default_timezone_set('Asia/Phnom_Penh');
        try {
            $service = new Google_Service_Blogger($client);
            $posts   = new Google_Service_Blogger_Post();
            $posts->setTitle($data->title);
            $str = stripslashes($data->bodytext);
            $str = str_replace("<br />", "\n", $str);
            $posts->setContent($str);
            //$label = array('khmer1','test1');
            $posts->setLabels(array($data->label));

            /*set date*/
            if (empty($data->setdate)) {
                $date = date("c");
                $posts->setUpdated($date);
                $posts->setPublished($date);
            } else if ($data->setdate) {
                $dateset = $data->setdate;
                $date    = $dateset . 'T';
                $date .= date("H:i:s");
                $posts->setUpdated($date);
                $posts->getPublished($date);
            }
            /*end set date*/

            /*set customcode*/
            if (!empty($data->customcode)) {
                $Location = new Google_Service_Blogger_PostLocation();
                $Location->setName($data->customcode);
                $Location->setLat('37.16031654673677');
                $Location->setLng('-108.984375');
                $Location->setSpan('51.044069,82.617188');
                $posts->setLocation($Location);
            }
            /*end set customcode*/

            /*add post*/
            if (empty($data->editpost)) {
                $getpost = $service->posts->insert($data->bid, $posts);
                $pid     = $getpost->id;
            } else {
                $setUpdateBlogSms[] = $service->posts->update($data->bid, $data->pid, $posts);
                $pid                = $data->pid;
            }
            return $pid;
            die;
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
            return false;
            //echo $exc->getTraceAsString();
        }
    }

    public function delete_blog_post($service, $posts, $bid, $pid)
    {
        if (!empty($service) && !empty($bid) && !empty($pid)) {
            return @$service->posts->delete($bid, $pid);
        } else {
            return false;
        }
    }    

    public function MoviePost($getBlogId,$getPost)
    {
        $pImage = $getPost->image;
        $playlist = $this->getPlaylist($getPost->list,$getPost->title, $getPost->image);

        $bodytext = '';
    	$dataContent          = new stdClass();
		$dataContent->setdate = false;        
		$dataContent->editpost = $pid;
		$dataContent->pid      = $pid;
		$dataContent->customcode = '';
		$dataContent->bid     = $bid;
		$dataContent->title    = $getPost->title;        
		$dataContent->bodytext = $bodytext;
		$dataContent->label    = 'lotta';
		//$getpost               = blogger_post($client,$dataContent);
    }

    public function getLabelBySpec($setLabel ,$LabelType, $LabelStatus)
    {
        switch ($setLabel) {
            case 'china':
                if($LabelType == 'Drama') {
                    if($LabelStatus == 'Continue') {
                        $Cates = 'Movies, chinese movies, Series Movie, Chinese Continue, AD1';
                    } else {
                       $Cates = 'Movies, chinese movies, Series Movies, Series Chinese, AD1'; 
                    }
                    
                } else {
                    $Cates = 'Movies, chinese movies, Short Movies, Short Chinese, AD1';
                }
                break;
            case 'thai':
                if($LabelType == 'Drama') {
                    if($LabelStatus == 'Continue') {
                        $Cates = 'Movies, Thai - Khmer, Series Movies,Series Thai,  Continue, Thai Continue, withads';
                    } else {
                       $Cates = 'Movies, Thai - Khmer, Series Movies, Series Thai, withads'; 
                    }
                    
                } else {
                    $Cates = 'Movies, Thai - Khmer, Short Movies, Short Thai, withads';
                }
                break;
            case 'Korea':
                if($LabelType == 'Drama') {
                    if($LabelStatus == 'Continue') {
                        $Cates = 'Movies, Korean - Khmer Movies, Series Movies, Series Korean, Continue,Korean Continue, AD1';
                    } else {
                       $Cates = 'Movies, Thai - Khmer, Series Movies, Series Thai, withads'; 
                    }
                    
                } else {
                    $Cates = 'Movies, Korean Movies, Short Movies, Short Korean, AD1';
                }
                break;
            case 'khmer':
                if($LabelType == 'Drama') {
                    if($LabelStatus == 'Continue') {
                        $Cates = 'Movies, Khmer Movie, Series Movies, Khmer Continue, Continue,withads';
                    } else {
                       $Cates = 'Movies, Khmer Movie, Series Movies, Khmer Series, AD1'; 
                    }
                    
                } else {
                    $Cates = 'Movies, khmer Movie, Short Movies, Khmer Short, AD1';
                }
                break;
            case 'India':
                if($LabelType == 'Drama') {
                    if($LabelStatus == 'Continue') {
                        $Cates = 'Movies, India Movies, Series Movies, Series India, Continue,withads';
                    } else {
                       $Cates = 'Movies, India Movies, Series Movies, Series India, withads'; 
                    }
                    
                } else {
                    $Cates = 'Movies, India Movies, Short Movies, Short India, withads';
                }
                break;
            case 'Hong Kong':
                if($LabelType == 'Drama') {
                    if($LabelStatus == 'Continue') {
                        $Cates = 'Movies, chinese movies, Series Movie, Chinese Continue,Hongkong Movies, AD1';
                    } else {
                       $Cates = 'Hongkong Movies,Movies, chinese movies, Series Movies, Series Chinese, AD1'; 
                    }
                    
                } else {
                    $Cates = 'Hongkong Movies,Movies, chinese movies, Short Movies, Short Chinese, AD1';
                }
                break;
            case 'Taiwan':
                if($LabelType == 'Drama') {
                    if($LabelStatus == 'Continue') {
                        $Cates = 'Movies, chinese movies, Series Movie, Chinese Continue,Taiwan, AD1';
                    } else {
                       $Cates = 'Taiwan,Movies, chinese movies, Series Movies, Series Chinese, AD1'; 
                    }
                    
                } else {
                    $Cates = 'Taiwan,Movies, chinese movies, Short Movies, Short Chinese, AD1';
                }
                break;
            default:
                $Cates = 'Movies, withads,' . implode(',', $labels);
                break;
        }
        return $Cates;
    }

    public function getPlaylist($param, $title, $thumb = '')
    {

        if(!empty($param)) {
            $list = '<script>var list = [';
            $last_key = count($param);
            foreach ($param as $key => $value) {
              switch ($value->vtype) {
                case 'vimeo':
                  $vType = 'vi';
                  break;
                case 'docs.google':
                  $vType = 'gd';
                  break;
                case 'dailymotion':
                  $vType = 'dm';
                  break;
                case 'yt':
                  $vType = 'yt';
                  break;
                case 'fbvid':
                  $vType = 'fb';
                  break;
                case 'ok':
                  $vType = 'ok';
                  break;
                default:
                  $vType = 'if';
                  break;
              }
                if ($key == $last_key) {
                    $list .= '{"vid": "' . $value->vid . '","t": "' . $vType . '"}';
                } else {
                    if($key == 0) {
                      $list .= '{"vid": "' . $value->vid . '","t": "' . $vType . '","title": "' . $title . '","image": "'.$thumb.'"},';
                    } else {
                      $list .= '{"vid": "' . $value->vid . '","t": "' . $vType . '"},';
                    }
                }
            }
            $list .='];Videoplayer(list);</script>';
            return $list;
        } else {
            return false;
        }        
    }

    public function resize_image($url, $imgsize, $height = '')
    {
        if (preg_match('/blogspot/', $url)) {
            //inital value
            $newsize = "s" . $imgsize;
            $newurl  = "";
            //Get Segments
            $path     = parse_url($url, PHP_URL_PATH);
            $segments = explode('/', rtrim($path, '/'));
            //Get URL Protocol and Domain
            $parsed_url = parse_url($url);
            $domain     = $parsed_url['scheme'] . "://" . $parsed_url['host'];

            $newurl_segments = array(
                $domain . "/",
                $segments[1] . "/",
                $segments[2] . "/",
                $segments[3] . "/",
                $segments[4] . "/",
                $newsize . "/", //change this value
                $segments[6],
            );
            $newurl_segments_count = count($newurl_segments);
            for ($i = 0; $i < $newurl_segments_count; $i++) {
                $newurl = $newurl . $newurl_segments[$i];
            }
            return $newurl;
        } elseif (preg_match('/googleusercontent/', $url)) {
            if (preg_match('/s72-c/', $url)) {
                //$newurl = str_replace('s72-c', 's'.$imgsize, $url);
                //inital value
                $newsize = "w" . $imgsize;
                $newurl = "";
                
                //Get Segments
                $path = parse_url($url, PHP_URL_PATH);
                $segments = explode('/', rtrim($path, '/'));
                
                //Get URL Protocol and Domain
                $parsed_url = parse_url($url);
                $domain = $parsed_url['scheme'] . "://" . $parsed_url['host'];
                $newurl_segments = array($domain . "/", $segments[1] . "/", $segments[2] . "/", $segments[3] . "/", $segments[4] . "/", $newsize . $height . "/",
                 //change this value
                $segments[6]);
                $newurl_segments_count = count($newurl_segments);
                for ($i = 0; $i < $newurl_segments_count; $i++) {
                    $newurl = $newurl . $newurl_segments[$i];
                }
            } else {
                $newsize = "=s" . $imgsize;
                $segments = explode('=', $url);
                $newurl = $segments[0].$newsize;
            }
            return $newurl;
        } else {
            return $url;
        }
    }
}