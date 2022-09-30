<header class="header navbar navbar-fixed-top" role="banner">        
    <div class="container">
        <ul class="nav navbar-nav">
            <li class="nav-toggle"><a href="javascript:void(0);" title=""><i class="icon-reorder"></i></a></li>
        </ul>
        <a class="navbar-brand" href=""> <img src="assets/img/logo.png" alt="logo" /> <strong>AD</strong>MIN </a> <a href="#" class="toggle-sidebar bs-tooltip" data-placement="bottom" data-original-title="Toggle navigation"> <i class="icon-reorder"></i> </a>
        <ul class="nav navbar-nav navbar-left hidden-xs hidden-sm">
            <li><a href="<?php echo base_url;?>index.php"> Home</a></li>               
        </ul>
        <ul class="nav navbar-nav">
            <li class="dropdown user"> <a href="#" class="dropdown-toggle" data-toggle="dropdown"> <i class="icon-male"></i> <span class="username">
                         Youtube</span> <i class="icon-caret-down small"></i> </a>
                <ul class="dropdown-menu"> 
                    <li><a href="<?php echo base_url;?>index.php"><i class="icon-youtube-play"></i> Post by Chanel</a></li>
                    <li><a href="<?php echo base_url;?>blogger/post_video.php"><i class="icon-share"></i> Post by Url</a></li>
                    <li><a href="<?php echo base_url;?>share.php?do=share"><i class="icon-share"></i> Share now</a></li>
                </ul>
            </li>
        </ul>
        <ul class="nav navbar-nav">
            <li class="dropdown user"> <a href="#" class="dropdown-toggle" data-toggle="dropdown"> <i class="icon-male"></i> <span class="username">
                         Blogger</span> <i class="icon-caret-down small"></i> </a>
                <ul class="dropdown-menu"> 
                    <li><a href="<?php echo base_url;?>blogger/index.php"><i class="icon-youtube-play"></i> List</a></li>
                    <li><a href="<?php echo base_url;?>blogger/add.php"><i class="icon-share"></i> Add</a></li>
                </ul>
            </li>
        </ul>
        <ul class="nav navbar-nav navbar-right">
            <li class="dropdown user"> <a href="#" class="dropdown-toggle" data-toggle="dropdown"> <i class="icon-male"></i> <span class="username">
                         <?php echo !empty($_SESSION['email']) ?$_SESSION['email']: 'Not Login';?></span> <i class="icon-caret-down small"></i> </a>
                <ul class="dropdown-menu"> 
                    <li><a href="<?php echo base_url;?>login.php"><i class="icon-key"></i> Login</a></li>
                    <li><a href="<?php echo base_url;?>login.php?renew=1"><i class="icon-key"></i> New Login</a></li>
                </ul>
            </li>
        </ul>
    </div>
</header>
<!--

This is a minified version of the ThemeForest-theme "Melon - Flat & Responsive Admin Template".



Author: Simon 'Stammi' Stamm <http://themeforest.net/user/Stammi?ref=stammi>
http://envato.stammtec.de/themeforest/melon/


Note: If you buy my template on ThemeForest, you will receive the non-minified and commented/ documentated version!

This is a minified version to prevent stealing.

-->