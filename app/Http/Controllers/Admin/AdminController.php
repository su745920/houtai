<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\{Admin, AdminRole, Agent, Users, Setting};

use Earnp\GoogleAuthenticator\GoogleAuthenticator;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;

class AdminController extends Controller
{

    private $agent_max_level = 4;

    function __construct()
    {
        $this->agent_max_level = Setting::getValueByKey('agent_max_level', 0);
    }


    public function delGoogle(Request $request)
    {
        $admin = Admin::find(request()->input('id'));
        if (session()->get('admin_is_super') != '1') {
            if ($request->ajax()) {
                return response()->json([
                    'code'=>403,
                    'msg'=>'权限不足,请联系管理员',
                ]);
            } else {
                abort(403, '权限不足,请联系管理员');
            }
        }
        $admin->google_secret="";
        $admin->save();

        return $this->success('操作成功');

    }
    
    public function delAgentGoogle(Request $request)
    {
        $agent = Agent::find(request()->input('id'));
        if (session()->get('admin_is_super') != '1') {
            if ($request->ajax()) {
                return response()->json([
                    'code'=>403,
                    'msg'=>'权限不足,请联系管理员',
                ]);
            } else {
                abort(403, '权限不足,请联系管理员');
            }
        }
        $agent->google_secret="";
        $agent->save();

        return $this->success('操作成功');

    }



    public function google(Request $request){
        $adminId=$request->input("id");
        $admin_user=Users::getById($adminId);
        $createSecret = GoogleAuthenticator::CreateSecret();
        $createSecret['qrcode'] = QrCode::encoding('UTF-8')->size(180)->margin(1)->generate($createSecret['codeurl']);
       
       echo 1;
       

        return view('admin.manager.google', ['admin_user' => $admin_user, 'roles' => $createSecret]);
    }
    
    public function users(Request $request)
    {
        if (session()->get('admin_is_super') != '1') {
            if ($request->ajax()) {
                return response()->json([
                    'code'=>403,
                    'msg'=>'权限不足,请联系管理员',
                ]);
            } else {
                abort(403, '权限不足,请联系管理员');
            }
        }
        $adminuser = Admin::all();
        $count = $adminuser->count();
        return response()->json(['code' => 0, 'count' => $count, 'msg' => '', 'data' => $adminuser]);
    }

    public function managerIndex()
    {
        $authorityList=session()->get("authorityList");
        return view('admin.manager.index',['authorityList' => $authorityList]);
    }
    
    public function adminRoles()
    {
     
        $authorityList=session()->get("authorityList");

        return view('admin.manager.admin_roles',['authorityList' => $authorityList]);
    }

    public function add(Request $request)
    {
        if (session()->get('admin_is_super') != '1') {
            if ($request->ajax()) {
                return response()->json([
                    'code'=>403,
                    'msg'=>'权限不足,请联系管理员',
                ]);
            } else {
                abort(403, '权限不足,请联系管理员');
            }
        }
        $id = request()->input('id', null);
        if (empty($id)) {
            $adminUser = new Admin();
        } else {
            $adminUser = Admin::find($id);
            if ($adminUser == null) {
                abort(404);
            }
        }
        $roles = AdminRole::all();


        return view('admin.manager.add', ['admin_user' => $adminUser, 'roles' => $roles]);
    }

