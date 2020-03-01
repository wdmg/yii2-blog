<?php

use yii\db\Migration;

/**
 * Class m200229_163618_blog_tags
 */
class m200229_163618_blog_tags extends Migration
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

        $this->createTable('{{%blog_tags}}', [

            'id' => $this->primaryKey(),

            'name' => $this->string(128)->notNull(),
            'alias' => $this->string(128)->notNull(),

            'title' => $this->string(255)->null(),
            'description' => $this->string(255)->null(),
            'keywords' => $this->string(255)->null(),

            'created_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
            'created_by' => $this->integer(11)->null(),
            'updated_at' => $this->datetime()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_by' => $this->integer(11)->null(),


        ], $tableOptions);

        $this->createIndex('{{%idx-blog-tags-alias}}', '{{%blog_tags}}', ['name', 'alias']);

        // If exist module `Users` set foreign key `created_by`, `updated_by` to `users.id`
        if (class_exists('\wdmg\users\models\Users')) {
            $this->createIndex('{{%idx-blog-tags-created}}','{{%blog_tags}}', ['created_by'],false);
            $this->createIndex('{{%idx-blog-tags-updated}}','{{%blog_tags}}', ['updated_by'],false);
            $userTable = \wdmg\users\models\Users::tableName();
            $this->addForeignKey(
                'fk_blog_tags_to_users1',
                '{{%blog_tags}}',
                'created_by',
                $userTable,
                'id',
                'NO ACTION',
                'CASCADE'
            );
            $this->addForeignKey(
                'fk_blog_tags_to_users2',
                '{{%blog_tags}}',
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
        $this->dropIndex('{{%idx-blog-tags-alias}}', '{{%blog_tags}}');

        if(class_exists('\wdmg\users\models\Users')) {
            $this->dropIndex('{{%idx-blog-tags-created}}', '{{%blog_tags}}');
            $this->dropIndex('{{%idx-blog-tags-updated}}', '{{%blog_tags}}');
            $userTable = \wdmg\users\models\Users::tableName();
            if (!(Yii::$app->db->getTableSchema($userTable, true) === null)) {
                $this->dropForeignKey(
                    'fk_blog_tags_to_users1',
                    '{{%blog_tags}}'
                );
                $this->dropForeignKey(
                    'fk_blog_tags_to_users2',
                    '{{%blog_tags}}'
                );
            }
        }

        $this->truncateTable('{{%blog_tags}}');
        $this->dropTable('{{%blog_tags}}');
    }

}
