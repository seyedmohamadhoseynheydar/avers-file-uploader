<?php
namespace avers\aversFileUploader;

use yii\web\AssetBundle;

class Asset extends AssetBundle {

    public $sourcePath = '@vendor/avers-file-uploader/file-uploader/src/js';
    public $js = ['image-uploader.js'];
    public $depends = [
    ];
}
