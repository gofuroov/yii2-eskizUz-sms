<?php

namespace backend\modules\smsgo\models;

use common\models\User;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "sms_history".
 *
 * @property int $id
 * @property int|null $user_id
 * @property string $phone
 * @property string|null $message
 * @property string $from
 * @property string $callback_url
 * @property int|null $status
 * @property int|null $message_id
 * @property string|null $status_date
 * @property int|null $created_at
 * @property int|null $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 *
 * @property User $createdBy
 * @property User $updatedBy
 * @property User $user
 */
class SmsHistory extends \yii\db\ActiveRecord
{
    /**
     * Waiting - СМС в ожидании отправления оператору;
     * TRANSMTD - СМС передан сотовому оператору, но со стороны оператора обратно не получено статус смс сообщений;
     * DELIVRD - доставлено;
     * UNDELIV - недоставлено, обычно причиной может быть то что абонент блокируется со стороны оператора(недостаточно средст или долг);
     * EXPIRED - срок жизни смс истек(когда абонент в течение сутки не выходил на связь. У билайн если в теение часа);
     * REJECTD - один из основных причин это то что номер находится в черном списке;
     * DELETED - ошибка при отправки запроса(например когда адрес отправителя указан неверно);
     */
    public const STATUS_WAITING = 1;
    public const STATUS_TRANSMTD = 2;
    public const STATUS_DELIVERED = 3;
    public const STATUS_UNDELIV = 4;
    public const STATUS_EXPIRED = 5;
    public const STATUS_REJECTD = 6;
    public const STATUS_DELETED = 7;
    public const STATUS_OTHER = 0;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'sms_history';
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
            [['user_id', 'status', 'message_id', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['phone', 'from', 'callback_url'], 'required'],
            [['status_date'], 'safe'],
            [['phone', 'message', 'from', 'callback_url'], 'string', 'max' => 255],
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
            'user_id' => 'Foydalanuvchi',
            'phone' => 'Telefon raqami',
            'message' => 'Xabar',
            'from' => 'Qaysi raqamdan',
            'callback_url' => 'Callback Url',
            'status' => 'Status',
            'message_id' => 'Message ID',
            'status_date' => 'Status Vaqti',
            'created_at' => 'Yaratilgan',
            'updated_at' => 'O\'zgartirilgan',
            'created_by' => "Jo'natuvchi",
            'updated_by' => 'O\'zgartirdi',
        ];
    }

    /**
     * @return string
     */
    public function renderStatus(): string
    {
        return self::statuses()[$this->status] ?? '-';
    }

    /**
     * @return string[]
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_WAITING => "Kutilmoqda",
            self::STATUS_TRANSMTD => "Operatorga yuborilgan",
            self::STATUS_DELIVERED => "Yetkazildi",
            self::STATUS_UNDELIV => "Yetkazilmadi",
            self::STATUS_EXPIRED => "Muddati tugadi",
            self::STATUS_REJECTD => "Bekor qilindi",
            self::STATUS_DELETED => "O'chirilgan",

            self::STATUS_OTHER => "Xatolik",
        ];
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status): void
    {
        switch ($status) {
            case 'Waiting':
                $this->status = self::STATUS_WAITING;
                break;
            case 'TRANSMTD' :
                $this->status = self::STATUS_TRANSMTD;
                break;
            case 'DELIVRD' :
                $this->status = self::STATUS_DELIVERED;
                break;
            case 'UNDELIV' :
                $this->status = self::STATUS_UNDELIV;
                break;
            case 'EXPIRED' :
                $this->status = self::STATUS_EXPIRED;
                break;
            case 'REJECTD' :
                $this->status = self::STATUS_REJECTD;
                break;
            case 'DELETED' :
                $this->status = self::STATUS_DELETED;
                break;
            default:
                $this->status = self::STATUS_OTHER;
        }
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

    /**
     * Gets query for [[user]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser(): \yii\db\ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
