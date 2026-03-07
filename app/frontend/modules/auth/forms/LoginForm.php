<?php

namespace frontend\modules\auth\forms;

use yii\base\Model;

class LoginForm extends Model
{
    public $email;
    public $password;
    public $rememberMe = false;

    public function rules(): array
    {
        return [
            [['email', 'password'], 'required'],
            [['email'], 'email'],
            [['rememberMe'], 'boolean'],
            [['password'], 'string', 'min' => 6],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'email' => 'Email',
            'password' => 'Password',
            'rememberMe' => 'Remember me',
        ];
    }
}
