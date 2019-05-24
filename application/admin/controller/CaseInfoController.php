<?php

namespace app\admin\controller;

use app\admin\common\Constant;
use app\admin\common\Tool;
use app\admin\model\CaseInfo;
use app\admin\model\WebUser;
use think\Db;

class CaseInfoController extends BaseController
{
    public function index()
    {
        $this->assign('search_date_s', date('Y-m-d', strtotime('-1 month')));
        $this->assign('search_date_e', date('Y-m-d'));
        return view();
    }

    public function get_list()
    {
        $start = $this->request->param('start');
        $length = $this->request->param('length');
        $map = $this->process_query('case_info.name|case_info.number');
        $user_name = $this->request->param('user_name');
        if($user_name){
            $map['wu.name'] = ['like', "%{$user_name}%"];
        }

        $order = 'case_info.accept_time desc';
        $recordCount = db('case_info')
            ->join('web_user wu', 'wu.id=case_info.web_user_id', 'LEFT')->where($map)->count();
        $records = db('case_info case_info')
            ->join('web_user wu', 'wu.id=case_info.web_user_id', 'LEFT')
            ->where($map)
            ->field('case_info.id,case_info.warning,case_info.name,case_info.number,case_info.accept_time,
                case_info.current_stage,case_info.status,case_info.due_time,case_info.over_time,case_info.complete_time,
                case_info.record_time,case_info.type_name,
                case when wu.name is null then case_info.user_name else wu.name end as user_name,
                case_info.department,case_info.cell')
            ->limit($start, $length)->order($order)->select();

        return json(array(
            'draw' => $this->request->param('draw'),
            'recordsTotal' => $recordCount,
            'recordsFiltered' => $recordCount,
            'data' => $records
        ));
    }

    public function delete()
    {
        $id = $this->request->param('id');
        $model = CaseInfo::get($id);
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

    public function _item_import()
    {
        return view();
    }

    public function import()
    {
        $file = $this->request->file('import_file');
        if (!empty($file)) {
            $file_types = explode(".", $_FILES['import_file']['name']); // ["name"] => string(25) "excel文件名.xls"
            $file_type = strtolower($file_types [count($file_types) - 1]);//xls后缀
            /*判别是不是.xls文件，判别是不是excel文件*/
            if ($file_type != "xls" && $file_type != "xlsx") {
                return json(array(
                    'status' => 0,
                    "message" => "请上传Excel文件"
                ));
            }

            $dirStr = '\public\upload\import';
            $info = $file->move(ROOT_PATH . $dirStr);

            if (!$info) {
                return json(array(
                    'status' => 0,
                    "message" => "上传失败"
                ));
            }

            $filepath = $dirStr . '\\' . $info->getSaveName();
            $filepath = str_replace("\\", "\\\\", $filepath);

            //从Excel文件中取得数据
            $excelData = Tool::getExcel_Data($file_type, $filepath);
            if (count($excelData) == 1) {
                return json(array(
                    'status' => 0,
                    "message" => "文档无数据"
                ));
            }

            $msg = "";

            //判断必填
            $keyRow = $excelData[1];

            $import_row = ['超期预警','案件名称','部门受案号','受理时间','当前阶段','案件状态','到期日期','办结日期','完成日期',
                '归档日期','案件类别名称','承办检察官','办案部门','办案单元'];
            if($import_row != $keyRow){
                return json(array(
                    'status' => 0,
                    "message" => "导入的模板不正确\n" . $msg
                ));
            }
            foreach ($excelData as $key => $row) {
                if ($key > 1) {
                    foreach ($row as $i => $column) {
                        if ($i > 0 && $i <= 3 && !$column) {
                            $msg .= "第" . $key . "行 " . $keyRow[$i] . "不能为空\n";
                        }
                        if ($i == 11 && !$column) {
                            $msg .= "第" . $key . "行 " . $keyRow[$i] . "不能为空\n";
                        }
                    }
                }
            }

            //导入只能是检察官的案件
            foreach ($excelData as $key => $row) {
                if ($key > 1) {
                    $web_user_name = $row[11];
                    $user_type_list = db('setting')
                        ->where(['type'=>'user_type','code'=>Constant::USER_TYPE_PROSECUTOR])
                        ->column('id');
                    $web_user = WebUser::get(['name' => $web_user_name, 'user_type'=>['in',$user_type_list]]);
                    if (!$web_user) {
                        $msg .= "检察官【{$web_user_name}】不存在\n";
                    }
                    $excelData[$key][11] = $web_user['id'];
                }
            }

            if ($msg) {
                return json(array(
                    'status' => 2,
                    "message" => "导入错误信息：\n" . $msg
                ));
            }

            /*对生成的数组进行数据库的写入*/
            $insert_data = [];
            $update_data = [];
            foreach ($excelData as $k => $v) {
                $array = [];
                if ($k > 1) {
                    $array['warning'] = isset($v[0]) ? $v[0] : '';
                    $array['name'] = $v[1];
                    $array['number'] = $v[2];
                    if (isset($v[3])) {
                        $stamp_date3 = \PHPExcel_Shared_Date::ExcelToPHP($v[3]);//将获取的奇怪数字转成时间戳，该时间戳会自动带上当前日期
                        $array['accept_time'] = gmdate("Y-m-d", $stamp_date3);//这个就是excel表中的数据了，棒棒的！
                    }

                    $array['current_stage'] = isset($v[4]) ? $v[4] : '';
                    $array['status'] = isset($v[5]) ? $v[5] : '';

                    if (isset($v[6]) && !empty($v[6])) {
                        $stamp_date6 = \PHPExcel_Shared_Date::ExcelToPHP($v[6]);//将获取的奇怪数字转成时间戳，该时间戳会自动带上当前日期
                        $array['due_time'] = gmdate("Y-m-d", $stamp_date6);//这个就是excel表中的数据了，棒棒的！
                    } else {
                        $array['due_time'] = null;
                    }
                    if (isset($v[7]) && !empty($v[7])) {
                        $stamp_date7 = \PHPExcel_Shared_Date::ExcelToPHP($v[7]);//将获取的奇怪数字转成时间戳，该时间戳会自动带上当前日期
                        $array['over_time'] = gmdate("Y-m-d", $stamp_date7);//这个就是excel表中的数据了，棒棒的！
                    } else {
                        $array['over_time'] = null;
                    }
                    if (isset($v[8]) && !empty($v[8])) {
                        $stamp_date8 = \PHPExcel_Shared_Date::ExcelToPHP($v[8]);//将获取的奇怪数字转成时间戳，该时间戳会自动带上当前日期
                        $array['complete_time'] = gmdate("Y-m-d", $stamp_date8);//这个就是excel表中的数据了，棒棒的！
                    } else {
                        $array['complete_time'] = null;
                    }
                    if (isset($v[9]) && !empty($v[9])) {
                        $stamp_date9 = \PHPExcel_Shared_Date::ExcelToPHP($v[9]);//将获取的奇怪数字转成时间戳，该时间戳会自动带上当前日期
                        $array['record_time'] = gmdate("Y-m-d", $stamp_date9);//这个就是excel表中的数据了，棒棒的！
                    } else {
                        $array['record_time'] = null;
                    }
                    $array['type_name'] = isset($v[10]) ? $v[10] : '';
                    if (isset($v[11]) && !empty($v[11])) {
                        $array['web_user_id'] = $v[11];
                    }
                    $array['department'] = isset($v[12]) ? $v[12] : '';
                    $array['cell'] = isset($v[13]) ? $v[13] : '';
                    $array['type'] = 0;

                    $case = CaseInfo::get(['number'=>$v[2]]);
                    if ($case) {
                        $array['id'] = $case['id'];
                        $update_data[] = $array;
                    } else {
                        $insert_data[] = $array;
                    }
                }
            }

            // 启动事务
            Db::startTrans();

            try {
                $result = 1;
                //插入
                if (count($insert_data)) {
                    $result = model('case_info')->insertAll($insert_data);
                }
                //更新
                if (count($update_data)) {
                    $result = model('case_info')->saveAll($update_data);
                }

                if ($result) {
                    // 提交事务
                    Db::commit();
                    return json(array(
                        'status' => 1,
                        "message" => "导入成功"
                    ));
                } else {
                    // 回滚事务
                    Db::rollback();
                    return json(array(
                        'status' => 0,
                        "message" => "导入失败"
                    ));
                }
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();

                return json(array(
                    'status' => 0,
                    "message" => $e->getMessage()
                ));
            }
        }
    }

    public function export_data()
    {
        $map = $this->process_query('case_info.name|case_info.number');
        $user_name = $this->request->param('user_name');
        if($user_name){
            $map['wu.name'] = ['like', "%{$user_name}%"];
        }

        $order = 'case_info.accept_time desc';
        $records = db('case_info case_info')
            ->join('web_user wu', 'wu.id=case_info.web_user_id', 'LEFT')
            ->where($map)
            ->field('case_info.id,case_info.warning,case_info.name,case_info.number,case_info.accept_time,
                case_info.current_stage,case_info.status,case_info.due_time,case_info.over_time,case_info.complete_time,
                case_info.record_time,case_info.type_name,
                case when wu.name is null then case_info.user_name else wu.name end as user_name,
                case_info.department,case_info.cell')
            ->order($order)->select();

        $tit = array(
            "超期预警"=>"string",
            "案件名称"=>"string",
            "部门受案号"=>"string",
            "受理时间"=>"string",
            "当前阶段"=>"string",
            "案件状态"=>"string",
            "到期日期"=>"string",
            "办结日期"=>"string",
            "完成日期"=>"string",
            "归档日期"=>"string",
            "案件类别"=>"string",
            "承办检察官"=>"string",
            "办案部门"=>"string",
            "办案单元"=>"string"
        );

        $key = array(
            "warning",
            "name",
            "number",
            "accept_time",
            "current_stage",
            "status",
            "due_time",
            "over_time",
            "complete_time",
            "record_time",
            "type_name",
            "user_name",
            "department",
            "cell"
        );

        Tool::export("检察官案件信息列表",$tit,$key,$records);
    }
}
