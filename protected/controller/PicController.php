<?php
/**
 * Created by PhpStorm.
 * User: kouk0
 * Date: 2019/1/30
 * Time: 12:26
 */

class PicController extends Controller
{
    public function actionGet()
    {
        header('Content-type: image/jpeg');
        require(APP_DIR.'/protected/include/Image.php');
        $pid=arg("pid");
        if(!empty($pid)){
            $targetPath = CONFIG::GET("MHS_PIC_SERVICE_ROOT");
            $pic = $targetPath.$pid;
            if(file_exists($pic)) //确保文件存在
            {
                $size = arg('size');
                if(!empty($size))
                    echo image_resize(file_get_contents($pic), $size, $size);   //调整图片尺寸
                else
                    echo file_get_contents($pic); //原图输出模式
            }
            else{
                // TODO 返回一个默认的空图片
            }


        }
    }

}