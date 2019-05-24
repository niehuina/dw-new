<?php

namespace app\admin\controller;

use app\admin\model\Link;
use app\admin\common\Constant;

class LinkController extends BaseController
{
    public function index()
    {
        return view();
    }

    public function get_list()
    {
        $start = $this->request->param('start');
        $length = $this->request->param('length');
        $map = $this->process_query('id');

        $order = 'display_order desc';
        $recordCount = db('link')->where($map)->count();
        $records = db('link')->where($map)
            ->field('link.id,link.name,link.redirect_url,link.display_order,link.created_time')
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
            $model = Link::get($id);
            $edit_state = true;
        }
        $this->assign('model', $model);
        $this->assign('edit_state', $edit_state);
        return view();
    }

    public function save()
    {
        $data = input('post.');
        if ($this->is_exist($data['name'], $data['id'])) {
            return json(array(
                'status' => 0,
                'message' => '网站名称已存在'
            ));
        }
        if (empty($data['id'])) {
            $model = new Link ();
            $data['created_user_id'] = $this->userId;
            $data['created_time'] = date('Y-m-d H:i:s');
        } else {
            $model = Link::get($data['id']);
            if (empty($model)) {
                return json(array(
                    'status' => 0,
                    'message' => '记录不存在'
                ));
            }
            $data['updated_time'] = date('Y-m-d H:i:s');
            $data['updated_user_id'] = $this->userId;
        }

        $file = $this->request->file('file');
        if (!empty($file)) {
            $upload_dir = '\public\upload\img';
            $info = $file->move(ROOT_PATH . $upload_dir);
            if ($info) {
                $file_name = $upload_dir . '\\' . $info->getSaveName();
                $data['picture_url'] = $file_name;
            } else {
                return api_error('上传失败');
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
        $model = Link::get($id);
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
        $where['name'] = $key;
        if (!empty($id)) {
            $where['id'] = array('<>', $id);
        }
        $list = db('link')->where($where)->count();
        if ($list > 0) {
            return true;
        }
        return false;
    }
}
