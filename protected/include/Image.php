<?php
/**
 * Created by PhpStorm.
 * User: gaofan
 * Date: 2019-01-30
 * Time: 14:28
 */

function UploadPic($iid)
{
    $fileTypes = array('jpg', 'jpeg', 'png'); //支持的图片格式
    $picMaxSize = 1024 * 1024; //图片限制大小
    $targetPath = CONFIG::GET("MHS_PIC_SERVICE_ROOT");

    if (!empty($_FILES)) {
        if($_FILES['pic']['type'] != "") // 确认文件类型

            $ext = pathinfo($_FILES['pic']['name'])['extension'];
        if (!in_array($ext,$fileTypes))
            return 200001;

        if($_FILES['pic']['size'] > $picMaxSize) //大小过大
            return 200002;

        if(!IsMyItem($iid))
            return 200003; // 权限认证，不能修改他人的物品图片
        else {
            // TODO 已存在则删除
            move_uploaded_file($_FILES['pic']['tmp_name'],$targetPath.$iid);
            return 200; //成功
        }

    }
    //没有图片，无需上传
    return 200; //成功
}

//来自https://blog.csdn.net/maoxinwen1/article/details/79202442

/**
 * @param $imagedata    图像数据
 * @param $width        缩放宽度
 * @param $height       缩放高度
 * @param int $per      缩放比例，为0不缩放，>0忽略参数2、3的宽高
 * @return bool|string
 */
function image_resize($imagedata, $width, $height, $per = 0) {
    // 1 = GIF，2 = JPG，3 = PNG，4 = SWF，5 = PSD，6 = BMP，7 = TIFF(intel byte order)，8 = TIFF(motorola byte order)，9 = JPC，10 = JP2，11 = JPX，12 = JB2，13 = SWC，14 = IFF，15 = WBMP，16 = XBM

    // 获取图像信息
    list($bigWidth, $bigHight, $bigType) = getimagesizefromstring($imagedata);

    // 缩放比例
    if ($per > 0) {
        $width  = $bigWidth * $per;
        $height = $bigHight * $per;
    }

    // 创建缩略图画板
    $block = imagecreatetruecolor($width, $height);

    // 启用混色模式
    imagealphablending($block, false);

    // 保存PNG alpha通道信息
    imagesavealpha($block, true);

    // 创建原图画板
    $bigImg = imagecreatefromstring($imagedata);

    // 缩放
    imagecopyresampled($block, $bigImg, 0, 0, 0, 0, $width, $height, $bigWidth, $bigHight);

    // 生成临时文件名
    $tmpFilename = tempnam(sys_get_temp_dir(), 'image_');

    // 保存
    switch ($bigType) {
        case 1: imagegif($block, $tmpFilename);
            break;

        case 2: imagejpeg($block, $tmpFilename);
            break;

        case 3: imagepng($block, $tmpFilename);
            break;
    }

    // 销毁
    imagedestroy($block);

    $image = file_get_contents($tmpFilename);

    unlink($tmpFilename);

    return $image;
}
