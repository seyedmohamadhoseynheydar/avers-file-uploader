<?php


namespace avers\fileUploader;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;
use yii\web\View;
use avers\fileUploader\models\File;

class GalleryUploader extends Widget
{

    public $form;
    public $form_name;
    public $form_name_capital;
    public $formId;
    public $model;
    public $mainImage_id;
    public $select_from_site = "true";
    public $select_main_image = "false";
    public $title;
    public $skin;



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
            <input type="hidden" id="upload-multi-image-avers"
               url="'.\yii\helpers\Url::to(['file/upload-all', 'key' => 'files', 'allowedType' => 'image']).'">
            <input type="hidden" id="web-directory-avers" value="'.\yii\helpers\Url::to('@web/').'">
        ';
        if ($this->title !== false){
            $html .= '<label>';
            if (!empty($this->title)){
                $html .= '' . $this->title . '';
            } else {
                $html .= '' . Yii::t('app', 'add image gallery') . '';
            }
            $html .= '</label>';
        }
        $html .= '<span class="hidden">';
        $html .= '' . $this->form->field($this->model, 'files[]')->fileInput(['multiple' => true,'onchange' => 'uploadMulti("' . $this->form_name . '","' . $this->form_name_capital . '", "'.$this->select_main_image.'","' . $this->formId . '","' . $this->skin . '")']) . '';
        $html .= '</span>';
        if ($this->skin != 'bazar-rouz-iranian'){
            $html .= '<button onclick=\'openMultiUpload("' . $this->form_name . '")\' type="button" class="btn btn-primary btn-sm ml-4px">
                        <i class="fa fa-upload"></i>
                  </button>';
        }
        if ($this->select_from_site == "true") {
             $html .= '<button type="button" id="select-multi-image"
                            class="btn btn-primary btn-sm ml-4px"><i class="fa fa-folder-open"></i>
                      </button>';
        }
       

        $html .= '<div class="row" id="image-gallary" style="margin-top: 10px">';
        if (!$this->model->isNewRecord) {
            if (!is_null($this->model->collection_id)) {
                $files = File::find()->where(['collection_id' => $this->model->collection_id])->all();
                if ($files) {
                    foreach ($files as $file) {

                        $html .= '<div this-image="image-'.$file->id.'" this-id="'.$file->id.'" class="col-md-4 contain-image-gallary" style="margin-top:10px">';
                        $html .= '<div class="hidden">';
                        $html .= '<input typ="text" class="input-image-gallary" name="'.$this->form_name_capital.'[images][]" value="'.$file->id.'">';
                        $html .= '</div>';

                        if ($this->select_main_image == "true") {
                            if ($this->model->image_id == $file->id) {
                                $checked = "checked";
                            } else {
                                $checked = "";
                            }
                            $html .= ' <div class="row">
                                            <div class="col-md-2 col-2">
                                                <button type="button" class="btn btn-danger btn-sm remove-image-gallary" this-image="image-'.$file->id.'">
                                                    <i class="fa fa-close"></i>
                                                </button>
                                            </div>
                                            <div class="col-md-10 col-10">
                                                <label for="main_image_'.$file->id.'">تصویر اصلی</label>
                                                <input '.$checked.' class="form-check-input" type="radio" name="'.$this->form_name_capital.'[image_id]" value="'.$file->id.'" id="main_image_'.$file->id.'">
                                            </div>
                                        </div>
                                        ';
                        }
                        if ($this->skin == 'bazar-rouz-iranian'){
                            $html .= '<img style="max-height: 190px;border:1px solid #18E3A4;border-radius:10px;margin-top:10px;margin-bottom:10px" src="'.$this->model->getImageUriById($file->id, null, null, File::RESIZE_INSIDE) .'">';
                        } else {
                            $html .= '<img style="with:50%" src="'.$this->model->getImageUriById($file->id, null, null, File::RESIZE_INSIDE) .'">';
                        }
                        $html .= '</div>';
                    }
                }

            }
        }
        if ($this->skin == 'bazar-rouz-iranian'){
            $html .= '<div onclick=\'openMultiUpload("' . $this->form_name . '")\' class="col-md-4 fa fa-plus-square-o" style="color: #18E3A4;margin-top: 10px;margin-bottom: 10px;border: 1px solid #18E3A4;border-radius: 10px">';        
                    <i class="fa fa-plus-square-o" style="color: #18E3A4; font-size: 100px"></i>               
            $html .= '</div>';
        }
        $html .= '</div>';
         if ($this->select_from_site == "true") {
            $html .= '<div class="modal fade" id="selectImageModal-multi" tabindex="-1" role="dialog" aria-hidden="true">';
                $html .= '<div class="modal-dialog modal-md">';
                    $html .= '<div class="modal-content">';

                        $html .= '<div class="modal-header">';
                            $html .= '<button type="button" class="close close-modal-image-multi">';
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
                                                    $html .= '<img this-id="'.$file->id.'" this-src="'.$file->getImageUriById($file->id).'" src="'.$file->getImageUriById($file->id, 100, 100).'">';
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
                            $html .= '<button type="button" class="btn btn-default close-modal-image-multi">';
                                $html .= '' . Yii::t('app', 'Cancel') . '';
                            $html .= '</button>';
                            $html .= '<button id="btn-select-files-multi" form-name="' . $this->form_name . '" type="button" class="btn btn-primary">';
                                $html .= '' . Yii::t('app', 'Submit') . '';
                            $html .= '</button>';
                        $html .= '</div>';


                    $html .= '</div>';
                $html .= '</div>';
            $html .= '</div>';
         }
        return $html;
    }


}?>
