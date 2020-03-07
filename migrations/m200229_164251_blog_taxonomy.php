<?php

use yii\db\Migration;

/**
 * Class m200229_164251_blog_taxonomy
 */
class m200229_164251_blog_taxonomy extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%blog_taxonomy}}', [
            'id' => $this->bigPrimaryKey(),
            'post_id' => $this->bigInteger(11)->notNull(),
            'taxonomy_id' => $this->integer(11)->notNull(),
            'type' => $this->tinyInteger(1)->null()->defaultValue(0),
        ], $tableOptions);
        $this->createIndex('{{%idx-blog-taxonomy}}', '{{%blog_taxonomy}}', ['post_id', 'taxonomy_id']);

        $this->addForeignKey(
            'fk_blog_taxonomy',
            '{{%blog_taxonomy}}',
            'post_id',
            '{{%blog_posts}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('{{%idx-blog-taxonomy}}', '{{%blog_taxonomy}}');
        $this->dropForeignKey(
            'fk_blog_taxonomy',
            '{{%blog_taxonomy}}'
        );
        $this->truncateTable('{{%blog_taxonomy}}');
        $this->dropTable('{{%blog_taxonomy}}');
    }

}
