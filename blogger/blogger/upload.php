<?php
include dirname(__FILE__) .'/../top.php';
// $imagePath = dirname(__FILE__) . '/../uploads/2018-08-05_13-28-53.jpg';
// $logoPath = dirname(__FILE__) . '/../uploads/watermark/web-logo.png';


//function uploadImageFile() { // Note: GD library is required for this function

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $iWidth = $_POST['w'];
        $iHeight = $_POST['h']; // desired image result dimensions
        $iJpgQuality = 90;
        $resize_to   = 800;

        if ($_FILES) {

            // if no errors and size less than 250kb
            if (! $_FILES['image_file']['error'] && $_FILES['image_file']['size'] < 1000 * 3000) {
                if (is_uploaded_file($_FILES['image_file']['tmp_name'])) {

                    // new unique filename
                    $newName = md5(time().rand());
                    $sTempFileName = dirname(__FILE__) . '/../uploads/cache/' . $newName;

                    // move uploaded file into cache folder
                    move_uploaded_file($_FILES['image_file']['tmp_name'], $sTempFileName);

                    // change file permission to 644
                    @chmod($sTempFileName, 0644);

                    if (file_exists($sTempFileName) && filesize($sTempFileName) > 0) {
                        $aSize = getimagesize($sTempFileName); // try to obtain image info
                        if (!$aSize) {
                            @unlink($sTempFileName);
                            return;
                        }

                        // check for image type
                        switch($aSize[2]) {
                            case IMAGETYPE_JPEG:
                                $sExt = '.jpg';

                                // create a new image from file 
                                $vImg = @imagecreatefromjpeg($sTempFileName);
                                break;
                            /*case IMAGETYPE_GIF:
                                $sExt = '.gif';

                                // create a new image from file 
                                $vImg = @imagecreatefromgif($sTempFileName);
                                break;*/
                            case IMAGETYPE_PNG:
                                $sExt = '.png';

                                // create a new image from file 
                                $vImg = @imagecreatefrompng($sTempFileName);
                                break;
                            default:
                                @unlink($sTempFileName);
                                return;
                        }

                        /* resize */
                        include dirname(__FILE__) .'/../library/ChipVN/Loader.php';
                        \ChipVN\Loader::registerAutoLoad();

                        // create a new true color image
                        $vDstImg = @imagecreatetruecolor( $iWidth, $iHeight );

                        // copy and resize part of an image with resampling
                        imagecopyresampled($vDstImg, $vImg, 0, 0, (int)$_POST['x1'], (int)$_POST['y1'], $iWidth, $iHeight, (int)$_POST['w'], (int)$_POST['h']);

                        // define a result image filename
                        $sResultFileName = $sTempFileName . $sExt;

                        // output image to file
                        imagejpeg($vDstImg, $sResultFileName, $iJpgQuality);
                        @unlink($sTempFileName);
                        
                        if ($resize_to > 0) {
                            /*resize image*/
                            $maxDim = $resize_to;
                            $file_name = $sResultFileName;
                            list($width, $height, $type, $attr) = getimagesize( $file_name );
                            if ( $width < $maxDim || $height < $maxDim ) {
                                $target_filename = $file_name;
                                $ratio = $width/$height;
                                if( $ratio > 1) {
                                    $new_width = $maxDim;
                                    $new_height = $maxDim/$ratio;
                                } else {
                                    $new_width = $maxDim*$ratio;
                                    $new_height = $maxDim;
                                }

                                $src = imagecreatefromstring( file_get_contents( $file_name ) );
                                $dst = imagecreatetruecolor( $new_width, 415 );
                                imagecopyresampled( $dst, $src, 0, 0, 0, 0, $new_width, $new_height, $width, $height );
                                imagedestroy( $src );
                                imagejpeg( $dst, $target_filename ); // adjust format as needed
                                imagedestroy( $dst );
                            }
                            /*end resize image*/
                        }
                        /* get some option*/

                        $hotImg = false;
                        if(!empty($_POST['vdotype'])) {
                            $hotImg = $_POST['vdotype'];
                        }
                        /* end get some option*/

                        /* watermark */
                        
                        $service  = 'Picasa';
                        $uploader = \ChipVN\Image_Uploader::factory($service);
                        $uploader->login('104724801112461222967', '0689989@Sn');

                        $imagePath = $sResultFileName;
                        $watermark = 1;
                        /* logo (right bottom, right center, right top, left top, .v.v.) */
                        if(!empty($_POST['watermark'])) {
                            $logoPosition = 'lb';
                            $logoPath = dirname(__FILE__) . '/../uploads/watermark/web-logo.png';
                            \ChipVN\Image::watermark($imagePath, $logoPath, $logoPosition);
                        }
                        
                        
                        if ($hotImg) {
                            $hotPos = 'rt';
                            $hot = dirname(__FILE__) . '/../uploads/watermark/hot/'.$hotImg.'.png';
                            \ChipVN\Image::watermark($imagePath, $hot, $hotPos);
                        }
                        /*with button player*/
                        // $play = dirname(__FILE__) . '/../uploads/watermark/play-button.png';
                        // $pPos = 'cc';
                        // if ($watermark) {
                        //     \ChipVN\Image::watermark($imagePath, $play, $pPos);
                        // }
                        $uploader->setAlbumId('6139092860158818081');
                        
                        //$iamge = $uploader->upload($imagePath);
                        $iamge = $imagePath;
                        //@unlink($sResultFileName);
                        //return $iamge;
                    }
                }
            }
        }
        if(preg_match('/error/', $iamge)) {
            $data = array("error" => $iamge); 
        } else {
            $data = array("image" => $iamge); 
        }       
        echo json_encode($data);
    }
    
