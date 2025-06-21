<?php

namespace App\Http\Controllers\Admin;

use App\Models\RebateRules;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use App\Models\News as NewsModel;
use App\Models\NewsCategory;
use DB;
use Validator;

class RebateRulesController extends Controller
{

    public function postEdit(Request $request)
    {
        $id=$request->input("id");
        $news = RebateRules::find($id);
        $cateList = NewsCategory::all();
//        $this->validate($request, [
//            'title' => 'required|min:1|max:64',
//            'lang' => 'required',
//        ]);
        $news->title = $request->input('title');
        $news->cat = $request->input('cat');


        $news->lang = $request->input('lang', 'zh');

        $result = $news->save();
        return $result ? $this->success('编辑成功！') : $this->error('编辑失败！');
    }


    public function edit(Request $request, $id = 0)
    {
        $news = RebateRules::find($id);
        $cateList = NewsCategory::all();
        $lang_list = array_keys(RebateRules::getLangeList());
        $data = [
            'news' => $news,

            'langList' => $lang_list,
        ];
        return view('admin.rebate_rules.add', $data);
    }

    public function index(Request $request, $c_id = 0, $keyword = '')
    {
        $c_id = intval($request->input('c_id', '0'));
        $keyword = trim($request->input('keyword', ''));
        $lang = trim($request->input('lang', ''));
        //$cateList = NewsCategory::all();
        $cateList= NewsCategory::where('is_show', 1)->orderBy('sorts', 'asc')->get();
        $news = self::newsList($c_id, $keyword, $lang, 10);
        $lang_list = RebateRules::getLangeList();
        $count = count($news);
        $data = [
            'count'=> $count,
            'news' => $news->appends([
                'keyword' => $keyword,
                'lang' => $lang,
            ])
        ];


        $authorityList=session()->get("authorityList");
        return view('admin.rebate_rules.index', [
            'data'=> $data,
            'lang_list' => $lang_list,
            'authorityList' => $authorityList
        ]);
    }

    public static function newsList($cId = 0, $keyword = '', $lang = '', $num = 0)
    {
        $keyword = '%' . $keyword . '%';
        $news_query = RebateRules::where(function ($query) use ($cId, $keyword, $lang) {
            !empty($keyword) && $query->where('title', 'like', $keyword);
            !empty($lang) && $query->where('lang', $lang);
        })->orderBy('id', 'asc');
        $news = $num != 0 ? $news_query->paginate($num) : $news_query->get();
        return $news;
    }

}