<?php


namespace avers\aversFileUploader;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;
use yii\web\View;

class ImageUploader extends Widget
{



    public $form;
    public $form_name;
    public $form_name_capital;
    public $model;
    public $mainImage_id;


    public function init()
    {
        parent::init();
        Asset::register( $this->getView() );
        if ($this->model->image_id != null) {
            $this->mainImage_id = $this->model->image_id;
        } else {
            $this->mainImage_id = false;
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
        $html .= '' . $this->form->field($this->model, 'mainimage')->fileInput(['onchange' => 'uploadImage("' . $this->form_name . '","' . $this->form_name_capital . '")']) . '';
        $html .= '</span>';
        $html .= '<button onclick=\'openUploadFile("' . $this->form_name . '")\' type="button" class="btn btn-primary btn-sm">
                        <i class="fa fa-upload"></i>
                  </button>';
        $html .= '<button type="button" id="select-one-image"
                        class="btn btn-primary btn-sm"><i class="fa fa-folder-open"></i>
                  </button>';
        $html .= '<button id="remove-image" type="button" class="btn btn-danger btn-sm">
                        <i class="fa fa-close"></i>
                  </button>';
        $html .= '<div id="main-image" style="margin-top: 10px">';
        if ($this->mainImage_id) {
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
