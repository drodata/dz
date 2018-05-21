<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[Forum]].
 *
 * @see Forum
 */
class ForumQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Forum[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Forum|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
