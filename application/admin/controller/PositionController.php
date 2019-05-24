<?php

namespace app\admin\controller;

use app\admin\model\Position;
use app\admin\common\Constant;
use app\admin\model\WebUser;
use think\Db;

class PositionController extends BaseController
{
    public function index()
    {
        return view();
    }

    public function get_list()
    {
        $start = $this->request->param('start');
        $length = $this->request->param('length');
        $map = $this->process_query('position.name|position.number');
        $map['position.deleted'] = '0';

        $order = 'position.number asc';
        $recordCount = db('position')->where($map)->count();
        $records = db('position')->where($map)
            ->field('position.id,position.number,position.name,position.remark')
            ->limit($start, $length)->order($order)->select();

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
            $model = Position::get($id);
            $edit_state = true;
        }
        $this->assign('model', $model);
        $this->assign('edit_state', $edit_state);
        return view();
    }

    public function save()
    {
        $data = input('post.');
        if ($this->is_exist('number', $data['number'], $data['id'])) {
            return json(array(
                'status' => 0,
                'message' => '职务编号已存在'
            ));
        }
        if ($this->is_exist('name', $data['name'], $data['id'])) {
            return json(array(
                'status' => 0,
                'message' => '职务名称已存在'
            ));
        }
        if (empty($data['id'])) {
            $model = new Position ();
            $data['deleted'] = 0;
            $data['created_user_id'] = $this->userId;
            $data['created_time'] = date('Y-m-d H:i:s');
        } else {
            $model = Position::get($data['id']);
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
        $model = Position::get($id);
        if (empty($model)) {
            return json(array(
                'status' => 0,
                'message' => '记录不存在'
            ));
        }

        $w_user = Db::query("select * from web_user where deleted = 0 and find_in_set({$id}, position_ids)");
        if(count($w_user) > 0){
            return json(array(
                'status' => 0,
                'message' => '该职务下有关联的前台用户，不能删除'
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

    private function is_exist($key, $value, $id = '')
    {
        $where[$key] = $value;
        $where['deleted'] = 0;
        if (!empty($id)) {
            $where['id'] = array('<>', $id);
        }
        $list = db('position')->where($where)->count();
        if ($list > 0) {
            return true;
        }
        return false;
    }
}
