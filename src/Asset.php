<?php
namespace avers\aversFileUploader;

use yii\web\AssetBundle;

class Asset extends AssetBundle {

    public $sourcePath = '@vendor/avers-file-uploader/file-uploader/src';
    public $js = ['assets/js/image-uploader.js'];
    public $css = ['assets/css/image-uploader.css'];
    public $depends = [
    ];
}
