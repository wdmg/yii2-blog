<?php

use yii\db\Migration;

/**
 * Class m200229_131724_blog_posts
 */
class m200229_131724_blog_posts extends Migration
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

        $this->createTable('{{%blog_posts}}', [

            'id' => $this->bigPrimaryKey(),
            'name' => $this->string(128)->notNull(),
            'alias' => $this->string(128)->notNull(),

            'image' => $this->string(255)->null(),

            'excerpt' => $this->string(255)->null(),
            'content' => $this->text()->null(),

            'title' => $this->string(255)->null(),
            'description' => $this->string(255)->null(),
            'keywords' => $this->string(255)->null(),

            'in_sitemap' => $this->boolean()->defaultValue(true),
            'in_rss' => $this->boolean()->defaultValue(true),
            'in_turbo' => $this->boolean()->defaultValue(true),
            'in_amp' => $this->boolean()->defaultValue(true),

            'status' => $this->tinyInteger(1)->null()->defaultValue(0),

            'created_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
            'created_by' => $this->integer(11)->null(),
            'updated_at' => $this->datetime()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_by' => $this->integer(11)->null(),


        ], $tableOptions);

        $this->createIndex('{{%idx-blog-posts-alias}}', '{{%blog_posts}}', ['name', 'alias']);
        $this->createIndex('{{%idx-blog-posts-status}}', '{{%blog_posts}}', ['alias', 'status']);

        if ($this->db->driverName === 'mysql')
            $this->createIndex('{{%idx-blog-posts-content}}','{{%blog_posts}}', ['name', 'excerpt', 'content(250)'],false);
        else
            $this->createIndex('{{%idx-blog-posts-content}}','{{%blog_posts}}', ['name', 'excerpt', 'content'],false);

        // If exist module `Users` set foreign key `created_by`, `updated_by` to `users.id`
        if (class_exists('\wdmg\users\models\Users')) {
            $this->createIndex('{{%idx-blog-posts-created}}','{{%blog_posts}}', ['created_by'],false);
            $this->createIndex('{{%idx-blog-posts-updated}}','{{%blog_posts}}', ['updated_by'],false);
            $userTable = \wdmg\users\models\Users::tableName();
            $this->addForeignKey(
                'fk_blog_posts_to_users1',
                '{{%blog_posts}}',
                'created_by',
                $userTable,
                'id',
                'NO ACTION',
                'CASCADE'
            );
            $this->addForeignKey(
                'fk_blog_posts_to_users2',
                '{{%blog_posts}}',
                'updated_by',
                $userTable,
                'id',
                'NO ACTION',
                'CASCADE'
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('{{%idx-blog-posts-alias}}', '{{%blog_posts}}');
        $this->dropIndex('{{%idx-blog-posts-status}}', '{{%blog_posts}}');
        $this->dropIndex('{{%idx-blog-posts-content}}', '{{%blog_posts}}');

        if (class_exists('\wdmg\users\models\Users')) {
            $this->dropIndex('{{%idx-blog-posts-created}}', '{{%blog_posts}}');
            $this->dropIndex('{{%idx-blog-posts-updated}}', '{{%blog_posts}}');
            $userTable = \wdmg\users\models\Users::tableName();
            if (!(Yii::$app->db->getTableSchema($userTable, true) === null)) {
                $this->dropForeignKey(
                    'fk_blog_posts_to_users1',
                    '{{%blog_posts}}'
                );
                $this->dropForeignKey(
                    'fk_blog_posts_to_users2',
                    '{{%blog_posts}}'
                );
            }
        }

        $this->truncateTable('{{%blog_posts}}');
        $this->dropTable('{{%blog_posts}}');
    }

}
