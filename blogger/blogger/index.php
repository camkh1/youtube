<?php
include dirname(__FILE__) .'/../top.php';
if (empty($_SESSION['tokenSessionKey'])) {
    $client = new Google_Client();
    $client->setAccessToken($_SESSION['tokenSessionKey']);
    if(!$client->isAccessTokenExpired()){
        header('Location: ' . base_url .'login.php?back=' . urlencode($CURRENT_URL));
    }
}
include dirname(__FILE__) .'/../library/blogger.php';
if(empty($_GET['search'])){
function checkDuplicate($bid,$label='',$max=3,$start = 1){
    if(!empty($label)) {
        $link_blog = 'https://www.blogger.com/feeds/'.$bid.'/posts/summary/-/'.urlencode($label).'?max-results='.$max .'&start-index='.$start.'&alt=json-in-script';
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

function getPaginationString($page = 1, $totalitems, $limit = 15, $adjacents = 1, $targetpage = "/", $pagestring = "?page=")
{       
    //defaults
    if(!$adjacents) $adjacents = 1;
    if(!$limit) $limit = 15;
    if(!$page) $page = 1;
    if(!$targetpage) $targetpage = "/";
    
    //other vars
    $prev = $page - 1;                                  //previous page is page - 1
    $next = $page + 1;                                  //next page is page + 1
    $lastpage = ceil($totalitems / $limit);             //lastpage is = total items / items per page, rounded up.
    $lpm1 = $lastpage - $page;                              //last page minus 1
    $start_loop = $page;
    if($lpm1 <= 5)
    {
     $start_loop = $lastpage - 5;
    }
    $end_loop = $start_loop + 4;
    
    $pagination = "";
    $margin = '0';
    $padding = '0';
    if($lastpage > 1)
    {   
        $pagination .= "<ul class=\"pagination\">";
        //previous button
        if ($page > 1) 
            $pagination .= "<li><a href=\"$targetpage$pagestring$prev\">← prev</a></li>";
        else
            $pagination .= "<li class=\"prev disabled\"><a href=\"#\">← prev</a></li>";    
        
        //pages 
        if ($lastpage < 7 + ($adjacents * 2))   //not enough pages to bother breaking it up
        {   
            for($counter=$start_loop; $counter<=$end_loop; $counter++)
            {
                if ($counter == $page)
                    $pagination .= "<li class=\"active\"><a href=\"#\">$counter</a></li>";
                else
                    $pagination .= "<li><a href=\"" . $targetpage . $pagestring . $counter . "\">$counter</a></li>";                 
            }
        }
        elseif($lastpage >= 7 + ($adjacents * 2))   //enough pages to hide some
        {
            //close to beginning; only hide later pages
            if($page < 1 + ($adjacents * 3))        
            {
                for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++)
                {
                    if ($counter == $page)
                        $pagination .= "<li class=\"active\"><a href=\"#\">$counter</a></li>";
                    else
                        $pagination .= "<li><a href=\"" . $targetpage . $pagestring . $counter . "\">$counter</a></li>";                 
                }
                $pagination .= "<li class=\"elipses disabled\"><a href=\"#\">...</a></li>";
                $pagination .= "<li><a href=\"" . $targetpage . $pagestring . $lpm1 . "\">$lpm1</a></li>";
                $pagination .= "<li><a href=\"" . $targetpage . $pagestring . $lastpage . "\">$lastpage</a></li>";       
            }
            //in middle; hide some front and some back
            elseif($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2))
            {
                $pagination .= "<li><a href=\"" . $targetpage . $pagestring . "1\">1</a></li>";
                $pagination .= "<li><a href=\"" . $targetpage . $pagestring . "2\">2</a></li>";
                $pagination .= "<li class=\"elipses disabled\"><a href=\"#\">...</a></li>";
                for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++)
                {
                    if ($counter == $page)
                        $pagination .= "<li class=\"active\"><a href=\"#\">$counter</a></li>";
                    else
                        $pagination .= "<li><a href=\"" . $targetpage . $pagestring . $counter . "\">$counter</a></li>";                 
                }
                $pagination .= "<li class=\"elipses disabled\"><a href=\"#\">...</a></li>";
                $pagination .= "<li><a href=\"" . $targetpage . $pagestring . $lpm1 . "\">$lpm1</a></li>";
                $pagination .= "<li><a href=\"" . $targetpage . $pagestring . $lastpage . "\">$lastpage</a></li>";       
            }
            //close to end; only hide early pages
            else
            {
                $pagination .= "<li><a href=\"" . $targetpage . $pagestring . "1\">1</a></li>";
                $pagination .= "<li><a href=\"" . $targetpage . $pagestring . "2\">2</a></li>";
                $pagination .= "<li class=\"elipses\"><a href=\"#\">...</a></li>";
                for ($counter = $lastpage - (1 + ($adjacents * 3)); $counter <= $lastpage; $counter++)
                {
                    if ($counter == $page)
                        $pagination .= "<li class=\"active\"><a href=\"#\">$counter</a></li>";
                    else
                        $pagination .= "<li><a href=\"" . $targetpage . $pagestring . $counter . "\">$counter</a></li>";                 
                }
            }
        }
        
        //next button
        if ($page < $counter - 1) 
            $pagination .= "<li><a href=\"" . $targetpage . $pagestring . $next . "\">next →</a></li>";
        else
            $pagination .= "<li class=\"disabled\"><a href=\"#\">next →</a></li>";
        $pagination .= "</ul>\n";
    }
    
    return $pagination;

}
$label = !empty($_GET['l']) ? $_GET['l'] : '';
if(!empty($_GET['cat'])) {
    if($_GET['cat'] == 1) {
        unset($_SESSION['label']);
    } else {
        $_SESSION['label'] = $_GET['cat']; 
    }       
}
$label = !empty($_SESSION['label']) ? $_SESSION['label'] : $label;
$bid = !empty($_GET['b']) ? $_GET['b'] : default_blog;
$max = !empty($_GET['max']) ? $_GET['max'] : 10;
$page = !empty($_GET['page']) ? $_GET['page'] : 1;
$data = checkDuplicate($bid,$label,$max,$page);

