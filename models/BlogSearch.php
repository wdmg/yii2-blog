<?php

namespace wdmg\blog\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use wdmg\validators\JsonValidator;
use wdmg\blog\models\Blog;

/**
 * BlogSearch represents the model behind the search form of `wdmg\blog\models\Blog`.
 */
class BlogSearch extends Blog
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'in_sitemap', 'in_rss', 'in_turbo', 'in_amp'], 'integer'],
            [['name', 'categories', 'tags', 'alias', 'excerpt', 'title', 'description', 'keywords', 'status'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Blog::find()->alias('blog');

        // add conditions that should always apply here
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'alias', $this->alias])
            ->andFilterWhere(['like', 'excerpt', $this->excerpt])
            ->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'keywords', $this->keywords]);

        if ($this->in_sitemap !== "*")
            $query->andFilterWhere(['like', 'in_sitemap', $this->in_sitemap]);

        if ($this->in_rss !== "*")
            $query->andFilterWhere(['like', 'in_rss', $this->in_rss]);

        if ($this->in_turbo !== "*")
            $query->andFilterWhere(['like', 'in_turbo', $this->in_turbo]);

        if ($this->in_amp !== "*")
            $query->andFilterWhere(['like', 'in_amp', $this->in_amp]);

        if ($this->status !== "*")
            $query->andFilterWhere(['like', 'status', $this->status]);

        if (intval($this->categories) !== 0) {
            $query->leftJoin(['taxonomy_cats' => Taxonomy::tableName()], '`taxonomy_cats`.`post_id` = `blog`.`id`');
            $query->andFilterWhere([
                'taxonomy_cats.type' => Blog::TAXONOMY_CATEGORIES,
                'taxonomy_cats.taxonomy_id' => intval($this->categories)
            ]);
        }

        if (intval($this->tags) !== 0) {
            $query->leftJoin(['taxonomy_tags' => Taxonomy::tableName()], '`taxonomy_tags`.`post_id` = `blog`.`id`');
            $query->andFilterWhere([
                'taxonomy_tags.type' => Blog::TAXONOMY_TAGS,
                'taxonomy_tags.taxonomy_id' => intval($this->tags)
            ]);
        }

        return $dataProvider;
    }

}
