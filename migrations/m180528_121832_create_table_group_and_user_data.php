<?php

use yii\db\Migration;

/**
 * Class m180528_121832_create_table_group_and_user_data
 */
class m180528_121832_create_table_group_and_user_data extends Migration
{
    public $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('group', [
            'id' => $this->primaryKey(),
            'name' => $this->string(45)->notNull(),
            'min' => $this->integer()->notNull(),
            'max' => $this->integer()->notNull(),
        ], $this->tableOptions);

        $this->createTable('user_data', [
            'user_id' => $this->primaryKey(),
            'credit1' => $this->integer()->notNull(),
            'credit2' => $this->integer()->notNull(),
            'credit3' => $this->integer()->notNull(),
            'credit4' => $this->integer()->notNull(),
            'post' => $this->integer()->notNull(),
            'comment' => $this->integer()->notNull(),
            'follow' => $this->integer()->notNull(),
            'follower' => $this->integer()->notNull(),
        ], $this->tableOptions);

        $this->addForeignKey(
            'fk-user_data-user',
            'user_data', 'user_id',
            'user', 'id',
            'NO ACTION', 'NO ACTION'
        );

        $this->addColumn('user', 'group_id', $this->integer()->notNull()->defaultValue(1)->after('email'));
        $this->batchInsert('group', ['name', 'min'], [
            ['Level 1', 30],
            ['Level 2', 70],
            ['Level 3', 100],
            ['Level 4', 150],
            ['Level 5', 200],
            ['Level 6', 500],
        ]);
        $this->addForeignKey(
            'fk-user-group',
            'user', 'group_id',
            'group', 'id',
            'NO ACTION', 'NO ACTION'
        );
        $this->dropColumn('user', 'group');
        $this->dropColumn('user', 'credit');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180528_121832_create_table_group_and_user_data cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180528_121832_create_table_group_and_user_data cannot be reverted.\n";

        return false;
    }
    */
}
