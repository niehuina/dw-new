<?php


namespace app\admin\controller;


use app\admin\model\Setting;

class WebsiteController extends BaseController
{
    public function index()
    {
        $list = Setting::all(['type'=>'website']);

        $data = array();
        foreach ($list as $key=>$item){
            $data[$item['code']] = $item;
        }

        $this->assign('data', $data);
        return view();
    }

    public function save()
    {
        $type_rows = $_REQUEST['type'];

        $file = $this->request->file('file');
        if (!empty($file)) {
            $upload_dir = '\public\upload\banner';
            $info = $file->move(ROOT_PATH . $upload_dir);
            if ($info) {
                $website_logo_url = $upload_dir . '\\' . $info->getSaveName();
            } else {
                return json(array(
                    'status' => 0,
                    "message" => "上传失败."
                ));
            }
        }

        foreach ($type_rows as $type) {
            $code_row = $_REQUEST[$type];
            if(isset($website_logo_url)){
                $code_row['website_logo'] = $website_logo_url;
            }
            $data = db("setting")->where(['type' => $type])->select();
            $code_rows = array_column($data,'code');

            foreach ($code_row as $key => $row) {
                if (array_search($key, $code_rows) === false) {
                    $add_row = new Setting();
                    $add_row['type'] = $type;
                    $add_row['code'] = $key;
                    $add_row['value'] = $row;
                    $add_row->save();
                }
            }

            if ($data) {
                foreach ($data as $key => $item) {
                    $edit_row = $item;
                    if (isset($code_row[$item['code']])) {
                        $edit_row['value'] = $code_row[$item['code']];
                        Setting::update($edit_row);
                    }
                }
            }
        }

        return json(array(
            'status' => 1,
            "message" => "保存成功"
        ));
    }
}