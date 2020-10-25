<?php

namespace avers\fileUploader\models;

use Yii;

/**
 * This is the model class for table "file".
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $type
 * @property string $title
 * @property string $uri
 * @property string $name
 * @property string $path
 * @property string $position
 *
 * @property News[] $news
 */
class File extends Main
{
    const TYPE_IMAGE = 1;
    const TYPE_ATTACHMENT = 2;
    const TYPE_VIDEO = 3;
    const TYPE_VIDEO_Convert = 4;
    const TYPE_IMAGE_VIDEO = 5;
    const TYPE_AUDIO = 6;

    const RESIZE_INSIDE = 'i';
    const RESIZE_CROP = 'c';

    public $uploadedFile;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'file';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'uri', 'title', 'name', 'path'], 'required'],
            [['created_at', 'type'], 'integer'],
            [['uri'], 'string', 'max' => 8],
            [['title'], 'string', 'max' => 128],
            [['name', 'path'], 'string', 'max' => 64],
            [['position'], 'string', 'max' => 32],
            [['uploadedFile'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg', 'maxFiles' => 1],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNews()
    {
        return $this->hasMany(News::className(), ['image_id' => 'id']);
    }

    public static function saveFile($url, $name)
    {
        $extension = pathinfo($url, PATHINFO_EXTENSION);
        $filename = floor((microtime(true) * 1000)) . "." . strtolower($extension);

        $imageFile = @file_get_contents($url);
        if (!$imageFile) {
            return false;
        }

        $file = new File();

        $folders = date('y/m/d');
        $path = $file->getBasePath() . '/' . $folders;
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        if (file_put_contents($path . "/" . $filename, $imageFile)) {
            $file->type = $file::TYPE_IMAGE;
            $file->uri = $file->getUniqueUri();
            $file->title = mb_substr($name, 0, 64);
            $file->name = $file->generateFileName($name) . "." . strtolower($extension);
            $file->path = $folders . DIRECTORY_SEPARATOR . $filename;
            if ($file->save()) {
                return $file;
            } else {
                return false;
            }
        }
        return false;
    }

}
