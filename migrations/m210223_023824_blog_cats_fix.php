<?php

use yii\db\Migration;

/**
 * Class m210223_023824_blog_cats_fix
 */
class m210223_023824_blog_cats_fix extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $defaultLocale = null;
        if (isset(Yii::$app->sourceLanguage))
            $defaultLocale = Yii::$app->sourceLanguage;

        if (
            is_null($this->getDb()->getSchema()->getTableSchema('{{%blog_cats}}')->getColumn('is_default')) &&
            !is_null($this->getDb()->getSchema()->getTableSchema('{{%blog_cats}}')->getColumn('locale'))
        ) {
            $this->addColumn('{{%blog_cats}}', 'is_default', $this->tinyInteger(1)->null()->defaultValue(0)->after('locale'));
            $this->createIndex('{{%idx-blog-cats-default}}', '{{%blog_cats}}', ['is_default']);

            $this->delete('{{%blog_cats}}', [
                'id' => 1,
                'alias' => 'uncategorized'
            ]);

            $this->insert('{{%blog_cats}}', [
                'id' => 1,
                'name' => 'Uncategorized',
                'alias' => 'uncategorized',
                'title' => 'Uncategorized posts',
                'locale' => $defaultLocale,
                'is_default' => 1
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        if (
            !is_null($this->getDb()->getSchema()->getTableSchema('{{%blog_posts}}')->getColumn('is_default')) &&
            !is_null($this->getDb()->getSchema()->getTableSchema('{{%blog_posts}}')->getColumn('locale'))
        ) {
            $this->delete('{{%blog_cats}}', ['is_default' => 1]);
            $this->dropIndex('{{%idx-blog-cats-default}}', '{{%blog_cats}}');
            $this->dropColumn('{{%blog_posts}}', 'is_default');
            $this->insert('{{%blog_cats}}', [
                'id' => 1,
                'parent_id' => 0,
                'name' => 'Uncategorized',
                'alias' => 'uncategorized',
                'title' => 'Uncategorized posts'
            ]);
        }
    }

}
