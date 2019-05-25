<?php

namespace app\admin\controller;

use app\admin\common\Cache;
use app\admin\model\MemberShouldPay;
use app\admin\common\Constant;

class MemberShouldPayController extends BaseController
{
    public function index()
    {
        return view();
    }

    public function get_list()
    {
        $start = $this->request->param('start');
        $length = $this->request->param('length');
        $map = $this->process_query('wu.name');
        $map['member_should_pay.deleted'] = '0';
        $map['member_should_pay.year'] = date('Y');

        $order = 'CONVERT(wu.name USING gbk) asc';
        $recordCount = db('member_should_pay')
            ->join('web_user wu', 'wu.id=member_should_pay.web_user_id and wu.deleted =0', 'left')
            ->where($map)->count();
        $records = db('member_should_pay')->where($map)
            ->join('web_user wu', 'wu.id=member_should_pay.web_user_id and wu.deleted =0', 'left')
            ->field('member_should_pay.id,member_should_pay.web_user_id,member_should_pay.year,member_should_pay.money,
                          member_should_pay.created_time,member_should_pay.updated_time,
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

    public function _item_maintain()
    {
        $id = $this->request->param('id');
        $model = null;
        $edit_state = false;
        if (!empty($id)) {
            $model = MemberShouldPay::get($id);
            $web_user_list = Cache::key_value('web_user');
            $model['web_user_name'] = get_value($model['web_user_id'], $web_user_list);
            $edit_state = true;
        }

        $web_user_list = array();
        if (!$edit_state) {
            $order = 'CONVERT(wu.name USING gbk) asc';
            $map['pm.deleted'] = '0';
            $map['pay.web_user_id'] = ['EXP','IS NULL'];

            $list = db('party_member pm')
                ->join('web_user wu', 'wu.id=pm.web_user_id and wu.deleted =0', 'left')
                ->join('member_should_pay pay', 'wu.id=pay.web_user_id and pay.deleted =0 and pay.year=' . date('Y'), 'left')
                ->where($map)
                ->field('wu.id,wu.name')
                ->order($order)->select();
            foreach ($list as $item) {
                $web_user_list[$item['id']] = $item['name'];
            }

            $model['year'] = date('Y');

            if(count($web_user_list)){
                $model['first_text'] =  '请选择';
            }else{
                $model['first_text'] =  '当前年度应缴党费已设置完毕';
            }
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
            $model = new MemberShouldPay ();
            $data['deleted'] = 0;
            $data['created_user_id'] = $this->userId;
            $data['created_time'] = date('Y-m-d H:i:s');
        } else {
            $model = MemberShouldPay::get($data['id']);
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
        return json(array(
            'status' => 1,
            'message' => '保存成功'
        ));
    }

    public function delete()
    {
        $id = $this->request->param('id');
        $model = MemberShouldPay::get($id);
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
        $list = db('member_should_pay')->where($where)->count();
        if ($list > 0) {
            return true;
        }
        return false;
    }
}
