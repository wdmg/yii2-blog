<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use wdmg\widgets\Editor;
use wdmg\widgets\SelectInput;

/* @var $this yii\web\View */
/* @var $model wdmg\blog\models\Posts */
/* @var $form yii\widgets\ActiveForm */
?>

    <div class="blog-form">
        <?php $form = ActiveForm::begin([
            'id' => "addTagForm",
            'enableAjaxValidation' => true,
            'options' => [
                'enctype' => 'multipart/form-data'
            ]
        ]); ?>
        <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
        <?php
            $output = '';
            if (($tagURL = $model->getTagUrl(true, true)) && $model->id) {
                $output = Html::a($model->getTagUrl(true, false), $tagURL, [
                        'target' => '_blank',
                        'data-pjax' => 0
                    ]);
            }

            if (!empty($output))
                echo Html::tag('label', Yii::t('app/modules/blog', 'Tag URL')) . Html::tag('fieldset', $output) . '<br/>';

        ?>
        <?= $form->field($model, 'alias')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'title')->textInput() ?>
        <?= $form->field($model, 'description')->textarea(['rows' => 3]) ?>
        <?= $form->field($model, 'keywords')->textarea(['rows' => 3]) ?>
        <hr/>
        <div class="form-group">
            <?= Html::a(Yii::t('app/modules/blog', '&larr; Back to list'), ['tags/index'], ['class' => 'btn btn-default pull-left']) ?>&nbsp;
            <?= Html::submitButton(Yii::t('app/modules/blog', 'Save'), ['class' => 'btn btn-success pull-right']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>

<?php $this->registerJs(<<< JS
$(document).ready(function() {
    function afterValidateAttribute(event, attribute, messages)
    {
        if (attribute.name && !attribute.alias && messages.length == 0) {
            var form = $(event.target);
            $.ajax({
                    type: form.attr('method'),
                    url: form.attr('action'),
                    data: form.serializeArray(),
                }
            ).done(function(data) {
                if (data.alias && form.find('#blog-alias').val().length == 0) {
                    form.find('#blog-alias').val(data.alias);
                    form.yiiActiveForm('validateAttribute', 'blog-alias');
                }
            }).fail(function () {
                /*form.find('#options-type').val("");
                form.find('#options-type').trigger('change');*/
            });
            return false; // prevent default form submission
        }
    }
    $("#addTagForm").on("afterValidateAttribute", afterValidateAttribute);
});
JS
); ?>