<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use App\Models\PrizeModel;
use DB;


class AdminPrizeController extends Controller
{
    use ValidatesRequests;


    public static function newsList($cId = 0, $keyword = '', $lang = '', $num = 0)
    {
        $keyword = '%' . $keyword . '%';
        $news_query = PrizeModel::where(function ($query) use ($cId, $keyword, $lang) {
            !empty($keyword) && $query->where('title', 'like', $keyword);

        })->orderBy('id', 'desc');
        $news = $num != 0 ? $news_query->paginate($num) : $news_query->get();
        return $news;
    }


    public static function newsDiscussList($nId = 0, $num = 0)
    {
        if ($nId == 0) {
            return false;
        } else {
            if ($num != 0) {
                $discuss = NewsDiscuss::where('n_id', $nId)->orderBy('sorts', 'asc')->paginate($num);
            } else {
                $discuss = NewsDiscuss::where('n_id', $nId)->orderBy('sorts', 'asc')->get();
            }
        }
        return $discuss;
    }

    public function index(Request $request, $c_id = 0, $keyword = '')
    {
        $c_id = intval($request->input('c_id', '0'));
        $keyword = trim($request->input('keyword', ''));
        $lang = trim($request->input('lang', ''));
        //$cateList = NewsCategory::all();
        $news = self::newsList($c_id, $keyword, $lang, 10);
        $count = count($news);
        $data = [
            'count' => $count,
            'news' => $news->appends([
                'c_id' => $c_id,
                'keyword' => $keyword,
                'lang' => $lang,
            ]),

        ];
        $authorityList = session()->get("authorityList");
        return view('admin.prize.index', [
            'data' => $data,
            'authorityList' => $authorityList
        ]);
    }


    public function add(Request $request)
    {
        return view('admin.prize.add', []);
    }


    public function postAdd(Request $request)
    {
        $news = new PrizeModel();
        $news->title = $request->input('title', '');
        $news->vi_title = $request->input('vi_title', '');
        $news->id_title = $request->input('id_title', "");
        $cover = $request->input('cover', "");

        if (!empty($cover) && strlen($cover) > 10) {
            $news->cover = $cover;
        }

        $news->create_time = date('Y-m-d H:i:s');
        $news->update_time = date('Y-m-d H:i:s');

        $news->vi_title = $request->input('vi_title', '');
        $news->id_title = $request->input('id_title', '');
        $news->th_title = $request->input('th_title', '');
        $news->kor_title = $request->input('kor_title', '');
        $news->en_title = $request->input("en_title", "");
        $news->winning_probability = $request->input('winning_probability', 0);
        $news->prize_type=$request->input('prize_type');
        $news->price=$request->input("price");

        $result = $news->save();
        return $result ? $this->success('添加成功!') : $this->error('添加失败!');
    }

    /**
     * 编辑新闻表单
     *
     * @return
     */

    public function edit(Request $request, $id = 0)
    {
        $news = PrizeModel::find($id);
        $data = [
            'news' => $news,


        ];
        return view('admin.prize.add', $data);
    }

    /**
     * 处理编辑表单数据
     */

    public function postEdit(Request $request, $id = 0)
    {
        $news = PrizeModel::find($id);

        $news->title = $request->input('title');

        $news->vi_title = $request->input('vi_title', '');
        $news->id_title = $request->input('id_title', '');
        $news->th_title = $request->input('th_title', '');
        $news->kor_title = $request->input('kor_title', '');
        $news->winning_probability = $request->input('winning_probability', 0);

        $news->prize_type=$request->input('prize_type');
        $news->price=$request->input("price");


        $cover = $request->input('cover', "");





        if (!empty($cover) && strlen($cover) > 10) {
            $news->cover = $cover;
        } else {
            $news->cover = empty($cover) ? URL("images/zwtp.png") : $cover;
        }

        $result = $news->save();
        return $result ? $this->success('编辑成功！') : $this->error('编辑失败！');
    }

    /**
     * 后台删除新闻
     * @param $id 新闻ID
     * @param $togetherDel 同评论一起删除
     * @return
     */

