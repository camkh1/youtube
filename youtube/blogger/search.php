<?php
include dirname(__FILE__) .'/../top.php';
if (empty($_SESSION['tokenSessionKey'])) {
    $client = new Google_Client();
    if($client->isAccessTokenExpired()){
        header('Location: ' . base_url .'login.php?back=' . urlencode($CURRENT_URL));
    }
}
include dirname(__FILE__) .'/../library/blogger.php';

/* 
* /search.php?start=1&keyword=keyword here&frompost=12345
*/
function search($keyWord='',$bid,$label='',$max=3,$start = 1){
    if(!empty($label)) {
        $link_blog = 'https://www.blogger.com/feeds/'.$bid.'/posts/summary/-/'.$label.'?max-results='.$max .'&start-index='.$start.'&alt=json-in-script';
        $response = file_get_contents($link_blog);
        $response = str_replace('gdata.io.handleScriptLoaded({', '{',$response);
        $response = str_replace('}}]}});', '}}]}}',$response);
        $html = json_decode($response);
    } else {
        $link_blog = 'https://www.blogger.com/feeds/'.$bid.'/posts/summary?max-results='.$max .'&start-index='.$start.'&alt=json-in-script';
        echo $link_blog;
        echo '<br/>';
        $response = file_get_contents($link_blog);
        $response = str_replace('gdata.io.handleScriptLoaded({', '{',$response);
        $response = str_replace('}}]}});', '}}]}}',$response);
        $html = json_decode($response);        
    }
    if(!empty($keyWord)) {
    	$check = getPost($html,$keyWord);
    	if(!empty($check)) {
    		return $check;
    	} else {
    		return false;
    	}
    } else {
    	return $html;
    }    
}
function getPost($data,$keyWord){
	if(!empty($data->feed->entry)) {
		foreach ($data->feed->entry as $key => $entry) {
			$title = @$entry->title->{'$t'};
			$title = str_replace('[', '', $title);
			$title = str_replace(']', '', $title);
			$title = str_replace('(', '', $title);
			$title = str_replace(')', '', $title);
			$title = str_replace('||', '', $title);
			//$title = str_replace(',', '', $title);
			if (preg_match("/{$keyWord}/i", $title)) {
				$arr   = explode('-', $entry->id->{'$t'});
	        	$pid   = $arr[2];
				return array('title'=> $title,'pid'=>$pid);
			}
		}
	} else {
		return array('runout' => 1);
	}
	
}

$file = new file();
$blogger = new blogger();
if(!empty($_GET['start'])) {
	$_SESSION['to_post_id'] = !empty($_GET['frompost']) ? $_GET['frompost'] : 'search_found';
	$data = array();
	$search = dirname(__FILE__) . '/../uploads/blogger/posts/search.csv';
	$fileNames = $_SESSION['to_post_id'];
		$DsearchFound = dirname(__FILE__) . '/../uploads/blogger/posts/'.$fileNames.'.csv';
	@unlink($DsearchFound);
	@unlink($search);
	if(!empty($_GET['action']) && $_GET['action'] == '1') {
		$data[] = array(default_blog,0);
	} else {
		$jsonTxt = dirname(__FILE__) . '/../uploads/files/blogs/blogid.csv';
		$getBlogId = $file->getFileContent($jsonTxt);
		foreach ($getBlogId as $value) {
			$pos = strpos($value->bid, default_blog);
			if ($pos === false) {
				$data[] = array($value->bid,0);
	        } else {
	            $data[] = array($value->bid,1); 
	        }
		}
	}
	$fp = fopen($search, 'w');
    foreach ($data as $fields) {
        fputcsv($fp, $fields);
    }
    fclose($fp);


	$getIdToSearch = $file->getFileContent($search,'csv');
	foreach ($getIdToSearch as $key => $row) {
		if($row[1] == 0) {
			$bid = $row[0];
		}
	}

	
	$keyWordA = $_GET['keyword'];
	$keyWord = urlencode($keyWordA);
	echo '<script type="text/javascript">window.location = "' . base_url . 'blogger/search.php?search=1&keyword='.$keyWord.'&bid=' . $bid . '&sart=1#1";</script>';
}

