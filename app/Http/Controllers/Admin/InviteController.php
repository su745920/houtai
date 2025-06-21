<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\{AccountLog, Users, Setting};

class InviteController extends Controller
{
    //邀请返佣
    public function return()
    {
        $authorityList=session()->get("authorityList");
        return view("admin.invite.return",['authorityList' => $authorityList]);
    }

    public function returnList(Request $request)
    {
        $limit = $request->input('limit', 10);
        $user = $request->input('account', '');
        $code = $request->input('code','');

        $users = Users::where("id", '>', 0);
        $total = 0;
        if(!empty($code)){
            $user_info = Users::where("extension_code", $code)->first();
            $items = [];
            if (!empty($user_info)) {
                $users = $users->where('parent_id', $user_info->id);
            }
        }
        else if(!empty($user)){
            $user_info = Users::where("phone", 'like', '%' . $user . '%')->orwhere('email', 'like', '%' . $user . '%')->first();
            $items = [];
            if (!empty($user_info)) {
                $users = $users->where('parent_id', $user_info->id);
            }
        }
        $list = $users->orderBy('id', 'desc')->paginate($limit);
        foreach ($list as $vo){
            $country_code=$vo->country_code;
            if (empty($country_code)||"undefined"==$country_code){
                $vo->country_code="";
            }
        }
        $items = $list->items();
        $total = $list->total();
        return response()->json(['code' => 0, 'data' => $items, 'count' => $total]);
    }

    public function del(Request $request)
    {
        $id = $request->input('id');
        $accountlog = AccountLog::find($id);
        if (empty($accountlog)) {
            $this->error("记录未找到");
        }

        try {
            $accountlog->delete();
            return $this->success('删除成功');
        } catch (\Exception $ex) {
            return $this->error($ex->getMessage());
        }
    }
    
    //会员推荐关系图
    public function childs()
    {
        return view("admin.invite.childs");
    }


    public function getTree()
    {
        $data = Users::orderBy('id', 'asc')->get()->toArray();
        $list = $this->getSubTree($data);
        return response()->json(['code' => 0, 'data' => $list]);
    }

    public function getSubTree($data, $id = 0, $level = 0)
    {
        $list = array();
        foreach ($data as $key => $value) {
            $val = [];
            $val['parent_id'] = strval($value['parent_id']);
            if ($val['parent_id'] == $id) {
                $val['id'] = $value['id'];
                $val['name'] = $value['account'];
                $val['level'] = $level;
                $val['children'] = self::getSubTree($data, $value['id'], $level + 1);
                $list[]     = $val;
            }
        }

        return $list;
    }

    //邀请背景图
    public function bgIndex()
    {
        return view("admin.invite.bgIndex");
    }

    public function bgList(Request $request)
    {
        $limit = $request->input('limit', 10);

        $list = new InviteBg();

        $list = $list->orderBy('id', 'desc')->paginate($limit);
        return response()->json(['code' => 0, 'data' => $list->items(), 'count' => $list->total()]);
    }

    public function bgdel(Request $request)
    {
        $id = $request->input('id');
        $bg = InviteBg::find($id);
        if (empty($bg)) {
            $this->error("图片未找到");
        }

        try {
            $bg->delete();
            return $this->success('删除成功');
        } catch (\Exception $ex) {
            return $this->error($ex->getMessage());
        }
    }

    public function edit(Request $request)
    {

        $id = $request->input('id', 0);
        if (empty($id)) {
            $bg = new InviteBg();
            $bg->create_time = time();
        } else {
            $bg = InviteBg::find($id);
        }

        return view('admin.invite.edit', ['res' => $bg]);
    }

    public function doedit()
    {
        $id = request()->input("id");
        $pic = request()->input("pic");
        if (empty($pic)) {
            return  $this->error('图片必须上传');
        }
        if (empty($id)) {
            $bg = new InviteBg();
            $bg->create_time = time();
        } else {
            $bg = InviteBg::find($id);
        }
        $bg->pic = $pic;
        try {
            $bg->save();
            return $this->success('操作成功');
        } catch (\Exception $ex) {
            return $this->error($ex->getMessage());
        }
    }

    public function share()
    {
        $share_title = Setting::getValueByKey('share_title', '');
        $share_content = Setting::getValueByKey('share_content', '');
        $share_url = Setting::getValueByKey('share_url', '');

        return view('admin.invite.share', ['title' => $share_title, 'content' => $share_content, 'url' => $share_url]);
    }

    public function postShare(Request $request)
    {
        $title = request()->input("share_title");
        $content = request()->input("share_content");
        $url = request()->input("share_url");

        if (empty($title) || empty($content) || empty($url)) {
            return $this->error('请填写完整信息');
        }
        $data = $request->all();

        try {

            foreach ($data as $key => $value) {
                Setting::updateValueByKey($key, $value);
            }
            return $this->success('操作成功');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }

    public function changeParant(Request $request)
    {
        $account = $request->input('account','');
        $parent_account = $request->input('parent_account','');
        $check_all = $request->input('check_all','');
        $check_id_list = $request->input('check_id_list',[]);
        $user_info = Users::where("phone", 'like', '%' . $parent_account . '%')->orwhere('email', 'like', '%' . $parent_account . '%')->first();
       
        if (empty($user_info)) {
            return $this->error("变更的上级用户未找到");
        }
        if($check_all == 'on'){
            $old_user_info = Users::where("phone", 'like', '%' . $account . '%')->orwhere('email', 'like', '%' . $account . '%')->first(); 
            if (empty($old_user_info)) {
                return $this->error("原上级用户未找到");
            }
            else{
                $check_id_list = [];
                $list = Users::where('parent_id', $old_user_info->id)->get();
                foreach ($list as $vo){
                    $check_id_list[] = $vo['id'];
                }
            }
        }
        if (empty($check_id_list)) {
            return $this->error("输入的用户未找到下级用户");
        }
        try {
            $affect_rows = Users::whereIn('id', $check_id_list)->update([
                'parent_id' => $user_info->id,
            ]);
            return $this->success('变更成功:' . count($check_id_list) . '条,设置成功:' . $affect_rows . '条');
        } catch (\Exception $ex) {
            return $this->error($ex->getMessage());
        }
    }
}
