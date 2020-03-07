<?php
use yii\helpers\Html;
use yii\helpers\HtmlPurifier;

/* @var $this yii\web\View */
/* @var $model wdmg\blog\models\Posts */

?>
<div class="post">
    <h2><?= Html::encode($model->title); ?></h2>

    <?php
    if ($model->image) {
        echo '<div class="col-xs-12 col-sm-12">' . Html::img($model->getImagePath(true) . '/' . $model->image, ['class' => 'img-responsive']) . '</div>';
    }
    ?>

    <?= HtmlPurifier::process($model->content); ?>

</div>