<?php

namespace avers\fileUploader\models;

use Yii;
use avers\fileUploader\models\File;
use yii\helpers\Url;
use yii\helpers\Html;

class Main extends \yii\db\ActiveRecord
{
    const STATUS_PENDING = 1;
    const STATUS_PUBLISH = 2;
    const STATUS_DISABLE = 3;

    const NAME_SIDE_MENU_BACKEND = 'sideBarMenuBackend';

    const NOIMAGE_ID = 1;

    const TYPE_CATEGORY = 1;
    const TYPE_PAGE = 2;
    const TYPE_LINK = 3;
    const TYPE_NEWS = 4;
    const TYPE_FEED = 5;
    const TYPE_SIDE_MENU_BACKEND = 6;
    const TYPE_FILE = 7;


    public function getBasePath()
    {
        return Yii::$app->params['mediaPath'];
    }

    public function getStatuses()
    {
        return [
            self::STATUS_PENDING => Yii::t('app', 'Waiting'),
            self::STATUS_PUBLISH => Yii::t('app', 'Published'),
            self::STATUS_DISABLE => Yii::t('app', 'InActive'),
        ];
    }

    public function getStatusName($status = null)
    {
        if ($status === null)
            $status = $this->status;
        return isset($this->statuses[$status]) ? $this->statuses[$status] : Yii::t('app', 'Unknown');
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'Id'),
            'status' => Yii::t('app', 'Status'),
            'type' => Yii::t('app', 'Type'),
            'updated_at' => Yii::t('app', 'Update Time'),
            'created_at' => Yii::t('app', 'Create Time'),
            'publish_time' => Yii::t('app', 'Publish Time'),

            'username' => Yii::t('app', 'Username'),
            'email' => Yii::t('app', 'Email'),
            'password' => Yii::t('app', 'Password'),
            'name' => Yii::t('app', 'Name'),
            'family' => Yii::t('app', 'Family'),
            'title' => Yii::t('app', 'Title'),
            'lead' => Yii::t('app', 'Lead'),
            'summary' => Yii::t('app', 'Summary'),
            'body' => Yii::t('app', 'Body'),
            'text' => Yii::t('app', 'Text'),
            'caption' => Yii::t('app', 'Caption'),
            'message' => Yii::t('app', 'Message'),

            'parent' => Yii::t('app', 'Parent'),
            'thread' => Yii::t('app', 'Thread'),
            'priority' => Yii::t('app', 'Priority'),
            'path' => Yii::t('app', 'Path'),
            'position' => Yii::t('app', 'Position'),
            'module' => Yii::t('app', 'Module'),
            'link' => Yii::t('app', 'Link'),
            'date' => Yii::t('app', 'Date'),
            'view' => Yii::t('app', 'Visit'),

            'file_set' => Yii::t('app', 'File Set'),
            'action_set' => Yii::t('app', 'Action Set'),

            'cnt_view' => Yii::t('app', 'Visit Count'),

            'user_id' => Yii::t('app', 'User'),
            'confirmer_id' => Yii::t('app', 'Corroborant'),
            'news_id' => Yii::t('app', 'News'),
            'category_id' => Yii::t('app', 'News Service'),
            'image_id' => Yii::t('app', 'Image'),
            'from_id' => Yii::t('app', 'From'),
            'to_id' => Yii::t('app', 'To'),

