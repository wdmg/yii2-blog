<?php

use wdmg\widgets\TagsInput;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use wdmg\widgets\Editor;
use wdmg\widgets\SelectInput;

/* @var $this yii\web\View */
/* @var $model wdmg\blog\models\Posts */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="blog-form row">
    <?php $form = ActiveForm::begin([
        'id' => "addPostForm",
        'enableAjaxValidation' => true,
        'options' => [
            'enctype' => 'multipart/form-data'
        ]
    ]); ?>
    <div class="col-xs-12 col-sm-12 col-md-8 col-lg-9">
        <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
        <?php
            $output = '';
            if (($postURL = $model->getPostUrl(true, true)) && $model->id) {
                $output = Html::a($model->getPostUrl(true, false), $postURL, [
                    'target' => '_blank',
                    'data-pjax' => 0
                ]);
            }

            if (!empty($output))
                echo Html::tag('label', Yii::t('app/modules/blog', 'Post URL')) . Html::tag('fieldset', $output) . '<br/>';

        ?>
        <?= $form->field($model, 'alias')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'excerpt')->textarea(['rows' => 3]) ?>
        <?= $form->field($model, 'content')->widget(Editor::class, [
            'options' => [
                'id' => 'posts-form-content',
            ],
            'pluginOptions' => []
        ]) ?>

        <?php
            if ($model->image) {
                echo '<div class="row">';
                echo '<div class="col-xs-12 col-sm-3 col-md-2">' . Html::img($model->getImagePath(true) . '/' . $model->image, ['class' => 'img-responsive']) . '</div>';
                echo '<div class="col-xs-12 col-sm-9 col-md-10">' . $form->field($model, 'file')->fileInput() . '</div>';
                echo '</div><br/>';
            } else {
                echo $form->field($model, 'file')->fileInput();
            }
        ?>

        <?= $form->field($model, 'title')->textInput() ?>
        <?= $form->field($model, 'description')->textarea(['rows' => 3]) ?>
        <?= $form->field($model, 'keywords')->textarea(['rows' => 3]) ?>
        <hr/>
        <div class="form-group">
            <?= Html::a(Yii::t('app/modules/blog', '&larr; Back to list'), ['posts/index'], ['class' => 'btn btn-default pull-left']) ?>&nbsp;
            <?= Html::submitButton(Yii::t('app/modules/blog', 'Save'), ['class' => 'btn btn-success pull-right']) ?>
        </div>
    </div>
    <div class="col-xs-12 col-sm-12 col-md-4 col-lg-3">

        <?= $form->field($model, 'categories')->checkboxList($categoriesList, [
            'class' => 'list-group',
            'onclick' => "$(this).val( $('input:checkbox:checked').val()); ", // if you use required as a validation rule you will need this for the time being until a fix is in place by yii2
            'item' => function($index, $label, $name, $checked, $value) {
                return '<li class="list-group-item"><label><input type="checkbox" ' . (($checked) ? "checked" : "") . ' name="' . $name . '" value="' . $value . '" tabindex="' . $index . '">&nbsp;' . $label . '</label></li>';
            }
        ]) ?>

        <?= $form->field($model, 'tags')->widget(TagsInput::class, [
            'options' => [
                'id' => 'posts-form-tags',
                'class' => 'form-control',
                'placeholder' => Yii::t('app/modules/blog', 'Type tags...')
            ],
            'pluginOptions' => [
                'autocomplete' => Yii::$app->request->absoluteUrl,
                'format' => 'json',
                'minInput' => 2,
                'maxTags' => 100
            ]
        ]); ?>

        <?= $form->field($model, 'in_sitemap', [
            'template' => "{label}\n<br/>{input}\n{hint}\n{error}"
        ])->checkbox(['label' => Yii::t('app/modules/blog', '- display in the sitemap')])->label(Yii::t('app/modules/blog', 'Sitemap'))
        ?>
        <?= $form->field($model, 'in_rss', [
            'template' => "{label}\n<br/>{input}\n{hint}\n{error}"
        ])->checkbox(['label' => Yii::t('app/modules/blog', '- display in the rss-feed')])->label(Yii::t('app/modules/blog', 'RSS-feed'))
        ?>
        <?= $form->field($model, 'in_turbo', [
            'template' => "{label}\n<br/>{input}\n{hint}\n{error}"
        ])->checkbox(['label' => Yii::t('app/modules/blog', '- display in the turbo-pages')])->label(Yii::t('app/modules/blog', 'Yandex turbo'))
        ?>
        <?= $form->field($model, 'in_amp', [
            'template' => "{label}\n<br/>{input}\n{hint}\n{error}"
        ])->checkbox(['label' => Yii::t('app/modules/blog', '- display in the AMP pages')])->label(Yii::t('app/modules/blog', 'Google AMP'))
        ?>

        <?= $form->field($model, 'status')->widget(SelectInput::class, [
            'items' => $statusModes,
            'options' => [
                'id' => 'posts-form-status',
                'class' => 'form-control'
            ]
        ]); ?>
        <hr/>
        <div class="form-group">
            <?= Html::submitButton(Yii::t('app/modules/blog', 'Save'), ['class' => 'btn btn-block btn-success pull-right']) ?>
        </div>
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
                    form.find('#posts-alias').val(data.alias);
                    form.yiiActiveForm('validateAttribute', 'posts-alias');
                }
            }).fail(function () {
                /*form.find('#options-type').val("");
                form.find('#options-type').trigger('change');*/
            });
            return false; // prevent default form submission
        }
    }
    $("#addPostForm").on("afterValidateAttribute", afterValidateAttribute);
});
JS
); ?>