<?php

namespace app\events;

use Yii;
use yii\base\Event;

class UserGroupUpgradeEvent extends Event
{
    /**
     * @var app\models\Group 升级前的用户组实例
     *
     */
    public $oldGroup;

    /**
     * @var app\models\Group 升级后的用户组实例
     *
     */
    public $newGroup;
}
