<?php

namespace app\admin\controller;

use app\admin\common\Cache;
use app\admin\common\Tool;
use app\admin\model\MemberDueHistory;
use app\admin\common\Constant;

class MemberDueHistoryController extends BaseController
{
    public function index()
    {
        $month_list = $this->getMonthList();
        $this->assign('month_list', $month_list);
        return view();
    }

    public function get_list()
    {
        $start = $this->request->param('start');
        $length = $this->request->param('length');
        $map = $this->process_query('wu.name');
        $map['member_due_history.deleted'] = '0';
        $map['member_due_history.year'] = date('Y');

        $search_month=$this->request->param('search_month');
        if($search_month){
            $map['member_due_history.month'] = $search_month;
        }

        $order = 'CONVERT(wu.name USING gbk) asc';
        $recordCount = db('member_due_history')
            ->join('web_user wu', 'wu.id=member_due_history.web_user_id and wu.deleted =0', 'left')
            ->where($map)->count();
        $records = db('member_due_history')
            ->join('web_user wu', 'wu.id=member_due_history.web_user_id and wu.deleted =0', 'left')
            ->where($map)
            ->field('member_due_history.id,member_due_history.web_user_id,member_due_history.year,
                         member_due_history.month,member_due_history.money,member_due_history.created_time,member_due_history.updated_time,
                         wu.name as web_user_name')
            ->limit($start, $length)->order($order)->select();

        foreach ($records as $key => $item) {
            //$records[$key]['type'] = get_value($item['type'], Constant::TYPE_LIST);
        }

        return json(array(
            'draw' => $this->request->param('draw'),
            'recordsTotal' => $recordCount,
            'recordsFiltered' => $recordCount,
            'data' => $records
        ));
    }

    //获取已缴党费月份
    public function get_paid_month($web_user_id, $year)
    {
        $where['deleted'] = 0;
        $where['web_user_id'] = $web_user_id;
        $where['year'] = $year;
        $list = db('member_due_history')->where($where)->field('month')->select();

        return array_column($list, 'month');
    }

    //获取应缴金额
    public function get_should_pay_money($web_user_id, $year)
    {

        $where['deleted'] = 0;
        $where['web_user_id'] = $web_user_id;
        $where['year'] = $year;
        $should_pay_money = db('member_should_pay')->where($where)->field('money')->find();

        $should_pay = -1;
        if ($should_pay_money && isset($should_pay_money['money']) && !empty($should_pay_money['money'])) {
            $should_pay = $should_pay_money['money'];
        }

        return $should_pay;
    }


    public function getDueInfo($web_user_id)
    {
        $current_year = date('Y');
        $paid_month_list = $this->get_paid_month($web_user_id, $current_year);
        $month_list = array();
        for ($i = 1; $i <= 12; $i++) {
            if (in_array($i, $paid_month_list)) {
                array_push($month_list,sprintf("%02d", $i));
                //$month_list['month'] = sprintf("%02d", $i);
            }
        }

        $should_pay_money = $this->get_should_pay_money($web_user_id, $current_year);

        $dur_info = array();
        $dur_info['paid_month'] = $month_list;
        $dur_info['should_pay'] = $should_pay_money;

        return json(array(
            'status' => 1,
            'result' => $dur_info
        ));
    }

    public function _item_maintain()
    {
        $current_year = date('Y');
        $id = $this->request->param('id');
        $model = null;
        $edit_state = false;
        if (!empty($id)) {
            $model = MemberDueHistory::get($id);
            $web_user_list = Cache::key_value('web_user');
            $model['web_user_name'] = get_value($model['web_user_id'], $web_user_list);
            $edit_state = true;
        }

        $web_user_list = array();
        if (!$edit_state) {
            $order = 'CONVERT(wu.name USING gbk) asc';
            $map['pm.deleted'] = '0';

            $list = db('party_member pm')
                ->join('web_user wu', 'wu.id=pm.web_user_id and wu.deleted =0', 'left')
                ->where($map)
                ->field('wu.id,wu.name')
                ->order($order)->select();
            foreach ($list as $item) {
                $web_user_list[$item['id']] = $item['name'];
            }

            $model['year'] = $current_year;
        }

        $month_list = $this->getMonthList();

        $this->assign('model', $model);
        $this->assign('edit_state', $edit_state);
        $this->assign('web_user_list', $web_user_list);
        $this->assign('month_list', $month_list);
        return view();
    }

    private function getMonthList(){
        $month_list = array();
        for ($i = 1; $i <= 12; $i++) {
            $month_list[$i] = sprintf("%02d", $i);
        }

        return $month_list;
    }

    public function save_pay_cost()
    {
        $data = input('post.');
        if ($this->is_exist($data['id'], $data['id'])) {
            return json(array(
                'status' => 0,
                'message' => '记录重复'
            ));
        }

        $months=$data['months'];
        $sum_money = $data['money'];
        unset($data['months']);

        if(!is_numeric($sum_money) && empty($sum_money)){
            return json(array(
                'status' => 0,
                'message' => '缴纳金额错误'
            ));
        }

        foreach ($months as $m)
        {
            $model = new MemberDueHistory();
            $data['money']=$sum_money / count($months);
            $data['month']=$m;
            $data['deleted'] = 0;
            $data['created_user_id'] = $this->userId;
            $data['created_time'] = date('Y-m-d H:i:s');

            $model->data($data);
            $model->save();
        }

        return json(array(
            'status' => 1,
            'message' => '保存成功'
        ));
    }

    public function delete()
    {
        $id = $this->request->param('id');
        $model = MemberDueHistory::get($id);
        if (empty($model)) {
            return json(array(
                'status' => 0,
                'message' => '记录不存在'
            ));
        }
        $model->deleted = 1;
        $model->deleted_user_id = $this->userId;
        $model->deleted_time = date('Y-m-d H:i:s');
        $model->save();
        return json(array(
            'status' => 1,
            'message' => '删除成功'
        ));
    }

    private function is_exist($key, $id = '')
    {
        return false;
        $where['key'] = $key;
        $where['deleted'] = 0;
        if (!empty($id)) {
            $where['id'] = array('<>', $id);
        }
        $list = db('member_due_history')->where($where)->count();
        if ($list > 0) {
            return true;
        }
        return false;
    }
}
