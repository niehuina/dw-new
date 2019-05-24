<?php

namespace app\admin\controller;

use app\admin\common\Cache;
use app\admin\common\Tool;
use app\admin\model\DictHoliday;
use app\admin\model\VacationRecord;
use app\admin\model\WebUser;
use app\admin\common\Constant;
use think\Db;
use think\Model;

class VacationRecordController extends BaseController
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
        $map['vr.deleted'] = '0';
        $map['year(vr.start_time)'] = date('Y');
        $map['year(vr.end_time)'] = date('Y');

        $order = 'CONVERT(wu.name USING gbk) asc,vr.start_time desc';
        $recordCount = db('vacation_record vr')
            ->join('web_user wu', 'wu.deleted=0 and wu.id=vr.web_user_id')->where($map)->count();
        $records = db('vacation_record vr')
            ->join('web_user wu', 'wu.deleted=0 and wu.id=vr.web_user_id')
            ->where($map)
            ->field('vr.id,vr.web_user_id,DATE_FORMAT(vr.start_time,\'%Y-%m-%d\') as start_time,
                DATE_FORMAT(vr.end_time,\'%Y-%m-%d\') as end_time,vr.days,vr.type,
                wu.name as web_user_name')
            ->limit($start, $length)->order($order)->select();

        foreach ($records as $key => $item) {
            $records[$key]['type_name'] = get_value($item['type'], Constant::VACATION_TYPES);
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
            $model = VacationRecord::get($id);
            $edit_state = true;
        }
        $web_user_list = Cache::key_value('web_user');
        $vacation_type_list = Constant::VACATION_TYPES;
        $holiday_list = [];//db('dict_holiday')->where(['deleted' => 0])->field('holiday')->select();

        if($edit_state) {
            $model['web_user_name'] = get_value($model['web_user_id'], $web_user_list);
        }
        $this->assign('holiday_list', json_encode(array_column($holiday_list, 'holiday')));
        $this->assign('vacation_type_list', $vacation_type_list);
        $this->assign('web_user_list', $web_user_list);
        $this->assign('model', $model);
        $this->assign('edit_state', $edit_state);
        return view();
    }

    public function _item_maintain2()
    {
        $id = $this->request->param('id');
        $model = null;
        $edit_state = false;
        if (!empty($id)) {
            $model = VacationRecord::get($id);
            $edit_state = true;
        }
        $current_year = date('Y');

        $web_user_list = Cache::key_value('web_user');
        $vacation_type_list = Constant::VACATION_TYPES;
        $holiday_list=[];//db('dict_holiday')->where(['deleted'=>0])->field('holiday')->select();

        if($edit_state) {
            $model['web_user_name'] = get_value($model['web_user_id'], $web_user_list);
        }

        $this->assign('current_year', $current_year);
        $this->assign('holiday_list', json_encode(array_column($holiday_list,'holiday')));
        $this->assign('vacation_type_list', $vacation_type_list);
        $this->assign('web_user_list', $web_user_list);
        $this->assign('model', $model);
        $this->assign('edit_state', $edit_state);
        return view();
    }

    public function save()
    {
        $data = input('post.');
        if ($this->is_exist($data['start_time'], $data['end_time'],$data['web_user_id'],$data['id'])) {
            return json(array(
                'status' => 0,
                'message' => '记录重复,请检查日期'
            ));
        }

        $cover_loss = 0;
        if (empty($data['id'])) {
            $model = new VacationRecord ();
            $data['deleted'] = 0;
            $data['created_user_id'] = $this->userId;
            $data['created_time'] = date('Y-m-d H:i:s');
        } else {
            $model = VacationRecord::get($data['id']);
            if (empty($model)) {
                return json(array(
                    'status' => 0,
                    'message' => '记录不存在'
                ));
            }
            $cover_loss = $model->days;
            $data['updated_time'] = date('Y-m-d H:i:s');
            $data['updated_user_id'] = $this->userId;
        }

        if($data['type']==1) {
            $map=null;
            $map['vacation_record.deleted'] = '0';
            $map['vacation_record.type'] = '0';
            $map['vacation_record.web_user_id'] = $data['web_user_id'];
            $sum_sick_leave_days = db('vacation_record')->where($map)
                ->field('vacation_record.days')->sum('vacation_record.days');

            $web_user_model = WebUser::get($data['web_user_id']);
            $total_vacation_days = Tool::get_year_vacation($web_user_model->entry_time,$sum_sick_leave_days);

            $map=null;
            $map['vacation_record.deleted'] = '0';
            $map['vacation_record.type'] = '1';
            $map['vacation_record.web_user_id'] = $data['web_user_id'];
            $sum_vacation_days = db('vacation_record')->where($map)
                ->field('vacation_record.days')->sum('vacation_record.days');
            $overplus_vacation_days = $total_vacation_days - ($sum_vacation_days - $cover_loss);

            if($overplus_vacation_days<0){
                $overplus_vacation_days=0;
            }
            if ($data['days'] > $overplus_vacation_days) {
                return json(array(
                    'status' => 0,
                    'message' => '年假不足！[' . $web_user_model->name . ']剩余年假天数：' . $overplus_vacation_days
                ));
            }
        }else{
            if (empty($data['id'])) {
                $where['vacation_record.deleted'] = '0';
                $where['vacation_record.web_user_id'] = $data['web_user_id'];
                $where['vacation_record.type'] = '0';
                /*$list = db('vacation_record')->where($where)->count();
                if ($list > 0) {
                    return json(array(
                        'status' => 0,
                        'message' => '病假记录重复'
                    ));
                }*/
            }

            /*$year=$data['year'];
            $data['start_time'] = $year."-01-01";
            $data['end_time'] = $year."-12-31";*/
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
        $model = VacationRecord::get($id);
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

    private function is_exist($start_time,$end_time,$web_user_id, $id = '')
    {
        $map="";
        if (!empty($id)) {
            $map = "and id <> {$id}";
        }

        $time_sql="(('{$start_time}' >= start_time and '{$start_time}' <= end_time) or ('{$end_time}' >= start_time and '{$end_time}' <= end_time) ) ";
        $sql = "SELECT COUNT(*) AS tp_count FROM `vacation_record` WHERE  `web_user_id` = {$web_user_id} {$map} AND `deleted` = 0 and $time_sql LIMIT 1";

        $list=Db::query($sql);

        if (count($list) > 0 && $list[0]['tp_count']>0) {
            return true;
        }
        return false;
    }
}
