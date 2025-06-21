<?php

namespace App\Http\Controllers\Agent;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Request;
use App\DAO\UserDAO;
use App\Models\PrizePool;
use App\Models\Setting;
use App\Models\Users;
use App\Models\UserReal;
use App\Models\Agent;
use App\Utils\IdCardIdentity;
use App\Events\RealNameEvent;

class UserRealController extends Controller
{
    public function index()
    {
        $authorityList=session()->get("authorityList");
        return view("agent.userReal.index",['authorityList' =>$authorityList]);
    }

    //用户列表
    public function list(Request $request)
    {
        $limit = $request->input('limit', 10);
        $account = $request->input('account', '');

        $list = new UserReal();
        
        // 查询代理关联的
        $agent_id = Agent::getAgentId();
        $users = Users::where('agent_note_id',$agent_id)->get()->pluck('id');
        $list = $list->where(function ($query) use ($users) {
            $query->whereIn('user_id', $users);
        });
        
        if (!empty($account)) {
            $list = $list->whereHas('user', function ($query) use ($account) {
                $query->where("phone", 'like', '%' . $account . '%')->orwhere('email', 'like', '%' . $account . '%');
            });
        }

        // 过滤初级认证
        $list = $list->where('auth_status','!=',1);
        $list = $list->orderBy('id', 'desc')->paginate($limit);
        return response()->json(['code' => 0, 'data' => $list->items(), 'count' => $list->total()]);
    }

    public function detail(Request $request)
    {
        $id = $request->input('id', 0);
        if (empty($id)) {
            return $this->error("参数错误");
        }
        $result = UserReal::find($id);
        return view('agent.userReal.info', ['result' => $result]);
    }

    public function del(Request $request)
    {
        $id = $request->input('id');
        $userreal = UserReal::find($id);
        if (empty($userreal)) {
            $this->error("认证信息未找到");
        }
        try {

            $userreal->delete();
            return $this->success('删除成功');
        } catch (\Exception $ex) {
            return $this->error($ex->getMessage());
        }
    }
 
    //状态审核
    public function auth(Request $request)
    {
        $id = $request->input('id', 0);
        //$remark = $request->input('remark', '');
        $userreal = UserReal::find($id);
        if (empty($userreal)) {
            return $this->error('参数错误');
        }
        if ($userreal->review_status == 1) {
            //查询users表判断是否为第一次实名认证
            $user = Users::find($userreal->user_id);
            $is_realname = $user->is_realname;
            if ($is_realname == 1) {
                //1:未实名认证过  2：实名认证过
                $real_zhitui = Users::where("is_realname", 2)->where("parent_id", $userreal->user_id)->count();//实名认证过的有效直推人数
                //获取下级的总人数
                $member = Users::get()->toArray();
                $real_teamnumber = $this->GetTeamMember($member, $userreal->user_id);//实名认证过的团队人数
                $user->real_teamnumber = $real_teamnumber;
                $user->is_realname = 2;
                $user->save();//自己实名认证获取通证结束

            }
            $userreal->review_status = 2;
        } elseif ($userreal->review_status == 2) {
            $userreal->review_status = 1;
        } else {
            $userreal->review_status = 1;
        }
        try {
            //$userreal->remark =$remark;
            $userreal->save();
            //用户实名事件
            //event(new RealNameEvent($user));
            return $this->success('操作成功');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }
    //审核通过
    public function passauth(Request $request)
    {
        $id = $request->input('id', 0);
        $remark = $request->input('remark', '');
        
        $userreal = UserReal::find($id);
        if (empty($userreal)) {
            return $this->error('参数错误');
        }
        //查询users表判断是否为第一次实名认证
        $user = Users::find($userreal->user_id);
        $is_realname = $user->is_realname;

        if ($is_realname == 0 || $is_realname == null) {
            //1:未实名认证过  2：实名认证过
            $real_zhitui = Users::where("is_realname", 2)->where("parent_id", $userreal->user_id)->count();//实名认证过的有效直推人数
            //获取下级的总人数
            $member = Users::get()->toArray();
            $real_teamnumber = $this->GetTeamMember($member, $userreal->user_id);//实名认证过的团队人数
            $user->real_teamnumber = $real_teamnumber;
            $user->is_realname = 1;
            $userreal->advanced_user=0;
            $user->save();//自己实名认证获取通证结束

        }

        if ($is_realname == 1) {
            //1:未实名认证过  2：实名认证过
            $real_zhitui = Users::where("is_realname", 2)->where("parent_id", $userreal->user_id)->count();//实名认证过的有效直推人数
            //获取下级的总人数
            $member = Users::get()->toArray();
            $real_teamnumber = $this->GetTeamMember($member, $userreal->user_id);//实名认证过的团队人数
            $user->real_teamnumber = $real_teamnumber;
            $user->is_realname = 2;
            $userreal->advanced_user=2;
            $user->save();//自己实名认证获取通证结束

        }
        $userreal->review_status = 2;
        
        try {
            $userreal->remark = $remark;
            $userreal->save();
            //用户实名事件
            //event(new RealNameEvent($user));
            return $this->success('操作成功');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }
    //审核拒绝
    public function refuseauth(Request $request)
    {
        $id = $request->input('id', 0);
        $remark = $request->input('remark', '');
        
        $userreal = UserReal::find($id);
        if (empty($userreal)) {
            return $this->error('参数错误');
        }
        $userreal->review_status = 3;
        
        try {
            $userreal->remark = $remark;
            $userreal->save();
            //用户实名事件
            //event(new RealNameEvent($user));
            return $this->success('操作成功');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }

    //递归查询用户下级所有人数
    public function GetTeamMember($members, $mid)
    {
        $Teams = array();//最终结果
        $mids = array($mid);//第一次执行时候的用户id
        do {
            $othermids = array();
            $state = false;
            foreach ($mids as $valueone) {
                foreach ($members as $key => $valuetwo) {
                    if ($valuetwo['parent_id'] == $valueone) {
                        //实名认证通过的团队人数
                        $Teams[] = $valuetwo['id'];//找到我的下级立即添加到最终结果中
                        $othermids[] = $valuetwo['id'];//将我的下级id保存起来用来下轮循环他的下级
                        //                        array_splice($members,$key,1);//从所有会员中删除他
                        $state = true;
                    }
                }
            }
            $mids = $othermids;//foreach中找到的我的下级集合,用来下次循环
        } while ($state == true);
        //$Teams=Users::where("parents_path","like","%$mid%")->where("is_realname","=",2)->count();
        $Teams = Users::whereIn("id", $Teams)->where("is_realname", "=", 2)->count();
        return $Teams;
    }
}
