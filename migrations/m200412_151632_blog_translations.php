<?php

use yii\db\Migration;

/**
 * Class m200412_151632_blog_translations
 */
class m200412_151632_blog_translations extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $defaultLocale = null;
        if (isset(Yii::$app->sourceLanguage))
            $defaultLocale = Yii::$app->sourceLanguage;

        if (is_null($this->getDb()->getSchema()->getTableSchema('{{%blog_posts}}')->getColumn('source_id'))) {
            $this->addColumn('{{%blog_posts}}', 'source_id', $this->bigInteger()->null()->after('id'));

            // Setup foreign key to source id
            $this->createIndex('{{%idx-blog-posts-source}}', '{{%blog_posts}}', ['source_id']);
            $this->addForeignKey(
                'fk_blog_posts_to_source',
                '{{%blog_posts}}',
                'source_id',
                '{{%blog_posts}}',
                'id',
                'NO ACTION',
                'CASCADE'
            );

        }
        if (is_null($this->getDb()->getSchema()->getTableSchema('{{%blog_posts}}')->getColumn('locale'))) {
            $this->addColumn('{{%blog_posts}}', 'locale', $this->string(10)->defaultValue($defaultLocale)->after('status'));
            $this->createIndex('{{%idx-blog-posts-locale}}', '{{%blog_posts}}', ['locale']);

            // If module `Translations` exist setup foreign key `locale` to `trans_langs.locale`
            if (class_exists('\wdmg\translations\models\Languages')) {
                $langsTable = \wdmg\translations\models\Languages::tableName();
                $this->addForeignKey(
                    'fk_blog_posts_to_langs',
                    '{{%blog_posts}}',
                    'locale',
                    $langsTable,
                    'locale',
                    'NO ACTION',
                    'CASCADE'
                );
            }
        }

        if (is_null($this->getDb()->getSchema()->getTableSchema('{{%blog_cats}}')->getColumn('source_id'))) {
            $this->addColumn('{{%blog_cats}}', 'source_id', $this->integer(11)->null()->after('id'));

            // Setup foreign key to source id
            $this->createIndex('{{%idx-blog-cats-source}}', '{{%blog_cats}}', ['source_id']);
            $this->addForeignKey(
                'fk_blog_cats_to_source',
                '{{%blog_cats}}',
                'source_id',
                '{{%blog_cats}}',
                'id',
                'NO ACTION',
                'CASCADE'
            );

        }
        if (is_null($this->getDb()->getSchema()->getTableSchema('{{%blog_cats}}')->getColumn('locale'))) {
            $this->addColumn('{{%blog_cats}}', 'locale', $this->string(10)->defaultValue($defaultLocale)->after('keywords'));
            $this->createIndex('{{%idx-blog-cats-locale}}', '{{%blog_cats}}', ['locale']);

            // If module `Translations` exist setup foreign key `locale` to `trans_langs.locale`
            if (class_exists('\wdmg\translations\models\Languages')) {
                $langsTable = \wdmg\translations\models\Languages::tableName();
                $this->addForeignKey(
                    'fk_blog_cats_to_langs',
                    '{{%blog_cats}}',
                    'locale',
                    $langsTable,
                    'locale',
                    'NO ACTION',
                    'CASCADE'
                );
            }
        }

        if (is_null($this->getDb()->getSchema()->getTableSchema('{{%blog_tags}}')->getColumn('source_id'))) {
            $this->addColumn('{{%blog_tags}}', 'source_id', $this->integer(11)->null()->after('id'));

            // Setup foreign key to source id
            $this->createIndex('{{%idx-blog-tags-source}}', '{{%blog_tags}}', ['source_id']);
            $this->addForeignKey(
                'fk_blog_tags_to_source',
                '{{%blog_tags}}',
                'source_id',
                '{{%blog_tags}}',
                'id',
                'NO ACTION',
                'CASCADE'
            );

        }
        if (is_null($this->getDb()->getSchema()->getTableSchema('{{%blog_tags}}')->getColumn('locale'))) {
            $this->addColumn('{{%blog_tags}}', 'locale', $this->string(10)->defaultValue($defaultLocale)->after('keywords'));
            $this->createIndex('{{%idx-blog-tags-locale}}', '{{%blog_tags}}', ['locale']);

            // If module `Translations` exist setup foreign key `locale` to `trans_langs.locale`
            if (class_exists('\wdmg\translations\models\Languages')) {
                $langsTable = \wdmg\translations\models\Languages::tableName();
                $this->addForeignKey(
                    'fk_blog_tags_to_langs',
                    '{{%blog_tags}}',
                    'locale',
                    $langsTable,
                    'locale',
                    'NO ACTION',
                    'CASCADE'
                );
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        if (!is_null($this->getDb()->getSchema()->getTableSchema('{{%blog_posts}}')->getColumn('source_id'))) {
            $this->dropIndex('{{%idx-blog-posts-source}}', '{{%blog_posts}}');
            $this->dropColumn('{{%blog_posts}}', 'source_id');
            $this->dropForeignKey(
                'fk_blog_posts_to_source',
                '{{%blog_posts}}'
            );
        }
        if (!is_null($this->getDb()->getSchema()->getTableSchema('{{%blog_posts}}')->getColumn('locale'))) {
            $this->dropIndex('{{%idx-blog-posts-locale}}', '{{%blog_posts}}');
            $this->dropColumn('{{%blog_posts}}', 'locale');

            if (class_exists('\wdmg\translations\models\Languages')) {
                $langsTable = \wdmg\translations\models\Languages::tableName();
                if (!(Yii::$app->db->getTableSchema($langsTable, true) === null)) {
                    $this->dropForeignKey(
                        'fk_blog_posts_to_langs',
                        '{{%blog_posts}}'
                    );
                }
            }
        }

        if (!is_null($this->getDb()->getSchema()->getTableSchema('{{%blog_cats}}')->getColumn('source_id'))) {
            $this->dropIndex('{{%idx-blog-cats-source}}', '{{%blog_cats}}');
            $this->dropColumn('{{%blog_cats}}', 'source_id');
            $this->dropForeignKey(
                'fk_blog_cats_to_source',
                '{{%blog_cats}}'
            );
        }
        if (!is_null($this->getDb()->getSchema()->getTableSchema('{{%blog_cats}}')->getColumn('locale'))) {
            $this->dropIndex('{{%idx-blog-cats-locale}}', '{{%blog_cats}}');
            $this->dropColumn('{{%blog_cats}}', 'locale');

            if (class_exists('\wdmg\translations\models\Languages')) {
                $langsTable = \wdmg\translations\models\Languages::tableName();
                if (!(Yii::$app->db->getTableSchema($langsTable, true) === null)) {
                    $this->dropForeignKey(
                        'fk_blog_cats_to_langs',
                        '{{%blog_cats}}'
                    );
                }
            }
        }

        if (!is_null($this->getDb()->getSchema()->getTableSchema('{{%blog_tags}}')->getColumn('source_id'))) {
            $this->dropIndex('{{%idx-blog-tags-source}}', '{{%blog_tags}}');
            $this->dropColumn('{{%blog_tags}}', 'source_id');
            $this->dropForeignKey(
                'fk_blog_tags_to_source',
                '{{%blog_tags}}'
            );
        }
        if (!is_null($this->getDb()->getSchema()->getTableSchema('{{%blog_tags}}')->getColumn('locale'))) {
            $this->dropIndex('{{%idx-blog-tags-locale}}', '{{%blog_tags}}');
            $this->dropColumn('{{%blog_tags}}', 'locale');

            if (class_exists('\wdmg\translations\models\Languages')) {
                $langsTable = \wdmg\translations\models\Languages::tableName();
                if (!(Yii::$app->db->getTableSchema($langsTable, true) === null)) {
                    $this->dropForeignKey(
                        'fk_blog_tags_to_langs',
                        '{{%blog_tags}}'
                    );
                }
            }
        }
    }
}