if(!empty($_GET['search']) && !empty($_GET['bid'])) {

	$blogID = $_GET['bid'];
	$keyWordA = $_GET['keyword'];
	$keyWord = urlencode($keyWordA);
	$start = $_GET['sart'];
	//$post = search($keyWordA,$blogID,'',500,$start);
	$post = $blogger->searchPost($keyWordA,$blogID);
	var_dump($post);die;
	if(empty($post) && empty($post['runout'])):?>
	<script type="text/javascript">
		setTimeout(function(){
			var setNum = 50;
			var moreNum = window.location.hash.substring(1);
			var setnew = 500 + Number(moreNum);
			window.location = "<?php echo base_url;?>blogger/search.php?search=1&bid=<?php echo $blogID;?>&keyword=<?php echo $keyWord;?>&sart="+setnew+"#" + setnew;
		}, 1000);
	</script>
	<?php elseif(!empty($post) && empty($post['runout'])):
		$fileNames = $_SESSION['to_post_id'];
		$searchFound = dirname(__FILE__) . '/../uploads/blogger/posts/'.$fileNames.'.csv';
		$checkLine = $file->cleanDuplicatePost($searchFound,$blogID);
		if(empty($checkLine)) { 
			$handle = fopen($searchFound, "a");
	        fputcsv($handle, array($blogID,$post['pid']));
	        fclose($handle);
    	}

        /*start save current id*/
        $data = array();
        $search = dirname(__FILE__) . '/../uploads/blogger/posts/search.csv';
        $searchN = $file->getFileContent($search,'csv');
	    foreach ($searchN as $key => $row) {
	        $bid = $row[0];
	        $status = $row[1];	        
	        if(empty($status) && $bid == $blogID) {
	            $data[] = array($bid,1);
	        } else {
	            $data[] = array($bid,$status);
	        } 
	    }
	    $fp = fopen($search, 'w');
	    foreach ($data as $fields) {
	        fputcsv($fp, $fields);
	    }
	    fclose($fp);
	    /*end start save current id*/

	    /*start search new blog*/
	    $next = false;
	   	$getIdToSearch = $file->getFileContent($search,'csv');
		foreach ($getIdToSearch as $key => $row) {
			if($row[1] == 0) {
				$next = $row[0];
			}
		}
		$keyWordA = $_GET['keyword'];
		$keyWord = urlencode($keyWordA);
		if(!empty($next)) {
			echo '<script type="text/javascript">window.location = "' . base_url . 'blogger/search.php?search=1&bid=' . $next . '&keyword='.$keyWord.'&sart=1#1";</script>';
		} else {
			if($_SESSION['to_post_id'] == 'search_found') {
				header('Location: ' . base_url . 'blogger/index.php?search='.$_SESSION['to_post_id']);
			exit();
			} else {
				header('Location: ' . base_url . 'blogger/add.php?id='.$_SESSION['to_post_id']);
				exit();
			}
		}		
	    /*End start search new blog*/
	else :
		$fileNames = $_SESSION['to_post_id'];
		$searchFound = dirname(__FILE__) . '/../uploads/blogger/posts/'.$fileNames.'.csv';
		$checkLine = $file->cleanDuplicatePost($searchFound,$blogID);
		if(empty($checkLine)) {
			$handle = fopen($searchFound, "a");
	        fputcsv($handle, array($blogID,''));
	        fclose($handle);
    	}

		/*start save bid that not found id*/
        $data = array();
        $search = dirname(__FILE__) . '/../uploads/blogger/posts/search.csv';
        $searchN = $file->getFileContent($search,'csv');
	    foreach ($searchN as $key => $row) {
	        $bid = $row[0];
	        $status = $row[1];	        
	        if(empty($status) && $bid == $blogID) {
	            $data[] = array($bid,1);
	        } else {
	            $data[] = array($bid,$status);
	        } 
	    }
	    $fp = fopen($search, 'w');
	    foreach ($data as $fields) {
	        fputcsv($fp, $fields);
	    }
	    fclose($fp);
	    /*end start save bid that not found id*/

	    /*start search new blog*/
	    $gnext = false;
	   	$getIdToSearch = $file->getFileContent($search,'csv');
		foreach ($getIdToSearch as $key => $row) {
			if($row[1] == 0) {
				$gnext = $row[0];
			}
		}
		$keyWordA = $_GET['keyword'];
		$keyWord = urlencode($keyWordA);
		if(!empty($gnext)) {
			echo '<script type="text/javascript">window.location = "' . base_url . 'blogger/search.php?search=1&bid=' . $gnext . '&keyword='.$keyWord.'&sart=1#1";</script>';
		} else {
			if($_SESSION['to_post_id'] == 'search_found') {
				header('Location: ' . base_url . 'blogger/index.php?search='.$_SESSION['to_post_id']);
			exit();
			} else {
				header('Location: ' . base_url . 'blogger/add.php?id='.$_SESSION['to_post_id']);
				exit();
			}
		}
	    /*End start search new blog*/
	 endif;?>
<?php 
}
;?>