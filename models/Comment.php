<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "comment".
 *
 * @property int $id
 * @property string $content
 *
 * @property PostComment[] $postComments
 */
class Comment extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'comment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['content'], 'required'],
            [['content'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'content' => 'Content',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPostComments()
    {
        return $this->hasMany(PostComment::className(), ['comment_id' => 'id']);
    }

    /**
     * 通用评论记录创建后，向 post_comment 写入记录
     *
     * @param int $event->data 帖子 ID
     */
    public function insertPostComment($event)
    {
        $comment = new PostComment([
            'post_id' => $event->data,
            'comment_id' => $this->id,
        ]);

        if (!$comment->save()) {
            throw new \yii\db\Exception('Failed');
        }
    }
}
