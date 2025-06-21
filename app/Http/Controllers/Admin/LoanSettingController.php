<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use App\Models\LoanSetting;
use App\Models\LoanOrder;
use DB;
use Validator;

class LoanSettingController extends Controller
{
    use ValidatesRequests;

 public function index()
    {
        return view("admin.loan.loan_settings");
    }

    public function lists()
    {
        $limit = request()->input('limit', 10);
        $result = new LoanSetting();
        $list = $result->orderBy('id', 'desc')->paginate($limit);
        return response()->json(['code' => 0, 'data' => $list->items(), 'count' => $list->total()]);
    }


    /**
     * AJAX动态获取分类
     * @return array
     */
    
    public function getLoanSettingList()
    {
        $loanSetting = self::loanSettingList();
        $count = $loanSetting->count();        
        $data = [
            'count' => $count,
            'loanSetting' => $loanSetting
        ];
        return $data;
    }

    /**
    * 添加贷款设置表单
    *
    * @return 
    */

    public function loanSettingAdd(Request $request)
    {
        return view('admin.loan.loan_settings_add');
    }

    /**
    * 处理添加贷款设置数据
    *
    * @return 
    */
    public function postLoanSettingAdd(Request $request)
    {
        $loanSetting = new LoanSetting();
        $this->validate($request, [
            'loan_days' => 'required|min:1|max:365',
            'loan_rate' => 'required|min:0.001|max:100',
            'loan_institution' => 'required|min:1|max:30',
        ]);
        $loanSetting->loan_institution = $request->input('loan_institution', '');
        $loanSetting->loan_days = $request->input('loan_days', 0);
        $loanSetting->loan_rate = $request->input('loan_rate', 0);
        $loanSetting->sorts = $request->input('sorts', 0);
        $loanSetting->status = $request->input('status', 1);
        $result = $loanSetting->save();
        return $result ? $this->success('添加成功！') : $this->error('添加失败！');
    }

    /**
    * 编辑贷款设置表单
    *
    * @return 
    */

    public function loanSettingEdit(Request $request, $id = 0)
    {
        $loanSetting = LoanSetting::find($id);
        return view('admin.loan.loan_settings_add', $loanSetting);
    }

    /**
    * 处理编辑贷款设置数据
    *
    * @return 
    */

    public function PostLoanSettingEdit(Request $request, $id = 0)
    {
        $loanSetting = LoanSetting::find($id);
        $this->validate($request, [
            'loan_days' => 'required|min:1|max:365',
            'loan_rate' => 'required|min:0.001|max:100',
            'loan_institution' => 'required|min:1|max:30',
        ]);
        $loanSetting->loan_institution = $request->input('loan_institution', '');
        $loanSetting->loan_days = $request->input('loan_days', 0);
        $loanSetting->loan_rate = $request->input('loan_rate', 0);
        $loanSetting->sorts = $request->input('sorts', 0);
        $loanSetting->status = $request->input('status', 1);
        $result = $loanSetting->save();
        return $result ? $this->success('修改成功！') : $this->error('修改失败！');
    }

    /**
    * 后台删除贷款设置
    *
    * @return 
    */

    public function loanSettingDel(Request $request, $id = 0)
    {
        //需要先判断所属贷款设置下是否有订单
        $count = LoanOrder::where('loan_setting_id',  $id)->count();
        if($count > 0) {
            return $this->error('删除失败，原因：贷款设置下有贷款订单！');
        } else {
            $result = LoanSetting::destroy($id);
            return $result ? $this->success('删除成功！') : $this->error('删除失败！');
        }
    }
    
     /**
    * 是否显示
    *
    * @return 
    */

    public function showStatus(Request $request)
    {
        $id = $request->input('id',0);
        $loanSetting = LoanSetting::find($id);
        $loanSetting->status = $loanSetting->status == 1 ? 0 : 1;
        $result = $loanSetting->save();
       return $result ? $this->success('操作成功！') : $this->error('操作失败！');
    }
    
    
}
