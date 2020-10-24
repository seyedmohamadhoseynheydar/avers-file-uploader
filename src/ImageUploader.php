<?php


namespace avers\aversFileUploader;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;
use yii\web\View;

class ImageUploader extends Widget
{



    public $form;
    public $model;



    public function init()
    {
        parent::init();
        $this->registerJsFile('../js/image-uploader.js', ['positions' => View::POS_END]);
        if ($this->model->image_id != null) {
            $mainImage_id = $this->model->image_id;
        } else {
            $mainImage_id = '';
        }


    }

    public function run()
    {
        parent::init();

        $html = '';
        $html .= '<label>';
        $html .= '' . Yii::t('app', 'upload main image') . '';
        $html .= '</label>';
        $html .= '<span class="hidden">';
        $html .= '' . $this->form->field($this->model, 'image_id', ['template' => '{input}'])->hiddenInput() . '';
        $html .= '</span>';
        $html .= '<span class="hidden">';
        $html .= '' . $this->form->field($this->model, 'mainimage')->fileInput(['onchange' => 'uploadImage("news","News")']) . '';
        $html .= '</span>';
        $html .= '<button onclick=\'openUploadFile("news")\' type="button" class="btn btn-primary btn-sm">
                        <i class="fa fa-upload"></i>
                  </button>';
        $html .= '<button type="button" id="select-one-image"
                        class="btn btn-primary btn-sm"><i class="fa fa-folder-open"></i>
                  </button>';
        $html .= '<button id="remove-image" type="button" class="btn btn-danger btn-sm">
                        <i class="fa fa-close"></i>
                  </button>';
        $html .= '<div id="main-image" style="margin-top: 10px">';
        if ($mainImage_id) {
            $html .= '' . $this->model->getImageTag(700) . '';
        }
        $html .= '</div>';
        $html .= '<div class="label-inline">';
        $html .= '' . $this->form->field($this->model, "styles[image_position]")->radioList([
                "center" => "مرکز", "top" => "بالا", "bottom" => "پایین", "right" => "راست", "left" => "چپ"
            ])->label("بخش اصلی تصویر") . '';
        $html .= '</div>';
        $html .= '<p class="text-muted">';
        $html .= '(' . Yii::t("app", "Maximum 5 MB") . ')';
        $html .= '</p>';
        return $html;

    }

}