            'imageFile' => Yii::t('app', 'Image')
        ];
    }

    public function uploadOne($file, $name = null, $type = File::TYPE_IMAGE, $collection = null)
    {
        $filename = floor((microtime(true) * 100)) . "." . strtolower($file->extension);
        $folders = date('y/m/d');
        $path = $this->getBasePath() . '/' . $folders;
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        $file->saveAs($path . "/" . $filename);
        $fileModel = new File();
        $fileModel->type = $type;
        $fileModel->uri = $this->uniqueUri;
        $fileModel->title = empty($name) ? (string)time() : mb_substr($name, 0, 64);
        $fileModel->name = $this->generateFileName($name) . "." . strtolower($file->extension);
        $fileModel->path = $folders . DIRECTORY_SEPARATOR . $filename;
        $fileModel->collection_id = $collection;


        if ($fileModel->save()) {
            return $fileModel->id;
        } else {
            return false;
        }
    }

    public function uploadAll($files, $name = null, $type = File::TYPE_IMAGE, $collection = null)
    {
        $fileSet = [];
        $folders = date('y/m/d');
        $path = $this->getBasePath() . '/' . $folders;
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        foreach ($files as $file) {
            $filename = floor((microtime(true) * 100)) . "." . strtolower($file->extension);
            $file->saveAs($path . DIRECTORY_SEPARATOR . $filename);
            $fileModel = new File();
            $fileModel->type = $type;
            $fileModel->uri = $this->uniqueUri;
            $fileModel->title = empty($name) ? (string)time() : mb_substr($name, 0, 64);
            $fileModel->name = $this->generateFileName($name) . "." . strtolower($file->extension);
            $fileModel->path = $folders . DIRECTORY_SEPARATOR . $filename;
            $fileModel->collection_id = $collection;

            if ($fileModel->save()) {
                $fileSet[] = $fileModel->id;
            }
        }

        return $fileSet;
    }


    public function uploadAllPure($files, $collection = null)
    {
        $result = [];
        foreach ($files as $file) {
            $result[] = $this->uploadOnePure($file);
        }
        return $result;
    }

    public function uploadOnePure($file, $name = null)
    {
        $extension = pathinfo($file["upload"]["name"], PATHINFO_EXTENSION);
        if (empty($name)) {
            $filename = floor((microtime(true) * 1000)) . "." . strtolower($extension);
        } else {
            $filename = $this->generateFileName($name) . "." . strtolower($extension);
        }

        // Allow certain file formats
        $type = File::TYPE_ATTACHMENT;
        if (strpos($file["upload"]["type"], 'image') === 0) {
            $type = File::TYPE_IMAGE;
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'jpe', 'gif', 'tiff', 'tif', 'bmp'];
            if (false && !in_array($extension, $allowedExtensions)) {
                return Yii::t('app', 'Only {ext} extensions are accepted', ['ext' => implode(', ', $allowedExtensions)]);
            }
        } elseif (strpos($file["upload"]["type"], 'video') === 0) {
            $type = File::TYPE_VIDEO;
            if (false && !in_array($extension, ['mp4', 'flv', 'ogg'])) {
                return Yii::t('app', 'Only {ext} extensions are accepted', ['ext' => 'mp4, flv, ogg']);
            }
        } elseif (strpos($file["upload"]["type"], 'audio') === 0) {
            $type = File::TYPE_AUDIO;
            /*            if (false && !in_array($extension, ['mp4', 'flv', 'ogg'])) {
                            return Yii::t('app', 'Only {ext} extensions are accepted', ['ext' => 'mp4, flv, ogg']);
                        }*/
        }
        //make folder for our file
        $fileModel = new File();

        $folders = date('y/m/d');
        $path = $this->getBasePath() . '/' . $folders;
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        //filling data for file to save in db
        if (move_uploaded_file($file["upload"]["tmp_name"], $path . "/" . $filename)) {
            $fileModel->type = $type;
            $fileModel->uri = $this->getUniqueUri();
            $fileModel->title = empty($name) ? (string)time() : mb_substr($name, 0, 64);
            $fileModel->name = $filename;
            $fileModel->path = $folders . DIRECTORY_SEPARATOR . $filename;
            if ($fileModel->save()) {
                return $fileModel;
            } else {
                return Yii::t('app', 'There is a problem saving the file information.');
            }
        }
        echo Yii::t('app', 'There is a problem saving the file information.');
        return Yii::t('app', 'There is a problem saving the file information.');
    }

    public function getFileUriById($id)
    {
        $file = File::find()->where(['id' => $id])->limit(1)->select(['uri', 'name', 'path', 'type'])->one();
        if ($file === null)
            return false;
        return Url::to(['file/download', 'id' => $file->uri]);
    }

    public function generateFileName($name = null)
    {
        if (empty($name)) {
            return (string)time();
        }
        $nameWords = explode(" ", $name);
        $count = count($nameWords);
        if ($count < 5)
            return str_replace(" ", "-", $name);
        else {
            $newName = [];
            for ($i = 4; $i > 0; $i--) {
                $newName[] = $nameWords[$count - $i];
            }
            return implode("-", $newName);
        }
    }

    public function getUniqueUri()
    {
        do {
            $uniquri = $this->generateUri();
            $model = File::find()->where(['uri' => $uniquri])->select(['id'])->limit(1)->one();
        } while ($model != null);
        return $uniquri;
    }

    private function generateUri()
    {
        $ui = uniqid();
        $a[] = substr($ui, 11, 2);
        $a[] = substr($ui, 7, 3);
        $a[] = substr($ui, 5, 1);
        $a[] = substr($ui, 10, 2);
        $uniquri = implode('', $a);
        return $uniquri;
    }

    public function getImageUri($width = null, $height = null, $resize = null, $scheme = false)
    {
        return $this->getImageUriBase(null, $width, $height, $resize, $scheme);
    }

    public function getImageUriById($id, $width = null, $height = null, $resize = null)
    {
        return $this->getImageUriBase($id, $width, $height, $resize);
    }

    public function getImageUriBase($image_id = null, $width = null, $height = null, $resize = null, $scheme = false)
    {
        if (empty($image_id)) {
            $image_id = $this->image_id;
        }
        $file = File::find()
            ->where(['id' => $image_id])
            ->limit(1)
            ->select(['uri', 'name', 'path'])
            ->one();
        if ($file === null)

            $file = File::findOne(self::NOIMAGE_ID);


        $params['id'] = $file->uri;

        if (!empty($width))
            $params['width'] = $width;
        if (!empty($height))
            $params['height'] = $height;
        if (!empty($resize))
            $params['resize'] = $resize;

        $params['name'] = $file->name;
        return Url::to(array_merge(['file/image'], $params), $scheme);
    }

    public function getImageTag($width = null, $height = null, $resize = null, $attributes = [])
    {
        return $this->getImageTagBase(null, $width, $height, $resize, $attributes);
    }

    public function getImageTagBase($image_id = null, $width = null, $height = null, $resize = null, $attributes = [])
    {
        if (empty($image_id)) {
            $image_id = $this->image_id;
        }
        $file = File::find()
            ->where(['id' => $image_id])
            ->limit(1)
            ->select(['uri', 'title', 'name', 'path'])
            ->one();
        if ($file === null)
            $file = File::findOne(self::NOIMAGE_ID);

        $params['id'] = $file->uri;

        if (!empty($width))
            $params['width'] = $width;
        if (!empty($height))
            $params['height'] = $height;
        if (!empty($resize))
            $params['resize'] = $resize;
        $params['name'] = $file->name;
        $src = Url::to(array_merge(['file/image'], $params));
        return Html::img($src, array_merge(['alt' => $file->title], $attributes));
    }

    public function getVideoTag($video_id = null, $controls = true, $autoplay = false)
    {
        if (empty($video_id)) {
            $video_id = $this->video_id;
        }
        $file = File::find()
            ->where(['id' => $video_id])
            ->limit(1)
            ->select(['uri', 'title', 'name', 'path'])
            ->one();
        if ($file === null)
            return null;

        $params['id'] = $file->uri;
        $params['name'] = $file->name;
        $src = Url::to(array_merge(['file/video'], $params));
        return '<video src="' . $src . '" ' . ($controls ? 'controls' : '') . ' ' . ($autoplay ? 'autoplay' : '') . '></video>';
    }

    public function findDayStatistic($day)
    {
        $statistic = Statistic::find()->where(['date' => $day])->one();
        if ($statistic) {
            $statistic->view += 1;
            $statistic->save();
            return $statistic;
        }
        $statistic = new Statistic();
        $statistic->date = $day;
        $statistic->view = 1;
        $statistic->save();
        return $statistic;
    }

    public function getVideoUriById($id)
    {
        return $this->getVideoUriBase($id);
    }


    public function getVideoUriBase($video_id)
    {

        $file = File::find()
            ->where(['id' => $video_id])
            ->limit(1)
            ->select(['uri', 'name', 'path'])
            ->one();

        $params['id'] = $file->uri;
        if (!empty($width))
            $params['width'] = $width;
        if (!empty($height))
            $params['height'] = $height;

        $params['name'] = $file->name;

        return Url::to(array_merge(['file/video'], $params));
    }

    public function convertVideo($file, $collection)
    {


        $fileVideo = File::find()->where(['id' => $file])->one();
        $baseVideo = $this->getBasePath() . "/" . $fileVideo->path;
        $basePath = $this->getBasePath() . "/";

        $filepath = substr($fileVideo->path, 0, 12) + 1;
        $imagepath = substr($fileVideo->path, 0, 12) + 2;
        exec("avconv -i " . $baseVideo . " -vcodec libx264 -an -f mp4 " . $basePath . $filepath . ".mp4");
        exec("avconv -i " . $baseVideo . " -r 1 -s 1366x768 -f image2 " . $basePath . $imagepath . ".png");

        $imageModel = new File();
        $imageModel->type = File::TYPE_IMAGE_VIDEO;
        $imageModel->uri = $this->uniqueUri;
        $imageModel->title = mb_substr($fileVideo->title + 2, 0, 64);
        $imageModel->name = $this->generateFileName($fileVideo->title + 2) . ".png";
        $imageModel->path = $imagepath . ".png";
        $imageModel->collection_id = $collection;
        $imageModel->save();


        $fileModel = new File();
        $fileModel->type = File::TYPE_VIDEO_Convert;
        $fileModel->uri = $this->uniqueUri;
        $fileModel->title = mb_substr($fileVideo->title + 1, 0, 64);
        $fileModel->name = $this->generateFileName($fileVideo->title + 1) . ".mp4";
        $fileModel->path = $filepath . ".mp4";
        $fileModel->collection_id = $collection;
        if ($fileModel->save()) {
            return $fileModel->id;
        } else {
            return false;
        }

    }


    public function repeatConvertVideo($file)
    {


        $fileVideo = File::find()->where(['id' => $file])->one();
        $baseVideo = $this->getBasePath() . "/" . $fileVideo->path;
        $basePath = $this->getBasePath() . "/";

        $filepath = substr($fileVideo->path, 0, 12) + 1;
        exec("avconv -i " . $baseVideo . " -vcodec libx264 -an -f mp4 " . $basePath . $filepath . ".mp4");

        $fileModel = new File();
        $fileModel->type = File::TYPE_VIDEO_Convert;
        $fileModel->uri = $this->uniqueUri;
        $fileModel->title = mb_substr($fileVideo->title + 1, 0, 64);
        $fileModel->name = $this->generateFileName($fileVideo->title + 1) . ".mp4";
        $fileModel->path = $filepath . ".mp4";
        $fileModel->collection_id = $collection;
        if ($fileModel->save()) {
            return $fileModel->id;
        } else {
            return false;
        }

    }


    public static function en2pn($value)
    {
        $en = ["1", "2", "3", "4", "5", "6", "7", "8", "9", "0"];
        $pn = ["۱", "۲", "۳", "۴", "۵", "۶", "۷", "۸", "۹", "۰"];
        return str_replace($en, $pn, $value);
    }

    public static function pn2en($value)
    {
        $en = ["1", "2", "3", "4", "5", "6", "7", "8", "9", "0"];
        $pn = ["۱", "۲", "۳", "۴", "۵", "۶", "۷", "۸", "۹", "۰"];
        return str_replace($pn, $en, $value);
    }

    public static function an2en($value)
    {
        $en = ["1", "2", "3", "4", "5", "6", "7", "8", "9", "0"];
        $an = ["١", "٢", "٣", "٤", "٥", "٦", "٧", "٨", "٩", "٠"];
        return str_replace($an, $en, $value);
    }

    public static function summarizeText($text, $length = 64, $wordBreak = false, $maxLength = 75)
    {
        if ($wordBreak === false) {
            if (mb_strlen($text) > $length) {
                $pos = mb_strpos($text, " ", $length - 1);
                $pos = $pos > $maxLength ? $length : $pos;
                $text = mb_substr($text, 0, $pos) . " ...";
            }
        } else {
            $text = mb_substr($text, 0, $length) . " ...";
        }
        return $text;
    }

    public static function getPastTime($time, $isGregorian = true)
    {
        $pastTime = time() - $time;
        if ($pastTime < 60) {
            $pastString = Yii::t('app', 'now');
        } elseif ($pastTime < 3600) {
            $pastString = floor($pastTime / 60) . ' ' . Yii::t('app', 'mins');
        } elseif ($pastTime < (24 * 3600)) {
            $pastString = floor($pastTime / 3600) . ' ' . Yii::t('app', 'hours');
        } elseif ($pastTime < (7 * 24 * 3600)) {
            $pastString = floor($pastTime / (24 * 3600)) . ' ' . Yii::t('app', 'days');
        } else {
            if ($isGregorian) {
                $pastString = date('Y M d', $time);
            } else {
                $pastString = Yii::$app->jdate->date('d F y', $time);
            }
        }
        return $pastString;
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                if ($this->hasAttribute('created_at')) {
                    $this->created_at = time();
                }
                if ($this->hasAttribute('status') && empty($this->status)) {
                    $this->status = self::STATUS_PENDING;
                }
                if ($this->hasAttribute('user_id') && empty($this->user_id)) {
                    $this->user_id = Yii::$app->user->id;
                }
            }
            return true;
        }
    }

    public function getTypes()
    {
        return [

            self::TYPE_CATEGORY => Yii::t('app', 'Category'),
            self::TYPE_PAGE => Yii::t('app', 'Static Page'),
            self::TYPE_LINK => Yii::t('app', 'Custom Link'),
            self::TYPE_NEWS => Yii::t('app', 'News'),
            self::TYPE_FEED => Yii::t('app', 'Feed')
        ];
    }

    public function getTypeName($type = null)
    {
        if ($type == null) {
            $type = $this->type;
        }
        return $this->types[$type];
    }

    public static function getSelectedThemeId()
    {
        $theme = Theme::findOne(['status' => 1]);
        if ($theme){
            return  $theme->id;
        } else {
            return null;
        }
    }

    public static function getSelectedTheme()
    {
        $theme = Theme::findOne(['status' => 1]);
        if ($theme){
            return  $theme;
        } else {
            return null;
        }
    }

}