$totalResults = $data->feed->{'openSearch$totalResults'}->{'$t'};
$startIndex = $data->feed->{'openSearch$startIndex'}->{'$t'};
$PerPage = $data->feed->{'openSearch$itemsPerPage'}->{'$t'};
$category = $data->feed->category;
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
                            <h4><i class="icon-reorder"></i> Responsive Table <code>table-responsive</code></h4>
                            <div class="toolbar no-padding">
                                <div class="btn-group"> <span class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span> </div>
                            </div>
                        </div>
                        <div class="widget-content no-padding">
                            <div id="DataTables_Table_0_wrapper" class="dataTables_wrapper form-inline" role="grid">
                                <div class="row">
                                    <div class="dataTables_header clearfix">
                                        <div class="col-md-3">
                                            <div id="DataTables_Table_0_length" class="dataTables_length">
                                                <label>                                    
                                                    <select name="DataTables_Table_0_length" class="form-control">
                                                        <option value="5" selected="selected">
                                                            5
                                                        </option>
                                                        <option value="10">
                                                            10
                                                        </option>
                                                        <option value="25">
                                                            25
                                                        </option>
                                                        <option value="50">
                                                            50
                                                        </option>
                                                        <option value="-1">
                                                            All
                                                        </option>
                                                    </select>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <label>                                    
                                                <select id="input17" class="select2-select-00 col-md-12 full-width-fix" onchange="onChageLabel(this.value)">
                                                    <option value="1">
                                                        Filter by Category
                                                    </option>
                                                    <?php if(!empty($category)):
                                                        foreach ($category as $key => $term):?>
                                                    <option value="<?php echo $term->term;?>" <?php if(!empty($label) && $label == $term->term): echo 'selected'; endif;?>>
                                                        <?php echo $term->term;?>
                                                    </option>
                                                    <?php endforeach; endif;?>
                                                </select>
                                            </label>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="dataTables_filter" id="DataTables_Table_0_filter">
                                                
                                                <form method="get" action="<?php echo base_url;?>blogger/search.php">
                                                    <a class="btn btn-info" href="post/searchbloggerbost"><i class="icon-search"></i></a>
                                                <label>
                                                    <input type="hidden" name="start" value="1">
                                                    <input type="hidden" name="action" value="1">
                                                    <div class="input-group">
                                                        <span class="input-group-addon">
                                                            <i class="icon-search">
                                                            </i>
                                                        </span>
                                                        <input type="text" aria-controls="DataTables_Table_0" class="form-control pull-right" name="keyword" />
                                                    </div>
                                                </label>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <table class="table table-striped table-bordered table-hover table-checkable table-responsive datatable dataTable" id="DataTables_Table_0" aria-describedby="DataTables_Table_0_info">
                                    <thead>
                                        <tr>
                                            <th>
                                                <input type="checkbox" class="uniform" name="allbox" id="checkAll" />
                                            </th>
                                            <th style="width: 50%">
                                                Name
                                            </th>
                                            <th class="hidden-xs">
                                                Categories
                                            </th>
                                            <th>
                                                Status
                                            </th>
                                            <th>
                                                Action
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody role="alert" aria-live="polite" aria-relevant="all">
                                        <?php if(!empty($data)):
                                            $blogger = new blogger();
                                         foreach ($data->feed->entry as $key => $value):
                                            //echo '<pre>';
                                            //var_dump($value);
                                            //echo '</pre>';

                                            
                                            //label
                                            $labels = array();
                                            $labelLink = array();
                                            //$status = 'End';
                                            foreach ($value->category as $categorys) {
                                                $labels[] = $categorys->term;
                                                $labelLink[] = '<a href="'.base_url.'blogger/index.php?cat='.urlencode($categorys->term).'"><span class="label label-info">'.$categorys->term . '</span></a>';
                                                if(preg_match('/Continue/', $categorys->term)) {
                                                    //$status = 'End';
                                                }
                                            }
                                            $cat = implode(',', $labels);
                                            if(preg_match('/Continue/', $cat)) {
                                                $status = '<span class="label label-danger">Continue</span>';
                                            } else {
                                                $status = '<span class="label label-success">End</span>';
                                            }
                                            //link
                                            foreach ($value->link as $links) {
                                                if($links->rel == 'alternate') {
                                                    $link = $links->href;
                                                }
                                            }
                                            $arr   = explode('-', $value->id->{'$t'});
                                            $pid   = $arr[2];
                                            //$img = $blogger->resize_image($value->{'media$thumbnail'}->url,'72-c');
                                            $img = @$value->{'media$thumbnail'}->url;
                                            ?><tr class="odd">
                                            <td class="checkbox-column  sorting_1">
                                               <input type="checkbox" id="itemid" name="itemid[]" class="uniform" value="<?php echo @$pid; ?>" />
                                            </td>
                                            <td class=" "><span class="responsiveExpander"></span>
                                                <a href="<?php echo @$link;?>" target="_blank"><img src="<?php
                                                 echo $img;?>" style="float: left;max-width: 72px" class="img-rounded" />&nbsp;<?php echo @$value->title->{'$t'};?>
                                            </a>
                                            </td>
                                            <td class=" "><?php echo implode(' ', $labelLink);?></td>
                                            <td class=" "><?php echo $status;?></td>
                                            <td class=" ">
                                                <div class="btn-group">
                                                <button class="btn btn-sm dropdown-toggle" data-toggle="dropdown">
                                                    <i class="icol-cog"></i>
                                                    <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a href="<?php echo base_url; ?>blogger/add.php?id=<?php echo @$pid; ?>&title=<?php echo urlencode(@$value->title->{'$t'});?>&img=<?php echo @$img;?>&l=<?php echo @urlencode($cat);?>"><i class="icon-edit"></i> Add & Edit</a>
                                                    </li>
                                                    <li>
                                                        <a href="<?php echo base_url; ?>blogger/edit.php?id=<?php echo @$pid; ?>"><i class="icon-edit"></i> Edit</a>
                                                    </li>
                                                    <li>
                                                        <a href="<?php echo base_url; ?>blogger/delete.php?id=<?php echo @$pid; ?>&do=post"><span style="color: red;"><i class="icon-remove"></i> Remove</span></a>                                                    
                                                    </li>
                                                    <li>
                                                         <a data-modal="true" data-text="Do you want to delete this Blog?" data-type="confirm" data-class="error" data-layout="top" data-action="blogger/delete.php?id=<?php echo @$pid; ?>" class="btn-notification"><i class="icon-remove"></i> Remove</a>
                                                    </li>
                                                </ul>
                                            </td>
                                        </tr><?php endforeach;?>                                      
                                        <?php endif;?>                                      
                                    </tbody>
                                </table>
                                <div class="row">
                                    <div class="dataTables_footer clearfix">
                                        <div class="col-md-6">
                                            <div class="dataTables_info" id="DataTables_Table_0_info">Showing <?php echo $PerPage;?> to <?php echo ($PerPage * $page);?> of <?php echo $totalResults;?> entries</div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="dataTables_paginate paging_bootstrap">
                                                <?php
                                                $targetpage = base_url . 'blogger/index.php';
                                                $pagestring = '?page=';
                                                $adjacents = 2;
                                                 echo getPaginationString($page, $totalResults, $PerPage, $adjacents, $targetpage, $pagestring);?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
    <script type="text/javascript" src="<?php echo base_url; ?>assets/plugins/noty/packaged/jquery.noty.packaged.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url; ?>assets/plugins/select2/select2.min.js"></script>
    <link href="<?php echo base_url;?>assets/css/plugins/select2.css" rel="stylesheet" type="text/css" />
    <script type="text/javascript">
        $( document ).ready(function() {
            <?php if(!empty($_GET['m'])):?>
                var success = generate('<?php echo $_GET['m'];?>');
                setTimeout(function () {
                    $.noty.closeAll();
                }, 4000);
            <?php endif;?>
            $(".btn-notification").click(function() {
                    var b = $(this);
                    noty({
                        text: b.data("text"),
                        type: b.data("class"),
                        layout: b.data("layout"),
                        timeout: 2000, modal: b.data("modal"),
                        buttons: (b.data("type") != "confirm") ? false : [{addClass: "btn btn-primary", text: "Ok", onClick: function(c) {
                                    c.close();
                                    window.location = "<?php echo base_url; ?>" + b.data("action");
                                }}, {addClass: "btn btn-danger", text: "Cancel", onClick: function(c) {
                                    c.close();
                                    noty({force: true, text: 'You clicked "Cancel" button', type: "error", layout: b.data("layout")});
                                    setTimeout(function() {
                                        $.noty.closeAll();
                                    }, 4000);
                                }
                            }]});
                    return false
                });             
                $("#input17").select2();
            
        }); 
        function generate(type) {
            var n = noty({
                text: type,
                type: type,
                dismissQueue: false,
                layout: 'top',
                theme: 'defaultTheme'
            });
            console.log(type + ' - ' + n.options.id);
            return n;
        }

        function generateAll() {
            generate('alert');
            generate('information');
            generate('error');
            generate('warning');
            generate('notification');
            generate('success');
        }  
        function onChageLabel(val) {
            if (val) window.location.href= '<?php echo base_url;?>blogger/index.php?cat=' + val
        }             
    </script>
</body>
</html>
<?php } else {
    include 'search/index_found.php';
}?>