<?php

namespace App\Repositories\Access;

use App\Repositories\EloquentRepository;
use App\Model\User;
use App\Model\Menu;
use App\Model\UserRelationalRole;
use App\Model\UserRelationalGroup;
use App\Model\RoleRelationalGroup;
use Illuminate\Support\Facades\DB;
use App\Model\AccessRelationalRole;

class AccessAuthorityRepository extends EloquentRepository
{

    protected $UserRelationalRole;
    protected $UserRelationalGroup;
    protected $Menu;
    protected $RoleRelationalGroup;
    protected $AccessRelationalRole;

    public function __construct()
    {
        parent::__construct(new User());
        $this->UserRelationalRole = new UserRelationalRole();
        $this->UserRelationalGroup = new UserRelationalGroup();
        $this->Menu = new Menu();
        $this->RoleRelationalGroup = new RoleRelationalGroup();
        $this->AccessRelationalRole = new AccessRelationalRole();
    }

    //菜单访问权限
    public function menuAccess($route = '')
    {

        //无用户组和无角色的
        if (!$this->UserRelationalRole->where('uid', '=', session('user.uid'))->exists() && !$this->UserRelationalGroup->where('uid', '=', session('user.uid'))->exists()) {

            return false;
            //有用户组无角色的
        } else {

            //查询用户组是否分配角色
            $gid = array_column($this->UserRelationalGroup->where('uid', '=', session('user.uid'))->get(['gid'])->toArray(), 'gid');//获取用户所属用户组id

            $group_rid = array_column($this->RoleRelationalGroup->whereIn('gid', $gid)->get(['rid'])->toArray(), 'rid');//获取用户组所属角色ID

            $user_rid = array_column($this->UserRelationalRole->where('uid', '=', session('user.uid'))->get(['rid'])->toArray(), 'rid');//获取用户所属角色ID

            $rids = array_unique(array_merge($group_rid, $user_rid));//合并所有的角色

            //用户组未分配角色的
            if (empty($rids)) {
                return false;
            }
        }

        $aids = array_column($this->AccessRelationalRole->whereIn('rid', $rids)->get(['aid'])->toArray(), 'aid');//获取用户所有的权限ID

        $mid = $this->Menu->where('route', '=', $route)->value('id');//获取当前菜单ID

        $current_aid = DB::table('access_relational_menu')->where('mid', '=', $mid)->value('aid');//当前菜单所属权限ID

        return in_array($current_aid, $aids); //用户权限中是否包含当前权限ID
    }
}