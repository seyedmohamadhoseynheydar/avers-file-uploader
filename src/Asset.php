<?php
namespace avers\fileUploader;

use yii\web\AssetBundle;

class Asset extends AssetBundle {

    public $sourcePath = '@vendor/avers/file-uploader/src';
    public $js = ['assets/js/file-uploader.js'];
    public $css = ['assets/css/file-uploader.css'];
    public $depends = [
    ];
}
