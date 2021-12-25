<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%sms_setting}}`.
 */
class m211007_172742_create_sms_setting_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%sms_setting}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'info' => $this->string(),
            'value' => $this->string(512)->notNull(),

            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
        ]);

        $this->addForeignKey("FK_sms_setting__created_by", "{{%sms_setting}}", "created_by", '{{%user}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey("FK_sms_setting__updated_by", "{{%sms_setting}}", "updated_by", '{{%user}}', 'id', 'CASCADE', 'CASCADE');

        $this->batchInsert('{{%sms_setting}}', ['name', 'info', 'value'], [
            ['email', 'Email manzili', 'test@test.uz'],
            ['password', 'Paroli', '12345678'],
            ['token', 'Token', 'ABCDEF'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey("FK_sms_setting__created_by", "{{%sms_setting}}");
        $this->dropForeignKey("FK_sms_setting__updated_by", "{{%sms_setting}}");

        $this->dropTable('{{%sms_setting}}');
    }
}
