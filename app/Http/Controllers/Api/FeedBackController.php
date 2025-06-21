<?php

/**
 * Created by Vscode
 * User: LDH
 * 投诉建议
 *  */

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\FeedBack;
use App\Models\Users;
use App;

class FeedBackController extends Controller
{
    //反馈信息列表
    public function myFeedBackList(Request $request)
    {
        $limit = request()->input('limit', 10);
        $page = request()->input('page', 1);
        $user_id = Users::getUserId();
        $feedBackList = FeedBack::where('user_id', $user_id)
            ->orderBy('id', 'desc')
            ->paginate($limit, ['*'], 'page', $page);
        foreach ($feedBackList->items() as &$value) {
            unset($value->replay_content);
        }
        return $this->success(array(
            "list" => $feedBackList->items(), 'count' => $feedBackList->total(),
            "page" => $page, "limit" => $limit
        ));
    }
    //反馈信息内容，包括回复信息
    public function feedBackDetail()
    {
        $id = request()->input('id', 10);
        $feedBack = FeedBack::find($id);
        return $this->success($feedBack);
    }
    //提交反馈信息
    public function feedBackAdd()
    {
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $user_id = Users::getUserId();
        $content = request()->input('content', '');
        if (empty($content)) {
            return $this->error(trans('common.nrbnwk'));
        }
        $img = request()->input('img', '');
        try {
            $feedBack = new FeedBack();
            $feedBack->user_id = $user_id;
            $feedBack->content = $content;
            $feedBack->is_reply = 0;
            $feedBack->img = $img;
            $feedBack->create_time = time();
            $feedBack->save();
            return $this->success(trans('common.tjcgwmhjkgnhf'));
        } catch (\Exception $ex) {
            return $this->error($ex->getMessage());
        }
    }
}
