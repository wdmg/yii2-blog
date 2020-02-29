<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model wdmg\blog\models\Blog */

$this->title = Yii::t('app/modules/blog', 'View blog post');
$this->params['breadcrumbs'][] = ['label' => $this->context->module->name, 'url' => ['blog/index']];
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
            'description:ntext',
            'keywords:ntext',
            [
                'attribute' => 'in_sitemap',
                'format' => 'html',
                'value' => function($data) {
                    if ($data->in_sitemap)
                        return '<span class="fa fa-check text-success"></span>';
                    else
                        return '<span class="fa fa-remove text-danger"></span>';
                }
            ],
            [
                'attribute' => 'in_rss',
                'format' => 'html',
                'value' => function($data) {
                    if ($data->in_rss)
                        return '<span class="fa fa-check text-success"></span>';
                    else
                        return '<span class="fa fa-remove text-danger"></span>';
                }
            ],
            [
                'attribute' => 'in_turbo',
                'format' => 'html',
                'value' => function($data) {
                    if ($data->in_turbo)
                        return '<span class="fa fa-check text-success"></span>';
                    else
                        return '<span class="fa fa-remove text-danger"></span>';
                }
            ],
            [
                'attribute' => 'in_amp',
                'format' => 'html',
                'value' => function($data) {
                    if ($data->in_amp)
                        return '<span class="fa fa-check text-success"></span>';
                    else
                        return '<span class="fa fa-remove text-danger"></span>';
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
            'created_by',
            'created_at:datetime',
            'updated_by',
            'updated_at:datetime'
        ],
    ]); ?>
    <hr/>
    <div class="form-group">
        <?= Html::a(Yii::t('app/modules/blog', '&larr; Back to list'), ['blog/index'], ['class' => 'btn btn-default pull-left']) ?>&nbsp;
        <?= Html::a(Yii::t('app/modules/blog', 'Update'), ['blog/update', 'id' => $model->id], ['class' => 'btn btn-primary pull-right']) ?>
    </div>
</div>