<?php
namespace avers\fileUploader\controllers;
use Yii;
use yii\web\HttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\imagine\Image;
use \avers\fileUploader\models\File;
use \avers\fileUploader\models\FileSearch;
use \avers\fileUploader\models\Main;

// use Imagine\Gd;
use Imagine\Image\Box;
use Imagine\Image\Point;

class FileController extends Controller
{
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all File models.
     * @return mixed
     * @throws HttpException
     */
    public function actionIndex()
    {
        if (\Yii::$app->user->can('viewFile')) {
            $searchModel = new FileSearch();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

            return $this->render('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        } else {
            throw new HttpException(403, Yii::t('app', 'You do not have permission'));
        }
    }

    public function actionAjaxUpload($key = 'upload', $allowedType = null, $formName = null)
    {
        $model = new File();

        if (strpos($_FILES[$formName]['type'][$key], 'video') === 0) {
            $type = File::TYPE_VIDEO;
        } elseif (strpos($_FILES[$formName]['type'][$key], 'image') === 0) {
            $type = File::TYPE_IMAGE;
        } else {
            $type = File::TYPE_ATTACHMENT;
        }
        if (!isset($_FILES[$formName]['name'][$key])) {
            $r = [
                'uploaded' => 0,
                'error' => ['message' => Yii::t('app', 'no file selected')]
            ];
            return $this->asJson($r);
        }

        //var_dump($_FILES[$formName]['type'][$key]);
        //exit;

        if ($allowedType) {
            if ($allowedType == 'image') {
                $checkType = File::TYPE_IMAGE;
            } elseif ($allowedType == 'video') {
                $checkType = File::TYPE_IMAGE;
            } else {
                $checkType = File::TYPE_ATTACHMENT;
            }

            if ($checkType != $type) {
                $r = [
                    'uploaded' => 0,
                    'error' => ['message' => Yii::t('app', 'Only {ext} extensions are accepted', ['ext' => $allowedType])]
                ];
                return $this->asJson($r);
            }
        }
        if ($type == File::TYPE_IMAGE) {
            if ($_FILES[$formName]['size'][$key] > 5000000) {
                $r = [
                    'uploaded' => 0,
                    'error' => ['message' => Yii::t('app', 'maximum 5MB are accepted', ['size' => 5000000])]
                ];
                return $this->asJson($r);
            }
        }
        if ($type == File::TYPE_VIDEO) {
            if ($_FILES[$formName]['size'][$key] > 30000000) {
                $r = [
                    'uploaded' => 0,
                    'error' => ['message' => Yii::t('app', 'maximum 30MB are accepted', ['size' => 30000000])]
                ];
                return $this->asJson($r);
            }
        }
        $file['upload']['name'] = $_FILES[$formName]['name'][$key];
        $file['upload']['type'] = $_FILES[$formName]['type'][$key];
        $file['upload']['tmp_name'] = $_FILES[$formName]['tmp_name'][$key];
        $file['upload']['error'] = $_FILES[$formName]['error'][$key];
        $file['upload']['size'] = $_FILES[$formName]['size'][$key];

        $file_name = null;
        $main_file_name = $_FILES[$formName]['name'][$key];
        if (isset($main_file_name)) {
            $main_file_name = explode('.', $main_file_name);
            if (isset($main_file_name[0])){
                $file_name = $main_file_name[0];
            }
        }

        $result = $model->uploadOnePure($file, $file_name, $type);

        if (isset($result->id)) {
            $r = [
                'uploaded' => 1,
                'fileName' => $result->name,
                'id' => $result->id,
            ];
            if ($result->type == $result::TYPE_IMAGE) {
                $r['url'] = $result->getImageUriBase($result->id);
            } else {
                $r['url'] = $result->getFileUriById($result->id);
            }
        } else {
            $r = [
                'uploaded' => 0,
                'error' => ['message' => Yii::t('app', $result)]
            ];
        }
        return $this->asJson($r);
    }

    public function actionAjaxMultiImage($key = 'upload', $allowedType = null, $formName = null)
    {
        $model = new File();
        $arrImageId = [];
        $arrImageName = [];
        $arrImageUrl = [];
        foreach ($_FILES[$formName]['type'][$key] as $key1 => $value1) {
            if (strpos($value1, 'video') === 0) {
                $type = File::TYPE_VIDEO;
            } elseif (strpos($value1, 'image') === 0) {
                $type = File::TYPE_IMAGE;
            } else {
                $type = File::TYPE_ATTACHMENT;
            }
            if ($allowedType) {

                $checkType = File::TYPE_ATTACHMENT;
                if ($allowedType == 'image') {
                    $checkType = File::TYPE_IMAGE;
                } elseif ($allowedType == 'video') {
                    $checkType = File::TYPE_VIDEO;
                }
                if (isset($type)) {

                    if ($checkType != $type) {
                        $r = [
                            'uploaded' => 0,
                            'error' => ['message' => Yii::t('app', 'Only {ext} extensions are accepted', ['ext' => $allowedType])]
                        ];
                        return $this->asJson($r);
                    }
                }
            }

            if ($checkType == File::TYPE_IMAGE) {
                if ($_FILES[$formName]['size'][$key][$key1] > 5000000) {
                    $r = [
                        'uploaded' => 0,
                        'error' => ['message' => Yii::t('app', 'maximum 5MB are accepted', ['size' => 5000000])]
                    ];
                    return $this->asJson($r);
                }
            }
            $file['upload']['name'] = $_FILES[$formName]['name'][$key][$key1];
            $file['upload']['tmp_name'] = $_FILES[$formName]['tmp_name'][$key][$key1];
            $file['upload']['size'] = $_FILES[$formName]['size'][$key][$key1];
            $file['upload']['type'] = $_FILES[$formName]['type'][$key][$key1];
            $file['upload']['error'] = $_FILES[$formName]['error'][$key][$key1];

            $file_name = null;
            $main_file_name = $_FILES[$formName]['name'][$key][$key1];
            if (isset($main_file_name)) {
                $main_file_name = explode('.', $main_file_name);
                if (isset($main_file_name[0])){
                    $file_name = $main_file_name[0];
                }
            }


            $result = $model->uploadOnePure($file, $file_name, $type);
            $arrImageId[] = $result->id;
            $arrImageName[] = $result->name;
            $arrImageUrl[] = $result->getImageUriBase($result->id, null, 200);
        }
        $r = [
            'uploaded' => 1,
            'fileName' => $arrImageName,
            'id' => $arrImageId,
            'count' => count($_FILES[$formName]['type'][$key]),
            'url' => $arrImageUrl
        ];

        return $this->asJson($r);
    }

    public function actionAjaxMultiUpload($key = 'upload', $allowedType = null, $formName = null, $id = null)
    {
        $model = new File();
        foreach ($_FILES[$formName]['type'][$key] as $key1 => $value1) {
            foreach ($value1 as $k1 => $v1) {


                if ($v1 != '' && $k1 == $id) {

                    if (strpos($v1, 'video') === 0) {
                        $type = File::TYPE_VIDEO;

                    } elseif (strpos($v1, 'image') === 0) {

                        $type = File::TYPE_IMAGE;

                    } else {
                        $type = File::TYPE_ATTACHMENT;

                    }


                }


            }


        }


        if ($allowedType) {

            $checkType = File::TYPE_ATTACHMENT;
            if ($allowedType == 'image') {
                $checkType = File::TYPE_IMAGE;
            } elseif ($allowedType == 'video') {
                $checkType = File::TYPE_VIDEO;
            }

            if (isset($type)) {

                if ($checkType != $type) {
                    $r = [
                        'uploaded' => 0,
                        'error' => ['message' => Yii::t('app', 'Only {ext} extensions are accepted', ['ext' => $allowedType])]
                    ];
                    return $this->asJson($r);
                }
            }


        }

        $file_name = null;
        foreach ($_FILES[$formName]['name'][$key] as $key1 => $value1) {
            foreach ($value1 as $k1 => $v1) {
                if ($v1 != '' && $k1 == $id) {
                    $file['upload']['name'] = $v1;
                    $main_file_name = $file['upload']['name'];
                    if (isset($main_file_name)) {
                        $main_file_name = explode('.', $main_file_name);
                        if (isset($main_file_name[0])){
                            $file_name = $main_file_name[0];
                        }
                    }
                }

            }
        }

        foreach ($_FILES[$formName]['type'][$key] as $key2 => $value2) {
            foreach ($value2 as $k2 => $v2) {
                if ($v2 != '' && $k2 == $id) {
                    $file['upload']['type'] = $v2;
                }
            }
        }

        foreach ($_FILES[$formName]['tmp_name'][$key] as $key3 => $value3) {
            foreach ($value3 as $k3 => $v3) {
                if ($v3 != '' && $k3 == $id) {

                    $file['upload']['tmp_name'] = $v3;
                }
            }
        }

        foreach ($_FILES[$formName]['error'][$key] as $key4 => $value4) {
            foreach ($value4 as $k4 => $v4) {
                if ($v4 != '' && $k4 == $id) {

                    $file['upload']['error'] = $v4;
                }
            }
        }

        foreach ($_FILES[$formName]['size'][$key] as $key5 => $value5) {
            foreach ($value5 as $k5 => $v5) {
                if ($v5 != '' && $k5 == $id) {
                    $file['upload']['size'] = $v5;
                }
            }
        }


        if (isset($type)) {
            $result = $model->uploadOnePure($file, $file_name, $type);

            if (isset($result->id)) {
                $r = [
                    'uploaded' => 1,
                    'fileName' => $result->name,
                    'id' => $result->id,
                ];
                if ($result->type == $result::TYPE_IMAGE) {
                    $r['url'] = $result->getImageUriBase($result->id);
                } else {
                    $r['url'] = $result->getFileUriById($result->id);
                }
            } else {
                $r = [
                    'uploaded' => 0,
                    'error' => ['message' => Yii::t('app', $result)]
                ];
            }
            return $this->asJson($r);
        }

    }


    public function actionUploadAll($key = null, $allowedType = null, $formName = 'upload')
    {
//        var_dump($_FILES);
//        exit();

        if ($key == '') {
            $files = [];
            if (isset($_FILES['upload'])) {
                foreach ($_FILES['upload']['name'] as $key => $name) {
                    $files[]['upload'] = [
                        'name' => $_FILES['upload']['name'][$key],
                        'type' => $_FILES['upload']['type'][$key],
                        'tmp_name' => $_FILES['upload']['tmp_name'][$key],
                        'error' => $_FILES['upload']['error'][$key],
                        'size' => $_FILES['upload']['size'][$key],
                    ];
                }
            }

        } else {
            $files = [];
            if (isset($_FILES[$formName])) {
                foreach ($_FILES[$formName]['name'][$key] as $key1 => $name) {

                    $files[]['upload'] = [
                        'name' => $_FILES[$formName]['name'][$key][$key1],
                        'type' => $_FILES[$formName]['type'][$key][$key1],
                        'tmp_name' => $_FILES[$formName]['tmp_name'][$key][$key1],
                        'error' => $_FILES[$formName]['error'][$key][$key1],
                        'size' => $_FILES[$formName]['size'][$key][$key1],
                    ];
                    if (strpos($_FILES[$formName]['type'][$key][$key1], 'video') === 0) {
                        $type = File::TYPE_VIDEO;
                    } elseif (strpos($_FILES[$formName]['type'][$key][$key1], 'image') === 0) {
                        $type = File::TYPE_IMAGE;
                    } elseif (strpos($_FILES[$formName]['type'][$key][$key1], 'audio') === 0) {
                        $type = File::TYPE_AUDIO;
                    } else {
                        $type = File::TYPE_ATTACHMENT;
                    }
                    if ($allowedType) {

                        $checkType = File::TYPE_ATTACHMENT;
                        if ($allowedType == 'image') {
                            $checkType = File::TYPE_IMAGE;
                        } elseif ($allowedType == 'video') {
                            $checkType = File::TYPE_VIDEO;
                        } elseif ($allowedType == 'audio') {
                            $checkType = File::TYPE_AUDIO;
                        }
                        if ($checkType != $type) {
                            $r = [
                                'uploaded' => 0,
                                'error' => ['message' => Yii::t('app', 'Only {ext} extensions are accepted', ['ext' => $allowedType])]
                            ];
                            return $this->asJson($r);
                        }
                    }
                    if (isset($checkType) && $checkType == File::TYPE_IMAGE && $_FILES[$formName]['size'][$key][$key1] > 5000000) {
                        $r = [
                            'uploaded' => 0,
                            'error' => ['message' => Yii::t('app', 'maximum 5MB are accepted', ['size' => 5000000])]
                        ];
                        return $this->asJson($r);
                    }
                }
            }

        }
        $results = (new Main())->uploadAllPure($files);
        $r = [];
        /** @var File $result */
        foreach ($results as $key => $result) {
            if (isset($result->id)) {
                $r[$key] = [
                    'uploaded' => 1,
                    'fileName' => $result->name,
                    'id' => $result->id
                ];
                if ($result->type == $result::TYPE_IMAGE) {
                    $r[$key]['url'] = $result->getImageUriBase($result->id, 750);
                    $r[$key]['type'] = 'image';
                } else {
                    $r[$key]['url'] = $result->getFileUriById($result->id);
                    if ($result->type == $result::TYPE_VIDEO) {
                        $r[$key]['type'] = 'video';
                    } elseif ($result->type == $result::TYPE_AUDIO) {
                        $r[$key]['type'] = 'audio';
                    } else {
                        $r[$key]['type'] = 'attachment';
                    }
                }
            } else {
                $r[$key] = [
                    'uploaded' => 0,
                    'error' => ['message' => $result]
                ];
            }
        }
        return $this->asJson($r);

    }

    public function actionGetImages()
    {
        $html = '';
        $files = File::find()->where(['type' => File::TYPE_IMAGE])->andWhere(['!=', 'id', 1])->orderBy('id desc')->all();
        if ($files) {
            foreach ($files as $file) {
                $html .= ' <div class="image-box" this-id="' . $file->id . '" this-src="' . $file->getImageUriById($file->id) . '">
                                    <img this-id="' . $file->id . '" this-src="' . $file->getImageUriById($file->id) . '" src="' . $file->getImageUriById($file->id, 100, 100) . '">
                                    <div class="overlay hidden" >
                                            <i class="fa fa-check"></i>
                                    </div>

                                </div>';

            }
        }
        return $this->asJson($html);
    }

    public function actionUpload($key = 'upload', $formName = null)
    {

        $model = new File();
        $type = File::TYPE_IMAGE;
        if (isset($_FILES['upload']) && $_FILES['upload']['type'] == 'video/mp4') {
            $type = File::TYPE_VIDEO;
        }
        $result = $model->uploadOnePure($_FILES, null, $type);
        /*
        $r = [
            "resourceType" => "Files",
            "currentFolder" => [
                "path" => "/",
                "url" => "/ckfinder/userfiles/files/",
                "acl" => 255
            ],
            "fileName" => "fileName.jpg",
            "uploaded" => 1
        ];
        */
        if (isset($result->id)) {
            $r = [
                'uploaded' => 1,
                'fileName' => $result->name,
            ];
            if ($result->type == $result::TYPE_IMAGE) {
                $r['url'] = $result->getImageUriBase($result->id);
            } else {
                $r['url'] = $result->getFileUriById($result->id);
            }
        } else {
            $r = [
                'uploaded' => 0,
                'error' => ['message' => Yii::t('app', $result)]
            ];
        }
        return $this->asJson($r);
    }

    public function actionBrowse()
    {
    }

    public function actionDownload($id)
    {
        $file = File::find()->where(['uri' => $id])->select(['name', 'path'])->limit(1)->one();
        if ($file === null) {
            return null;
        }
        $path = $file->basePath . "/" . $file->path;
        return \Yii::$app->response->sendFile($path);
    }

    public function actionVideo($id)
    {
        if (!isset($id) || empty($id)){
            return null;
        }
        $file = File::find()->where(['uri' => $id])->select(['name', 'path'])->limit(1)->one();
        if ($file === null) {
            return null;
        }
        $path = $file->basePath . "/" . $file->path;
        return \Yii::$app->response->sendFile($path);
    }

    public function actionImage($id, $width = null, $height = null, $resize = File::RESIZE_CROP, $name = null)
    {
        $isWindows = false;
        if (strcasecmp(substr(PHP_OS, 0, 3), 'WIN') == 0) {
            $isWindows = true;
        }
        if ($isWindows && YII_ENV_DEV) {
            $file = File::find()->where(['uri' => $id])->select(['name', 'path'])->limit(1)->one();

            if ($file === null) {
                return null;
            }
            $image = Image::getImagine();
            $newImage = $image->open($file->basePath . "/" . $file->path);
            $size = $newImage->getSize();
            $width = $width > $size->getWidth() ? $size->getWidth() : $width;
            $height = $height > $size->getHeight() ? $size->getHeight() : $height;

            if (empty($width) && !empty($height)) {
                $width = (int)(($height / $size->getHeight()) * $size->getWidth());
            } elseif (!empty($width) && empty($height)) {
                $height = (int)(($width / $size->getWidth()) * $size->getHeight());
            } elseif (empty($width) && empty($height)) {
                $width = $size->getWidth();
                $height = $size->getHeight();
            } elseif ($resize == File::RESIZE_CROP) {
                $hrate = $size->getHeight() / $height;
                $wrate = $size->getWidth() / $width;
                if ($hrate < $wrate) { // horizentall
                    $newWidth = (int)(($height / $size->getHeight()) * $size->getWidth());
                    $newHeight = $height;
                    $pointX = (int)(($newWidth - $width) / 2);
                    $pointY = 0;
                } else {
                    $newWidth = $width;
                    $newHeight = (int)(($width / $size->getWidth()) * $size->getHeight());
                    $pointX = 0;
                    $pointY = (int)(($newHeight - $height) / 2);
                }
                $newImage->thumbnail(new Box($newWidth, $newHeight))
                    ->crop(new Point($pointX, $pointY), new Box($width, $height))
                    ->show(substr($file->path, (strpos($file->path, ".") + 1)));
                exit();
            }
            $newImage->thumbnail(new Box($width, $height))
                ->show(substr($file->path, (strpos($file->path, ".") + 1)));
            exit();
        } else {
            $etag = md5(implode(Yii::$app->request->get()));
            header('Cache-Control:public, max-age=804800');
            header('Etag:"' . $etag . '"');
            header('Pragma:public');
            header('Expires:0');

            /** @var File $file */
            $file = File::find()->where(['uri' => $id])->select(['name', 'path', 'position'])->limit(1)->one();
            if ($file === null) {
                return null;
            }
            $fileName = $file->path;
            if (mb_strrchr($file->path, '/')) {
                $fileName = mb_substr(mb_strrchr($file->path, '/'), 1);
            }
            $path = str_replace($fileName, '', $file->path);
            $realPath = $file->basePath . "/" . $path;
            $realPath .= $width ? 'w' . $width : '';
            $realPath .= $height ? 'h' . $height : '';
            if ($width && $height && $resize != File::RESIZE_CROP) {
                $realPath .= 's' . $resize;
            }
            $realPath .= (($width || $height) ? '-' : '') . $fileName;

            if (!file_exists($realPath)) {
                $this->saveImage($realPath, $file, $width, $height, $resize);
            }
            $this->serveImage($realPath);
        }
    }

    /**
     * Updates an existing File model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate()
    {
        $Selectedgallery = $this->findModel($_POST['id']);

        if (isset($_POST['hasEditable'])) {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $value = $_POST['name'];
            $Selectedgallery->title = $value;
            $Selectedgallery->save();
            return ['output' => $value, 'message' => ''];
        }

    }

    public function actionUpdatename()
    {
        $Selectedgallery = $this->findModel($_POST['id']);

        if (isset($_POST['hasEditable'])) {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $value = $_POST['name'];
            $Selectedgallery->name = $value;
            $Selectedgallery->save();
            return ['output' => $value, 'message' => ''];
        }

    }

    private function saveImage($realPath, $file, $width = null, $height = null, $resize = File::RESIZE_CROP)
    {
        $image = Image::getImagine();
        $newImage = $image->open($file->basePath . "/" . $file->path);
        $size = $newImage->getSize();
        $width = $width > $size->getWidth() ? $size->getWidth() : $width;
        $height = $height > $size->getHeight() ? $size->getHeight() : $height;

        if (empty($width) && !empty($height)) {
            $width = (int)(($height / $size->getHeight()) * $size->getWidth());
        } elseif (!empty($width) && empty($height)) {
            $height = (int)(($width / $size->getWidth()) * $size->getHeight());
        } elseif (empty($width) && empty($height)) {
            $width = $size->getWidth();
            $height = $size->getHeight();
        } elseif ($resize == File::RESIZE_CROP) {
            $hrate = $size->getHeight() / $height;
            $wrate = $size->getWidth() / $width;
            if ($hrate < $wrate) { // horizentall
                $newWidth = (int)(($height / $size->getHeight()) * $size->getWidth());
                $newHeight = $height;
//                 $pointX = (int)(($newWidth - $width) / 2);
                $pointY = 0;
                if ($file->position == 'right') {
                    $pointX = (int)($newWidth - $width);
                } elseif ($file->position == 'left') {
                    $pointX = 0;
                } else {
                    $pointX = (int)(($newWidth - $width) / 2);
                }
            } else {
                $newWidth = $width;
                $newHeight = (int)(($width / $size->getWidth()) * $size->getHeight());
                $pointX = 0;
//                 $pointY = (int)(($newHeight - $height) / 2);
                  if ($file->position == 'top') {
                    $pointY = 0;
                } elseif ($file->position == 'bottom') {
                    $pointY = (int)($newHeight - $height);
                } else {
                    $pointY = (int)(($newHeight - $height) / 2);
                }
            }
            $newImage->thumbnail(new Box($newWidth, $newHeight))
                ->crop(new Point($pointX, $pointY), new Box($width, $height))
                ->save($realPath);
            return true;
        }
        $newImage->thumbnail(new Box($width, $height))->save($realPath, ['quality' => 80]);
        return true;
    }

    private function serveImage($path)
    {
        $ext = mb_substr($path, mb_strpos($path, '.') + 1);
        header("Content-type: image/$ext");
        echo file_get_contents($path); // || readfile($realPath);
        return true;
    }


    /**
     * Deletes an existing File model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
//    public function actionDelete($id)
//    {
//        $this->findModel($id)->delete();
//
//        return $this->redirect(['index']);
//    }

    /**
     * Finds the File model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return File the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = File::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }
}
