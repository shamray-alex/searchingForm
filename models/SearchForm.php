<?php

namespace app\models;

use yii\base\Model;

class SearchForm extends Model
{

    public $meta_category;
    public $text;
    public $is_image;

    public function rules()
    {
        return [
            ['text', 'string'],
            ['meta_category', 'integer'],
            ['is_image', 'boolean']
        ];
    }

}