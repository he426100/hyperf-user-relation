<?php

declare(strict_types=1);
/**
 * This file is part of he426100/hyperf-user-relation.
 *
 * @link     https://github.com/he426100/hyperf-user-relation
 * @contact  mrpzx001@gmail.com
 * @license  https://github.com/he426100/hyperf-user-relation/blob/master/LICENSE
 */
namespace He426100\UserRelation;

use Hyperf\Database\Model\Model;
use Hyperf\DbConnection\Db;

trait UserRelation
{
    /**
     * 添加上下级关系.
     *
     * @param int $parent_id 上级
     * @param int $user_id 下级
     * @return array
     */
    public function addParent(int $parent_id, int $user_id)
    {
        if (! $this->canSetParent($user_id, $parent_id)) {
            throw new \Exception('上下级关系不正确');
        }
        /** @var Model */
        $relationModel = config('user_relation.user_relation_model');

        $lists = [];
        // 建立跟上级的关系
        $lists[] = ['parent_id' => $parent_id, 'user_id' => $user_id, 'level' => 1];
        // 如果上级有上级，把我跟每一个上级建立关系
        $grand_parents = $relationModel::query()->where('user_id', $parent_id)->select(['parent_id', 'level'])->orderBy('level', 'asc')->get();
        foreach ($grand_parents as $gp) {
            $lists[] = ['parent_id' => $gp['parent_id'], 'user_id' => $user_id, 'level' => $gp['level'] + 1];
        }
        // 如果我有下级，把我所有下级跟我的上级建立关系
        $children = $relationModel::query()->where('parent_id', $user_id)->select(['user_id', 'level'])->orderBy('level', 'asc')->get();
        foreach ($children as $child) {
            $lists[] = ['parent_id' => $parent_id, 'user_id' => $child['user_id'], 'level' => $child['level'] + 1];
            // 还要跟我的上级的所有上级建立关系
            foreach ($grand_parents as $gp) {
                $lists[] = ['parent_id' => $gp['parent_id'], 'user_id' => $child['user_id'], 'level' => $child['level'] + $gp['level'] + 1];
            }
        }
        return Db::transaction(static function () use ($relationModel, $lists) {
            $ret = [];
            foreach ($lists as $data) {
                $ret[] = $relationModel::create($data);
            }
            return $ret;
        });
    }

    /**
     * 更改上级.
     *
     * @param int $user_id 用户id
     * @param int $parent_id 新上级id，为0时仅去除原有上级
     */
    public function updateParent(int $user_id, int $parent_id = 0)
    {
        if (! $this->canSetParent($user_id, $parent_id)) {
            throw new \Exception('上下级关系不正确');
        }
        Db::transaction(function () use ($user_id, $parent_id) {
            $this->deleteParent($user_id);
            if ($parent_id > 0) {
                $this->addParent($parent_id, $user_id);
            }
        });
    }

    /**
     * 去除上级.
     *
     * @param int $user_id 用户id
     */
    public function deleteParent(int $user_id)
    {
        return Db::transaction(static function () use ($user_id) {
            /** @var Model */
            $relationModel = config('user_relation.user_relation_model');

            // 先跟原来的上级们断掉关系
            $relationModel::query()->where('user_id', $user_id)->delete();
            // 要断绝跟上级们的关系，还得加上我的下级们
            $children = $relationModel::query()->where('parent_id', $user_id)->select(['user_id', 'level'])->get();
            foreach ($children as $child) {
                // 对每一个下级而言，跟“我”的关系不用变，但是在我之上的更远关系应该删除，无论更远的上级是谁
                $relationModel::query()->where('user_id', $child['user_id'])->where('level', '>', $child['level'])->delete();
            }
        });
    }

    /**
     * 是否可以设置上级.
     */
    public function canSetParent(int $user_id, int $parent_id): bool
    {
        /** @var Model */
        $relationModel = config('user_relation.user_relation_model');

        if ($parent_id == $user_id) { // 不能把自己设为上级
            return false;
        }
        if ($relationModel::query()->where('parent_id', $user_id)->where('user_id', $parent_id)->exists()) { // 不能把下级设为上级
            return false;
        }
        if ($relationModel::query()->where('user_id', $user_id)->where('parent_id', $parent_id)->where('level', 1)->exists()) { // 不能重复设置上下级
            return false;
        }
        return true;
    }
}
