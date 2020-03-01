<?php

namespace wdmg\blog\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%blog_taxonomy}}".
 *
 * @property int $id
 * @property int $post_id
 * @property int $taxonomy_id
 * @property int $type
 */

class Taxonomy extends ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%blog_taxonomy}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $rules = [
            [['post_id', 'taxonomy_id', 'type'], 'required'],
            [['post_id', 'taxonomy_id', 'type'], 'integer'],
        ];
        return $rules;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app/modules/blog', 'ID'),
            'post_id' => Yii::t('app/modules/blog', 'Post ID'),
            'taxonomy_id' => Yii::t('app/modules/blog', 'Taxonomy ID'),
            'type' => Yii::t('app/modules/blog', 'Type'),
        ];
    }

    /**
     * Returns all blog taxonomy
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

}
