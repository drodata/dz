<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "forum".
 *
 * @property int $id
 * @property string $name 名称
 * @property int $post_count 帖子数
 *
 * @property Post[] $posts
 */
class Forum extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'forum';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['post_count'], 'default', 'value' => 0],
            [['name'], 'required'],
            [['post_count'], 'integer'],
            [['name'], 'string', 'max' => 45],
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
            'post_count' => 'Post Count',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPosts()
    {
        return $this->hasMany(Post::className(), ['forum_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return ForumQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ForumQuery(get_called_class());
    }

    public function syncPostCount($event)
    {
        if ($event->name == Post::EVENT_AFTER_INSERT) {
            $this->post_count++;
        } else {
            $this->post_count--;
        }

        if (!$this->save()) {
            throw new \yii\db\Exception('Failed');
        }
    }
}
