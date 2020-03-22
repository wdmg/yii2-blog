<?php

use wdmg\helpers\StringHelper;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model wdmg\blog\models\Categories */

$this->title = Yii::t('app/modules/blog', 'Updating category: {name}', [
    'name' => $model->name,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/modules/blog', 'Blog'), 'url' => ['list/index']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/modules/blog', 'All categories'), 'url' => ['cats/index']];
$this->params['breadcrumbs'][] = ['label' => StringHelper::stringShorter($model->name, 64), 'url' => ['cats/view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app/modules/blog', 'Edit');


?>
<div class="page-header">
    <h1><?= Html::encode($this->title) ?><?= ($model->id === 1) ? " <span class=\"text-muted\">(" . Yii::t('app/modules/blog', 'default') . ")</span>" : ""?><small class="text-muted pull-right">[v.<?= $this->context->module->version ?>]</small></h1>
</div>
<div class="blog-cats-update">
    <?= $this->render('_form', [
        'module' => $module,
        'model' => $model,
        'parentsList' => $model->getParentsList(false, true)
    ]); ?>
</div>