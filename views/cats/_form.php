<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use wdmg\widgets\SelectInput;
use wdmg\widgets\LangSwitcher;
use wdmg\widgets\AliasInput;

/* @var $this yii\web\View */
/* @var $model wdmg\blog\models\Categories */
/* @var $form yii\widgets\ActiveForm */
?>

    <div class="blog-form">
        <?php
            echo LangSwitcher::widget([
                'label' => Yii::t('app/modules/blog', 'Language version'),
                'model' => $model,
                'renderWidget' => 'button-group',
                'createRoute' => 'cats/create',
                'updateRoute' => 'cats/update',
                'supportLocales' => $this->context->module->supportLocales,
                'versions' => (isset($model->source_id)) ? $model->getAllVersions($model->source_id, true) : $model->getAllVersions($model->id, true),
                'options' => [
                    'id' => 'locale-switcher',
                    'class' => 'pull-right'
                ]
            ]);
        ?>
        <?php $form = ActiveForm::begin([
            'id' => "addCategoryForm",
            'enableAjaxValidation' => true,
            'options' => [
                'enctype' => 'multipart/form-data'
            ]
        ]); ?>
        <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'alias')->widget(AliasInput::class, [
            'labels' => [
                'edit' => Yii::t('app/modules/blog', 'Edit'),
                'save' => Yii::t('app/modules/blog', 'Save')
            ],
            'options' => [
                'baseUrl' => ($model->id) ? $model->url : Url::to($model->getRoute(), true)
            ]
        ])->label(Yii::t('app/modules/blog', 'Category URL')); ?>

        <div class="panel panel-default">
            <div class="panel-heading">
                <h6 class="panel-title">
                    <a data-toggle="collapse" href="#postMetaTags">
                        <?= Yii::t('app/modules/blog', "SEO") ?>
                    </a>
                </h6>
            </div>
            <div id="postMetaTags" class="panel-collapse collapse">
                <div class="panel-body">
                    <?= $form->field($model, 'title')->textInput() ?>
                    <?= $form->field($model, 'description')->textarea(['rows' => 3]) ?>
                    <?= $form->field($model, 'keywords')->textarea(['rows' => 3]) ?>
                </div>
            </div>
        </div>

        <?= $form->field($model, 'parent_id')->widget(SelectInput::class, [
            'items' => $parentsList,
            'options' => [
                'class' => 'form-control'
            ]
        ])->label(Yii::t('app/modules/blog', 'Parent category')); ?>

        <?= $form->field($model, 'locale')->widget(SelectInput::class, [
            'items' => $languagesList,
            'options' => [
                'id' => 'news-form-locale',
                'class' => 'form-control'
            ]
        ])->label(Yii::t('app/modules/blog', 'Language')); ?>

        <hr/>
        <div class="form-group">
            <?= Html::a(Yii::t('app/modules/blog', '&larr; Back to list'), ['cats/index'], ['class' => 'btn btn-default pull-left']) ?>&nbsp;
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
                if (data.alias && form.find('#categories-alias').val().length == 0) {
                    form.find('#categories-alias').val(data.alias);
                    form.find('#categories-alias').change();
                    form.yiiActiveForm('validateAttribute', 'categories-alias');
                }
            }).fail(function () {
                /*form.find('#options-type').val("");
                form.find('#options-type').trigger('change');*/
            });
            return false; // prevent default form submission
        }
    }
    $("#addCategoryForm").on("afterValidateAttribute", afterValidateAttribute);
});
JS
); ?>