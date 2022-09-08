<?php
include dirname(__FILE__) .'/../top.php';
if (empty($_SESSION['tokenSessionKey'])) {
    $client = new Google_Client();
    if($client->isAccessTokenExpired()){
        header('Location: ' . base_url .'login.php?back=' . urlencode($CURRENT_URL));
    }
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
                        <h3>Auto Post to Blogger and Facebook
                        </h3>
                    </div>
                </div>
                

                <!-- data -->
                <div class="col-md-12">
                    <div class="widget box">
                        <div class="widget-header">
                            <h4>Search</h4>
                            <div class="toolbar no-padding">
                                <div class="btn-group"> <span class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span> </div>
                            </div>
                        </div>
                        <div class="widget-content no-padding">
                            ssssssssssss
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