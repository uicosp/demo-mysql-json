<?php
/**
 * @author: 鱼肚 <uicosp@gmail.com>
 * @date: 2017/8/3
 */
function sql()
{
    printf("DATE: %s\n", date('Y-m-d H:i:s', time()));
    $no = 0;

    DB::listen(function ($query) use (&$no) {
        $sql = $query->sql;
        foreach ($query->bindings as $binding) {
            $sql = preg_replace('/\?/', "'" . $binding . "'", $sql, 1);
        }
        printf("#%d [%sms] %s\n", $no, $query->time, $sql);
        $no++;
    });
}