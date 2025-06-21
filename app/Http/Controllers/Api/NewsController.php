<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\{News, NewsCategory};
use App\Models\Setting;
use App\Models\Users;
use App;

class NewsController extends Controller
{
    public function get(Request $request)
    {
        $id = $request->input('id', 0);
        if (empty($id)) {
            return $this->error(trans('common.cscw'));
        }
        $news = News::find($id);
        return $this->success($news);
    }

    //帮助中心,新闻分类
    public function getCategory()
    {
        $results = NewsCategory::where('is_show', 1)->orderBy('sorts')->get(['id', 'name'])->toArray();
        return $this->success($results);
    }

    //推荐新闻
    public function recommend()
    {
        $results = News::where('recommend', 1)->orderBy('id', 'desc')->get(['id', 'title', 'c_id'])->toArray();
        return $this->success($results);
    }

    // 获取分类下的文章
    public function getArticle(Request $request)
    {
        $limit = $request->get('limit', 15);
        $page = $request->get('page', 1);
        $category_id = $request->get('c_id');
        $lang = $request->get('lang','en');
        $user_id = Users::getUserId();
        if(!$lang){
            $lang = 'en';
        }
       
        $cache_key_name = "news_cid_{$category_id}_lang_{$lang}_page_{$page}";
        // if (Cache::has($cache_key_name)) {
        //     $article = Cache::get($cache_key_name);
        // } else {
      
            if (empty($category_id)) {
                $article = News::where('lang', $lang)
                    ->where('c_id', 11)
                    ->orderBy('sorts', 'desc')
                    ->orderBy('id', 'desc')
                    ->paginate($limit, ['*'], 'page', $page);
            } else {
                $article_query = News::where('lang', $lang)
                    ->where('c_id', $category_id)
                    ->orderBy('sorts', 'desc')
                    ->orderBy('id', 'desc');
                if($user_id && $category_id == 27) {
                    $article_query = $article_query->where('user_id',$user_id);
                }
                $article = $article_query->paginate($limit, ['*'], 'page', $page);
            }
            // 判断是否存在文章，不存在获取英文版的文章
            if(count($article->items()) == 0) {
                 $article_query = News::where('lang', 'en')
                    ->where('c_id', $category_id)
                    ->orderBy('sorts', 'desc')
                    ->orderBy('id', 'desc');
                if($user_id && $category_id == 27) {
                    $article_query = $article_query->where('user_id',$user_id);
                }
                $article = $article_query->paginate($limit, ['*'], 'page', $page);
            }
            foreach ($article->items() as &$value) {
                //unset($value->content);
                // unset($value->recommend);
                unset($value->display);
                unset($value->discuss);
                unset($value->author);
                unset($value->audit);
                unset($value->browse_grant);
                unset($value->keyword);
                unset($value->abstract);
                unset($value->views);
                //unset($value->create_time);
                unset($value->update_time);
            }
            //Cache::put($cache_key_name, $article, Carbon::now()->addMinutes(15));
        //}
        return $this->success([
            "list" => $article->items(),
            'count' => $article->total(),
            "page" => $page,
            "limit" => $limit
        ]);
    }

    //获取返佣规则新闻
    public function getInviteReturn()
    {

        $c_id = 23;//返佣类型
        $news = News::where('c_id', $c_id)->orderBy('id', 'desc')->first();
        if (empty($news)) {
            return $this->error(trans('common.xwbcz'));
        }
        $data['news'] = $news;
        //相关新闻
        $article = News::where('c_id', $c_id)->where('id', '<>', $news->id)->orderBy('id', 'desc')->get(['id', 'c_id', 'title'])->toArray();

        $data['relation_news'] = $article;
        return $this->success($data);
    }
    
    /*
    客服中心
    */
    public function onlineService(Request $request){
        $url = Setting::getValueByKey('service_url', '');
        $paper_url = Setting::getValueByKey('registered_jump', '');
        
        $data["service_url"] = $url;
        $data["app_download_url"] = $paper_url;
        return $this->success($data);
    }
    
    
    public function popupNews(Request $request)
    {
        $lang = $request->get('lang');
        $results = News::where('c_id',20)->where('lang',$lang)
            ->orderBy('id', 'desc')->get(['id', 'title', 'content'])->first();
        return $this->success($results);
    }
     // vip列表
    public function levelList() {
        $data = DB::table('user_level')->where('id',1)->orderBy("id","desc")->first();
        return $this->success($data);
    }
   
}
