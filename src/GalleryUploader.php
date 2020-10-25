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

        $html = '
            <input type="hidden" id="upload-multi-image-avers"
               url="'.\yii\helpers\Url::to(['@vendor/avers/fileUploader/src/controllers/file/upload-all', 'key' => 'files', 'allowedType' => 'image']).'">
            <input type="hidden" id="web-directory-avers" value="'.\yii\helpers\Url::to('@vendor/avers/fileUploader/src/controllers/').'">
        ';
        $html .= '<label>';
        $html .= '' . Yii::t('app', 'add image gallery') . '';
        $html .= '</label>';
        $html .= '<span class="hidden">';
        $html .= '' . $this->form->field($this->model, 'files[]')->fileInput(['multiple' => true,'onchange' => 'uploadMulti("' . $this->form_name . '","' . $this->form_name_capital . '")']) . '';
        $html .= '</span>';
        $html .= '<button onclick=\'openMultiUpload("' . $this->form_name . '")\' type="button" class="btn btn-primary btn-sm ml-4px">
                        <i class="fa fa-upload"></i>
                  </button>';
        $html .= '<button type="button" id="select-multi-image"
                        class="btn btn-primary btn-sm ml-4px"><i class="fa fa-folder-open"></i>
                  </button>';

        $html .= '<div class="row" id="image-gallary" style="margin-top: 10px">';
        if (!$this->model->isNewRecord) {
            if (!is_null($this->model->collection_id)) {
                $files = File::find()->where(['collection_id' => $this->model->collection_id])->all();
                if ($files) {
                    foreach ($files as $file) {

                        $html .= '<div this-image="image-'.$file->id.'" this-id="'.$file->id.'" class="col-md-6 contain-image-gallary" style="margin-top:10px">';
                        $html .= '<div class="hidden">';
                        $html .= '<input typ="text" class="input-image-gallary" name="'.$this->form_name_capital.'[images][]" value="'.$file->id.'">';
                        $html .= '</div>';
                        $html .= '<button type="button" class="btn btn-danger btn-sm remove-image-gallary" this-image="image-'.$file->id.'">';
                        $html .= '<i class="fa fa-close"></i>';
                        $html .= '</button>';
                        $html .= '<img style="" src="'.$this->model->getImageUriById($file->id, null, 150, File::RESIZE_INSIDE) .'">';
                        $html .= '</div>';
                    }
                }

            }
        }
        $html .= '</div>';
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
                        $html .= '<button id="btn-select-files" type="button" class="btn btn-primary">';
                            $html .= '' . Yii::t('app', 'Submit') . '';
                        $html .= '</button>';
                    $html .= '</div>';


                $html .= '</div>';
            $html .= '</div>';
        $html .= '</div>';

        return $html;
    }


}?>
