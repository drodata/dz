<?php

namespace app\models;

use Yii;
use drodata\helpers\Html;
use drodata\behaviors\TimestampBehavior;
use drodata\behaviors\BlameableBehavior;

/**
 * This is the model class for table "post_comment".
 *
 * @property int $id
 * @property int $post_id
 * @property int $comment_id
 * @property int $created_at
 * @property int $created_by
 *
 * @property Comment $comment
 * @property Post $post
 * @property PostCommentFavorite $postCommentFavorite
 */
class PostComment extends \yii\db\ActiveRecord
{
    public function init()
    {
        // 新建、删除评论后更新 `post.comment_count`
        $this->on(self::EVENT_AFTER_INSERT, [$this->post, 'syncCommentCount']);
        // 新建评论后调整评论者积分
        $this->on(self::EVENT_AFTER_INSERT, [Yii::$app->user->identity, 'syncData'], 'create-comment');
    }
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'post_comment';
    }

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
            [['post_id', 'comment_id'], 'required'],
            [['post_id', 'comment_id', 'created_at', 'created_by'], 'integer'],
            [['comment_id'], 'exist', 'skipOnError' => true, 'targetClass' => Comment::className(), 'targetAttribute' => ['comment_id' => 'id']],
            [['post_id'], 'exist', 'skipOnError' => true, 'targetClass' => Post::className(), 'targetAttribute' => ['post_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'post_id' => 'Post ID',
            'comment_id' => 'Comment ID',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getComment()
    {
        return $this->hasOne(Comment::className(), ['id' => 'comment_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPost()
    {
        return $this->hasOne(Post::className(), ['id' => 'post_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFavorite()
    {
        return $this->hasOne(PostCommentFavorite::className(), ['post_comment_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreator()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }
}
