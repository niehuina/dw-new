<?php


namespace app\admin\controller;


use app\admin\common\Constant;
use app\admin\model\Task;
use app\admin\model\TaskItem;
use app\admin\model\TaskItemAttachment;
use app\admin\model\TaskItemUser;
use think\Db;

class TaskItemReviewController extends BaseController
{
    public function index()
    {
        $type_list = db('task_type')->where(['deleted'=>0])->field('id,name')->select();
        $this->assign('task_type_list',$type_list);
        $this->assign('status_review',Constant::REVIEW_STATUS_SUCCESS);
        $this->assign('review_status_list',Constant::REVIEW_STATUS);
        $this->assign('review_status_wating',Constant::REVIEW_STATUS_WATING);
        return view();
    }

    public function get_list()
    {
        $start = $this->request->param('start');
        $length = $this->request->param('length');
        $map = $this->process_query('task.title|task_item.name');
        $task_type =  $this->request->param('task_type');
        $review_status =  $this->request->param('review_status');
        if($task_type){
            $map['task.type_id'] = $task_type;
        }

        if(!empty($review_status)){
            $map['tiu.review_status'] = $review_status;
        }

        $map['tiu.status'] = '2';//已完成

        $order = 'tiu.review_status asc,tiu.review_time desc,tiu.created_time desc';
        $recordCount = db('task_item_user tiu')
            ->join('task', 'task.id=tiu.task_id')
            ->join('task_item', 'task_item.id=tiu.task_item_id')
            ->join('task_user tu', 'tu.task_id=tiu.task_id and tu.web_user_id=tiu.complete_web_user_id','left')
            ->join('web_user wu', 'wu.id=tu.web_user_id', 'LEFT')
            ->join('user', 'user.id=tiu.review_user_id', 'LEFT')
            ->where($map)->count();
        $records = db('task_item_user tiu')
            ->join('task', 'task.id=tiu.task_id')
            ->join('task_item', 'task_item.id=tiu.task_item_id')
            ->join('web_user wu', 'wu.id=tiu.web_user_id', 'LEFT')
            ->join('user', 'user.id=tiu.review_user_id', 'LEFT')
            ->where($map)
            ->field('tiu.id,task.title,task_item.name,tiu.status,user.name as reviewer,
                tiu.review_time,tiu.review_status,wu.name as web_user_name')
            ->limit($start, $length)->order($order)->select();

        foreach ($records as $key => $item) {
            $records[$key]['status_name'] = get_value($item['status'], Constant::TASK_STATUS);
            $records[$key]['review_status_name'] = get_value($item['review_status'], Constant::REVIEW_ACTION);
        }

        return json(array(
            'draw' => $this->request->param('draw'),
            'recordsTotal' => $recordCount,
            'recordsFiltered' => $recordCount,
            'data' => $records
        ));
    }

    public function _item_review()
    {
        $id = $this->request->param('id');
        $model = null;
        $edit_state = true;
        if (!empty($id)) {
            $model = TaskItemUser::get($id);
            $map['web_user_id'] = $model['web_user_id'];
            $map['task_item_id'] = $model['task_item_id'];
        }

        $attachment_list = TaskItemAttachment::all($map);
        $this->assign('attachment_list', $attachment_list);

        /*if($model['review_status'] == Constant::REVIEW_STATUS_WATING){
            $model['review_status'] = Constant::REVIEW_STATUS_SUCCESS;
        }*/
        $review_status_list = Constant::REVIEW_ACTION;
        $this->assign('review_status_list', $review_status_list);
        $this->assign('model', $model);
        $this->assign('edit_state', $edit_state);
        return view();
    }

    public function save()
    {
        $data = input('post.');

        $model = TaskItemUser::get($data['id']);
        if (empty($model)) {
            return json(array(
                'status' => 0,
                'message' => '记录不存在'
            ));
        }
        $data['review_time'] = date('Y-m-d H:i:s');
        $data['review_user_id'] = $this->userId;

        // 启动事务
        Db::startTrans();
        try {
            $model->data($data)->save();

            $task_id = $data['task_id'];

            $map['deleted'] = 0;
            $map['task_id'] = $task_id;
            $task_items_count = db('task_item')->where($map)->count();

            $map1['task_id'] = $task_id;
            $task_user_count = db('task_user')->where($map1)->count();

            $map_2['task_id'] = $task_id;
            $map_2['review_status'] = Constant::REVIEW_STATUS_SUCCESS;//完成
            $task_items_complete_count = db('task_item_user')->where($map_2)->count();

            //如果任务下面的所有项目都完成，则更新任务完成
            $task = Task::get($data['task_id']);
            if ($task_items_complete_count == $task_items_count * $task_user_count) {
                $task->status = Constant::TASK_STATUS_COMPLETED;
                $task->save();
            }else{
                $task->status = Constant::TASK_STATUS_IN_PROGRESS;
                $task->save();
            }

            // 提交事务
            Db::commit();
            return json(array(
                'status' => 1,
                "message" => "保存成功"
            ));
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();

            return json(array(
                'status' => 0,
                "message" => $e->getMessage()
            ));
        }
    }

    function download_file($file_path, $file_name)
    {
        $file = ROOT_PATH . $file_path;
        if (is_file($file)) {
            $length = filesize($file); //文件大小
            $type = mime_content_type($file); //文件类型
            $showname = $file_name;//ltrim(strrchr($file,'/'),'/'); //文件名
            header("Content-Description: File Transfer");
            header('Content-type: ' . $type);
            header('Content-Length:' . $length);
            $userBrowser = $_SERVER['HTTP_USER_AGENT'];
            if (preg_match('/MSIE/i', $userBrowser)) {
                $showname = urlencode($showname);
            }
            $showname = iconv('UTF-8', 'GBK//IGNORE', $showname);

            header('Content-Disposition: attachment; filename="' . $showname . '"');
            readfile($file);
            exit;
        } else {
            $this->assign('result', '文件已被删除！');
            return view();
            //exit();
        }
    }
}