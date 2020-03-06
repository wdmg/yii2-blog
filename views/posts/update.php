<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model wdmg\blog\models\Blog */

$this->title = Yii::t('app/modules/blog', 'Updating post: {name}', [
    'name' => $model->name,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/modules/blog', 'All posts'), 'url' => ['posts/index']];
$this->params['breadcrumbs'][] = Yii::t('app/modules/blog', 'Edit');


?>
<div class="page-header">
    <h1><?= Html::encode($this->title) ?> <small class="text-muted pull-right">[v.<?= $this->context->module->version ?>]</small></h1>
</div>
<div class="blog-update">
    <?= $this->render('_form', [
        'module' => $module,
        'model' => $model,
        'categoriesList' => $model->getAllCategoriesList(false),
        'tagsList' => $model->getTagsList(),
        'statusModes' => $model->getStatusesList(),
    ]); ?>
</div>