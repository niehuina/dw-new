<?php

namespace app\admin\controller;

use app\admin\model\PublicityCourt;
use app\admin\common\Constant;

class PublicityCourtController extends BaseController
{
    public function index()
    {
        return view();
    }

    public function get_list()
    {
        $start = $this->request->param('start');
        $length = $this->request->param('length');
        $map = $this->process_query('pc.name');

        $order = 'court_time desc';
        $recordCount = db('publicity_court pc')->where($map)->count();
        $records = db('publicity_court pc')
            ->where($map)
            ->field('pc.id,pc.name,pc.court_time,pc.court_where,
                    pc.web_user_ids, pc.procedure,pc.is_open')
            ->limit($start, $length)->order($order)->select();

        foreach ($records as $key => $item) {
            $list = db('web_user')->where(['deleted' => 0, 'id' => ['in', $item['web_user_ids']]])->field('name')->select();
            $records[$key]['web_user_name'] = implode('、', array_column($list, 'name'));
            $records[$key]['procedure'] = get_value($item['procedure'], Constant::PROCEDURE_TYPE);
            $records[$key]['is_open'] = get_value($item['is_open'], Constant::YES_NO);
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
            $model = PublicityCourt::get($id);
            $edit_state = true;
        }

        $web_user_list = db('web_user wu')
            ->join('setting st', 'st.id=wu.user_type and st.type="user_type" and st.code="prosecutor"')
            ->where(['deleted' => 0])
            ->field('wu.name,wu.id')
            ->order('CONVERT(wu.name USING gbk) asc')
            ->select();

        $this->assign('model', $model);
        $this->assign('edit_state', $edit_state);
        $this->assign('web_user_list', $web_user_list);
        $this->assign('procedure_type', Constant::PROCEDURE_TYPE);
        $this->assign('is_open_list', Constant::YES_NO);
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
        $data['web_user_ids'] = implode(',', $data['web_user_ids']);
        if (empty($data['id'])) {
            $model = new PublicityCourt ();
        } else {
            $model = PublicityCourt::get($data['id']);
            if (empty($model)) {
                return json(array(
                    'status' => 0,
                    'message' => '记录不存在'
                ));
            }
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
        $model = PublicityCourt::get($id);
        if (empty($model)) {
            return json(array(
                'status' => 0,
                'message' => '记录不存在'
            ));
        }
        $model->delete();
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
        $list = db('publicity_court')->where($where)->count();
        if ($list > 0) {
            return true;
        }
        return false;
    }
}
