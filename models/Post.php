<?php

namespace app\models;

use Yii;
use drodata\helpers\Html;
use drodata\behaviors\TimestampBehavior;
use drodata\behaviors\BlameableBehavior;

/**
 * This is the model class for table "post".
 *
 * @property int $id
 * @property int $forum_id
 * @property string $title
 * @property string $content
 * @property int $view_count
 * @property int $comment_count
 * @property int $created_at
 * @property int $created_by
 *
 * @property Forum $forum
 * @property User $createdBy
 * @property PostComment[] $postComments
 * @property PostFavorite $postFavorite
 */
class Post extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'post';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'updatedAtAttribute' => false,
            ],
            'blameable' => [
                'class' => BlameableBehavior::className(),
                'updatedByAttribute' => false,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['forum_id', 'title', 'content'], 'required'],
            [['forum_id', 'view_count', 'comment_count'], 'integer'],
            [['content'], 'string'],
            [['title'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'forum_id' => 'Forum ID',
            'title' => 'Title',
            'content' => 'Content',
            'view_count' => 'View Count',
            'comment_count' => 'Comment Count',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getForum()
    {
        return $this->hasOne(Forum::className(), ['id' => 'forum_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreator()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPostComments()
    {
        return $this->hasMany(PostComment::className(), ['post_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPostFavorite()
    {
        return $this->hasOne(PostFavorite::className(), ['post_id' => 'id']);
    }
}
