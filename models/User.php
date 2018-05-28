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
     * 更新金币数
     */
    public function syncCredit($event)
    {
        list($action, $amount) = $event->data;
        switch ($action) {
            case 'createPost':
                $msg = '发帖';
                break;
            case 'favorite':
                $msg = '点赞别人的帖子';
                break;
            case 'beFavorited':
                $msg = '被别人点赞你的帖子';
                break;
            case 'createComment':
                $msg = '发表评论';
                break;
        }

        $this->credit += $amount;

        // 积分变动写入日志
        $amt = $amount > 0 ? '+' . $amount : $amount;
        $message = "{$msg}，积分{$amt}";
        Yii::info($message, 'user.credit');


        // 根据当前积分判断是否升级用户组

        $group = ($this->credit - $this->credit%10)/10 + 1;
        if ($this->group < $group) {
            $oldGroup = $this->group;
            $this->group = $group;

            $event = new UserGroupUpgradeEvent([
                'oldGroupName' => $this->groupName($oldGroup),
                'newGroupName' => $this->groupName($group),
            ]);

            // 这里我们触发了自定义事件，通过向该事件上绑定 handler (已在开头 init() 内绑定), 我们可以做更多的事情
            $this->trigger(self::EVENT_GROUP_UPGRADED, $event);
        }

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
        $message = "等级从{$event->oldGroupName}升级为{$event->oldGroupName}";

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
