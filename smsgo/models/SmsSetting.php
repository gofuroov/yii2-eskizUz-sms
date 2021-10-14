<?php

namespace backend\modules\smsgo\models;

use common\models\User;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\web\ConflictHttpException;

/**
 * This is the model class for table "sms_setting".
 *
 * @property int $id
 * @property string $name
 * @property string|null $info
 * @property string $value
 * @property int|null $created_at
 * @property int|null $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 *
 * @property User $createdBy
 * @property User $updatedBy
 */
class SmsSetting extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'sms_setting';
    }

    /**
     * @throws ConflictHttpException
     */
    public static function getEmail(): string
    {
        $email = self::findOne(['name' => 'email']);
        if (is_null($email)) {
            throw new ConflictHttpException("Sms sozlamalarida xato. Email kiritilmagan.");
        }
        return $email->value;
    }

    /**
     * @throws ConflictHttpException
     */
    public static function getPassword(): string
    {
        $password = self::findOne(['name' => 'password']);
        if (is_null($password)) {
            throw new ConflictHttpException("Sms sozlamalarida xato. Parol kiritilmagan.");
        }
        return $password->value;
    }

    /**
     * @throws ConflictHttpException
     */
    public static function getToken(): string
    {
        $token = self::findOne(['name' => 'token']);
        if (is_null($token)) {
            throw new ConflictHttpException("Sms sozlamalarida xato. Token kiritilmagan.");
        }
        return $token->value;
    }

    /**
     * @param string $token
     * @return bool
     */
    public static function updateToken(string $token): bool
    {
        $model = self::findOne(['name' => 'token']);
        if (is_null($model)) {
            $model = new self(['name' => 'token']);
        }
        $model->value = $token;
        if ($model->save()) {
            return true;
        }
        \Yii::error($model->errors);
        return false;
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
            BlameableBehavior::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'value'], 'required'],
            [['created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['name', 'info'], 'string', 'max' => 255],
            [['value'], 'string', 'max' => 512],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['created_by' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['updated_by' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'info' => 'Info',
            'value' => 'Value',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Gets query for [[CreatedBy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    /**
     * Gets query for [[UpdatedBy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'updated_by']);
    }
}
