<?php

namespace wdmg\blog\models;

use Yii;
use yii\db\Expression;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\base\InvalidArgumentException;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\SluggableBehavior;

/**
 * This is the model class for table "{{%blog}}".
 *
 * @property int $id
 * @property string $name
 * @property string $alias
 * @property string $image_src
 * @property string $excerpt
 * @property string $content
 * @property string $title
 * @property string $description
 * @property string $keywords
 * @property boolean $in_sitemap
 * @property boolean $in_rss
 * @property boolean $in_turbo
 * @property boolean $in_amp
 * @property boolean $status
 * @property array $source
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 */
class Blog extends ActiveRecord
{
    public $route;
    const POST_STATUS_DRAFT = 0; // Blog post has draft
    const POST_STATUS_PUBLISHED = 1; // Blog post has been published

    public $file;
    public $url;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%blog}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'created_at',
                    ActiveRecord::EVENT_BEFORE_UPDATE => 'updated_at',
                ],
                'value' => new Expression('NOW()'),
            ],
            'blameable' => [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
            ],
            'sluggable' =>  [
                'class' => SluggableBehavior::class,
                'attribute' => ['name'],
                'slugAttribute' => 'alias',
                'ensureUnique' => true,
                'skipOnEmpty' => true,
                'immutable' => true,
                'value' => function ($event) {
                    return mb_substr($this->name, 0, 32);
                }
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $rules = [
            [['name', 'alias', 'content'], 'required'],
            [['name', 'alias'], 'string', 'min' => 3, 'max' => 128],
            [['name', 'alias'], 'string', 'min' => 3, 'max' => 128],
            [['excerpt', 'title', 'description', 'keywords', 'image'], 'string', 'max' => 255],
            [['file'], 'file', 'skipOnEmpty' => true, 'maxFiles' => 1, 'extensions' => 'png, jpg'],
            [['status', 'in_sitemap', 'in_rss', 'in_turbo', 'in_amp'], 'boolean'],
            ['alias', 'unique', 'message' => Yii::t('app/modules/pages', 'Param attribute must be unique.')],
            ['alias', 'match', 'pattern' => '/^[A-Za-z0-9\-\_]+$/', 'message' => Yii::t('app/modules/pages','It allowed only Latin alphabet, numbers and the «-», «_» characters.')],
            [['source', 'created_at', 'updated_at'], 'safe'],
        ];

        if (class_exists('\wdmg\users\models\Users')) {
            $rules[] = [['created_by', 'updated_by'], 'safe'];
        }

        return $rules;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/modules/blog', 'ID'),
            'name' => Yii::t('app/modules/blog', 'Name'),
            'alias' => Yii::t('app/modules/blog', 'Alias'),
            'image' => Yii::t('app/modules/blog', 'Image'),
            'file' => Yii::t('app/modules/blog', 'Image file'),
            'excerpt' => Yii::t('app/modules/blog', 'Excerpt'),
            'content' => Yii::t('app/modules/blog', 'Post text'),
            'title' => Yii::t('app/modules/blog', 'Title'),
            'description' => Yii::t('app/modules/blog', 'Description'),
            'keywords' => Yii::t('app/modules/blog', 'Keywords'),
            'in_sitemap' => Yii::t('app/modules/blog', 'In sitemap?'),
            'in_rss' => Yii::t('app/modules/blog', 'In RSS-feed?'),
            'in_turbo' => Yii::t('app/modules/blog', 'Yandex turbo-pages?'),
            'in_amp' => Yii::t('app/modules/blog', 'Google AMP?'),
            'status' => Yii::t('app/modules/blog', 'Status'),
            'source' => Yii::t('app/modules/blog', 'Source'),
            'created_at' => Yii::t('app/modules/blog', 'Created at'),
            'created_by' => Yii::t('app/modules/blog', 'Created by'),
            'updated_at' => Yii::t('app/modules/blog', 'Updated at'),
            'updated_by' => Yii::t('app/modules/blog', 'Updated by'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (is_array($this->source))
            $this->source = serialize($this->source);

        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function afterFind()
    {
        parent::afterFind();

        if (is_null($this->url))
            $this->url = $this->getUrl();

    }

    /**
     * @return array
     */
    public function getStatusesList($allStatuses = false)
    {
        if($allStatuses)
            return [
                '*' => Yii::t('app/modules/blog', 'All statuses'),
                self::POST_STATUS_DRAFT => Yii::t('app/modules/blog', 'Draft'),
                self::POST_STATUS_PUBLISHED => Yii::t('app/modules/blog', 'Published'),
            ];
        else
            return [
                self::POST_STATUS_DRAFT => Yii::t('app/modules/blog', 'Draft'),
                self::POST_STATUS_PUBLISHED => Yii::t('app/modules/blog', 'Published'),
            ];
    }

    /**
     * @return string
     */
    public function getRoute()
    {

        if (isset(Yii::$app->params["blog.blogRoute"])) {
            $blogRoute = Yii::$app->params["blog.blogRoute"];
        } else {

            if (!$module = Yii::$app->getModule('admin/blog'))
                $module = Yii::$app->getModule('blog');

            $blogRoute = $module->blogRoute;
        }

        return $blogRoute;
    }

    /**
     * @return string
     */
    public function getImagePath($absoluteUrl = false)
    {

        if (isset(Yii::$app->params["blog.blogImagePath"])) {
            $blogImagePath = Yii::$app->params["blog.blogImagePath"];
        } else {

            if (!$module = Yii::$app->getModule('admin/blog'))
                $module = Yii::$app->getModule('blog');

            $blogImagePath = $module->blogImagePath;
        }

        if ($absoluteUrl)
            return \yii\helpers\Url::to(str_replace('\\', '/', $blogImagePath), true);
        else
            return $blogImagePath;

    }

    /**
     *
     * @param $withScheme boolean, absolute or relative URL
     * @return string or null
     */
    public function getPostUrl($withScheme = true, $realUrl = false)
    {
        $this->route = $this->getRoute();
        if (isset($this->alias)) {
            if ($this->status == self::POST_STATUS_DRAFT && $realUrl)
                return \yii\helpers\Url::to(['default/view', 'alias' => $this->alias, 'draft' => 'true'], $withScheme);
            else
                return \yii\helpers\Url::to($this->route . '/' .$this->alias, $withScheme);

        } else {
            return null;
        }
    }

    /**
     * @return object of \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        if (class_exists('\wdmg\users\models\Users'))
            return $this->hasOne(\wdmg\users\models\Users::class, ['id' => 'created_by']);
        else
            return $this->created_by;
    }

    /**
     * @return object of \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        if (class_exists('\wdmg\users\models\Users'))
            return $this->hasOne(\wdmg\users\models\Users::class, ['id' => 'updated_by']);
        else
            return $this->updated_by;
    }


    public function upload($image = null)
    {
        if (!$image)
            return false;

        $path = Yii::getAlias('@webroot') . $this->getImagePath();
        if ($image) {
            // Create the folder if not exist
            if (\yii\helpers\FileHelper::createDirectory($path, $mode = 0775, $recursive = true)) {
                $fileName = $image->baseName . '.' . $image->extension;
                if ($image->saveAs($path . '/' . $fileName))
                    return $fileName;
            }
        }
        return false;
    }

    /**
     * Returns published blog posts
     *
     * @param null $cond sampling conditions
     * @param bool $asArray flag if necessary to return as an array
     * @return array|ActiveRecord|null
     */
    public function getPublished($cond = null, $asArray = false) {
        if (!is_null($cond) && is_array($cond))
            $models = self::find()->where(ArrayHelper::merge($cond, ['status' => self::POST_STATUS_PUBLISHED]));
        elseif (!is_null($cond) && is_string($cond))
            $models = self::find()->where(ArrayHelper::merge([$cond], ['status' => self::POST_STATUS_PUBLISHED]));
        else
            $models = self::find()->where(['status' => self::POST_STATUS_PUBLISHED]);

        if ($asArray)
            return $models->asArray()->all();
        else
            return $models->all();

    }

    /**
     * Returns all blog posts (draft and published)
     *
     * @param null $cond sampling conditions
     * @param bool $asArray flag if necessary to return as an array
     * @return array|ActiveRecord|null
     */
    public function getAll($cond = null, $asArray = false) {
        if (!is_null($cond))
            $models = self::find()->where($cond);
        else
            $models = self::find();

        if ($asArray)
            return $models->asArray()->all();
        else
            return $models->all();

    }

    /**
     * Returns the URL to the view of the current model
     *
     * @return string
     */
    public function getUrl()
    {
        if ($this->url === null)
            $this->url = $this->getPostUrl();

        return $this->url;
    }

}
