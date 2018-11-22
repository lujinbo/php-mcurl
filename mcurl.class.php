<?php
/**
 * Cutl 批量请求
 * @author yldbwdx@yeah.net
 * @date 2018-4-12
 */

class lib_mcurl
{
    public static $task = array();
    private static $curl = null;
    public static function add($taskname,$r=array())
    {
        if(strtolower($r['type']) == 'post')
        {
            self::$curl = self::curl_post($r['url'],$r['data'],$r['header']);
        }
        else
        {
            self::$curl = self::curl_get($r['url'],$r['data'],$r['header']);
        }

        self::$task[$taskname] = self::$curl;
    }

    /**
     * 提交GET请求，curl方法
     * @param string  $url     请求url地址
     * @param mixed   $data   GET数据,数组或类似id=1&k1=v1
     * @param array   $header   头信息
     * @param int    $timeout   超时时间
     * @param int    $port    端口号
     * @return obj 句柄对象
     */
    private static function curl_get($url, $data = array(), $header = array(), $timeout = 5, $port = 80)
    {
        $ch = curl_init();
        if (!empty($data)) {
            $data = is_array($data)?http_build_query($data): $data;
            $url .= (strpos($url,'?')?  '&': "?") . $data;
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_POST, 0);
        //curl_setopt($ch, CURLOPT_PORT, $port);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION ,1); //是否抓取跳转后的页面
        return $ch;
    }

    /**
     * 提交POST请求，curl方法
     * @param string  $url     请求url地址
     * @param mixed   $data   POST数据,数组或类似id=1&k1=v1
     * @param array   $header   头信息
     * @param int    $timeout   超时时间
     * @param int    $port    端口号
     * @return obj 句柄对象
     */
    private static function curl_post($url, $data = array(), $header = array(), $timeout = 10, $port = 80)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        //curl_setopt($ch, CURLOPT_PORT, $port);
        !empty ($header) && curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        return $ch;
    }

    /**
     * 批量执行curl请求
     * @return array
     */
    public static function exec()
    {
        $redata = array();
        $master = curl_multi_init();
        foreach(self::$task as $t)
        {
            curl_multi_add_handle($master,$t);
        }

        $running=null;
        // 执行批处理句柄
        do {
            usleep(10000);
            curl_multi_exec($master,$running);
        } while ($running > 0);

        // 关闭全部句柄
        foreach(self::$task as $k=>$t)
        {
            $redata[$k] = curl_multi_getcontent($t);
            if(self::is_json($redata[$k]))
            {
                $redata[$k] = json_decode($redata[$k],true);
            }

            curl_multi_remove_handle($master, $t);
        }

        curl_multi_close($master);
        return $redata;
    }

    /**
     * 检测是否是json格式数据
     */
    private static function is_json($string) 
    {
         json_decode($string);
         return (json_last_error() == JSON_ERROR_NONE);
    }
}

/*
$r1 = array(
    'url'=>'http://www.lujinbo1.com/curl2.php',
    'type'=>'get',
    'data'=>array(
        'k1'=>'v1',
        'k2'=>2,
        'abc'=>2
    ),
);
$r2 = array(
    'url'=>'http://www.lujinbo1.com/curl4.php',
    'type'=>'post',
    'data'=>array(
        'k3'=>'v3',
        'k4'=>4,
        'debug'=>1
    ),
);
lib_mcurl::add($taskname='t1',$r1);
lib_mcurl::add($taskname='t2',$r2);

$redata = lib_mcurl::exec();
print_r($redata);
*/
