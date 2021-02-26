<?php


namespace avers\fileUploader;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;
use yii\web\View;
use avers\fileUploader\models\File;

class ImageUploader extends Widget
{



    public $form;
    public $form_name;
    public $form_name_capital;
    public $formData_index;
    public $model;
    public $mainImage_id;
    public $multiply; //true or false
    public $multiply_index;
    public $multiply_container;
    


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

        $html = '           
            <input type="hidden" id="data-url-img-avers"
               url="'.\yii\helpers\Url::to(['file/ajax-upload', 'key' => 'mainimage', 'allowedType' => 'image']).'">
            <input type="hidden" id="ajax-upload-url-avers" url="'.\yii\helpers\Url::to(['file/ajax-upload']).'">
            <input type="hidden" id="data-url-multi-img-avers" url="'.\yii\helpers\Url::to(['file/ajax-multi-upload', 'key' => 'mainimage', 'allowedType' => 'image']).'">
            <input type="hidden" id="web-directory-avers" value="'.\yii\helpers\Url::to('@web/').'">
        ';
        $html .= '<label>';
        $html .= '' . Yii::t('app', 'upload main image') . '';
        $html .= '</label>';
        if (isset($this->multiply, $this->multiply_index) && $this->multiply && $this->multiply_index) {
            $html .= '<span class="hidden">';
            $html .= '' . $this->form->field($this->model, 'image_id[0][' . $this->multiply_index . ']', ['template' => '{input}'])->hiddenInput() . '';
            $html .= '</span>';
            $html .= '<span class="hidden">';
            $html .= '' . $this->form->field($this->model, 'mainimage[0][' . $this->multiply_index . ']')->fileInput(['this-id' => $this->multiply_index, 'this-parent' => 0, 'onchange' => 'uploadMultiImage(0, ' . $this->multiply_index . ', "' . $this->multiply_container . '", "' . $this->form_name . '", "' . $this->form_name_capital . '")']) . '';
            $html .= '</span>';
            $html .= '<button onclick=\'openUploadMultiFile(0, ' . $this->multiply_index . ', "' . $this->multiply_container . '", "' . $this->form_name . '")\' type="button" class="btn btn-primary btn-sm ml-4px"
            this-id="' . $this->multiply_index . '" this-parent="0"
            >
                        <i class="fa fa-upload"></i>
                  </button>';
            $html .= '<div id="main-image-0-' . $this->multiply_index . '" style="display: inline-block">';       
            $html .= '</div>';
        } else {
            $html .= '<span class="hidden">';
            $html .= '' . $this->form->field($this->model, 'image_id', ['template' => '{input}'])->hiddenInput() . '';
            $html .= '</span>';
            $html .= '<span class="hidden">';
            $html .= '' . $this->form->field($this->model, 'mainimage')->fileInput(['onchange' =>  'uploadImage("' . $this->form_name . '","' . $this->form_name_capital . '","","' . $this->formData_index . '")']) . '';
            $html .= '</span>';
            $html .= '<button onclick=\'openUploadFile("' . $this->form_name . '")\' type="button" class="btn btn-primary btn-sm ml-4px">
                        <i class="fa fa-upload"></i>
                  </button>';
        }
        
        if (!isset($this->multiply) || empty($this->multiply) || !$this->multiply) {
            $html .= '<button type="button" id="select-one-image"
                            class="btn btn-primary btn-sm ml-4px"><i class="fa fa-folder-open"></i>
                      </button>';
             $html .= '<button id="remove-image" form-name="' . $this->form_name . '" type="button" class="btn btn-danger btn-sm">
                        <i class="fa fa-close"></i>
                  </button>';
        }
       
        $html .= '<div id="main-image" style="margin-top: 10px">';
        if ($this->mainImage_id) {
            if ($this->form_name == 'category') {
                $html .= '' . $this->model->getImageTag(150) . '';
            } else {
                $html .= '' . $this->model->getImageTag(700) . '';
            }
        }
        $html .= '</div>';
        if ($this->form_name == 'news') {
            $html .= '<div class="label-inline">';
            $html .= '' . $this->form->field($this->model, "styles[image_position]")->radioList([
                "center" => "مرکز", "top" => "بالا", "bottom" => "پایین", "right" => "راست", "left" => "چپ"
            ])->label("بخش اصلی تصویر") . '';
            $html .= '</div>';
            $html .= '<p class="text-muted">';
            $html .= '(' . Yii::t("app", "Maximum 5 MB") . ')';
            $html .= '</p>';
        }
        $html .= '<div class="modal fade" id="selectImageModal-one" tabindex="-1" role="dialog" aria-hidden="true">';
            $html .= '<div class="modal-dialog modal-md">';
                $html .= '<div class="modal-content">';

                    $html .= '<div class="modal-header">';
                        $html .= '<button type="button" class="close close-modal-image-one">';
                            $html .= '<span aria-hidden="true">&times;</span>';
                        $html .= '</button>';
                        $html .= '<h4 class="modal-title">'.Yii::t('app', 'Add image from files').'</h4>';
                    $html .= '</div>';

                    $html .= '<div class="modal-body" style="height: 300px;overflow-y: auto;">';
                        $html .= '<div class="row">';
                            $html .= '<div class="col-md-12">';
                                $html .= '<div style="margin-right: 15px">';
                                    $files = File::find()
                                        ->where(['type' => File::TYPE_IMAGE])
                                        ->andWhere(['!=', 'id', 1])
                                        ->orderBy('id desc')
                                        ->limit(50)
                                        ->all();
                                    if ($files) {
                                        foreach ($files as $file) {
                                            $html .= '<div class="image-box" this-id="'.$file->id.'" this-src="'.$file->getImageUriById($file->id).'">';
                                                $html .= '<img this-id="'.$file->id.'" this-src="'.$file->getImageUriById($file->id).'" src="'.$file->getImageUriById($file->id, 150).'">';
                                                $html .= '<div class="overlay hidden">';
                                                     $html .= '<i class="fa fa-check"></i>';
                                                $html .= '</div>';
                                            $html .= '</div>';
                                        }
                                    }
                                $html .= '</div>';
                            $html .= '</div>';
                        $html .= '</div>';
                    $html .= '</div>';

                    $html .= '<div class="modal-footer">';
                        $html .= '<button type="button" class="btn btn-default close-modal-image-one">';
                            $html .= '' . Yii::t('app', 'Cancel') . '';
                        $html .= '</button>';
                        $html .= '<button id="btn-select-files-one" form-name="' . $this->form_name . '" type="button" class="btn btn-primary">';
                            $html .= '' . Yii::t('app', 'Submit') . '';
                        $html .= '</button>';
                    $html .= '</div>';


                $html .= '</div>';
            $html .= '</div>';
        $html .= '</div>';
        return $html;
    }

}