//}

    if(!empty($_GET['url'])) {
        $hotImg = @$_GET['hot'];
        include dirname(__FILE__) .'/../library/ChipVN/Loader.php';
        \ChipVN\Loader::registerAutoLoad();

        $imgUrl = resize_image($_GET['url'],1000);
        $info     = pathinfo($imgUrl);
        $newName = md5(time().rand());
        $sTempFileName = dirname(__FILE__) . '/../uploads/cache/' . $newName.'.'.$info['extension'];
        @copy($imgUrl, $sTempFileName);

        /*small image*/
        $maxSmall = 691;
        $file_name = $sTempFileName;
        list($width, $height, $type, $attr) = @getimagesize( $file_name );
        $target_filename = $file_name;
        $ratio = $width/$height;
        if( $ratio > 1) {
            $new_width = $maxSmall;
            $new_height = $maxSmall/$ratio;
        } else {
            $new_width = $maxSmall*$ratio;
            $new_height = $maxSmall;
        }
        $src = imagecreatefromstring( file_get_contents( $file_name ) );
        $dst = imagecreatetruecolor( $new_width, 635 );
        imagecopyresampled( $dst, $src, 0, 0, 0, 0, $new_width, $new_height, $width, $height );
        imagedestroy( $src );

        $sTempFileSmall = dirname(__FILE__) . '/../uploads/cache/' . $newName.'_635.'.$info['extension'];
        imagejpeg( $dst, $sTempFileSmall ); // adjust format as needed
        imagedestroy( $dst );
        /*End small image*/
        
        /*big image*/
        $maxDim = 1200;
        $file_name = $sTempFileName;
        list($width, $height, $type, $attr) = @getimagesize( $file_name );
        $target_filename = $file_name;
        $ratio = $width/$height;
        if( $ratio > 1) {
            $new_width = $maxDim;
            $new_height = $maxDim/$ratio;
        } else {
            $new_width = $maxDim*$ratio;
            $new_height = $maxDim;
        }
        $src = imagecreatefromstring( file_get_contents( $file_name ) );
        $dst = imagecreatetruecolor( $new_width, 635 );
        imagecopyresampled( $dst, $src, 0, 0, 0, 0, $new_width, $new_height, $width, $height );
        imagedestroy( $src );
        imagejpeg( $dst, $sTempFileName ); // adjust format as needed
        imagedestroy( $dst );
        /*End big image*/

        /*blur effect*/
        $file = $sTempFileName;
        $image = imagecreatefromjpeg($file);

            /* Get original image size */
            list($w, $h) = getimagesize($file);

            /* Create array with width and height of down sized images */
            $size = array('sm'=>array('w'=>intval($w/4), 'h'=>intval($h/4)),
                           'md'=>array('w'=>intval($w/2), 'h'=>intval($h/2))
                          );                       

            /* Scale by 25% and apply Gaussian blur */
            $sm = imagecreatetruecolor($size['sm']['w'],$size['sm']['h']);
            imagecopyresampled($sm, $image, 0, 0, 0, 0, $size['sm']['w'], $size['sm']['h'], $w, $h);

            for ($x=1; $x <=40; $x++){
                imagefilter($sm, IMG_FILTER_GAUSSIAN_BLUR, 999);
            } 

            imagefilter($sm, IMG_FILTER_SMOOTH,99);
            imagefilter($sm, IMG_FILTER_BRIGHTNESS, 10);        

            /* Scale result by 200% and blur again */
            $md = imagecreatetruecolor($size['md']['w'], $size['md']['h']);
            imagecopyresampled($md, $sm, 0, 0, 0, 0, $size['md']['w'], $size['md']['h'], $size['sm']['w'], $size['sm']['h']);
            imagedestroy($sm);

                for ($x=1; $x <=25; $x++){
                    imagefilter($md, IMG_FILTER_GAUSSIAN_BLUR, 999);
                } 

            imagefilter($md, IMG_FILTER_SMOOTH,99);
            imagefilter($md, IMG_FILTER_BRIGHTNESS, 10);        

        /* Scale result back to original size */
        imagecopyresampled($image, $md, 0, 0, 0, 0, $w, $h, $size['md']['w'], $size['md']['h']);
        imagedestroy($md);  

        // Apply filters of upsized image if you wish, but probably not needed
        //imagefilter($image, IMG_FILTER_GAUSSIAN_BLUR); 
        //imagefilter($image, IMG_FILTER_SMOOTH,99);
        //imagefilter($image, IMG_FILTER_BRIGHTNESS, 10);       

        //imagejpeg($image);
        imagejpeg( $image, $sTempFileName );
        imagedestroy($image);
        /*End blur effect*/

        /*merge image*/
        $hotPos = 'cc';
        \ChipVN\Image::watermark($sTempFileName, $sTempFileSmall, $hotPos);
        @unlink($sTempFileSmall);
        /*End merge image*/

        /*add banner type*/
        if (!empty($hotImg)) {
            $hot = 'rt';
            $hotSrc = dirname(__FILE__) . '/../uploads/watermark/hot/'.$hotImg.'.png';
            \ChipVN\Image::watermark($sTempFileName, $hotSrc, $hot);
        }
        /*End add banner type*/

        $logoPosition = 'lb';
        $logoPath = dirname(__FILE__) . '/../uploads/watermark/web-logo.png';
        \ChipVN\Image::watermark($sTempFileName, $logoPath, $logoPosition);
        $logo1Position = 'rb';
        $logo1 = dirname(__FILE__) . '/../uploads/watermark/web-logo1.png';
        \ChipVN\Image::watermark($sTempFileName, $logo1, $logo1Position);
    }

function resize_image($url, $imgsize, $height = '') {
        if (preg_match('/blogspot/', $url)) {
            
            //inital value
            $newsize = "s" . $imgsize;
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
            return $newurl;
        } 
        else if (preg_match('/googleusercontent/', $url)) {
            
            //inital value
            $newsize = "s" . $imgsize;
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
            return $newurl;
        } 
        else {
            return $url;
        }
    }
