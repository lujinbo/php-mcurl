## php批量执行curl同步请求


项目中如果action需要从多个接口获取数据，并且数据中不需要上下文内容关联可以使用批量curl请求接口提高效率。

* 支持 POST 和GET 两种请求方式

	 $r1 = array(
    	'url'=>'http://www.test.com/curl2.php',
    	'type'=>'get',
    	'data'=>array(
        	'k1'=>'v1',
        	'k2'=>2,
        	'abc'=>2
    	),
	);
	
	$r2 = array(
    	'url'=>'http://www.test.com/curl4.php',
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
