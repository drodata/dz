<?php
namespace app\models;

use Yii;
use yii\base\NotSupportedException;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

use drodata\helpers\Html;
use drodata\behaviors\TimestampBehavior;
use drodata\behaviors\BlameableBehavior;
use drodata\behaviors\LookupBehavior;
use app\events\UserGroupUpgradeEvent;

/**
 * User model
 *
 * @property integer $id
 * @property string $username
 * @property string $auth_key
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property integer $status
 * @property integer $group_id
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $last_logined_at
 */
class User extends ActiveRecord implements IdentityInterface
{
    // 用户组升级事件
    const EVENT_GROUP_UPGRADED = 'group-upgraded';

    const STATUS_ACTIVE = 1;

    public function init()
    {
        $this->on(self::EVENT_AFTER_INSERT, [$this, 'initData']);

        $this->on(self::EVENT_GROUP_UPGRADED, [$this, 'syncGroupId']);
        $this->on(self::EVENT_GROUP_UPGRADED, [$this, 'logGroupUgrade']);
    }
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
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
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['group_id', 'default', 'value' => 1],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['group_id', 'required'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGroup()
    {
        return $this->hasOne(Group::className(), ['id' => 'group_id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getData()
    {
        return $this->hasOne(UserData::className(), ['user_id' => 'id']);
    }
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * 根据 user_data 各积分列计算用户的总积分数
     */
    public function getCredits()
    {
        // 确保读取最新值
        unset($this->data);

        return $this->data->credit1 * 2
            + $this->data->credit2 * 3 
            + $this->data->credit3
            + $this->data->credit4;
    }

    /**
     * 根据用户总积分，检查用户等级变化情况
     *
     * @return app\models\Group[] 当用户组没有变化时，仅返回当前用户组实例，
     * 否则返回变化前后的用户组实例。通过检查返回值元素的个数就能判断用户是否发生变化
     */
    public function checkGroup()
    {
        $credits = $this->getCredits();

        $oldGroup = $this->group;
        $newGroup = Group::find()->where(['<=', 'min', $credits])
            ->andWhere(['>=', 'max', $credits])->one();

        if ($newGroup->id > $oldGroup->id) {
            return [$oldGroup, $newGroup];
        } else {
            return [$oldGroup];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    public static function syncLoginTime($event)
    {
        $user = $event->identity;
        $user->updateAttributes(['last_logined_at' => time()]);

        // 写入日志
        Yii::info('登录系统', 'user.login');
    }

    /**
     * 同步用户数据(user_data)
     * 由发帖后等操作触发
     *
     * @param string $event->data 操作名称，例如：'create-post' 等
     */
    public function syncData($event)
    {
        switch ($event->data) {
            case 'create-post':
                $credits = [
                    'credit1' => 5,
                    'credit2' => 3,
                ];
                $action = '发帖';
                $bonus = "credit1 + 5, credit2 + 3";
                break;
            case 'create-comment':
                $credits = [
                    'credit1' => 2,
                    'credit2' => 3,
                ];
                $action = '评论别人的帖子';
                $bonus = "credit1 + 2, credit2 + 3";
                break;
            case 'post-be-commented':
                $credits = [
                    'credit3' => 1,
                ];
                $action = '帖子被别人评论';
                $bonus = "credit3 + 1";
                break;
            case 'favorite-post':
                $credits = [
                    'credit1' => -1,
                ];
                $action = '点赞别人的帖子';
                $bonus = "credit1 - 1";
                break;
            case 'post-be-favorited':
                $credits = [
                    'credit1' => 1,
                ];
                $action = '帖子被人点赞';
                $bonus = "credit1 + 1";
                break;
        }
        $this->data->updateCounters($credits);

        // 计入积分日志
        Yii::info($action . $bonus, 'user.credit');


        // 积分改变后检查等级是否改变
        $groups = $this->checkGroup();
        if (count($groups) > 1) {

            list($oldGroup, $newGroup) = $groups;

            $event = new UserGroupUpgradeEvent([
                'oldGroup' => $oldGroup,
                'newGroup' => $newGroup,
            ]);

            $this->trigger(self::EVENT_GROUP_UPGRADED, $event);
        }
    }

    /**
     * 用户升级后，更新 user.group_id
     * @param UserGroupUpgradeEvent $event
     */
    public function syncGroupId($event)
    {
        $this->group_id = $event->newGroup->id;

        if (!$this->save()) {
            throw new \yii\db\Exception('Failed');
        }
    }
    /**
     * 用户升级后，计入日志
     * @param UserGroupUpgradeEvent $event
     */
    public function logGroupUgrade($event)
    {
        $message = "等级从{$event->oldGroup->name}升级为{$event->newGroup->name}";

        Yii::info($message, 'user.upgrade');
    }

    /**
     * 新建用户后，初始化 user_data 
     */
    public function initData($event)
    {
        $data = new UserData(['user_id' => $this->id]);
        if (!$data->save()) {
            throw new \yii\db\Exception('Failed');
        }
    }
}
