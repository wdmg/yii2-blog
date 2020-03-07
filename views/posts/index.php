<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\Pjax;
use wdmg\widgets\SelectInput;

/* @var $this yii\web\View */
/* @var $model wdmg\blog\models\Blog */

$this->title = Yii::t('app/modules/blog', 'All posts');
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="page-header">
    <h1>
        <?= Html::encode($this->title) ?> <small class="text-muted pull-right">[v.<?= $this->context->module->version ?>]</small>
    </h1>
</div>
<div class="blog-index">

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
                    if (($postURL = $model->getPostUrl(true, true)) && $model->id) {
                        $output .= '<br/>' . Html::a($model->getPostUrl(true, false), $postURL, [
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
                'attribute' => 'categories',
                'format' => 'html',
                'filter' => SelectInput::widget([
                    'model' => $searchModel,
                    'attribute' => 'categories',
                    'items' => $searchModel->getAllCategoriesList(true),
                    'options' => [
                        'id' => 'blogsearch-categories',
                        'class' => 'form-control'
                    ]
                ]),
                'headerOptions' => [
                    'class' => 'text-center'
                ],
                'contentOptions' => [
                    'class' => 'text-center'
                ],
                'value' => function($data) {
                    if ($categories = $data->getCategories()) {
                        $output = [];
                        foreach ($categories as $category) {
                            $output[] = Html::a($category->name, ['cats/view', 'id' => $category->id]);
                        }
                        return implode(", ", $output);
                    } else {
                        return null;
                    }
                }
            ],
            [
                'attribute' => 'tags',
                'format' => 'html',
                'filter' => SelectInput::widget([
                    'model' => $searchModel,
                    'attribute' => 'tags',
                    'items' => $searchModel->getAllTagsList(true),
                    'options' => [
                        'id' => 'blogsearch-tags',
                        'class' => 'form-control'
                    ]
                ]),
                'value' => function($data) {
                    if ($tags = $data->getTags()) {
                        $output = [];
                        foreach ($tags as $tag) {
                            $output[] = Html::a($tag->name, ['tags/view', 'id' => $tag->id]);
                        }
                        return implode(", ", $output);
                    } else {
                        return null;
                    }
                }
            ],

            [
                'attribute' => 'common',
                'label' => Yii::t('app/modules/blog','Common'),
                'format' => 'html',
                'headerOptions' => [
                    'class' => 'text-center'
                ],
                'contentOptions' => [
                    'class' => 'text-center'
                ],
                'value' => function($data) {
                    $output = '';
                    if ($data->in_sitemap)
                        $output .= '<span class="fa fa-fw fa-sitemap text-success" title="' . Yii::t('app/modules/blog','Present in sitemap') . '"></span>';
                    else
                        $output .= '<span class="fa fa-fw fa-sitemap text-danger" title="' . Yii::t('app/modules/blog','Not present in sitemap') . '"></span>';

                    $output .= "&nbsp;";

                    if ($data->in_rss)
                        $output .= '<span class="fa fa-fw fa-rss text-success" title="' . Yii::t('app/modules/blog','Present in RSS-feed') . '"></span>';
                    else
                        $output .= '<span class="fa fa-fw fa-rss text-danger" title="' . Yii::t('app/modules/blog','Not present in RSS-feed') . '"></span>';

                    $output .= "&nbsp;";

                    if ($data->in_turbo)
                        $output .= '<span class="fa fa-fw fa-rocket text-success" title="' . Yii::t('app/modules/blog','Present in Yandex.Turbo') . '"></span>';
                    else
                        $output .= '<span class="fa fa-fw fa-rocket text-danger" title="' . Yii::t('app/modules/blog','Not present in Yandex.Turbo') . '"></span>';

                    $output .= "&nbsp;";

                    if ($data->in_amp)
                        $output .= '<span class="fa fa-fw fa-bolt text-success" title="' . Yii::t('app/modules/blog','Present in Google AMP') . '"></span>';
                    else
                        $output .= '<span class="fa fa-fw fa-bolt text-danger" title="' . Yii::t('app/modules/blog','Not present in Google AMP') . '"></span>';

                    return $output;
                }
            ],
            [
                'attribute' => 'status',
                'format' => 'html',
                'filter' => SelectInput::widget([
                    'model' => $searchModel,
                    'attribute' => 'status',
                    'items' => $searchModel->getStatusesList(true),
                    'options' => [
                        'class' => 'form-control'
                    ]
                ]),
                'headerOptions' => [
                    'class' => 'text-center'
                ],
                'contentOptions' => [
                    'class' => 'text-center'
                ],
                'value' => function($data) {
                    if ($data->status == $data::POST_STATUS_PUBLISHED)
                        return '<span class="label label-success">'.Yii::t('app/modules/blog','Published').'</span>';
                    elseif ($data->status == $data::POST_STATUS_DRAFT)
                        return '<span class="label label-default">'.Yii::t('app/modules/blog','Draft').'</span>';
                    else
                        return $data->status;
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
            ]
        ]
    ]); ?>
    <hr/>
    <div>
        <?= Html::a(Yii::t('app/modules/blog', 'Add new post'), ['posts/create'], ['class' => 'btn btn-success pull-right']) ?>
    </div>
    <?php Pjax::end(); ?>
</div>

<?php echo $this->render('../_debug'); ?>
