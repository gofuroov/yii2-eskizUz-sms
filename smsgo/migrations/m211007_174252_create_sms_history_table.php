<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%sms_history}}`.
 */
class m211007_174252_create_sms_history_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%sms_history}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(),
            'phone' => $this->string()->notNull(),
            'message' => $this->string(),
            'from' => $this->string()->notNull(),
            'callback_url' => $this->string()->notNull(),
            'status' => $this->tinyInteger(),
            'message_id' => $this->integer(),
            'status_date' => $this->dateTime(),

            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
        ]);

        $this->addForeignKey("FK_sms_history__user_id", "{{%sms_history}}", "user_id", '{{%user}}', 'id', 'CASCADE', 'CASCADE');

        $this->addForeignKey("FK_sms_history__created_by", "{{%sms_history}}", "created_by", '{{%user}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey("FK_sms_history__updated_by", "{{%sms_history}}", "updated_by", '{{%user}}', 'id', 'CASCADE', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey("FK_sms_history__user_id", "{{%sms_history}}");
        $this->dropForeignKey("FK_sms_history__created_by", "{{%sms_history}}");
        $this->dropForeignKey("FK_sms_history__updated_by", "{{%sms_history}}");

        $this->dropTable('{{%sms_history}}');
    }
}
