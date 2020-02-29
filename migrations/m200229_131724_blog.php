<?php

use yii\db\Migration;

/**
 * Class m200229_131724_blog
 */
class m200229_131724_blog extends Migration
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

        $this->createTable('{{%blog}}', [

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

            'source' => $this->string(255)->null(),

            'created_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
            'created_by' => $this->integer(11)->notNull()->defaultValue(0),
            'updated_at' => $this->datetime()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_by' => $this->integer(11)->notNull()->defaultValue(0),


        ], $tableOptions);

        $this->createIndex('{{%idx-blog-alias}}', '{{%blog}}', ['name', 'alias']);
        $this->createIndex('{{%idx-blog-status}}', '{{%blog}}', ['alias', 'status']);
        $this->createIndex('{{%idx-blog-content}}','{{%blog}}', ['name', 'excerpt', 'content(250)'],false);
        $this->createIndex('{{%idx-blog-author}}','{{%blog}}', ['created_by', 'updated_by'],false);

        // If exist module `Users` set foreign key `created_by`, `updated_by` to `users.id`
        if (class_exists('\wdmg\users\models\Users') && isset(Yii::$app->modules['users'])) {
            $userTable = \wdmg\users\models\Users::tableName();
            $this->addForeignKey(
                'fk_blog_to_users',
                '{{%blog}}',
                'created_by, updated_by',
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
        $this->dropIndex('{{%idx-blog-alias}}', '{{%blog}}');
        $this->dropIndex('{{%idx-blog-status}}', '{{%blog}}');
        $this->dropIndex('{{%idx-blog-content}}', '{{%blog}}');
        $this->dropIndex('{{%idx-blog-author}}', '{{%blog}}');

        if(class_exists('\wdmg\users\models\Users') && isset(Yii::$app->modules['users'])) {
            $userTable = \wdmg\users\models\Users::tableName();
            if (!(Yii::$app->db->getTableSchema($userTable, true) === null)) {
                $this->dropForeignKey(
                    'fk_blog_to_users',
                    '{{%blog}}'
                );
            }
        }

        $this->truncateTable('{{%blog}}');
        $this->dropTable('{{%blog}}');
    }

}
