<?php
/**
 * @author Olimjon G'ofurov <gofuroov@gmail.com>
 * Date: 10/10/21
 * Time: 15:36
 */

namespace backend\modules\smsgo\models;

class SendSmsForm extends \yii\base\Model
{
    public $phone;
    public $text;

    public function rules()
    {
        return [
            [['phone', 'textextInput([])t'], 'required'],
            [['phone'], 'string'],
            ['phone', 'match', 'pattern' => '/\+[9][9][8] [0-9][0-9] [0-9][0-9][0-9] [0-9][0-9] [0-9][0-9]/'],
            [['text'], 'string', 'max' => 500]
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'phone' => 'Telefon raqami',
            'text' => 'Xabar matni'
        ];
    }
}