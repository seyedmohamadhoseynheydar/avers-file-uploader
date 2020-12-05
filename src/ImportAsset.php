<?php


namespace avers\fileUploader;


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
