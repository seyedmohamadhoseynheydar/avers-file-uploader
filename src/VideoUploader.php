<?php


namespace avers\fileUploader;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;
use yii\web\View;

class VideoUploader extends Widget
{



    public $form;
    public $form_name;
    public $form_name_capital;
    public $model;


    public function init()
    {
        parent::init();
        Asset::register( $this->getView() );
    }

    public function run()
    {
        parent::init();
        
        $html = '
            <input type="hidden" id="ajax-upload-url-avers" url="'. \yii\helpers\Url::to(['@vendor/avers/fileUploader/src/controllers/file/ajax-upload']) .'">
            <input type="hidden" id="web-directory-avers" value="'. \yii\helpers\Url::to('@vendor/avers/fileUploader/src/controllers/') .'">
        ';
        $html .= '<label>';
        $html .= '' . Yii::t('app', 'upload main video') . '';
        $html .= '</label>';
        $html .= '<span class="hidden">';
        $html .= '' . $this->form->field($this->model, 'video_id', ['template' => '{input}'])->hiddenInput() . '';
        $html .= '</span>';
        $html .= '<span class="hidden">';
        $html .= '' . $this->form->field($this->model, 'main_video')->fileInput(['onchange' => 'uploadVideo("' . $this->form_name . '","' . $this->form_name_capital . '")']) . '';
        $html .= '</span>';
        $html .= '<button onclick=\'openUploadVideo("' . $this->form_name . '")\' type="button" class="btn btn-primary btn-sm ml-4px">
                        <i class="fa fa-upload"></i>
                  </button>';
        $html .= '<button id="remove-video" type="button" class="btn btn-danger btn-sm">
                        <i class="fa fa-close"></i>
                  </button>';
        $html .= '<div id="main-video" style="margin-top: 10px">';
        if ($this->model->video_id) {
            $html .= '' . $this->model->getVideoTag() . '';
        }
        $html .= '</div>';
        $html .= '<p class="text-muted">';
        $html .= '(' . Yii::t("app", "Maximum 30 MB") . ')';
        $html .= '</p>';
        return $html;
    }
}
