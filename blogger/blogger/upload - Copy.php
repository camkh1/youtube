<?php
include dirname(__FILE__) .'/../top.php';
include dirname(__FILE__) .'/../library/ChipVN/Loader.php';
\ChipVN\Loader::registerAutoLoad();
$service  = 'Picasa';
$uploader = \ChipVN\Image_Uploader::factory($service);
$uploader->login('104724801112461222967', '0689989@Sn');
$imagePath = dirname(__FILE__) . '/../uploads/2018-08-05_13-28-53.jpg';
$logoPath = dirname(__FILE__) . '/../uploads/watermark/web-logo.png';


/* watermark */
$watermark = 1;
/* logo (right bottom, right center, right top, left top, .v.v.) */
$logoPosition = 'rb';
if ($watermark) {
    \ChipVN\Image::watermark($imagePath, $logoPath, $logoPosition);
}
$hot = dirname(__FILE__) . '/../uploads/watermark/hot/ch.png';
$hotPos = 'rt';
if ($watermark) {
    \ChipVN\Image::watermark($imagePath, $hot, $hotPos);
}
$play = dirname(__FILE__) . '/../uploads/watermark/play-button.png';
$pPos = 'cc';
if ($watermark) {
    \ChipVN\Image::watermark($imagePath, $play, $pPos);
}
// $uploader->setAlbumId('6139092860158818081');
// $url = $uploader->upload($imagePath);
// var_dump($url);
/*get albums ID*/
// $listAlbums = $uploader->listAlbums();
// var_dump($listAlbums);
