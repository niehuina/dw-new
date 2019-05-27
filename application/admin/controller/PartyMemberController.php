<?php

namespace app\admin\controller;

use app\admin\common\Cache;
use app\admin\common\Tool;
use app\admin\model\MemberDueHistory;
use app\admin\model\MemberShouldPay;
use app\admin\model\Organization;
use app\admin\model\PartyMember;
use app\admin\common\Constant;
use think\Db;

class PartyMemberController extends BaseController
{
    public function index()
    {
        return view();
    }

    public function get_list()
    {
        $current_year=date('Y');
        $start = $this->request->param('start');
        $length = $this->request->param('length');
        $map = $this->process_query('wu.name');
        $map['pm.deleted'] = '0';

        $order = 'CONVERT(org.name USING gbk) asc, CONVERT(wu.name USING gbk) asc';
        $recordCount = db('party_member pm')
            ->join('web_user wu', 'wu.deleted =0 and wu.id=pm.web_user_id','left')->where($map)->count();
        $join_sql = $this->getPartyMemberScore();
        $records = db('party_member pm')
            ->join('web_user wu', 'wu.deleted =0 and wu.id=pm.web_user_id','left')
            ->join('organization org', 'org.deleted=0 and org.id=pm.organ_id','left')
            ->join('(' . $join_sql . ') T ', 'T.web_user_id=pm.web_user_id', 'left')
            ->join('member_should_pay s ', 's.web_user_id=pm.web_user_id and s.year=' . $current_year . ' and s.deleted=0', 'left')
            ->where($map)
            ->field('pm.id,pm.web_user_id,wu.name as web_user_name,pm.organ_id,pm.join_time,pm.score,pm.created_time,pm.updated_time,
                case when year(from_days(datediff(now(), join_time))) >= 1 then year(NOW())-year(join_time) else 0 end as age,
                org.name as organ_name,ifnull(T.total_score,0) as total_score,
                (ifnull(s.money,0) * 12) as year_should_pay')
            ->limit($start, $length)->order($order)->select();

        foreach ($records as $key => $item) {
            $year_paid_money = 0;
            for ($i = 1; $i <= 12; $i++) {
                $money = 0;
                $money_list = db('member_due_history')
                    ->where(['deleted' => 0, 'web_user_id' => $item['web_user_id'], 'year' => $current_year, 'month' => $i])
                    ->field('money')->find();
                if (!empty($money_list['money'])) {
                    $money = $money_list['money'];
                }

                $records[$key]['money' . $i] = $money;
                $year_paid_money += $money;
            }

            $records[$key]['year_paid_money'] = number_format($year_paid_money, 2);
            $records[$key]['year_remaining_pay'] = number_format($item['year_should_pay'] - $year_paid_money,2);
            $records[$key]['age'] = $item['age'].'年';
        }

        return json(array(
            'draw' => $this->request->param('draw'),
            'recordsTotal' => $recordCount,
            'recordsFiltered' => $recordCount,
            'data' => $records
        ));
    }

    public function _item_maintain()
    {
        $id = $this->request->param('id');
        $model = null;
        $edit_state = false;
        if (!empty($id)) {
            $model = PartyMember::get($id);
            $organ = Organization::get($model['organ_id']);
            if ($organ) {
                $model['organ_name'] = $organ['name'];
            } else {
                $model['organ_name'] = '';
            }
            $edit_state = true;
        }

        $where['web_user.deleted']=0;
        if(!$edit_state) {
            $where['pm.id'] = array('EXP', 'IS NULL');
        }
        $list = db('web_user')
            ->distinct(true)
            ->join('party_member pm','pm.web_user_id=web_user.id and pm.deleted=0','left')
            ->field('web_user.id,web_user.name')
            ->where($where)->select();

        $web_user_list=array();
        foreach ($list as $item)
        {
            $web_user_list[$item['id']]=$item['name'];
        }
        $this->assign('model', $model);
        $this->assign('edit_state', $edit_state);
        $this->assign('web_user_list', $web_user_list);
        return view();
    }

    public function save()
    {
        $data = input('post.');
        if ($this->is_exist($data['id'], $data['id'])) {
            return json(array(
                'status' => 0,
                'message' => '记录重复'
            ));
        }
        if (empty($data['id'])) {
            $model = new PartyMember ();
            $data['deleted'] = 0;
            $data['created_user_id'] = $this->userId;
            $data['created_time'] = date('Y-m-d H:i:s');
        } else {
            $model = PartyMember::get($data['id']);
            if (empty($model)) {
                return json(array(
                    'status' => 0,
                    'message' => '记录不存在'
                ));
            }
            $data['updated_time'] = date('Y-m-d H:i:s');
            $data['updated_user_id'] = $this->userId;
        }
        $model->data($data);
        $model->save();
        Cache::clear('web_user');
        return json(array(
            'status' => 1,
            'message' => '保存成功'
        ));
    }

    public function delete()
    {
        $id = $this->request->param('id');
        $model = PartyMember::get($id);
        if (empty($model)) {
            return json(array(
                'status' => 0,
                'message' => '记录不存在'
            ));
        }

        $count = db('score_history')->where(['deleted'=>0,'web_user_id'=>$model['web_user_id']])->count();
        if($count > 0){
            return json(array(
                'status' => 0,
                'message' => '该党员已有积分记录，不能删除'
            ));
        }

        $count1 = db('member_should_pay')->where(['deleted'=>0,'web_user_id'=>$model['web_user_id']])->count();
        if($count1 > 0){
            return json(array(
                'status' => 0,
                'message' => '该党员已有应缴党费记录，不能删除'
            ));
        }

        $count2 = db('member_should_pay')->where(['deleted'=>0,'web_user_id'=>$model['web_user_id']])->count();
        if($count2 > 0){
            return json(array(
                'status' => 0,
                'message' => '该党员已有党费缴纳记录，不能删除'
            ));
        }

        $model->deleted = 1;
        $model->deleted_user_id = $this->userId;
        $model->deleted_time = date('Y-m-d H:i:s');
        $model->save();
        Cache::clear('web_user');
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
        $list = db('party_member')->where($where)->count();
        if ($list > 0) {
            return true;
        }
        return false;
    }

    //获取已缴党费月份
    public function get_paid_month($web_user_id,$year){
        $where['deleted']=0;
        $where['web_user_id']=$web_user_id;
        $where['year']=$year;
        $list = db('member_due_history')->where($where)->field('month')->select();

        return array_column($list,'month');
    }

    //获取应缴金额
    public function get_should_pay_money($web_user_id,$year){

        $where['deleted'] = 0;
        $where['web_user_id'] = $web_user_id;
        $where['year'] = $year;
        $should_pay_money= db('member_should_pay')->where($where)->field('money')->find();

        return $should_pay_money['money'];
    }

    public function _pay_cost()
    {
        $web_user_id = $this->request->param('web_user_id');
        $model = null;
        $edit_state = false;
        $web_user_list = Cache::key_value('web_user');
        $current_year=date('Y');

        $paid_month_list=$this->get_paid_month($web_user_id,$current_year);

        $year_list=Tool::get_year_list();

        $month_list=array();
        for($i=1;$i<=12;$i++){
            if(!in_array($i,$paid_month_list)) {
                $month_list[$i] = sprintf("%02d", $i);
            }
        }

        $should_pay_money=$this->get_should_pay_money($web_user_id,$current_year);

        $model['web_user_id'] = $web_user_id;
        $model['year'] = $current_year;
        $model['month'] = date('m');
        $model['should_pay_money'] = $should_pay_money;
        $model['web_user_name'] = get_value($web_user_id,$web_user_list);

        $this->assign('model', $model);
        $this->assign('edit_state', $edit_state);
        $this->assign('year_list', $year_list);
        $this->assign('month_list', $month_list);
        return view();
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

    public function getPartyMemberScore()
    {
        $status_success = Constant::REVIEW_STATUS_SUCCESS;
        $sql = "SELECT web_user_id,SUM(si.score) AS total_score  
                FROM score_history s
                INNER JOIN score_item si ON s.score_item_id = si.id and si.deleted=0
                WHERE s.deleted=0 and s.review_status = {$status_success} AND year(s.created_time)=year(CURRENT_DATE)
                GROUP BY web_user_id";
        return $sql;
    }

    public function _should_pay_cost()
    {
        $party_member_id = $this->request->param('party_member_id');

        if (empty($party_member_id)) {
            return view();
        }
        $party_member_model = PartyMember::get($party_member_id);
        $where['deleted'] = 0;
        $where['web_user_id'] = $party_member_model->web_user_id;
        $where['year'] = date('Y');
        $list = db('member_should_pay')->where($where)->find();

        if($list){
            $model = MemberShouldPay::get($list['id']);
        }else{
            $model = new MemberShouldPay();
            $model['web_user_id']=$party_member_model->web_user_id;
            $model['year']=date('Y');
            $model['money']=0;
        }

        $web_user_list = Cache::key_value('web_user');
        $model['web_user_name'] = get_value($party_member_model->web_user_id,$web_user_list);
        $this->assign('model', $model);
        return view();
    }

    public function save_should_pay_cost()
    {
        $data = input('post.');

        $where['deleted'] = 0;
        $where['web_user_id'] = $data['web_user_id'];
        $where['year'] = $data['year'];
        $list = db('member_should_pay')->where($where)->find();

        $model = null;
        if($list)
        {
            $model = MemberShouldPay::get($list['id']);
            $data['updated_user_id'] = $this->userId;
            $data['updated_time'] = date('Y-m-d H:i:s');
        }else{
            $model = new MemberShouldPay();
            $data['deleted'] = 0;
            $data['created_user_id'] = $this->userId;
            $data['created_time'] = date('Y-m-d H:i:s');
        }

        $model->data($data);
        $model->save();
        return json(array(
            'status' => 1,
            'message' => '保存成功'
        ));
    }
}
