<?php


namespace avers\fileUploader;

use yii\base\Widget;

class ImportAsset extends Widget
{



    public function init()
    {
        parent::init();
        Asset::register( $this->getView() );
      
    }

    public function run()
    {
        parent::init();
    }

}
