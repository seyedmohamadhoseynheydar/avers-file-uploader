<?php
namespace avers\aversFileUploader;

use yii\web\AssetBundle;

class Asset extends AssetBundle {

    public $sourcePath = '/js';
    public $js = ['image-uploader.js'];
    public $depends = [
    ];
}
