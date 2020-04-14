<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\Pjax;
use wdmg\widgets\SelectInput;

/* @var $this yii\web\View */
/* @var $model wdmg\blog\models\Categories */

$this->title = Yii::t('app/modules/blog', 'All categories');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/modules/blog', 'Blog'), 'url' => ['posts/index']];
$this->params['breadcrumbs'][] = $this->title;

if (isset(Yii::$app->translations) && class_exists('\wdmg\translations\FlagsAsset')) {
    $bundle = \wdmg\translations\FlagsAsset::register(Yii::$app->view);
} else {
    $bundle = false;
}

?>
<div class="page-header">
    <h1>
        <?= Html::encode($this->title) ?> <small class="text-muted pull-right">[v.<?= $this->context->module->version ?>]</small>
    </h1>
</div>
<div class="blog-cats-index">

    <?php Pjax::begin(); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'layout' => '{summary}<br\/>{items}<br\/>{summary}<br\/><div class="text-center">{pager}</div>',
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'name',
                'format' => 'raw',
                'value' => function($model) {
                    $output = Html::tag('strong', $model->name);
                    $output .= (($model->id === 1) ? " <span class=\"text-muted\">(" . Yii::t('app/modules/blog', 'default') . ")</span>" : "");
                    if (($categoryURL = $model->getCategoryUrl(true, true)) && $model->id) {
                        $output .= '<br/>' . Html::a($model->getCategoryUrl(true, false), $categoryURL, [
                                'target' => '_blank',
                                'data-pjax' => 0
                            ]);
                    }
                    return $output;
                }
            ],
            [
                'attribute' => 'title',
                'format' => 'raw',
                'value' => function($model) {
                    $output = mb_strimwidth(strip_tags($model->title), 0, 64, '…');

                    if (mb_strlen($model->title) > 81)
                        $output .= '&nbsp;' . Html::tag('span', Html::tag('span', '', [
                                'class' => 'fa fa-fw fa-exclamation-triangle',
                                'title' => Yii::t('app/modules/blog','Field exceeds the recommended length of {length} characters.', [
                                    'length' => 80
                                ])
                            ]), ['class' => 'label label-warning']);

                    return $output;
                }
            ],
            [
                'attribute' => 'description',
                'format' => 'raw',
                'value' => function($model) {
                    $output = mb_strimwidth(strip_tags($model->description), 0, 64, '…');

                    if (mb_strlen($model->description) > 161)
                        $output .= '&nbsp;' . Html::tag('span', Html::tag('span', '', [
                                'class' => 'fa fa-fw fa-exclamation-triangle',
                                'title' => Yii::t('app/modules/blog','Field exceeds the recommended length of {length} characters.', [
                                    'length' => 160
                                ])
                            ]), ['class' => 'label label-warning']);

                    return $output;
                }
            ],
            [
                'attribute' => 'keywords',
                'format' => 'raw',
                'value' => function($model) {
                    $output = mb_strimwidth(strip_tags($model->keywords), 0, 64, '…');

                    if (mb_strlen($model->keywords) > 181)
                        $output .= '&nbsp;' . Html::tag('span', Html::tag('span', '', [
                                'class' => 'fa fa-fw fa-exclamation-triangle',
                                'title' => Yii::t('app/modules/blog','Field exceeds the recommended length of {length} characters.', [
                                    'length' => 180
                                ])
                            ]), ['class' => 'label label-warning']);

                    return $output;
                }
            ],

            [
                'attribute' => 'posts',
                'label' => Yii::t('app/modules/blog', 'Posts'),
                'format' => 'html',
                'value' => function($data) {
                    if ($posts = $data->posts) {
                        return Html::a(count($posts), ['posts/index', 'cat_id' => $data->id]);
                    } else {
                        return 0;
                    }
                }
            ],
            [
                'attribute' => 'locale',
                'label' => Yii::t('app/modules/blog','Language versions'),
                'format' => 'raw',
                'filter' => false,
                'headerOptions' => [
                    'class' => 'text-center',
                    'style' => 'min-width:96px;'
                ],
                'contentOptions' => [
                    'class' => 'text-center'
                ],
                'value' => function($data) use ($bundle) {

                    $output = [];
                    $separator = ", ";
                    $versions = $data->getAllVersions($data->id, true);
                    $locales = ArrayHelper::map($versions, 'id', 'locale');

                    if (isset(Yii::$app->translations)) {
                        foreach ($locales as $item_locale) {

                            $locale = Yii::$app->translations->parseLocale($item_locale, Yii::$app->language);

                            if ($item_locale === $locale['locale']) { // Fixing default locale from PECL intl

                                if (!($country = $locale['domain']))
                                    $country = '_unknown';

                                $flag = \yii\helpers\Html::img($bundle->baseUrl . '/flags-iso/flat/24/' . $country . '.png', [
                                    'alt' => $locale['name']
                                ]);

                                if ($data->locale === $locale['locale']) // It`s source version
                                    $output[] = Html::a($flag,
                                        [
                                            'posts/update', 'id' => $data->id
                                        ], [
                                            'title' => Yii::t('app/modules/blog','Edit source version: {language}', [
                                                'language' => $locale['name']
                                            ])
                                        ]
                                    );
                                else  // Other localization versions
                                    $output[] = Html::a($flag,
                                        [
                                            'posts/update', 'id' => $data->id,
                                            'locale' => $locale['locale']
                                        ], [
                                            'title' => Yii::t('app/modules/blog','Edit language version: {language}', [
                                                'language' => $locale['name']
                                            ])
                                        ]
                                    );

                            }

                        }
                        $separator = "";
                    } else {
                        foreach ($locales as $locale) {
                            if (!empty($locale)) {

                                if (extension_loaded('intl'))
                                    $language = mb_convert_case(trim(\Locale::getDisplayLanguage($locale, Yii::$app->language)), MB_CASE_TITLE, "UTF-8");
                                else
                                    $language = $locale;

                                if ($data->locale === $locale) // It`s source version
                                    $output[] = Html::a($language,
                                        [
                                            'news/update', 'id' => $data->id
                                        ], [
                                            'title' => Yii::t('app/modules/blog','Edit source version: {language}', [
                                                'language' => $language
                                            ])
                                        ]
                                    );
                                else  // Other localization versions
                                    $output[] = Html::a($language,
                                        [
                                            'news/update', 'id' => $data->id,
                                            'locale' => $locale
                                        ], [
                                            'title' => Yii::t('app/modules/blog','Edit language version: {language}', [
                                                'language' => $language
                                            ])
                                        ]
                                    );
                            }
                        }
                    }


                    if (is_countable($output)) {
                        if (count($output) > 0) {
                            $onMore = false;
                            if (count($output) > 3)
                                $onMore = true;

                            if ($onMore)
                                return join(array_slice($output, 0, 3), $separator) . "&nbsp;…";
                            else
                                return join($separator, $output);

                        }
                    }

                    return null;
                }
            ],

            [
                'class' => 'yii\grid\ActionColumn',
                'header' => Yii::t('app/modules/blog','Actions'),
                'headerOptions' => [
                    'class' => 'text-center'
                ],
                'contentOptions' => [
                    'class' => 'text-center'
                ],
                'buttons'=> [
                    'view' => function($url, $data, $key) {
                        $output = [];
                        $versions = $data->getAllVersions($data->id, true);
                        $locales = ArrayHelper::map($versions, 'id', 'locale');
                        if (isset(Yii::$app->translations)) {
                            foreach ($locales as $item_locale) {
                                $locale = Yii::$app->translations->parseLocale($item_locale, Yii::$app->language);
                                if ($item_locale === $locale['locale']) { // Fixing default locale from PECL intl

                                    if ($data->locale === $locale['locale']) // It`s source version
                                        $output[] = Html::a(Yii::t('app/modules/blog','View source version: {language}', [
                                            'language' => $locale['name']
                                        ]), ['cats/view', 'id' => $data->id]);
                                    else  // Other localization versions
                                        $output[] = Html::a(Yii::t('app/modules/blog','View language version: {language}', [
                                            'language' => $locale['name']
                                        ]), ['cats/view', 'id' => $data->id, 'locale' => $locale['locale']]);

                                }
                            }
                        } else {
                            foreach ($locales as $locale) {
                                if (!empty($locale)) {

                                    if (extension_loaded('intl'))
                                        $language = mb_convert_case(trim(\Locale::getDisplayLanguage($locale, Yii::$app->language)), MB_CASE_TITLE, "UTF-8");
                                    else
                                        $language = $locale;

                                    if ($data->locale === $locale) // It`s source version
                                        $output[] = Html::a(Yii::t('app/modules/blog','View source version: {language}', [
                                            'language' => $language
                                        ]), ['cats/view', 'id' => $data->id]);
                                    else  // Other localization versions
                                        $output[] = Html::a(Yii::t('app/modules/blog','View language version: {language}', [
                                            'language' => $language
                                        ]), ['cats/view', 'id' => $data->id, 'locale' => $locale]);

                                }
                            }
                        }

                        if (is_countable($output)) {
                            if (count($output) > 1) {
                                $html = '';
                                $html .= '<div class="btn-group">';
                                $html .= Html::a(
                                    '<span class="glyphicon glyphicon-eye-open"></span> ' .
                                    Yii::t('app/modules/blog', 'View') .
                                    ' <span class="caret"></span>',
                                    '#',
                                    [
                                        'class' => "btn btn-block btn-link btn-xs dropdown-toggle",
                                        'data-toggle' => "dropdown",
                                        'aria-haspopup' => "true",
                                        'aria-expanded' => "false"
                                    ]);
                                $html .= '<ul class="dropdown-menu dropdown-menu-right">';
                                $html .= '<li>' . implode("</li><li>", $output) . '</li>';
                                $html .= '</ul>';
                                $html .= '</div>';
                                return $html;
                            }
                        }
                        return Html::a('<span class="glyphicon glyphicon-eye-open"></span> ' .
                            Yii::t('app/modules/blog', 'View'),
                            [
                                'cats/view',
                                'id' => $data->id
                            ], [
                                'class' => 'btn btn-link btn-xs'
                            ]
                        );
                    },
                    'update' => function($url, $data, $key) {
                        $output = [];
                        $versions = $data->getAllVersions($data->id, true);
                        $locales = ArrayHelper::map($versions, 'id', 'locale');
                        if (isset(Yii::$app->translations)) {
                            foreach ($locales as $item_locale) {
                                $locale = Yii::$app->translations->parseLocale($item_locale, Yii::$app->language);
                                if ($item_locale === $locale['locale']) { // Fixing default locale from PECL intl

                                    if ($data->locale === $locale['locale']) // It`s source version
                                        $output[] = Html::a(Yii::t('app/modules/blog','Edit source version: {language}', [
                                            'language' => $locale['name']
                                        ]), ['cats/update', 'id' => $data->id]);
                                    else  // Other localization versions
                                        $output[] = Html::a(Yii::t('app/modules/blog','Edit language version: {language}', [
                                            'language' => $locale['name']
                                        ]), ['cats/update', 'id' => $data->id, 'locale' => $locale['locale']]);

                                }
                            }
                        } else {
                            foreach ($locales as $locale) {
                                if (!empty($locale)) {

                                    if (extension_loaded('intl'))
                                        $language = mb_convert_case(trim(\Locale::getDisplayLanguage($locale, Yii::$app->language)), MB_CASE_TITLE, "UTF-8");
                                    else
                                        $language = $locale;

                                    if ($data->locale === $locale) // It`s source version
                                        $output[] = Html::a(Yii::t('app/modules/blog','Edit source version: {language}', [
                                            'language' => $language
                                        ]), ['cats/update', 'id' => $data->id]);
                                    else  // Other localization versions
                                        $output[] = Html::a(Yii::t('app/modules/blog','Edit language version: {language}', [
                                            'language' => $language
                                        ]), ['cats/update', 'id' => $data->id, 'locale' => $locale]);

                                }
                            }
                        }

                        if (is_countable($output)) {
                            if (count($output) > 1) {
                                $html = '';
                                $html .= '<div class="btn-group">';
                                $html .= Html::a(
                                    '<span class="glyphicon glyphicon-pencil"></span> ' .
                                    Yii::t('app/modules/blog', 'Edit') .
                                    ' <span class="caret"></span>',
                                    '#',
                                    [
                                        'class' => "btn btn-block btn-link btn-xs dropdown-toggle",
                                        'data-toggle' => "dropdown",
                                        'aria-haspopup' => "true",
                                        'aria-expanded' => "false"
                                    ]);
                                $html .= '<ul class="dropdown-menu dropdown-menu-right">';
                                $html .= '<li>' . implode("</li><li>", $output) . '</li>';
                                $html .= '</ul>';
                                $html .= '</div>';
                                return $html;
                            }
                        }
                        return Html::a('<span class="glyphicon glyphicon-pencil"></span> ' .
                            Yii::t('app/modules/blog', 'Edit'),
                            [
                                'cats/update',
                                'id' => $data->id
                            ], [
                                'class' => 'btn btn-link btn-xs'
                            ]
                        );
                    },
                    'delete' => function($url, $data, $key) {
                        $output = [];
                        $versions = $data->getAllVersions($data->id, true);
                        $locales = ArrayHelper::map($versions, 'id', 'locale');
                        if (isset(Yii::$app->translations)) {
                            foreach ($locales as $item_locale) {
                                $locale = Yii::$app->translations->parseLocale($item_locale, Yii::$app->language);
                                if ($item_locale === $locale['locale']) { // Fixing default locale from PECL intl

                                    if ($data->locale === $locale['locale']) // It`s source version
                                        $output[] = Html::a(Yii::t('app/modules/blog','Delete source version: {language}', [
                                            'language' => $locale['name']
                                        ]), ['cats/delete', 'id' => $data->id], [
                                            'data-method' => 'POST',
                                            'data-confirm' => Yii::t('app/modules/blog', 'Are you sure you want to delete the language version of this category?')
                                        ]);
                                    else  // Other localization versions
                                        $output[] = Html::a(Yii::t('app/modules/blog','Delete language version: {language}', [
                                            'language' => $locale['name']
                                        ]), ['cats/delete', 'id' => $data->id, 'locale' => $locale['locale']], [
                                            'data-method' => 'POST',
                                            'data-confirm' => Yii::t('app/modules/blog', 'Are you sure you want to delete the language version of this category?')
                                        ]);

                                }
                            }
                        } else {
                            foreach ($locales as $locale) {
                                if (!empty($locale)) {

                                    if (extension_loaded('intl'))
                                        $language = mb_convert_case(trim(\Locale::getDisplayLanguage($locale, Yii::$app->language)), MB_CASE_TITLE, "UTF-8");
                                    else
                                        $language = $locale;

                                    if ($data->locale === $locale) // It`s source version
                                        $output[] = Html::a(Yii::t('app/modules/blog','Delete source version: {language}', [
                                            'language' => $language
                                        ]), ['cats/delete', 'id' => $data->id], [
                                            'data-method' => 'POST',
                                            'data-confirm' => Yii::t('app/modules/blog', 'Are you sure you want to delete the language version of this category?')
                                        ]);
                                    else  // Other localization versions
                                        $output[] = Html::a(Yii::t('app/modules/blog','Delete language version: {language}', [
                                            'language' => $language
                                        ]), ['cats/delete', 'id' => $data->id, 'locale' => $locale], [
                                            'data-method' => 'POST',
                                            'data-confirm' => Yii::t('app/modules/blog', 'Are you sure you want to delete the language version of this category?')
                                        ]);

                                }
                            }
                        }

                        if (is_countable($output)) {
                            if (count($output) > 1) {
                                $html = '';
                                $html .= '<div class="btn-group">';
                                $html .= Html::a(
                                    '<span class="glyphicon glyphicon-trash"></span> ' .
                                    Yii::t('app/modules/blog', 'Delete') .
                                    ' <span class="caret"></span>',
                                    '#',
                                    [
                                        'class' => "btn btn-block btn-link btn-xs dropdown-toggle",
                                        'data-toggle' => "dropdown",
                                        'aria-haspopup' => "true",
                                        'aria-expanded' => "false"
                                    ]);
                                $html .= '<ul class="dropdown-menu dropdown-menu-right">';
                                $html .= '<li>' . implode("</li><li>", $output) . '</li>';
                                $html .= '</ul>';
                                $html .= '</div>';
                                return $html;
                            }
                        }
                        return Html::a('<span class="glyphicon glyphicon-trash"></span> ' .
                            Yii::t('app/modules/blog', 'Delete'),
                            [
                                'cats/delete',
                                'id' => $data->id
                            ], [
                                'class' => 'btn btn-link btn-xs',
                                'data-method' => 'POST',
                                'data-confirm' => Yii::t('app/modules/blog', 'Are you sure you want to delete this post?')
                            ]
                        );
                    }
                ],
            ]
        ],
        'pager' => [
            'options' => [
                'class' => 'pagination',
            ],
            'maxButtonCount' => 5,
            'activePageCssClass' => 'active',
            'prevPageCssClass' => '',
            'nextPageCssClass' => '',
            'firstPageCssClass' => 'previous',
            'lastPageCssClass' => 'next',
            'firstPageLabel' => Yii::t('app/modules/blog', 'First page'),
            'lastPageLabel'  => Yii::t('app/modules/blog', 'Last page'),
            'prevPageLabel'  => Yii::t('app/modules/blog', '&larr; Prev page'),
            'nextPageLabel'  => Yii::t('app/modules/blog', 'Next page &rarr;')
        ],
    ]); ?>
    <hr/>
    <div>
        <?= Html::a(Yii::t('app/modules/blog', 'Add new category'), ['cats/create'], ['class' => 'btn btn-add btn-success pull-right']) ?>
    </div>
    <?php Pjax::end(); ?>
</div>

<?php echo $this->render('../_debug'); ?>
