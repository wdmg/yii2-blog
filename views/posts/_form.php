<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use wdmg\widgets\Editor;
use wdmg\widgets\SelectInput;
use wdmg\widgets\TagsInput;
use wdmg\widgets\LangSwitcher;
use wdmg\widgets\AliasInput;

/* @var $this yii\web\View */
/* @var $model wdmg\blog\models\Posts */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="blog-form row">
    <div class="col-xs-12 col-sm-12">
        <?php
            echo LangSwitcher::widget([
                'label' => Yii::t('app/modules/blog', 'Language version'),
                'model' => $model,
                'renderWidget' => 'button-group',
                'createRoute' => 'posts/create',
                'updateRoute' => 'posts/update',
                'supportLocales' => $this->context->module->supportLocales,
                'versions' => (isset($model->source_id)) ? $model->getAllVersions($model->source_id, true) : $model->getAllVersions($model->id, true),
                'options' => [
                    'id' => 'locale-switcher',
                    'class' => 'pull-right'
                ]
            ]);
        ?>
    </div>

    <?php $form = ActiveForm::begin([
        'id' => "addPostForm",
        'enableAjaxValidation' => true,
        'options' => [
            'enctype' => 'multipart/form-data'
        ]
    ]); ?>
    <div class="col-xs-12 col-sm-12 col-md-8 col-lg-9">
        <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'alias')->widget(AliasInput::class, [
            'labels' => [
                'edit' => Yii::t('app/modules/blog', 'Edit'),
                'save' => Yii::t('app/modules/blog', 'Save')
            ],
            'options' => [
                'baseUrl' => ($model->id) ? $model->url : Url::to($model->getRoute(), true)
            ]
        ])->label(Yii::t('app/modules/blog', 'Post URL')); ?>

        <?= $form->field($model, 'excerpt')->textarea(['rows' => 3]) ?>
        <?= $form->field($model, 'content')->widget(Editor::class, [
            'options' => [
                'id' => 'posts-form-content',
            ],
            'pluginOptions' => []
        ]) ?>
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
        <div class="panel panel-default">
            <div class="panel-heading">
                <h6 class="panel-title">
                    <a data-toggle="collapse" href="#postOptions">
                        <?= Yii::t('app/modules/blog', "Other options") ?>
                    </a>
                </h6>
            </div>
            <div id="postOptions" class="panel-collapse collapse">
                <div class="panel-body">
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
                </div>
            </div>
        </div>
        <div class="hidden-xs hidden-sm">
            <hr/>
            <div class="form-group">
                <?= Html::a(Yii::t('app/modules/blog', '&larr; Back to list'), ['posts/index'], ['class' => 'btn btn-default pull-left']) ?>&nbsp;
                <?= Html::submitButton(Yii::t('app/modules/blog', 'Save'), ['class' => 'btn btn-save btn-success pull-right']) ?>
            </div>
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

        <?= $form->field($model, 'locale')->widget(SelectInput::class, [
            'items' => $languagesList,
            'options' => [
                'id' => 'news-form-locale',
                'class' => 'form-control'
            ]
        ])->label(Yii::t('app/modules/blog', 'Language')); ?>

        <?= $form->field($model, 'status')->widget(SelectInput::class, [
            'items' => $statusModes,
            'options' => [
                'id' => 'posts-form-status',
                'class' => 'form-control'
            ]
        ]); ?>
        <hr/>
        <div class="form-group hidden-xs hidden-sm">
            <?= Html::submitButton(Yii::t('app/modules/blog', 'Save'), ['class' => 'btn btn-save btn-block btn-success pull-right']) ?>
        </div>
        <div class="form-group hidden-md hidden-lg">
            <?= Html::a(Yii::t('app/modules/blog', '&larr; Back to list'), ['posts/index'], ['class' => 'btn btn-default pull-left']) ?>&nbsp;
            <?= Html::submitButton(Yii::t('app/modules/blog', 'Save'), ['class' => 'btn btn-success pull-right']) ?>
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
                if (data.alias && form.find('#posts-alias').val().length == 0) {
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