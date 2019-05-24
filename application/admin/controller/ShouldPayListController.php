<?php

namespace app\admin\controller;

use app\admin\common\Tool;
use app\admin\model\MemberDueHistory;
use app\admin\common\Constant;

class ShouldPayListController extends BaseController
{
    public function index()
    {
        $year_list = Tool::get_year_list();
        $this->assign('year_list', $year_list);
        $this->assign('current_year', date('Y'));
        return view();
    }

    public function get_list()
    {
        $current_year = date('Y');

        $search_year = $this->request->param('search_year');
        if (empty($search_year)) {
            $search_year = $current_year;
        }

        $start = $this->request->param('start');
        $length = $this->request->param('length');
        $map = $this->process_query('u.name');
        $map['party_member.deleted'] = '0';

        $order = 'CONVERT(u.name USING gbk) asc';
        $recordCount = db('party_member')
            ->join('web_user u', 'party_member.web_user_id=u.id and u.deleted=0')
            ->where($map)->count();
        $records = db('party_member')
            ->join('web_user u', 'party_member.web_user_id=u.id and u.deleted=0')
            ->join('member_should_pay s ', 's.web_user_id=party_member.web_user_id and s.year=' . $search_year . ' and s.deleted=0', 'left')
            ->where($map)
            ->field('party_member.web_user_id,u.name as web_user_name ,s.money as month_should_pay,(ifnull(s.money,0) * 12) as year_should_pay')
            ->limit($start, $length)->order($order)->select();

        foreach ($records as $key => $item) {
            $year_paid_money = 0;
            for ($i = 1; $i < 13; $i++) {
                $money = 0;
                $money_list = db('member_due_history')->where(['deleted' => 0, 'web_user_id' => $item['web_user_id'], 'year' => $search_year, 'month' => $i])->field('money')->find();
                if (!empty($money_list['money'])) {
                    $money = $money_list['money'];
                }

                $records[$key]['money' . $i] = number_format($money,2);
                $year_paid_money += $money;
            }

            $records[$key]['year_paid_money'] = number_format($year_paid_money,2);

        }

        return json(array(
            'draw' => $this->request->param('draw'),
            'recordsTotal' => $recordCount,
            'recordsFiltered' => $recordCount,
            'data' => $records
        ));
    }

    public function export_data()
    {
        $current_year = date('Y');

        $search_year = $this->request->param('search_year');
        if (empty($search_year)) {
            $search_year = $current_year;
        }

        $map = $this->process_query('u.name');
        $map['party_member.deleted'] = '0';

        $order = 'party_member.id desc';
        $records = db('party_member')
            ->join('web_user u', 'party_member.web_user_id=u.id and u.deleted=0')
            ->join('member_should_pay s ', 's.web_user_id=party_member.web_user_id and s.year=' . $search_year . ' and s.deleted=0', 'left')
            ->where($map)
            ->field('party_member.web_user_id,u.name as web_user_name ,s.money as month_should_pay,(ifnull(s.money,0) * 12) as year_should_pay')
            ->order($order)->select();

        foreach ($records as $key => $item) {
            $year_paid_money = 0;
            for ($i = 1; $i < 13; $i++) {
                $money = 0;
                $money_list = db('member_due_history')->where(['deleted' => 0, 'web_user_id' => $item['web_user_id'], 'year' => $search_year, 'month' => $i])->field('money')->find();
                if (!empty($money_list['money'])) {
                    $money = $money_list['money'];
                }

                $records[$key]['money' . $i] = number_format($money, 2);
                $year_paid_money += $money;
            }

            $records[$key]['year_paid_money'] = number_format($year_paid_money,2);

        }

        $tit = array(
            "姓名" => "string",
            "年应交" => "price",
            "年已交" => "price",
            "每月应交" => "price",
            "1月已交" => "price",
            "2月已交" => "price",
            "3月已交" => "price",
            "4月已交" => "price",
            "5月已交" => "price",
            "6月已交" => "price",
            "7月已交" => "price",
            "8月已交" => "price",
            "9月已交" => "price",
            "10月已交" => "price",
            "11月已交" => "price",
            "12月已交" => "price"
        );
        $key = array(
            "web_user_name",
            "year_should_pay",
            "year_paid_money",
            "month_should_pay",
            "money1",
            "money2",
            "money3",
            "money4",
            "money5",
            "money6",
            "money7",
            "money8",
            "money9",
            "money10",
            "money11",
            "money12"
        );

        Tool::export("党费缴纳统计", $tit, $key, $records);
    }

}
