<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model wdmg\blog\models\Tags */

$this->title = Yii::t('app/modules/blog', 'New tag');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/modules/blog', 'Blog'), 'url' => ['posts/index']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/modules/blog', 'All tags'), 'url' => ['tags/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="page-header">
    <h1><?= Html::encode($this->title) ?> <small class="text-muted pull-right">[v.<?= $this->context->module->version ?>]</small></h1>
</div>
<div class="blog-tags-create">
    <?= $this->render('_form', [
        'module' => $module,
        'model' => $model
    ]); ?>
</div>