<?php

namespace app\admin\common;

use PHPExcel_IOFactory;
use PHPExcel;

class Tool
{
    public static function export_excel($data, $header, $filename)
    {
        $array = array();
        array_push($array, $header);
        foreach ($data as $item) {
            $result = [];
            array_walk_recursive($item, function ($value) use (&$result) {
                array_push($result, $value);
            });
            array_push($array, $result);
        }
        vendor("PHPExcel.PHPExcel");
        $PHPExcel = new PHPExcel();
        $PHPSheet = $PHPExcel->getActiveSheet();
        $PHPSheet->setTitle('Sheet1');
        $PHPSheet->fromArray($array);
        $PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel2007');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');//禁止缓存
        $PHPWriter->save("php://output");
    }

    public static function get_month_range($date = '')
    {
        if (empty($date)) {
            $date = time();
        } else {
            $date = strtotime($date);
        }
        $month_start = date('Y-m-1 00:00:00', $date);
        $days = date('t', $date);
        $month_end = date('Y-m-' . $days . ' 23:59:59', $date);


        return [$month_start, $month_end];
    }

    public static function get_year_range($date = '')
    {
        if (empty($date)) {
            $date = time();
        } else {
            $date = strtotime($date);
        }
        $month_start = date('Y-1-1 00:00:00', $date);
        $month_end = date('Y-12-31 23:59:59', $date);

        return [$month_start, $month_end];
    }

    public static function get_month_to_date_range($date = '')
    {
        if (empty($date)) {
            $date = time();
        } else {
            $date = strtotime($date);
        }
        $month_start = date('Y-m-1 00:00:00', $date);
        $day = date('d', $date);
        $month_end = date('Y-m-' . $day . ' 23:59:59', $date);


        return [$month_start, $month_end];
    }

    public static function get_year_to_date_range($date = '')
    {
        if (empty($date)) {
            $date = time();
        } else {
            $date = strtotime($date);
        }
        $month_start = date('Y-1-1 00:00:00', $date);
        $day = date('d', $date);
        $month_end = date('Y-m-' . $day . ' 23:59:59', $date);


        return [$month_start, $month_end];
    }

    public static function getExcel_Data($file_type, $file_path)
    {
        vendor("PHPExcel.PHPExcel");
        vendor("PHPExcel.PHPExcel.IOFactory");
        vendor("PHPExcel.PHPExcel.Reader.Excel5");

        if (strtolower($file_type) == 'xls')//判断excel表类型为2003还是2007
        {
            $objReader = \PHPExcel_IOFactory::createReader('Excel5');

        } elseif (strtolower($file_type) == 'xlsx') {
            $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
        }

        $objReader->setReadDataOnly(true);
        $objPHPExcel = $objReader->load("." . $file_path);
        $objWorksheet = $objPHPExcel->getActiveSheet();
        $highestRow = $objWorksheet->getHighestRow();
        $highestColumn = $objWorksheet->getHighestColumn();
        $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);
        $excelData = array();
        for ($row = 1; $row <= $highestRow; $row++) {
            for ($col = 0; $col < $highestColumnIndex; $col++) {
                $excelData[$row][] = (string)$objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
            }
        }
        return $excelData;
    }

    public static function export($title, $tit, $key, $data, $isHasNumber = false)
    {
        ob_end_clean();

        include_once(APP_PATH . "/extra/xlsxwriter.php");
        $writer = new \XLSXWriter();

        $writer->writeSheetHeader('Sheet1', $tit);//optional

        foreach ($data as $k => $item) {
            $info = array();
            if ($isHasNumber) {
                $info[] = $k + 1;
            }
            foreach ($key as $column) {
                $info[] = $item[$column];
            }
            $writer->writeSheetRow('Sheet1', $info);
        }

        $userBrowser = $_SERVER['HTTP_USER_AGENT'];
        if ( preg_match( '/MSIE/i', $userBrowser ) ) {
            $title = urlencode($title);
        }
        $title = iconv('UTF-8', 'GBK//IGNORE', $title);

        $filename = $title . '.xlsx';
        header('Content-disposition: attachment; filename="' . \XLSXWriter::sanitize_filename($filename) . '"');
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        $writer->writeToStdOut();
        exit;
        die();
    }

    public static function get_year_vacation($join_time, $sick_leave_days)
    {
        list($birthYear, $birthMonth, $birthDay) = explode('-', date('Y-m-d', strtotime($join_time)));
        list($currentYear, $currentMonth, $currentDay) = explode('-', date('Y-m-d'));
        $age = $currentYear - $birthYear - 1;
        if ($currentMonth > $birthMonth || $currentMonth == $birthMonth && $currentDay >= $birthDay) {
            $age++;
        }

        $vacation_days = 0;
        if ($age > 0 && $age < 10) {
            if ($sick_leave_days <= 60) {
                $vacation_days = 5;
            }
        } elseif ($age >= 10 && $age < 20) {
            if ($sick_leave_days <= 90) {
                $vacation_days = 10;
            }
        } elseif ($age >= 20) {
            if ($sick_leave_days <= 120) {
                $vacation_days = 15;
            }
        }

        return $vacation_days;
    }

    public static function get_year_list(){
        $current_year=date('Y');
        $year_list=array();
        for($i = $current_year;$i > $current_year-10;$i--){
            $year_list[$i]=$i;
        }

        return $year_list;
    }
}
