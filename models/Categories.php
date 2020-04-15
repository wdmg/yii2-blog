<?php

namespace wdmg\blog\models;

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
 * This is the model class for table "{{%blog_cats}}".
 *
 * @property int $id
 * @property int $parent_id
 * @property string $name
 * @property string $alias
 * @property string $title
 * @property string $description
 * @property string $keywords
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 */

class Categories extends ActiveRecordML
{

    public $route;
    public $baseRoute;

    public $url;

    const DEFAULT_CATEGORY_ID = 1;

    public $moduleId = 'blog';
    private $_module;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        $this->_module = self::getModule(true);
        if (isset(Yii::$app->params["blog.catsRoute"]))
            $this->baseRoute = Yii::$app->params["blog.catsRoute"];
        elseif (isset($this->_module->catsRoute))
            $this->baseRoute = $this->_module->catsRoute;

    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%blog_cats}}';
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
            [['parent_id'], 'integer'],
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
            'parent_id' => Yii::t('app/modules/blog', 'Parent ID'),
            'name' => Yii::t('app/modules/blog', 'Name'),
            'alias' => Yii::t('app/modules/blog', 'Alias'),
            'title' => Yii::t('app/modules/blog', 'Title'),
            'description' => Yii::t('app/modules/blog', 'Description'),
            'keywords' => Yii::t('app/modules/blog', 'Keywords'),
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

    public function beforeDelete()
    {
        // Category for uncategorized posts has undeleted
        if ($data->id === self::DEFAULT_CATEGORY_ID)
            return false;

        return parent::beforeDelete();
    }


    /**
     * Returns all blog categories
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
     * @param bool $allLabel
     * @param bool $rootLabel
     * @return array
     */
    public function getParentsList($allLabel = true, $rootLabel = false)
    {

        if ($this->id) {
            $subQuery = self::find()->select('id')->where(['parent_id' => $this->id]);
            $query = self::find()->alias('categories')
                ->where(['not in', 'categories.parent_id', $subQuery])
                ->andWhere(['!=', 'categories.parent_id', $this->id])
                ->orWhere(['IS', 'categories.parent_id', null])
                ->andWhere(['!=', 'categories.id', $this->id])
                ->select(['id', 'name']);

            $list = $query->asArray()->all();
        } else {
            $list = self::find()->select(['id', 'name'])->asArray()->all();
        }

        if ($allLabel)
            return ArrayHelper::merge([
                '*' => Yii::t('app/modules/blog', '-- All categories --')
            ], ArrayHelper::map($list, 'id', 'name'));
        elseif ($rootLabel)
            return ArrayHelper::merge([
                0 => Yii::t('app/modules/blog', '-- Root category --')
            ], ArrayHelper::map($list, 'id', 'name'));
        else
            return ArrayHelper::map($list, 'id', 'name');
    }

    /**
     * Return the public route for categories URL
     * @return string
     */
    /*private function getRoute($route = null)
    {

        if (is_null($route)) {
            if (isset(Yii::$app->params["blog.catsRoute"])) {
                $route = Yii::$app->params["blog.catsRoute"];
            } else {

                if (!$module = Yii::$app->getModule('admin/blog'))
                    $module = Yii::$app->getModule('blog');

                $route = $module->catsRoute;
            }
        }

        if ($this->parent_id) {
            if ($parent = self::find()->where(['id' => intval($this->parent_id)])->one())
                return $parent->getRoute($route) ."/". $parent->alias;

        }

        return $route;
    }*/


    /**
     * Returns the URL to the view of the current blog category
     *
     * @return string
     */
    /*public function getUrl()
    {
        if ($this->url === null)
            $this->url = $this->getCategoryUrl();

        return $this->url;
    }*/

    /**
     * @return object of \yii\db\ActiveQuery
     */
    public function getPosts($cat_id = null, $asArray = false) {

        if (!($cat_id === false) && !is_integer($cat_id) && !is_string($cat_id))
            $cat_id = $this->id;

        $query = Posts::find()->alias('blog')
            ->select(['blog.id', 'blog.name', 'blog.alias', 'blog.content', 'blog.title', 'blog.description', 'blog.keywords'])
            ->leftJoin(['taxonomy' => Taxonomy::tableName()], '`taxonomy`.`post_id` = `blog`.`id`')
            ->where([
                'taxonomy.type' => Posts::TAXONOMY_CATEGORIES,
            ]);

        if (is_integer($cat_id))
            $query->andWhere([
                'taxonomy.taxonomy_id' => intval($cat_id)
            ]);

        if ($asArray)
            return $query->asArray()->all();
        else
            return $query->all();

    }

    /**
     *
     * @param $withScheme boolean, absolute or relative URL
     * @return string or null
     */
    public function getCategoryUrl($withScheme = true, $realUrl = false)
    {
        /*$this->route = $this->getRoute();
        if (isset($this->alias)) {
            return \yii\helpers\Url::to($this->route . '/' .$this->alias, $withScheme);
        } else {
            return null;
        }*/
        return $this->getModelUrl($withScheme, $realUrl);
    }
}
