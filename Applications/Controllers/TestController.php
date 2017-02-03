<?php
/**
 * 测试类
 */
class TestController extends AppController
{
    public function n() {
        file_put_contents('1.txt',$this->dparam);
        info('请求成功');
    }
    public function test()
    {
        echo 'ok';
        // file_put_contents('1.txt','111');
        // echo 1;
        // $a = new TaoBaoKeController;
        // D($a);
        // exit;
        // echo 1;
        //----------------测试Model层调用----------------
        //测试 通过大A方法 调用自定义模型类执行了多少次!  效率比直接 new模型类要高
        //D(A('Test:'));
        // D(A('Test'));
        //D(A('Test'));
        // D(A('Test'));
        // D(A('Index'));
        //D($test->test());
        //测试修改数据时候 是否过滤掉不存在的字段以及空和null
        // $arr = [
        //     'username' => 'adm',
        //     'hah' => '1',
        //     'password' => ''
        // ];
        // D(M('user')->where('id=1')->save($arr,false));
        // 也可以直接利用大A函数执行自定义模型类的某个方法 第二个参数为一维数组 表示往这个方法里面传递的参数 不传递参数可以不写第二个参数
        // 但是不建议这样使用 因为每次都会new一下这个对象之后才会调用这个方法  只能说声合理的去运用该方法吧！！！
        // D(A('Test:index'));
        // ----------------测试curd操作----------------
        // select save add方法 第一个参数设为false可查看sql语句
        // 测试快速分页
        // D(M('one')->page(1,20)->select(false));
        // 测试where条件调用
        // D(M('user')->where(['id' => ['=',2],'name' => ['=','王亚辉']],['OR'])->limit(1,1)->select(false));
        // D(M('user')->where("id=%d and name=%s",[1,'王亚辉'])->limit(2,3)->select(false));
        // 测试order 排序
        // D(M('user')->order('id')->select(false));
        // 测试add方法添加数据
        // D(M('user')->add(['username' => 2, 'password' => 3, 'phone' => 'iphone'],false));
        // 更改表前缀
        // 测试分组 以及倒序正序 连贯操作
        // D(M('test')->field('max(id)')->group('id')->order('id')->select(false));
        // 测试删除操作
        // D(M('user')->where(['id' => ['=',1],'name' => ['=','王亚辉']],['OR'])->save(false));
        // 测试查询全部数据
        // D(M('one')->select(false));
        // 测试批量添加数据  数据格式(二维数组) $a = [['name' => '王亚辉', 'sex' => '男'],['name' => '张三', 'sex' => '男']]
        // D(M('user')->batchAdd($a))
        // 测试count用法 select count(id) from user
        // D(M('user')->field('id')->count());
        // 测试sql语句执行 大M方法可以不传递参数
        // D(M()->query('select * from user'));
        // ----------------测试视图类调用----------------
        // 传递变量 view方法无参数时 默认加载 index.php
        // self::assing(['name' => '王亚辉','sex' => '男'])->view();
        // 不传递变量 只显示视图
        // $this->view('index');
        // 测试事务
        // ----------------测试公共函数库调用----------------
        //获取所有配置
        // D(C());
        //获取某一个配置值
        // D(C('DB_TYPE'));
        // 测试大M方法来获取到模型类实例
        // D(M('user')->field('id',true)->select());
        // 通过大M方法来更改表前缀 只对本次操作有效
        // $a = M('test','jp_');
        // D($a->select(false));
        //测试大I函数获取GET POST RAW参数
        // D(I('get:id'));
        // D(I('post:id'));
        // D(I('raw.data.id'));
        // D(I('raw.id'));
        // 能确保只有一种方式传递数据的时候可以使用自动获取 找到则就返回 不会再继续往下找
        // D(I('raw:data:name'));
        // if(isset)
        // 测试大I 获取所有get 或 POST 或raw参数数据
        // D(I('get'));
        // 测试search方法 从关联数组或者二维数组搜索指定值
        // D(search($_GET,'id'));
        // function filter_arr($arr=array()){
        //     foreach ($arr as $k => $v) {
        //         if($v !== '' && $v !== null){
        //             $_arr[$k] = $v;
        //         }
        //     }
        //     return $_arr;
        // }
        // D(filter_arr(['name' => 0]));
        // 测试get curl
        // $v = ['name' => [1,2,3]];
        // $post = get_curl('http://localhost:8080/HuiTao/test/a', json_encode($v));
        // echo '<pre>';
        // print_r($post);
    }
    public function a()
    {
        print_r($_POST);
    }
    //-------redis
    public function redis()
    {
        A('Goods:delAllGoods');
        echo 'ok';
    }
}