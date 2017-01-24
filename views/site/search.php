<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$form = ActiveForm::begin();

echo $form->field($model, 'meta_category')->dropDownList($list_category);

echo $form->field($model, 'text')->textInput();

echo $form->field($model, 'is_image')->checkbox();

echo Html::submitButton('search');

ActiveForm::end();

//echo result

if($result){
    //$data = new \yii\data\ActiveDataProvider(['query' => $result]);
    echo \yii\grid\GridView::widget([
        'dataProvider' => $result,
        'columns' => [
            'thingId','thingName','thingDescription'
        ]
    ]);
}
