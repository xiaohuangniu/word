<?php 
1、API的地址设计
   URL中应该带有以下四个参数：
       A、生产环境 或者 测试环境
       B、版本号 v{N}
       C、操作类型
       D、处理地址
   例如：http://www.junphp.com/test/v0.0.2/shop/nameSelect
   注意：URL结尾不加【/】符号

2、API的调用方式
   所有接口均使用HTTP(S)/POST方式传输参数，传输过程中应包含消息头和消息主体两部分。

3、消息头规范
   消息头一般需包含内容类型，内容类型（Content-Type）字段用于标识请求中的消息主体的编码方式。
   为了方便业务方的数据维护，一般放弃XML数据传输，使用JSON的交互方式，参数信息采用utf-8编码。
   因此需要配置消息头中的Content-Type 为application/json;charset=utf-8。

4、消息主体规范，也就是body请求内容：
   消息主体是信息交换过程中的具体内容，一般由以下五大参数组成。
       A、运营商标识（OperatorID），应该有独自的一套生成规则，例如：按省市区三级字母+八位唯一随机数生成
       B、凭证（Token）           ，为了安全性，Token应该存在有效性，建议有效期为2小时，需要通过请求Token生成接口获得，参考access_token模式。
       C、参数内容（Data）        ，你需要请求接口的内容主体。
          Data需要注意的是，当有为空参数的时候，不能使用null代替空格，因为在APP中null并不代表为空

       D、时间戳（TimeStamp）     ，接口请求时时间戳信息,格式为yyyyMMddHHmmss  PHP为:YmdHis
       E、数字签名（Sign）         , 为了接口参数的安全性，使用参数签名的验证方式，用于接口KEY对比。

5、每个运营商都应该拥有一段由英文+数字随机组成的十六位字符串：
      签名生成秘钥（Key）

6、数字签名(Sign)的生成方式：
      参数签名采用Sha1【处理后的（Data）】进行散列处理，然后采用Md5【密钥（Key）+Sha1后的（Data）+时间戳（TimeStamp）】的方式形成新的密文。
      A、Data的处理流程如下：
         * 1、请求参数名称a-z顺序排序，不带checkValue以及非空数据。
         * 2、排序后的数组转换成字符串，并做全大写转换
         * 3、Sha1后的字符串，做全小写转换
      B、签名算法如下：
         * 1、MD5【密钥（Key）+Sha1后的（Data）+时间戳（TimeStamp）】，并做全大写转换
      
      PHP示例代码如下：
     
/** 
 * @method 多维数组转字符串 
 * @param  type $array 
 * @return type $srting 
*/
function arrayToString($arr) { 
    if (is_array($arr)){ 
       return implode('', array_map('arrayToString', $arr)); 
    } 
    return $arr; 
} 

/**
* Data的处理流程如下：
* 1、请求参数名称a-z顺序排序，不带checkValue以及非空数据。
* 2、排序后的数组转换成字符串，并做全大写转换
* 3、Sha1后的字符串，做全小写转换
*/

$Data = array(
    'name' => '测试',
    'id'   => 3,
    'data' => array(
        'title' => '我是标题',
        'conent'=> '我是的内容',
    ),
    'time' => 'time',
);
ksort($Data, SORT_STRING);
$a = arrayToString($Data);
$b = strtoupper( sha1($a) );
$c = strtolower($b);

/**
 * 签名算法如下：
 * 1、MD5【密钥（Key）+Sha1后的（Data）+时间戳（TimeStamp）】，并做全大写转换
*/
$Key  = 'a1s2d3zxc78ahu75';
$Time = date('Y-m-d H:i:s',time());
$d = md5($Key. $c .$Time);
echo strtoupper($d);


7、返回参数规则
   在规范开发中，我们一般都不会使用0和1去作为返回值的编码，因为这跟布尔值的true和false会有所冲突。
   未此，我们会使用以下的格式来进行编码说明：
   00，   请求成功，
   4000*，请求失败，以及对应的失败说明
   500，  系统错误
   
   在规范开发中，API的返回值由以下三部分所组成：
    {
        'code': 00,
        'msg' : '请求成功',
        'data': '回调内容，一般是一个json结构',
    }

8、Token的存储。
   为了提高接口的请求效率，建议在Token的有效期内，不要多次请求更新Token值，而是把Token保存在本地文件中。
    

9、文件传输的注意方式：
   在开发规范中，我们都应该统一使用base64编码的方式，进行文件传输，以便于保持业务处理的一致性。

