<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user_data".
 *
 * @property int $user_id
 * @property int $credit1
 * @property int $credit2
 * @property int $credit3
 * @property int $credit4
 * @property int $post
 * @property int $comment
 * @property int $follow
 * @property int $follower
 *
 * @property User $user
 */
class UserData extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_data';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['credit1', 'credit2', 'credit3', 'credit4', 'post', 'comment', 'follow', 'follower'], 'default', 'value' => 0],
            [['credit1', 'credit2', 'credit3', 'credit4', 'post', 'comment', 'follow', 'follower'], 'required'],
            [['credit1', 'credit2', 'credit3', 'credit4', 'post', 'comment', 'follow', 'follower'], 'integer'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'User ID',
            'credit1' => 'Credit1',
            'credit2' => 'Credit2',
            'credit3' => 'Credit3',
            'credit4' => 'Credit4',
            'post' => 'Post',
            'comment' => 'Comment',
            'follow' => 'Follow',
            'follower' => 'Follower',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
