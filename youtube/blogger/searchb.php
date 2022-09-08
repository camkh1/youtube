<?php
include dirname(__FILE__) .'/../top.php';
if (empty($_SESSION['tokenSessionKey'])) {
    $client = new Google_Client();
    if($client->isAccessTokenExpired()){
        header('Location: ' . base_url .'login.php?back=' . urlencode($CURRENT_URL));
    }
}
if(!empty($_POST['keyword'])) {
    $keyword = urlencode($_POST['keyword']);
    //header("Location: " . base_url . "blogger/search.php?start=1&keyword=" . $keyword . "&frompost=".$_POST['idpost']);
}
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
                        <h3>Search
                        </h3>
                    </div>
                </div>
                

                <!-- data -->
                <div class="col-md-12">
                    <div class="widget box">
                        <div class="widget-header">
                            <h4><i class="icon-reorder"></i> Search post</h4>
                            <div class="toolbar no-padding">
                                <div class="btn-group"> <span class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span> </div>
                            </div>
                        </div>
                        <div class="widget-content no-padding">
                            <form method="post" id="validate" class="form-horizontal row-border">
                                <div class="form-group">
                                    <div class="col-md-12">
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="icon-search"></i></span>
                                            <input type="text" class="form-control required" name="keyword" required/>
                                            <div class="input-group-btn">
                                                <button type="submit" class="btn btn-info" name="submit" value="Serch">
                                                    Get code                           
                                                </button>
                                            </div>
                                        </div>
                                    </div>                            
                                </div>
                            </form>
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