<?php

use yii\db\Migration;

/**
 * Class m200229_162144_blog_cats
 */
class m200229_162144_blog_cats extends Migration
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

        $this->createTable('{{%blog_cats}}', [

            'id' => $this->primaryKey(),
            'parent_id' => $this->integer(11)->null(),

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

        $this->createIndex('{{%idx-blog-cats-alias}}', '{{%blog_cats}}', ['name', 'alias']);
        $this->createIndex('{{%idx-blog-cats-parent}}', '{{%blog_cats}}', ['parent_id']);

        // If exist module `Users` set foreign key `created_by`, `updated_by` to `users.id`
        if (class_exists('\wdmg\users\models\Users')) {
            $this->createIndex('{{%idx-blog-cats-created}}','{{%blog_cats}}', ['created_by'],false);
            $this->createIndex('{{%idx-blog-cats-updated}}','{{%blog_cats}}', ['updated_by'],false);
            $userTable = \wdmg\users\models\Users::tableName();
            $this->addForeignKey(
                'fk_blog_cats_to_users1',
                '{{%blog_cats}}',
                'created_by',
                $userTable,
                'id',
                'NO ACTION',
                'CASCADE'
            );
            $this->addForeignKey(
                'fk_blog_cats_to_users2',
                '{{%blog_cats}}',
                'updated_by',
                $userTable,
                'id',
                'NO ACTION',
                'CASCADE'
            );
        }

        $this->insert('{{%blog_cats}}', [
            'id' => 1,
            'parent_id' => 0,
            'name' => 'Uncategorized',
            'alias' => 'uncategorized',
            'title' => 'Uncategorized posts'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('{{%idx-blog-cats-alias}}', '{{%blog_cats}}');
        $this->dropIndex('{{%idx-blog-cats-parent}}', '{{%blog_cats}}');

        if(class_exists('\wdmg\users\models\Users')) {
            $this->dropIndex('{{%idx-blog-cats-created}}', '{{%blog_cats}}');
            $this->dropIndex('{{%idx-blog-cats-updated}}', '{{%blog_cats}}');
            $userTable = \wdmg\users\models\Users::tableName();
            if (!(Yii::$app->db->getTableSchema($userTable, true) === null)) {
                $this->dropForeignKey(
                    'fk_blog_cats_to_users1',
                    '{{%blog_cats}}'
                );
                $this->dropForeignKey(
                    'fk_blog_cats_to_users2',
                    '{{%blog_cats}}'
                );
            }
        }

        $this->truncateTable('{{%blog_cats}}');
        $this->dropTable('{{%blog_cats}}');
    }

}
