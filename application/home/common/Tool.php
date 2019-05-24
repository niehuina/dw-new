<?php

namespace app\home\common;

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
}
