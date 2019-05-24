<?php

namespace app\admin\controller;

use app\admin\model\PublishType;
use app\admin\common\Constant;

class PublishTypeController extends BaseController
{
   public function index()
   {
       return view();
   }

   public function get_list()
   {
       $start = $this->request->param('start');
       $length = $this->request->param('length');
       $map = $this->process_query('publish_type.name');
       $map['publish_type.deleted'] = '0';

       $order = 'display asc';
       $recordCount = db('publish_type')->where($map)->count();
       $records = db('publish_type')->where($map)
           ->field('publish_type.id,publish_type.name,publish_type.display,publish_type.created_time,publish_type.updated_time')
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
           $model = PublishType::get($id);
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
               'message' => '分类名称已存在'
           ));
       }
       if (empty($data['id'])) {
           $model = new PublishType ();
           $data['deleted'] = 0;
           $data['created_user_id'] = $this->userId;
           $data['created_time'] = date('Y-m-d H:i:s');
       } else {
           $model = PublishType::get($data['id']);
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
       $model = PublishType::get($id);
       if (empty($model)) {
           return json(array(
               'status' => 0,
               'message' => '记录不存在'
           ));
       }

       $count = db('section_info')->where(['deleted'=>0,'publish_type_id'=>$id])->count();
       if($count > 0){
           return json(array(
               'status' => 0,
               'message' => '该公示分类下有相关联的公示，不能删除'
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
       $where['name'] = $key;
       $where['deleted'] = 0;
       if (!empty($id)) {
           $where['id'] = array('<>', $id);
       }
       $list = db('publish_type')->where($where)->count();
       if ($list > 0) {
           return true;
       }
       return false;
   }
}
