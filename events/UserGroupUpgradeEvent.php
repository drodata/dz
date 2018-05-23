<?php

namespace app\events;

use Yii;
use yii\base\Event;

class UserGroupUpgradeEvent extends Event
{
    // 升级前的等级名称
    public $oldGroupName;

    // 升级后的等级名称
    public $newGroupName;
}
