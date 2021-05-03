<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model wdmg\blog\models\Posts */

if ($model->source_id)
    $this->title = Yii::t('app/modules/blog', 'New post version');
else
    $this->title = Yii::t('app/modules/blog', 'New post');

$this->params['breadcrumbs'][] = ['label' => Yii::t('app/modules/blog', 'All posts'), 'url' => ['posts/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="page-header">
    <h1><?= Html::encode($this->title) ?> <small class="text-muted pull-right">[v.<?= $this->context->module->version ?>]</small></h1>
</div>
<div class="blog-create">
    <?= $this->render('_form', [
        'module' => $module,
        'model' => $model,
        'categoriesList' => $model->getAllCategoriesList(false),
        'tagsList' => $model->getTagsList(),
        'statusModes' => $model->getStatusesList(),
    ]); ?>
</div>