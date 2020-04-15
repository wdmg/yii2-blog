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
use wdmg\validators\JsonValidator;
use wdmg\blog\models\Categories;
use wdmg\blog\models\Tags;
use wdmg\blog\models\Taxonomy;

/**
 * This is the model class for table "{{%blog_posts}}".
 *
 * @property int $id
 * @property int $source_id
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
 * @property string $locale
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 */
class Posts extends ActiveRecordML
{
    public $route;
    public $baseRoute;

    const STATUS_DRAFT = 0; // Blog post has draft
    const STATUS_PUBLISHED = 1; // Blog post has been published
    const TAXONOMY_CATEGORIES = 0; // Post taxnonomy by categories
    const TAXONOMY_TAGS = 1; // Post taxnonomy by tags

    public $file;
    public $url;

    public $categories;
    public $tags;

    public $moduleId = 'blog';
    private $_module;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        $this->_module = self::getModule(true);
        if (isset(Yii::$app->params["blog.baseRoute"]))
            $this->baseRoute = Yii::$app->params["blog.baseRoute"];
        elseif (isset($this->_module->baseRoute))
            $this->baseRoute = $this->_module->baseRoute;

    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%blog_posts}}';
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
            [['name', 'alias', 'content'], 'required'],
            [['name', 'alias'], 'string', 'min' => 3, 'max' => 128],
            [['name', 'alias'], 'string', 'min' => 3, 'max' => 128],
            ['categories', 'each', 'rule' => ['integer']],
            [['tags'], JsonValidator::class, 'message' => Yii::t('app/modules/blog', 'The value of field `{attribute}` must be a valid JSON, error: {error}.')],
            [['excerpt', 'title', 'description', 'keywords', 'image'], 'string', 'max' => 255],
            [['file'], 'file', 'skipOnEmpty' => true, 'maxFiles' => 1, 'extensions' => 'png, jpg'],
            [['status', 'in_sitemap', 'in_rss', 'in_turbo', 'in_amp'], 'boolean'],
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
            'image' => Yii::t('app/modules/blog', 'Image'),
            'file' => Yii::t('app/modules/blog', 'Image file'),
            'excerpt' => Yii::t('app/modules/blog', 'Excerpt'),
            'content' => Yii::t('app/modules/blog', 'Post text'),
            'categories' => Yii::t('app/modules/blog', 'Categories'),
            'tags' => Yii::t('app/modules/blog', 'Tags'),
            'title' => Yii::t('app/modules/blog', 'Title'),
            'description' => Yii::t('app/modules/blog', 'Description'),
            'keywords' => Yii::t('app/modules/blog', 'Keywords'),
            'in_sitemap' => Yii::t('app/modules/blog', 'In sitemap?'),
            'in_rss' => Yii::t('app/modules/blog', 'In RSS-feed?'),
            'in_turbo' => Yii::t('app/modules/blog', 'Yandex turbo-pages?'),
            'in_amp' => Yii::t('app/modules/blog', 'Google AMP?'),
            'locale' => Yii::t('app/modules/blog', 'Locale'),
            'status' => Yii::t('app/modules/blog', 'Status'),
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

        if ($categories = $this->getCategories($this->id, true))
            $this->categories = ArrayHelper::getColumn($categories, ['id']);

        if ($tags = $this->getTags($this->id, true)) {
            foreach ($tags as $tag) {
                $this->tags["tag_id:".$tag['id']] = $tag['name'];
            }
        }

        if (is_array($this->tags)) {
            $this->tags = \yii\helpers\Json::encode($this->tags);
        }

    }

    /**
     * @inheritdoc
     */
    public function beforeValidate()
    {
        if (is_string($this->tags) && JsonValidator::isValid($this->tags)) {
            $this->tags = \yii\helpers\Json::decode($this->tags);
        } elseif (is_array($this->tags)) {
            $this->tags = \yii\helpers\Json::encode($this->tags);
        }

        if (is_array($this->tags)) {
            $this->tags = \yii\helpers\Json::encode($this->tags);
        }

        return parent::beforeValidate();
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {

        if (is_string($this->tags) && JsonValidator::isValid($this->tags)) {
            $this->tags = \yii\helpers\Json::decode($this->tags);
        }

        // Set default category if category not be selected
        if ($insert && empty($this->categories))
            $this->categories = [1];

        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {

        /*if (!$this->addPostCategories() || !$this->addPostTags()) {
            Yii::$app->getSession()->setFlash(
                'danger',
                Yii::t(
                    'app/modules/blog',
                    'An error occurred while added a post categories or tags.'
                )
            );
        }*/

        $this->addPostCategories(); // @TODO: Need realize with link relation
        $this->addPostTags(); // @TODO: Need realize with link relation
        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @return string
     */
    public function getImagePath($absoluteUrl = false)
    {

        if (isset(Yii::$app->params["blog.imagePath"])) {
            $imagePath = Yii::$app->params["blog.imagePath"];
        } else {

            if (!$module = Yii::$app->getModule('admin/blog'))
                $module = Yii::$app->getModule('blog');

            $imagePath = $module->imagePath;
        }

        if ($absoluteUrl)
            return \yii\helpers\Url::to(str_replace('\\', '/', $imagePath), true);
        else
            return $imagePath;

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
     * @return object of \yii\db\ActiveQuery
     */
    public function getCategories($post_id = null, $asArray = false) {

        if (!($post_id === false) && !is_integer($post_id) && !is_string($post_id))
            $post_id = $this->id;

        $query = Categories::find()->alias('cats')
            ->select(['cats.id', 'cats.name', 'cats.parent_id', 'cats.alias', 'cats.title', 'cats.description', 'cats.keywords'])
            ->leftJoin(['taxonomy' => Taxonomy::tableName()], '`taxonomy`.`taxonomy_id` = `cats`.`id`')
            ->where([
                'taxonomy.type' => self::TAXONOMY_CATEGORIES
            ]);

        if (is_integer($post_id))
            $query->andWhere([
                'taxonomy.post_id' => intval($post_id)
            ]);

        if ($asArray)
            return $query->asArray()->all();
        else
            return $query->all();

    }

    /**
     * @return object of \yii\db\ActiveQuery
     */
    public function getTags($post_id = null, $asArray = false) {

        if (!($post_id === false) && !is_integer($post_id) && !is_string($post_id))
            $post_id = $this->id;

        $query = Tags::find()->alias('tags')
            ->select(['tags.id', 'tags.name', 'tags.alias', 'tags.title', 'tags.description', 'tags.keywords'])
            ->leftJoin(['taxonomy' => Taxonomy::tableName()], '`taxonomy`.`taxonomy_id` = `tags`.`id`')
            ->where([
                'taxonomy.type' => self::TAXONOMY_TAGS
            ]);

        if (is_integer($post_id))
            $query->andWhere([
                'taxonomy.post_id' => intval($post_id)
            ]);

        if ($asArray)
            return $query->asArray()->all();
        else
            return $query->all();

    }

    /**
     * @return array
     */
    public function getStatusesList($allStatuses = false)
    {
        if ($allStatuses)
            return [
                '*' => Yii::t('app/modules/blog', 'All statuses'),
                self::STATUS_DRAFT => Yii::t('app/modules/blog', 'Draft'),
                self::STATUS_PUBLISHED => Yii::t('app/modules/blog', 'Published'),
            ];
        else
            return [
                self::STATUS_DRAFT => Yii::t('app/modules/blog', 'Draft'),
                self::STATUS_PUBLISHED => Yii::t('app/modules/blog', 'Published'),
            ];
    }

    /**
     * @return array
     */
    public function getCategoriesList()
    {
        $list = [];
        if ($categories = $this->getCategories(null, true)) {
            $list = ArrayHelper::merge($list, ArrayHelper::map($categories, 'id', 'name'));
        }

        return $list;
    }

    /**
     * @return object of \yii\db\ActiveQuery
     */
    public function getAllCategories($cond = null, $select = ['id', 'name'], $asArray = false)
    {
        if ($cond) {
            if ($asArray)
                return Categories::find()->select($select)->where($cond)->asArray()->indexBy('id')->all();
            else
                return Categories::find()->select($select)->where($cond)->all();

        } else {
            if ($asArray)
                return Categories::find()->select($select)->asArray()->indexBy('id')->all();
            else
                return Categories::find()->select($select)->all();
        }
    }

    /**
     * @return array
     */
    public function getAllCategoriesList($allCategories = false)
    {
        $list = [];
        if ($allCategories)
            $list['*'] = Yii::t('app/modules/blog', 'All categories');

        $cond = null;
        if (!is_null($this->locale)) {
            $cond = [
                'locale' => $this->locale
            ];
        }

        if ($categories = $this->getAllCategories($cond, ['id', 'name'], true)) {
            $list = ArrayHelper::merge($list, ArrayHelper::map($categories, 'id', 'name'));
        }

        return $list;
    }

    /**
     * @return array
     */
    public function getTagsList()
    {
        $list = [];

        $cond = null;
        if (!is_null($this->locale)) {
            $cond = [
                'locale' => $this->locale
            ];
        }

        if ($tags = $this->getAllTags($cond, ['id', 'name'], true)) {
            $list = ArrayHelper::merge($list, ArrayHelper::map($tags, 'id', 'name'));
        }

        return $list;
    }


    /**
     * @return object of \yii\db\ActiveQuery
     */
    public function getAllTags($cond = null, $select = ['id', 'name'], $asArray = false)
    {
        if ($cond) {
            if ($asArray)
                return Tags::find()->select($select)->where($cond)->asArray()->indexBy('id')->all();
            else
                return Tags::find()->select($select)->where($cond)->all();

        } else {
            if ($asArray)
                return Tags::find()->select($select)->asArray()->indexBy('id')->all();
            else
                return Tags::find()->select($select)->all();
        }
    }

    /**
     * @return array
     */
    public function getAllTagsList($allTags = false)
    {
        $list = [];
        if ($allTags)
            $list['*'] = Yii::t('app/modules/blog', 'All tags');

        if ($tags = $this->getAllTags(null, ['id', 'name'], true)) {
            $list = ArrayHelper::merge($list, ArrayHelper::map($tags, 'id', 'name'));
        }

        return $list;
    }

    /**
     * Adds or removes a categories taxonomy for a publication model
     *
     * @return bool
     */
    private function addPostCategories() {
        $isOk = true;
        $data = false;
        $cats_ids = [];
        $new_cats_ids = [];
        $rem_cats_ids = [];

        // Receive categories already assigned publications before editing
        $existing_cats = $this->getCategories($this->id, true);
        $existing_cats_ids = ArrayHelper::getColumn($existing_cats, 'id', false);

        if (is_array($this->categories))
            $data = $this->categories;

        // Get the category IDs that were added when editing the publication.
        if (is_countable($data)) {
            foreach ($data as $cat_ids) {
                $cats_ids[] = intval($cat_ids);
                if (is_array($existing_cats_ids)) {
                    if (!in_array($cat_ids, $existing_cats_ids)) {
                        $new_cats_ids[] = intval($cat_ids);
                    }
                }
            }
        }

        // Checking which categories have been excluded from publication.
        if (is_array($existing_cats_ids)) {
            foreach ($existing_cats_ids as $cat_ids) {
                if (!in_array($cat_ids, $cats_ids))
                    $rem_cats_ids[] = intval($cat_ids);
            }
        }

        // Add taxonomy for new categories
        foreach ($new_cats_ids as $cat_id) {

            $taxonomy = new Taxonomy();
            $taxonomy->post_id = $this->id;
            $taxonomy->taxonomy_id = $cat_id;
            $taxonomy->type = self::TAXONOMY_CATEGORIES;

            if (!$taxonomy->save())
                $isOk = false;

        }

        // Delete taxonomy for excluded categories
        if (!empty($rem_cats_ids) && is_array($rem_cats_ids)) {
            $taxonomy = new Taxonomy();
            $taxonomy->deleteAll(['post_id' => $this->id, 'taxonomy_id' => $rem_cats_ids, 'type' => self::TAXONOMY_CATEGORIES]);
        }

        return $isOk;
    }

    /**
     * Adds or removes a tags taxonomy for a publication model
     *
     * @return bool
     */
    private function addPostTags() {
        $isOk = false;
        $data = false;
        $tags_ids = [];
        $new_tags = [];
        $new_tags_ids = [];
        $rem_tags_ids = [];

        // Get tags already assigned to publications before editing
        $existing_tags = $this->getTags($this->id, true);
        $existing_tags_ids = ArrayHelper::getColumn($existing_tags, 'id', false);

        if (is_string($this->tags) && JsonValidator::isValid($this->tags))
            $data = \yii\helpers\Json::decode($this->tags);
        elseif (is_array($this->tags))
            $data = $this->tags;

        // Retrieving the tag IDs that were added when editing the publication
        if (is_countable($data)) {
            foreach ($data as $key => $tag) {
                // Тег уже есть в базе данных, нужно лишь получить его ИД
                if (preg_match('/tag_id:(\d+)/', $key, $matches)) {
                    if ($tag_id = $matches[1]) {
                        $tags_ids[] = intval($tag_id);
                        if (is_array($existing_tags_ids)) {
                            if (!in_array($tag_id, $existing_tags_ids)) {
                                $new_tags_ids[] = intval($tag_id);
                            }
                        }
                    }
                } else { // then this is a new tag that is represented by a string
                    $new_tags[] = trim($tag);
                }
            }
        }


        // Add new tags and get their ID
        foreach ($new_tags as $tag_name) {
            $tag = new Tags();
            $tag->name = $tag_name;
            if ($tag->save()) {
                $new_tags_ids[] = intval($tag->id);
            } else {
                $tag->errors;
            }
        }

        // Checking Which Tags Have Been Excluded from Publication
        if (is_array($existing_tags_ids)) {
            foreach ($existing_tags_ids as $key => $tag_id) {
                if (!in_array($tag_id, $tags_ids))
                    $rem_tags_ids[] = intval($tag_id);
            }
        }

        // Add taxonomy for new tags
        foreach ($new_tags_ids as $tag_id) {

            $taxonomy = new Taxonomy();
            $taxonomy->post_id = $this->id;
            $taxonomy->taxonomy_id = $tag_id;
            $taxonomy->type = self::TAXONOMY_TAGS;

            if (!$taxonomy->save())
                $isOk = false;

        }

        // Delete taxonomy for excluded tags
        if (!empty($rem_tags_ids) && is_array($rem_tags_ids)) {
            $taxonomy = new Taxonomy();
            $taxonomy->deleteAll(['post_id' => $this->id, 'taxonomy_id' => $rem_tags_ids, 'type' => self::TAXONOMY_TAGS]);
        }

        return $isOk;
    }


    /**
     * Returns the URL to the view of the current model
     *
     * @param $withScheme boolean, absolute or relative URL
     * @return string or null
     */
    public function getPostUrl($withScheme = true, $realUrl = false)
    {
        return $this->getModelUrl($withScheme, $realUrl);
    }

}
