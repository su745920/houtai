<?php

namespace App\Http\Controllers\Admin;

use App\Models\DzpConfigCount;

use App\Models\DzpConfigModel;
use App\Models\PrizeModel;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;

use DB;


class AdminDzpController extends Controller
{
    public function dzp_config_count(){
        $po=DzpConfigCount::find(1);
        $data = [
            'po' => $po,


        ];
        return view('admin.dzp_config.dzp_config_count', $data);
    }
    public function postDzpConfigCount(Request $request){
        $data=DzpConfigCount::find(1);
        $cz_amount=$request->input("cz_amount");
        $data->cz_amount=$cz_amount;
        $result =$data->save();
        return $result ? $this->success('添加成功!') : $this->error('添加失败!');
    }



    public function index(Request $request, $id = 0)
    {
        $news = DzpConfigModel::find(1);
        $data = [
            'news' => $news,


        ];
        return view('admin.dzp_config.index', $data);
    }

    public function postIndex(Request $request, $id = 0)
    {
        $news = DzpConfigModel::find(1);


        $news->content_zh = $request->input('content_zh', '');
        $news->content_en = $request->input('content_en', '');
        $news->content_vi = $request->input('content_vi', "");


        $news->create_time = date('Y-m-d H:i:s');
        $news->update_time = date('Y-m-d H:i:s');

        $news->content_th = $request->input('content_th', '');
        $news->content_id = $request->input('content_id', '');



        $result = $news->save();
        return $result ? $this->success('添加成功!') : $this->error('添加失败!');



    }

}