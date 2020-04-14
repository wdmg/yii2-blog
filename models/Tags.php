<?php

namespace wdmg\blog\models;

use wdmg\validators\JsonValidator;
use Yii;
use yii\db\Expression;
//use yii\db\ActiveRecord;
use wdmg\base\models\ActiveRecordML;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\base\InvalidArgumentException;
use yii\behaviors\TimestampBehavior;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\SluggableBehavior;

/**
 * This is the model class for table "{{%blog_tags}}".
 *
 * @property int $id
 * @property int $source_id
 * @property string $name
 * @property string $alias
 * @property string $title
 * @property string $description
 * @property string $keywords
 * @property string $locale
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 */

class Tags extends ActiveRecordML
{

    public $route;
    public $baseRoute;

    public $url;

    public $moduleId = 'blog';
    private $_module;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        if (isset(Yii::$app->params["blog.tagsRoute"]))
            $this->baseRoute = Yii::$app->params["blog.tagsRoute"];
        elseif (isset($this->_module->tagsRoute))
            $this->baseRoute = $this->_module->tagsRoute;

    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%blog_tags}}';
    }

    /**
     * {@inheritdoc}
     */
    /*public function behaviors()
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
    }*/

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return ArrayHelper::merge([
            [['name', 'alias'], 'required'],
            [['name', 'alias'], 'string', 'min' => 3, 'max' => 128],
            [['name', 'alias'], 'string', 'min' => 3, 'max' => 128],
            [['title', 'description', 'keywords'], 'string', 'max' => 255],
            ['alias', 'unique', 'message' => Yii::t('app/modules/blog', 'Param attribute must be unique.')],
            ['alias', 'match', 'pattern' => '/^[A-Za-z0-9\-\_]+$/', 'message' => Yii::t('app/modules/blog','It allowed only Latin alphabet, numbers and the «-», «_» characters.')],
            [['created_at', 'updated_at'], 'safe'],
        ], parent::rules());
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/modules/blog', 'ID'),
            'source_id' => Yii::t('app/modules/blog', 'Source ID'),
            'name' => Yii::t('app/modules/blog', 'Name'),
            'alias' => Yii::t('app/modules/blog', 'Alias'),
            'title' => Yii::t('app/modules/blog', 'Title'),
            'description' => Yii::t('app/modules/blog', 'Description'),
            'keywords' => Yii::t('app/modules/blog', 'Keywords'),
            'locale' => Yii::t('app/modules/blog', 'Locale'),
            'created_at' => Yii::t('app/modules/blog', 'Created at'),
            'created_by' => Yii::t('app/modules/blog', 'Created by'),
            'updated_at' => Yii::t('app/modules/blog', 'Updated at'),
            'updated_by' => Yii::t('app/modules/blog', 'Updated by'),
        ];
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
     * Returns all blog tags
     *
     * @param null $cond sampling conditions
     * @param bool $asArray flag if necessary to return as an array
     * @return array|ActiveRecord|null
     */
    /*public function getAll($cond = null, $asArray = false) {
        if (!is_null($cond))
            $models = self::find()->where($cond);
        else
            $models = self::find();

        if ($asArray)
            return $models->asArray()->all();
        else
            return $models->all();

    }*/

    /**
     * Return the public route for tags URL
     * @return string
     */
    /*public function getRoute()
    {
        if (isset(Yii::$app->params["blog.tagsRoute"])) {
            $route = Yii::$app->params["blog.tagsRoute"];
        } else {

            if (!$module = Yii::$app->getModule('admin/blog'))
                $module = Yii::$app->getModule('blog');

            $route = $module->tagsRoute;
        }

        return $route;
    }*/

    /**
     *
     * @param $withScheme boolean, absolute or relative URL
     * @return string or null
     */
    public function getTagUrl($withScheme = true, $realUrl = false)
    {
        /*$this->route = $this->getRoute();
        if (isset($this->alias)) {
            return \yii\helpers\Url::to($this->route . '/' .$this->alias, $withScheme);
        } else {
            return null;
        }*/
        return $this->getModelUrl($withScheme, $realUrl);
    }

    /**
     * Returns the URL to the view of the current blog tag
     *
     * @return string
     */
    /*public function getUrl()
    {
        if ($this->url === null)
            $this->url = $this->getTagUrl();

        return $this->url;
    }*/


    /**
     * @return object of \yii\db\ActiveQuery
     */
    public function getPosts($tag_id = null, $asArray = false) {

        if (!($tag_id === false) && !is_integer($tag_id) && !is_string($tag_id))
            $tag_id = $this->id;

        $query = Posts::find()->alias('blog')
            ->select(['blog.id', 'blog.name', 'blog.alias', 'blog.content', 'blog.title', 'blog.description', 'blog.keywords'])
            ->leftJoin(['taxonomy' => Taxonomy::tableName()], '`taxonomy`.`post_id` = `blog`.`id`')
            ->where([
                'taxonomy.type' => Posts::TAXONOMY_TAGS,
            ]);

        if (is_integer($tag_id))
            $query->andWhere([
                'taxonomy.taxonomy_id' => intval($tag_id)
            ]);

        if ($asArray)
            return $query->asArray()->all();
        else
            return $query->all();

    }
}
