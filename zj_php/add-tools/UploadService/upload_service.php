<?php

/**
 *  
 *  最大上传文件大小为 1M。 
 * 
 *  >>> 图片上传
 *  上传文件如果是 jpg|png|gif 的图片，返回 xxx/big/xxx 格式的 url；
 *  可以通过把 big 替换成 middle、small、file 来瓶装出 大图、小图 和 原图的 url；
 *  大图最大尺寸：600 * 600； 中图最大尺寸：300 * 300；小图最大尺寸：100 * 100；
 * 
 *  >>> 上传文件
 *  如果上传的文件不是上述图片格式，返回 xxx/file/xxx 格式的 url；
 * 
 */

if (!isset($_REQUEST['sign']) || $_REQUEST['sign'] != 'WERGHJMOIJNBGHJLIM1234567890') {
    die("sign_error");
}

define("SERVER_HOST", "http://localhost/68_zj_php/trunk/zj_php/add-tools/UploadService/");


$fileData = $_REQUEST["file"];
if (strlen($fileData) > 1024 * 1024) {
    die("maxsize_1M");
}

$fileType = $_REQUEST["type"];
$fileType = ($fileType == "jpeg") ? "jpg" : $fileType;

$time = time();
$path = Date("Y/md", $time);

$fileName = md5($time + rand(123456, 1234567)) . "." . $fileType;

$rtUrl = null;
if (updateFile($fileData, "file/" . $path, $fileName)) {
    $rtUrl = SERVER_HOST . "file/" . $path . "/" . $fileName;
}

if (in_array($fileType, array("jpg", "jpeg", "png", "gif"))) {
    $valid = true;
    $valid = $valid && updateBigImage("file/" . $path . "/" . $fileName, "big", $path, $fileName, $fileType);
    $valid = $valid && updateBigImage("file/" . $path . "/" . $fileName, "middle", $path, $fileName, $fileType);
    $valid = $valid && updateBigImage("file/" . $path . "/" . $fileName, "small", $path, $fileName, $fileType);

    if ($valid) {
        $rtUrl = SERVER_HOST . "big/" . $path . "/" . $fileName;
    }
}

if ($rtUrl) {
    die($rtUrl);
}

die("fails");

/////////////////////////////////////////////////////////////////////////////

function updateBigImage($srcFile, $thumbTag, $path, $name, $type) {
    $imagePath = "big/" . $path;
    $dsize = 600;
    if ("middle" == $thumbTag) {
        $imagePath = "middle/" . $path;
        $dsize = 300;
    } else if ("small" == $thumbTag) {
        $imagePath = "small/" . $path;
        $dsize = 100;
    }

    $source = null;
    if ("jpg" == $type) {
        $source = imagecreatefromjpeg($srcFile);
    } else if ("png" == $type) {
        $source = imagecreatefrompng($srcFile);
    } else if ("gif" == $type) {
        $source = imagecreatefromgif($srcFile);
    }
    if (!$source) {
        return false;
    }

    list($sourceWidth, $sourceHeight) = getimagesize($srcFile);

    if (!file_exists($imagePath)) {
        createFolder($imagePath);
    }

    $width = $sourceWidth;
    $height = $sourceHeight;
    if ($width > $dsize) {
        $height = ($height * $dsize) / $width;
        $width = $dsize;
    }

    if ($height > $dsize) {
        $width = ($width * $dsize) / $height;
        $height = $dsize;
    }

    $newImg = imagecreatetruecolor($width, $height);
    imagecopyresized($newImg, $source, 0, 0, 0, 0, $width, $height, $sourceWidth, $sourceHeight);
    if ("jpg" == $type) {
        imagejpeg($newImg, $imagePath . "/" . $name);
    } else if ("png" == $type) {
        imagepng($newImg, $imagePath . "/" . $name);
    } else if ("gif" == $type) {
        imagegif($newImg, $imagePath . "/" . $name);
    }

    imagedestroy($source);
    imagedestroy($newImg);

    return true;
}

function updateFile($fileData, $filePath, $fileName) {
    if (!file_exists($filePath)) {
        createFolder($filePath);
    }

    $fileName = $filePath . "/" . $fileName;
    $file = fopen($fileName, "w");
    fwrite($file, $fileData);
    fclose($file);

    return true;
}

/**
 * 创建目录及赋予权限
 */
function createFolder($path) {
    if (!file_exists($path)) {
        createFolder(dirname($path));
        mkdir($path, 0777);
    }
    return true;
}

//$filename = 'icon22.jpg';
//$jpg = $_POST["file"];
//$file = fopen($filename,"w");
//fwrite($file,$jpg);
//fclose($file);
?>