    public function del(Request $request, $id = 0, $togetherDel = 0)
    {
        //判断新闻对应评论是否一同删除
        $result = false;
        $s = 0;
        if ($togetherDel) {
            DB::beginTransaction();
            NewsDiscuss::where('n_id', $id)->delete() && $s++;
            NewsModel::destroy($id) && $s++;
            $result = $s == 2;
            $result ? DB::commit() : DB::rollBack();
        } else {
            $result = NewsModel::destroy($id);
        }
        return $result ? $this->success('删除成功！') : $this->error('删除失败！');
    }

    /**
     * 后台新闻分类列表
     *
     * @return
     */

    public function cateIndex(Request $request)
    {
        $count = NewsCategory::count();
        $newsCate = self::newsCateList(10);
        $data = ['count' => $count, 'newsCate' => $newsCate];
        return view('admin.news.cateIndex', ['data' => $data]);
    }

    /**
     * AJAX动态获取分类
     * @return array
     */

    public function getCateList()
    {
        $newsCate = self::newsCateList();
        $count = $newsCate->count();
        $data = [
            'count' => $count,
            'cate' => $newsCate
        ];
        return $data;
    }

    /**
     * 添加新闻分类表单
     *
     * @return
     */

    public function cateAdd(Request $request)
    {
        return view('admin.news.cateAdd');
    }

    /**
     * 处理添加分类数据
     *
     * @return
     */
    public function postCateAdd(Request $request)
    {
        $cate = new NewsCategory();
        $this->validate($request, [
            'name' => 'required|min:1|max:64',
        ]);
        $cate->name = $request->input('name', '');
        $cate->sorts = $request->input('sorts', 0);
        $cate->is_show = $request->input('is_show', 1);
        $result = $cate->save();
        return $result ? $this->success('添加成功！') : $this->error('添加失败！');
    }

    /**
     * 编辑新闻分类表单
     *
     * @return
     */

    public function cateEdit(Request $request, $id = 0)
    {
        $cate = NewsCategory::find($id);
        return view('admin.news.cateAdd', $cate);
    }

    /**
     * 处理编辑新闻分类数据
     *
     * @return
     */

    public function PostCateEdit(Request $request, $id = 0)
    {
        $cate = NewsCategory::find($id);
        $this->validate($request, [
            'name' => 'required|min:1|max:64',
        ]);
        $cate->name = $request->input('name', '');
        $cate->sorts = $request->input('sorts', 0);
        $cate->is_show = $request->input('is_show', 1);
        $result = $cate->save();
        return $result ? $this->success('修改成功！') : $this->error('修改失败！');
    }

    /**
     * 后台删除新闻分类
     *
     * @return
     */

    public function cateDel(Request $request, $id = 0)
    {
        //需要先判断所属分类下是否有新闻
        $count = NewsModel::where('c_id', $id)->count();
        if ($count > 0) {
            return $this->error('删除失败，原因：对应分类下有新闻！');
        } else {
            $result = NewsCategory::destroy($id);
            return $result ? $this->success('删除成功！') : $this->error('删除失败！');
        }
    }

    /**
     * 新闻评论列表
     *
     * @return
     */

    public function discussIndex(Request $request, $nId = 0)
    {
        $newsDiscuss = self::newsDiscussList($nId, 20);
        $news = NewsModel::find($nId);
        $data = [
            'news' => $news,
            'newsDiscuss' => $newsDiscuss
        ];
        return view('admin.news.discussIndex', $data);
    }

    /**
     * AJAX获取评论列表
     * @return array
     */

    public function getDisscussList(Request $request, $nId = 0)
    {
        $count = NewsDiscuss::where('n_id', $nId)->count();
        $newsCate = self::newsDiscussList($nId);
        $data = [
            'count' => $count,
            'cate' => $newsCate
        ];
        return $data;
    }

    /**
     * 切换新闻评论显示状态
     *
     * @return
     */

    public function discussShowToggle(Request $request, $id = 0)
    {
        if ($id != 0) {
            $discuss = NewsDiscuss::find($id);
            $discuss->status = 1 - intval($discuss->status);
            $result = $discuss->save();
            return $result ? $this->success('显示状态改变成功！') : $this->error('显示状态改变失败！');
        } else {
            return $this->error('ID传参错误');
        }
    }

    /**
     * 删除新闻评论
     *
     * @return
     */

    public function discussDel(Request $request, $id = 0)
    {
        if ($id != 0) {
            $result = NewsDiscuss::destroy($id);
            return $result ? $this->success('删除成功！') : $this->error('删除失败！');
        } else {
            return $this->error('ID传参错误');
        }
    }
}
