<?php

namespace App\Repositories\AdminUser;

use App\Model\Role;
use App\Model\UserRelationalRole;
use App\Model\RoleRelationalGroup;
use App\Repositories\EloquentRepository;

class RoleRepository extends EloquentRepository
{

    protected $UserRelationalRole;
    protected $RoleRelationalGroup;

    public function __construct(Role $model, UserRelationalRole $UserRelationalRole, RoleRelationalGroup $RoleRelationalGroup)
    {
        parent::__construct($model);
        $this->UserRelationalRole = $UserRelationalRole;
        $this->RoleRelationalGroup = $RoleRelationalGroup;
    }


    public function delete($id)
    {
        //查询该角色下面是否有用户
        $user = $this->UserRelationalRole->where('rid', '=', $id)->exists();
        //查询该角色下面是否有用户组
        $group = $this->RoleRelationalGroup->where('rid', '=', $id)->exists();
        if ($user || $group) {
            return back()->withErrors('此角色下，有用户或用户组，无法删除');
        }

        return parent::delete($id); // TODO: Change the autogenerated stub
    }
}