    public function postAdd(Request $request)
    {
        if (session()->get('admin_is_super') != '1') {
            if ($request->ajax()) {
                return response()->json([
                    'code'=>403,
                    'msg'=>'权限不足,请联系管理员',
                ]);
            } else {
                abort(403, '权限不足,请联系管理员');
            }
        }
        $id = $request->input('id', null);
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'role_id' => 'required|numeric'
        ], [
            'username.required' => '姓名必须填写',
            'role_id.required'  => '角色必须选择',
            'role_id.numeric'   => '角色必须为数字'
        ]);
        if (empty($id)) {
            $adminUser = new Admin();
        } else {
            $adminUser = Admin::find($id);
            if ($adminUser == null) {
                return redirect()->back();
            }
        }
        $password = request()->input('password', '');
        $adminUser->role_id = request()->input('role_id', '0');
        if (request()->input('password', '') != '') {
            $adminUser->password = Users::MakePassword($password);
        }
        $validator->after(function ($validator) use ($adminUser, $id) {
            if (empty($id)) {
                if (Admin::where('username', request()->input('username'))->exists()) {
                    $validator->errors()->add('username', '用户已经存在');
                }
            }
        });

        $adminUser->username = request()->input('username', '');
        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }
        try {
            $adminUser->save();
        } catch (\Exception $ex) {
            $validator->errors()->add('error', $ex->getMessage());
            return $this->error($validator->errors()->first());
        }
        return $this->success('添加成功');
    }

    public function del(Request $request)
    {
        $admin = Admin::find(request()->input('id'));
        if (session()->get('admin_is_super') != '1') {
            if ($request->ajax()) {
                return response()->json([
                    'code'=>403,
                    'msg'=>'权限不足,请联系管理员',
                ]);
            } else {
                abort(403, '权限不足,请联系管理员');
            }
        }
        $bool = $admin->delete();
        if ($bool) {
            return $this->success('删除成功');
        } else {
            return $this->error('删除失败');
        }
    }

    public function agent()
    {
        $admin = Agent::where('is_admin', 1)->where('level', 0)->first();
        // var_dump($admin);
        // exit;
        if ($admin != null) {
            return redirect(route('agent'));
        } else {
            $hkok = DB::table('admin')->where('id', 1)->first();
            if ($hkok != null) {
                $insertData = [];
                $insertData['user_id'] = $hkok->id;
                $insertData['username'] = $hkok->username;
                $insertData['password'] = $hkok->password;
                $insertData['level'] = 0;
                $insertData['is_admin'] = 1;
                $insertData['reg_time'] = time();
                $insertData['pro_loss'] = 100.00;
                $insertData['pro_ser'] = 100.00;

                $id = DB::table('agent')->insertGetId($insertData);

                if ($id > 0) {
                    return redirect(route('agent'));
                } else {
                    return $this->error('失败');
                }
            }
        }
    }
    
     public function agentIndex()
    {
        $authorityList=session()->get("authorityList");
        return view('admin.agent.index',['authorityList' => $authorityList]);
    }
     public function agentUsers(Request $request)
    {

        $username = $request->input("username", "");
        $id = $request->input("id", 0);
        $is_lock = $request->input("is_lock", 2);
        $is_addson = $request->input("is_addson", 2);
        $parent_agent_id = $request->input("parent_agent_id", 0);

        $_self = Agent::where('id',1)->first();

        if ($_self === null) {
            return $this->outmsg('发生错误！请重新登录');
        }

        $where = [];
        if (!empty($username)) {
            $where[] = ['username', '=', $username];

            $_search_us = Agent::getUserByUsername($username);

            if ($_search_us == null) {
                return $this->error('该代理商不存在');
            } else {
                $level_path_Arr = explode(',', $_search_us->agent_path);
                if (!in_array($_self->id, $level_path_Arr)) {
                    return $this->error('该代理商并不属于您的团队');
                }
            }
        }
        if ($id > 0) {
            $where[] = ['id', '=', $id];

            $_search_us = Agent::getAgentById($id);
            if ($_search_us === null) {
                return $this->error('该代理商不存在');
            } else {
                $level_path_Arr = explode(',', $_search_us->agent_path);
                if (!in_array($_self->id, $level_path_Arr)) {
                    return $this->error('该代理商并不属于您的团队');
                }
            }
        }
        if (in_array($is_lock, [0, 1])) {
            $where[] = ['is_lock', '=', $is_lock];
        }
        if (in_array($is_addson, [0, 1])) {
            $where[] = ['is_addson', '=', $is_addson];
        }

        if ($parent_agent_id > 0) {
            $where[] = ['parent_agent_id', '=', $parent_agent_id];
        } else {
            $where[] = ['parent_agent_id', '=', $_self->id];
        }

        $result = Agent::where('status', 1)->where($where)->paginate(10);

        return $this->layuiData($result);
    }
    
     public function agentAdd(Request $request)
    {
        if (session()->get('admin_is_super') != '1') {
            if ($request->ajax()) {
                return response()->json([
                    'code'=>403,
                    'msg'=>'权限不足,请联系管理员',
                ]);
            } else {
                abort(403, '权限不足,请联系管理员');
            }
        }
        $id = request()->input('id', null);
        if (empty($id)) {
            $agentUser = new Agent();
        } else {
            $agentUser = Agent::find($id);
            if ($agentUser == null) {
                abort(404);
            }
        }

        return view('admin.agent.add', ['agent_user' => $agentUser]);
    }

    public function postAgentAdd(Request $request)
    {
        if (session()->get('admin_is_super') != '1') {
            if ($request->ajax()) {
                return response()->json([
                    'code'=>403,
                    'msg'=>'权限不足,请联系管理员',
                ]);
            } else {
                abort(403, '权限不足,请联系管理员');
            }
        }
        $id = $request->input('id', null);
        $validator = Validator::make($request->all(), [
            'username' => 'required'
        ], [
            'username.required' => '姓名必须填写'
        ]);
        if (empty($id)) {
            $agentUser = new Agent();
            $agentUser->reg_time = time();
        } else {
            $agentUser = Agent::find($id);
            if ($agentUser == null) {
                return redirect()->back();
            }
        }
        $password = request()->input('password', '');
        $is_lock = request()->input('is_lock', '0');
        $parent_agent_id = request()->input('parent_agent_id',1);
        $agentUser->is_lock = $is_lock;
        $agentUser->level = 1;
        
        $agentUser->is_addson = $request->input('is_addson', 1);
        $agentUser->pro_loss = $request->input('pro_loss', 0.00);
        $agentUser->pro_ser = $request->input('pro_ser', 0.00);
        $agentUser->is_admin = 0;
        $username = request()->input('username', '');
        
        $lock_time = 0;
        if($is_lock == 1) {
            $lock_time = time();
        }
        $agentUser->lock_time = $lock_time;
        if (request()->input('password', '') != '') {
            $agentUser->password = Users::MakePassword($password);
        }
        if(empty($parent_agent_id)) {
            $parent_agent_id = 1;
        }
        
        // 创建代理用户
        $user = new Users();
        $user->account_number = Str::random(37);;
        $user->type = 1;
        $user->lang = 'en';
        $user->password = Users::MakePassword('123456');
        $user->pay_password = Users::MakePassword('123456');
        $user->time = time();
        $user->head_portrait = URL("mobile/images/user_head.png");
        $user->extension_code = Users::getExtensionCode();
        $user->country_code = '86';
        if(!$user->save()) {
             return $this->error('参数错误');
        }
        
        $agentUser->user_id = $user->id;
        $agentUser->parent_agent_id = $parent_agent_id;
        $validator->after(function ($validator) use ($agentUser, $id,$username) {
            if (empty($id)) {
                if (Agent::where('username', $username)->exists()) {
                    $validator->errors()->add('username', '用户已经存在');
                }
            }
        });

        $agentUser->username = $username;
        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }
        try {
            $agentUser->save();
            $agentUser->agent_path = $agentUser->id . ',' . 1;
            $agentUser->save();
            $user->agent_id = $agentUser->id;
            $user->save();
        } catch (\Exception $ex) {
            $validator->errors()->add('error', $ex->getMessage());
            return $this->error($validator->errors()->first());
        }
        return $this->success('添加成功');
    }

    public function agentDel(Request $request)
    {
        $admin = Agent::find(request()->input('id'));
        if (session()->get('admin_is_super') != '1') {
            if ($request->ajax()) {
                return response()->json([
                    'code'=>403,
                    'msg'=>'权限不足,请联系管理员',
                ]);
            } else {
                abort(403, '权限不足,请联系管理员');
            }
        }
        $bool = $admin->delete();
        if ($bool) {
            return $this->success('删除成功');
        } else {
            return $this->error('删除失败');
        }
    }
    
     /**
     * 添加下级代理商时，查询该用户是否存在，是否已经是代理商等
     */
    public function searchuser(Request $request)
    {
        if ($request->isMethod('post')) {
            $username = $request->input("username", "");

            $_self =  Agent::where('id',1)->first();

            if ($_self === null) {
                return $this->outmsg('发生错误！请重新登录');
            }

            if (!empty($username) && $_self != null && !empty($_self)) {

                $user = Users::getByAccountNumber($username);
                if ($user != null) {

                    $agent = Agent::getUserByUsername($username);
                    if ($agent === null) {
                        $agent_max_level = Setting::getValueByKey('agent_max_level', 4);
                        if (($_self->level == $agent_max_level && $_self->is_admin == 0)) {
                            return $this->error("您是{$agent_max_level}级代理商，不能添加下级代理商");
                        } else if (($_self->is_addson == 0)) {
                             return $this->error('您尚未拥有添加下级代理商的权限');
                        } else if (($_self->is_lock == 1)) {
                             return $this->error('您的代理商帐号被锁定');
                        } else {
                            $returnData = [];
                            $returnData['user_id'] = $user->id;
                            $returnData['username'] = $user->account_number;
                            $returnData['son_level'] = 0;

                            if ($_self->level == 0 && $_self->is_admin == 1) {
                                $returnData['son_level'] = 1;
                            } else {
                                $returnData['son_level'] = $_self->level + 1;
                            }

                            $returnData['max_pro_loss'] = $_self->pro_loss;
                            $returnData['max_pro_ser'] = $_self->pro_ser;

                            return $this->success($returnData);
                        }
                    } else {
                         return $this->error('该用户已经是代理商');
                    }
                } else {
                    return $this->error('该用户不存在');
                }
            } else {
                return $this->error('该用户不存在');
            }
        } else {
            return $this->error('非法操作！');
        }
    }
    
    //添加代理商页面
    public function salesmenAdd()
    {
        $data = request()->all();
        return view("admin.agent.salesmen_add", ['d' => $data]);
    }

    public function salesmenEdit(Request $request)
    {
        $id = $request->input('id',0);
        $data = Agent::where('id',$id)->first();
        return view("admin.agent.salesmen_add", ['d' => $data]);
    }
    
    /**
     * 添加下级的代理商
     * @param Request $request
     */
    public function addSonAgent(Request $request)
    {

        $_self = Agent::where('id',1)->first();


        $id = $request->input('agent_id', 0); //下级代理商id
        $_son = Agent::getAgentById($id);

        if ($_self === null) {
            return $this->outmsg('发生错误！请重新登录');
        }

        if ($_son->level == $this->agent_max_level) {
            return $this->notice("该用户是{$this->agent_max_level}级代理商，不能添加下级代理商");
        }

        if ($_self->level == $this->agent_max_level) {
            return $this->notice("您是{$this->agent_max_level}级代理商，不能添加下级代理商");
        } else if (($_self->is_addson == 0)) {
            return $this->notice('您尚未拥有添加下级代理商的权限');
        } else if (($_self->is_lock == 1)) {
            return $this->notice('您的代理商帐号被锁定');
        }

        //判断下级
        $username = $request->input('username', 0);
        $user_id = $request->input('user_id', 0);

        $id = $request->input('id', 0);
        if (DB::table('users')->where('id', $user_id)->first() === null) {
            return $this->error("该用户不存在！请重新核对用户信息");
        }
        $ag = Agent::getUserByUsername($username);
        if ($ag !== null && $id == 0) {
            return $this->error("该用户已经是代理商！");
        }


        $rules = [
            'pro_loss' => 'required|numeric|min:0.01|max:' . $_son->pro_loss,   //验证下级代理商的头寸比例是否正确
            'pro_ser' => 'required|numeric|min:0.01|max:' . $_son->pro_ser, // //验证下级代理商的手续费比例是否正确
            'is_lock' => 'required|in:1,0',
            'is_addson' => 'required|in:1,0',
            'user_id' => 'required|integer|min:0',
            'id' => 'required|integer|min:0'
        ];

        $messages = [
            'pro_loss.required' => '头寸比例不能为空',
            'pro_loss.numeric' => '头寸比例只能为数字',
            'pro_loss.min' => '头寸比例最小值为0.01',
            'pro_loss.max' => '头寸比例最大值为' . $_son->pro_loss,
            'pro_ser.required' => '手续费比例不能为空',
            'pro_ser.numeric' => '手续费比例只能为数字',
            'pro_ser.min' => '手续费比例最小值为0.01',
            'pro_ser.max' => '手续费比例最大值为' . $_son->pro_ser,
            'is_lock.required' => '是否锁定不能为空',
            'is_lock.in' => '是否锁定参数错误',
            'is_addson.required' => '是否填新不能为空',
            'is_addson.in' => '是否填新参数错误',
            'user_id.required' => '参数类型错误',
            'user_id.integer' => '参数类型错误',
            'user_id.min' => '非法操作',
            'id.required' => '参数类型错误',
            'id.integer' => '参数类型错误',
            'id.min' => '非法操作'
        ];

        //创建验证器
        $validator = Validator::make($request->all(), $rules, $messages);
        //以上验证通过后 继续验证 .  测试用的～ ：）
        $validator->after(function ($validator) use ($request) {
            $user = Users::getById($request->input('user_id'));
            if (empty($user)) {
                return $validator->errors()->add('isUser', '没有此用户');
            }
        });

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }

        $user = Users::getById($request->input('user_id'));
        //判断添加下级的代理商的等级
        if ($_son->level == 0 && $_son->is_admin == 1) {
            $level = 1;
        } else {
            $level = $_son->level + 1;
        }

        if ($id > 0) {
            $agent = Agent::find($id);
        } else {

            //添加代理商时  用户授权码
            $authorization_code = $request->input('authorization_code', '');

            // if (!Cache::has('authorization_code_' . $user->id)) {
            //     return $this->error('用户授权码已失效,请重新生成');
            // }
            $user_code = Cache::get('authorization_code_' . $user->id);


            // if (!$authorization_code || ($authorization_code != $user_code)) {
            //     return $this->error('用户授权码不正确');
            // }
            $agent = new Agent();
            $agent->reg_time = time();
        }
        $agent->user_id = $user_id;
        $agent->username = $username;
        $agent->password = $user->password;
        $agent->parent_agent_id = $_son->id;  //上级代理商id，有别于user表中的parent_id。  这个id取的是agent产生的id,并不是users表中的id。特别要注意！
        $agent->level = $level;
        $agent->is_admin = 0;
        $agent->is_lock = $request->input('is_lock', 0);
        $agent->is_addson = $request->input('is_addson', 1);
        $agent->pro_loss = $request->input('pro_loss', 0.00);
        $agent->pro_ser = $request->input('pro_ser', 0.00);
        $agent->status = 1;

        try {
            if (!$agent->save()) {
                return $this->error("操作失败！请重试");
            }
            if ($_son->is_admin == 1) {
                $agent->agent_path = $agent->id . ',' . $_son->id;
            } else {
                $agent->agent_path = $agent->id . ',' . $_son->agent_path; //上级代理商id的字符串拼接，这个id取的是agent产生的id,并不是users表中的id。特别要注意！
            }
            if ($agent->save()) {

                //更新该用户的代理商id
                $_users = Users::find($user_id);
                $_users->agent_id = $agent->id;
                $_users->save();


                return $this->success("操作成功");
            } else {
                return $this->error("操作失败！请重试");
            }
        } catch (\Exception $ex) {                  //\Exception 捕获所有异常
            return $this->error($ex->getMessage()); // getMessage() 异常信息
        }
    }
    
    /**
     * 添加 编辑代理商
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addAgent(Request $request)
    {

        $_self = Agent::where('id',1)->first();

        if ($_self === null) {
            return $this->outmsg('发生错误！请重新登录');
        }
        //判断下级
        $username = $request->input('username', 0);
        $user_id = $request->input('user_id', 0);

        $id = $request->input('id', 0); //编辑
        if (DB::table('users')->where('id', $user_id)->first() === null) {
            return $this->error("该用户不存在！请重新核对用户信息");
        }
        $ag = Agent::getUserByUsername($username);
        if ($ag !== null && $id == 0) {
            return $this->error("该用户已经是代理商！");
        }

        //判断自己
        if (($_self->level == $this->agent_max_level && $_self->is_admin == 0 && $id == 0)) {
            return $this->notice("您是{$this->agent_max_level}级代理商，不能添加下级代理商");
        } else if (($_self->is_addson == 0)) {
            return $this->notice('您尚未拥有添加下级代理商的权限');
        } else if (($_self->is_lock == 1)) {
            return $this->notice('您的代理商帐号被锁定');
        }

        $rules = [
            'pro_loss' => 'required|numeric|min:0.00|max:' . $_self->pro_loss,   //验证下级代理商的头寸比例是否正确
            'pro_ser' => 'required|numeric|min:0.00|max:' . $_self->pro_ser, // //验证下级代理商的手续费比例是否正确
            'is_lock' => 'required|in:1,0',
            'is_addson' => 'required|in:1,0',
            'user_id' => 'required|integer|min:0',
            'id' => 'required|integer|min:0'
        ];

        $messages = [
            'pro_loss.required' => '头寸比例不能为空',
            'pro_loss.numeric' => '头寸比例只能为数字',
            'pro_loss.min' => '头寸比例最小值为0.01',
            'pro_loss.max' => '头寸比例最大值为' . $_self->pro_loss,
            'pro_ser.required' => '手续费比例不能为空',
            'pro_ser.numeric' => '手续费比例只能为数字',
            'pro_ser.min' => '手续费比例最小值为0.01',
            'pro_ser.max' => '手续费比例最大值为' . $_self->pro_ser,
            'is_lock.required' => '是否锁定不能为空',
            'is_lock.in' => '是否锁定参数错误',
            'is_addson.required' => '是否填新不能为空',
            'is_addson.in' => '是否填新参数错误',
            'user_id.required' => '参数类型错误',
            'user_id.integer' => '参数类型错误',
            'user_id.min' => '非法操作',
            'id.required' => '参数类型错误',
            'id.integer' => '参数类型错误',
            'id.min' => '非法操作'
        ];

        //创建验证器
        $validator = Validator::make($request->all(), $rules, $messages);
        //以上验证通过后 继续验证 .  测试用的～ ：）
        $validator->after(function ($validator) use ($request) {
            $user = Users::getById($request->input('user_id'));
            if (empty($user)) {
                return $validator->errors()->add('isUser', '没有此用户');
            }
        });

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }

        $user = Users::getById($request->input('user_id'));
        if ($id > 0) {
            $agent = Agent::find($id);
            //编辑下一级代理商密码
            if ($agent->parent_agent_id == $_self->id) {
                $agent_password = $request->input('agent_password', '');
                if ($agent_password) {
                    $agent->password = Users::MakePassword($agent_password);
                }
            }
        } else {

            //添加代理商时  用户授权码
            $authorization_code = $request->input('authorization_code', '');

            // if (!Cache::has('authorization_code_' . $user->id)) {
            //     return $this->error('用户授权码已失效,请重新生成');
            // }
            $user_code = Cache::get('authorization_code_' . $user->id);


            // if (!$authorization_code || ($authorization_code != $user_code)) {
            //     return $this->error('用户授权码不正确');
            // }
            $agent = new Agent();
            $agent->reg_time = time();
            $agent->parent_agent_id = $_self->id;  //上级代理商id，有别于user表中的parent_id。  这个id取的是agent产生的id,并不是users表中的id。特别要注意！

            //判断添加下级的代理商的等级
            if ($_self->level == 0 && $_self->is_admin == 1) {
                $level = 1;
            } else {
                $level = $_self->level + 1;
            }
            $agent->level = $level;
            $agent->password = $user->password;
        }

        $agent->user_id = $user_id;
        $agent->username = $username;
        // $agent->password = $user->password;
        $agent->is_admin = 0;
        $agent->is_lock = $request->input('is_lock', 0);
        $agent->is_addson = $request->input('is_addson', 1);
        $agent->pro_loss = $request->input('pro_loss', 0.00);
        $agent->pro_ser = $request->input('pro_ser', 0.00);
        $agent->status = 1;

        try {
            if (!$agent->save()) {
                return $this->error("操作失败！请重试");
            }
            if ($_self->is_admin == 1) {
                $agent->agent_path = $agent->id . ',' . $_self->id;
            } else {
                $agent->agent_path = $agent->id . ',' . $_self->agent_path; //上级代理商id的字符串拼接，这个id取的是agent产生的id,并不是users表中的id。特别要注意！
            }
            if ($agent->save()) {

                //更新该用户的代理商id
                $_users = Users::find($user_id);
                $_users->agent_id = $agent->id;
                $_users->save();


                return $this->success("操作成功");
            } else {
                return $this->error("操作失败！请重试");
            }
        } catch (\Exception $ex) {                  //\Exception 捕获所有异常
            return $this->error($ex->getMessage()); // getMessage() 异常信息
        }
    }

    public function updateAgent(Request $request)
    {
        //判断下级
        $agentid = $request->input('agentid', 0);
        $_h = Agent::getAgentById($agentid);
        if ($_h == null || $_h->id <= 0) {
            return $this->error("该用户不存在！请重新核对用户信息");
        }

        $rules = [
            'agentid' => 'required|numeric|min:1|max:999999999',   //id必须是数字
            'name' => 'required|in:is_lock,is_addson', //必须是指定的字段
            'value' => 'required|in:1,0'   //必须是指定的值
        ];

        $messages = [
            'agentid.required' => '用户id不能为空',
            'agentid.numeric' => '用户id只能为数字',
            'agentid.min' => '用户id最小值为1',
            'agentid.max' => '用户id最大值为999999999',
            'name.required' => '修改属性不能为空',
            'name.in' => '修改属性参数错误',
            'value.required' => '修改属性值不能为空',
            'value.in' => '修改属性值参数错误'
        ];

        //创建验证器
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }

        $agent = new Agent();
        $name = $request->input('name', 0);
        $value = $request->input('value', 0);

        if ($name == 'is_lock' && $value == 1) {
            $lock = time();
        } else {
            $lock = 0;
        }

        $res = $agent->where('id', $agentid)->update([$name => $value, 'lock_time' => $lock]);

        if ($res) {
            return $this->success('更新成功');
        } else {
            return $this->error('更新失败，请重新尝试');
        }
    }
}
