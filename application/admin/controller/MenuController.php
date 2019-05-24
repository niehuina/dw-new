<?php

namespace app\admin\controller;

use app\admin\model\Menu;
use app\admin\common\Constant;

class MenuController extends BaseController
{
    public function index()
    {
        return view();
    }

    public function get_list()
    {
        $start = $this->request->param('start');
        $length = $this->request->param('length');
        $map = $this->process_query('m.name');
        $map['m.deleted'] = '0';

        $order = 'm.id desc';
        $recordCount = db('menu m')
            ->join('menu p_m', 'p_m.id=m.parent_id', 'left')->where($map)->count();
        $records = db('menu m')
            ->join('menu p_m', 'p_m.id=m.parent_id', 'left')
            ->where($map)
            ->field('m.id,m.name,m.parent_id,p_m.name as p_name,m.level,m.preset,m.banner_url,
                    m.content_type,m.content_value,m.display_order,m.created_time,m.updated_time')
            ->limit($start, $length)->order($order)->select();

        foreach ($records as $key => $item) {
            $records[$key]['content_type'] = get_value($item['content_type'], Constant::MENU_TYPE_LIST);
            $records[$key]['preset'] = get_value($item['preset'], Constant::YES_NO);
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
            $model = Menu::get($id);
            $edit_state = true;
        }
        $menu_list = Menu::all(['deleted' => 0]);
        $this->assign('menu_list', $menu_list);
        $this->assign('yn_list', Constant::YES_NO);
        $this->assign('type_list', Constant::MENU_TYPE_LIST);
        $this->assign('model', $model);
        $this->assign('edit_state', $edit_state);
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
            $model = new Menu ();
            $data['deleted'] = 0;
            $data['created_user_id'] = $this->userId;
            $data['created_time'] = date('Y-m-d H:i:s');
        } else {
            $model = Menu::get($data['id']);
            if (empty($model)) {
                return json(array(
                    'status' => 0,
                    'message' => '记录不存在'
                ));
            }
            $data['updated_time'] = date('Y-m-d H:i:s');
            $data['updated_user_id'] = $this->userId;
        }
        $file = $this->request->file('picture');
        if (!empty($file)) {
            $upload_dir = '\public\upload\menu';
            $info = $file->move(ROOT_PATH . $upload_dir);
            if ($info) {
                $data['banner_url'] = $upload_dir . '\\' . $info->getSaveName();
            } else {
                return json(array(
                    'status' => 0,
                    "message" => "上传失败."
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
        $model = Menu::get($id);
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
        $list = db('menu')->where($where)->count();
        if ($list > 0) {
            return true;
        }
        return false;
    }
}
