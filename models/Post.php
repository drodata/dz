<?php

namespace app\models;

use Yii;
use yii\data\ActiveDataProvider;
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
class Post extends \drodata\db\ActiveRecord
{
    public function init()
    {
        // 新建、删除帖子后更新 `forum.post_count`
        $this->on(self::EVENT_AFTER_INSERT, [$this->forum, 'syncPostCount']);
        // 新建帖子后增加 5 个金币
        $this->on(self::EVENT_AFTER_INSERT, [Yii::$app->user->identity, 'syncCredit'], ['createPost', 5]);
    }
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
    public function getComments()
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

    /**
     *
     * @return ActiveDataProvider
     */
    public function getCommentsDataProvider()
    {
        return new ActiveDataProvider([
            'query' => $this->getComments()->orderBy('created_at'),
        ]);
    }

    /**
     * 判断帖子是否被当前登录用户点赞过
     */
    public function getWasFavoritedByCurrentUser()
    {
        return (bool) PostFavorite::findOne([
            'post_id' => $this->id,
            'created_by' => Yii::$app->user->id,
        ]);
    }

    /**
     * 点赞
     */
    public function favorite()
    {
        $favorite = new PostFavorite([
            'post_id' => $this->id,
            'created_by' => Yii::$app->user->id,
        ]);

        // 点赞后, 点赞人的金币数 -1
        $favorite->on(PostFavorite::EVENT_AFTER_INSERT, [Yii::$app->user->identity, 'syncCredit'], ['favorite', -1]);
        // 点赞后, 点赞帖子作者的金币数 +1
        $favorite->on(PostFavorite::EVENT_AFTER_INSERT, [$favorite->post->creator, 'syncCredit'], ['beFavorited', 1]);

        if (!$favorite->save()) {
            throw new \yii\db\Exception($favorite->stringifyErrors());
        }
    }
}
