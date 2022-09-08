<?php

/**
 * ChipVN Library
 * 
 * @package		ChipVN
 * @author		Phan Thanh Cong <ptcong90 at gmail dot com>
 * @copright	chiplove.9xpro aka ptcong90
 * @version		2.0
 * @release		Jul 25, 2013
 */
/**
 * This class use PHPThumb 3.0
 * @link http://phpthumb.gxdlabs.com/
 */

namespace ChipVN;

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'phpThumb/ThumbLib.inc.php';

class Image
{
    /**
     * @return boolean - TRUE if cop success
     */
    public static function crop($imagePath, $startX, $startY, $cropWidth, $cropHeight)
    {
        try {
            $thumb = \PhpThumbFactory::create($imagePath);
            $thumb->crop($imagePath, $startX, $startY, $cropWidth, $cropHeight);
            $thumb->save($imagePath);
            return true;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @return boolean - TRUE if crop success
     */
    public static function cropFromCenter($imagePath, $cropWidth, $cropHeight = null)
    {
        try {
            $thumb = \PhpThumbFactory::create($imagePath);
            $thumb->cropFromCenter($cropWidth, $cropHeight);
            $thumb->save($imagePath);
            return true;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @return boolean - TRUE if resize success
     */
    public static function resizePercent($imagePath, $percent = 0)
    {
        try {
            $thumb = \PhpThumbFactory::create($imagePath);
            $thumb->resizePercent($percent);
            $thumb->save($imagePath);
            return true;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @return boolean - TRUE if resize success
     */
    public static function resize($imagePath, $width, $height, $resize_mod = 'basic')
    {
        try {
            $width = intval($width);
            $height = intval($height);
            $thumb = \PhpThumbFactory::create($imagePath);
            //resize tự crop thích nghi
            if ($resize_mod == 'adaptive') {
                $thumb->adaptiveResize($width, $height);
            } else {
                //resize bình thường
                $thumb->resize($width, $height);
            }
            $thumb->save($imagePath);
            return true;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Rotate image by degree
     * 
     * @param string $imagePath
     * @param integer $degree
     * @return boolean
     */
    public static function rotate($imagePath, $degree = 180)
    {
        try {
            $thumb = \PhpThumbFactory::create($imagePath);
            $thumb->rotateImageNDegrees($degree);
            $thumb->save($imagePath);
            return TRUE;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @return boolean - TRUE if watermark success
     */
    public static function watermark($imagePath, $logoPath, $position = 'rb', $padding = 0)
    {
        try {
            $thumb = \PhpThumbFactory::create($imagePath);
            $thumb->resize(0, 0)->createWatermark($logoPath, $position, $padding);
            $thumb->save($imagePath);
            return true;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @return boolean - TRUE if leech success
     */
    public static function leech($imageUrl, $newImagePath)
    {
        $imageUrl = str_replace(' ', '%20', trim(urldecode($imageUrl)));

        if ($data = fopen($imageUrl, "rb")) {
            $newfile = fopen($newImagePath, "w");
            while ($buff = fread($data, 1024 * 8)) {
                fwrite($newfile, $buff);
            }
            fclose($data);
            fclose($newfile);
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Gets mime type of file
     * @return string|boolean - FALSE if mime not found
     */
    public static function mimeType($filePath)
    {
        $filename = realpath($filePath);
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (preg_match('/^(?:jpe?g|png|[gt]if|bmp|swf)$/', $extension)) {
            $file = getimagesize($filename);

            if (isset($file['mime']))
                return $file['mime'];
        }

        if (class_exists('finfo', FALSE)) {
            if ($info = new finfo(defined('FILEINFO_MIME_TYPE') ? FILEINFO_MIME_TYPE : FILEINFO_MIME)) {
                return $info->file($filename);
            }
        }

        if (ini_get('mime_magic.magicfile') AND function_exists('mime_content_type')) {
            return mime_content_type($filename);
        }
        return FALSE;
    }

}
