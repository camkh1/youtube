<?php
$fileNames = !empty($_GET['search_found']) ? $_GET['search_found'] : 'search_found';
$searchFound = dirname(__FILE__) . '/../../uploads/blogger/posts/'.$fileNames.'.csv';
$file = new file();
$search = $file->getFileContent($searchFound);
$data = array();
if(!empty($search)) {
    foreach ($search as $key => $value) {
        if(!empty($value->bname)) {
            $pid = $value->bname;
            $bid = $value->bid;
            break;
        }
    }
    $getPostSearch = 'https://www.googleapis.com/blogger/v3/blogs/'.$bid.'/posts/'.$pid.'?key=AIzaSyBM4KVC_25FUWH1auWDqsUfCcq30DFLkNM';
    $str = file_get_contents($getPostSearch);
    $data = json_decode($str);
}
?>
<!doctype html>
<html>
<head>
  <?php include __DIR__.'/../../head.php';?>
<title>Search found</title>
<script type="text/javascript" src="<?php echo base_url;?>assets/plugins/datatables/jquery.dataTables.min.js"></script>
</head>
<body>
  <?php include __DIR__.'/../../header.php';?>
      <div id="container">
        <div id="content">
            <div class="container">
                <?php include __DIR__.'/../../leftside.php';?>
                <div class="page-header">
                    <div class="page-title">
                        <h3>Search found
                        </h3>
                    </div>
                </div>
                

                <!-- data -->
                <div class="col-md-12">
                    <div class="widget box">
                        <div class="widget-header">
                            <h4><i class="icon-reorder"></i> Search found</h4>
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
                                            $content = @$data->content;
                                            if(!empty($content)):
                                            preg_match('/<img.+src=[\'"](?P<src>.+?)[\'"].*>/i', $content, $image);
                                            $blogger = new blogger();
                                            $labels = array();
                                            $labelLink = array();
                                            //$status = 'End';
                                            foreach ($data->labels as $categorys) {
                                                $labels[] = $categorys;
                                                $labelLink[] = '<a href="'.base_url.'blogger/index.php?cat='.urlencode($categorys).'"><span class="label label-info">'.$categorys . '</span></a>';
                                                if(preg_match('/Continue/', $categorys)) {
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
                                            $link = $data->url;
                                            $pid   = $search[0]->bname;
                                            $image = @$image['src'];
                                            ?><tr class="odd">
                                            <td class="checkbox-column  sorting_1">
                                               <input type="checkbox" id="itemid" name="itemid[]" class="uniform" value="<?php echo @$pid; ?>" />
                                            </td>
                                            <td class=" "><span class="responsiveExpander"></span>
                                                <a href="<?php echo @$link;?>" target="_blank"><img src="<?php
                                                $img = $blogger->resize_image($image,'72-c');
                                                 echo $img;?>" style="float: left;max-width: 72px" class="img-rounded" />&nbsp;<?php echo @$data->title;?>
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
                                                        <a href="<?php echo base_url; ?>blogger/add.php?id=<?php echo @$pid; ?>&title=<?php echo urlencode(@$data->title);?>&img=<?php echo $blogger->resize_image($image,'0');?>&l=<?php echo @urlencode($cat);?>"><i class="icon-edit"></i> Add & Edit</a>
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
                                        </tr>                                      
                                        <?php else:?>
                                            <tr>
                                                <td>Search not found</td>
                                            </tr>
                                        <?php endif; endif;?>                                      
                                    </tbody>
                                </table>
                                <div class="row">
                                    <div class="dataTables_footer clearfix">
                                        <div class="col-md-6">
                                            <div class="dataTables_info" id="DataTables_Table_0_info">Showing 0 to 0 of 0 entries</div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="dataTables_paginate paging_bootstrap">
                                                
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