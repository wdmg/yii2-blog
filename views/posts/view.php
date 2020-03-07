<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model wdmg\blog\models\Blog */

$this->title = Yii::t('app/modules/blog', 'View blog post');
$this->params['breadcrumbs'][] = ['label' => $this->context->module->name, 'url' => ['posts/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="page-header">
    <h1><?= Html::encode($this->title) ?> <small class="text-muted pull-right">[v.<?= $this->context->module->version ?>]</small></h1>
</div>
<div class="blog-view">

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
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
                'attribute' => 'image',
                'format' => 'html',
                'value' => function($model) {
                    if ($model->image) {
                        return Html::img($model->getImagePath(true) . '/' . $model->image, [
                            'class' => 'img-thumbnail',
                            'style' => 'max-height: 160px'
                        ]);
                    } else {
                        return null;
                    }
                }
            ],
            'title:ntext',
            [
                'attribute' => 'content',
                'format' => 'html',
                'contentOptions' => [
                    'style' => 'display:inline-block;max-height:360px;overflow-x:auto;'
                ]
            ],
            [
                'attribute' => 'categories',
                'label' => Yii::t('app/modules/blog', 'Categories'),
                'format' => 'html',
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
                'label' => Yii::t('app/modules/blog', 'Tags'),
                'format' => 'html',
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

            'description:ntext',
            'keywords:ntext',

            [
                'attribute' => 'common',
                'label' => Yii::t('app/modules/blog','Common'),
                'format' => 'html',
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
                'attribute' => 'created',
                'label' => Yii::t('app/modules/blog','Created'),
                'format' => 'html',
                'value' => function($data) {

                    $output = "";
                    if ($user = $data->createdBy) {
                        $output = Html::a($user->username, ['../admin/users/view/?id='.$user->id], [
                            'target' => '_blank',
                            'data-pjax' => 0
                        ]);
                    } else if ($data->created_by) {
                        $output = $data->created_by;
                    }

                    if (!empty($output))
                        $output .= ", ";

                    $output .= Yii::$app->formatter->format($data->updated_at, 'datetime');
                    return $output;
                }
            ],
            [
                'attribute' => 'updated',
                'label' => Yii::t('app/modules/blog','Updated'),
                'format' => 'html',
                'value' => function($data) {

                    $output = "";
                    if ($user = $data->updatedBy) {
                        $output = Html::a($user->username, ['../admin/users/view/?id='.$user->id], [
                            'target' => '_blank',
                            'data-pjax' => 0
                        ]);
                    } else if ($data->updated_by) {
                        $output = $data->updated_by;
                    }

                    if (!empty($output))
                        $output .= ", ";

                    $output .= Yii::$app->formatter->format($data->updated_at, 'datetime');
                    return $output;
                }
            ],

        ],
    ]); ?>
    <hr/>
    <div class="form-group">
        <?= Html::a(Yii::t('app/modules/blog', '&larr; Back to list'), ['list/index'], ['class' => 'btn btn-default pull-left']) ?>&nbsp;
        <?= Html::a(Yii::t('app/modules/blog', 'Update'), ['list/update', 'id' => $model->id], ['class' => 'btn btn-primary pull-right']) ?>
    </div>
</div>