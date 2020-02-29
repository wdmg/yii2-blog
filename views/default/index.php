<?php

use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
use yii\helpers\Url;
use yii\widgets\ListView;

/* @var $this yii\web\View */
/* @var $model wdmg\blog\models\Blog */

echo ListView::widget([
    'dataProvider' => $dataProvider,
    'itemView' => '_list',
]);

?>