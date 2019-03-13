<?php defined('SYSPATH') or die('No direct script access.');

/**
 * 公用类
 *
 * @author: ZDW
 * @date: 2013-04-01
 * @update:
 * @version: $Id: Utility.php 12705 2015-11-24 01:17:25Z liukaida $
 */
class Utility
{
    //中文切分
     public static function mb_str_split($str,$len){
        $arr= preg_split('/(?<!^)(?!$)/u', $str );
        $res = array_chunk($arr,$len);
        foreach ($res as $k=>$v){
            $res[$k]=implode('',$v);
        }
        return $res;
    }
    //加密
    public static function encrypt($string,$key){
        $cipher="AES-128-CBC";
        $iv = substr(md5($key),0,16);
        $ciphertext_raw = openssl_encrypt($string, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv);
        return base64_encode( $ciphertext_raw );
    }
    //解密
    public static function decrypt($string,$key){
        $c = base64_decode($string);
        $cipher="AES-128-CBC";
        $iv = substr(md5($key),0,16);
        $original_plaintext = openssl_decrypt($c, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv);
        return $original_plaintext;
    }

    public static function strToUtf8($str)
    {
        return iconv(mb_detect_encoding($str, array("ASCII",'UTF-8',"GB2312","GBK",'BIG5'))
                ,'utf-8//TRANSLIT',$str)
    }
    
    /**
     * 给用户生成唯一CODE
     *
     * @param string $data
     * @return string
     */
    public static function authcode($data = null)
    {
        if (PHP_SAPI == 'cli') {
            $http_host = '';
        } else {
            $http_scheme = (($scheme = isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : null) == 'off' || empty($scheme)) ? 'http' : 'https';
            $http_host = $http_scheme . '://' . $_SERVER['HTTP_HOST'];
        }
        return self::guid($http_host . $data . $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']);
    }

    /**
     * 生成guid
     *
     * @param  $randid  字符串
     * @return string   guid
     */
    public static function guid($mix = null)
    {
        if (is_null($mix)) {
            $randid = uniqid(mt_rand(), true);
        } else {
            if (is_object($mix) && function_exists('spl_object_hash')) {
                $randid = spl_object_hash($mix);
            } elseif (is_resource($mix)) {
                $randid = get_resource_type($mix) . strval($mix);
            } else {
                $randid = serialize($mix);
            }
        }
        $randid = strtoupper(md5($randid));
        $hyphen = chr(45);
        $result = array();
        $result[] = substr($randid, 0, 8);
        $result[] = substr($randid, 8, 4);
        $result[] = substr($randid, 12, 4);
        $result[] = substr($randid, 16, 4);
        $result[] = substr($randid, 20, 12);
        return implode($hyphen, $result);
    }

    /**
     * @param $table
     * 表名
     * @param $data
     * 二维数组
     * $arr = [
     * 0=>['a'=>1,'b'=>1,'c'=>1,'d'=>1,'e'=>1,'f'=>'2017-06-06',],
     * 1=>['a'=>2,'b'=>2,'c'=>2,'d'=>2,'e'=>2,'f'=>'vivo',],
     * 2=>['a'=>3,'b'=>3,'c'=>3,'d'=>3,'e'=>3,'f'=>'oneplus',],
     * ];
     * @return string sql语句
     */
    public static function insertMany($table, $data)
    {
        $sql = 'insert into ' . $table . ' (';
        $keys = array_keys($data[0]);
        $sql .= implode(',', $keys);
        $sql .= ') values (';
        foreach ($data as $k => $v) {
            foreach ($v as $key => $value) {
                if (is_string($value))
                    $v[$key] = "'" . $value . "'";
            }
            $sql .= implode(',', $v);
            if ($k == count($data) - 1) {
                $sql .= ');';
            } else {
                $sql .= '),(';
            }
        }
        return $sql;
    }

    /**
     *   生成JSON格式的正确消息
     *
     * @access  public
     * @param
     * @return  void
     */
    public static function jsonResult($content, $message = '', $append = array())
    {
        self::jsonResponse($content, 0, $message, $append);
    }

    /**
     * 创建一个JSON格式的错误信息
     *
     * @access  public
     * @param   string $msg
     * @return  void
     */
    public static function jsonError($msg)
    {
        self::jsonResponse('', 1, $msg);
    }

    /**
     * 创建一个JSON格式的数据
     *
     * @access  public
     * @param   string $content
     * @param   integer $error
     * @param   string $message
     * @param   array $append
     * @return  void
     */
    private static function jsonResponse($content = '', $error = "0", $message = '', $append = array())
    {

        $res = array('error' => $error, 'message' => $message, 'content' => $content);
        if (!empty($append)) {
            foreach ($append AS $key => $val) {
                $res[$key] = $val;
            }
        }
        $val = json_encode($res);
        exit($val);
    }

    /**
     *  API接口：生成JSON格式的正确消息
     * @param string $data 数据
     * @param string $msg 提示消息
     * @param array $append
     */
    public static function apiJsonResult($data, $msg = '', $append = array())
    {
        self::apiJsonResponse($data, '200', $msg, $append);
    }

    /**
     *  API接口：创建一个JSON格式的错误信息
     * @param string $error 错误代码
     * @param string $msg 提示消息
     */
    public static function apiJsonError($error, $msg)
    {
        self::apiJsonResponse('', $error, $msg);
    }

    /**
     * 创建一个JSON格式的数据
     *
     * @access  public
     * @param   string $data
     * @param   integer $error
     * @param   string $msg
     * @return  void
     */
    private static function apiJsonResponse($data = '', $error = '200', $msg = '', $append = array())
    {

        $res = array('status' => $error, 'message' => $msg, 'data' => $data);
        if (!empty($append)) {
            foreach ($append AS $key => $val) {
                $res[$key] = $val;
            }
        }
        $val = json_encode($res);
        exit($val);
    }

    /**
     * javascript escape php 实现
     * @param $string           the sting want to be escaped
     * @param $in_encoding
     * @param $out_encoding
     */
    public static function escape($string, $in_encoding = 'UTF-8', $out_encoding = 'UCS-2')
    {
        $return = '';
        if (function_exists('mb_get_info')) {
            for ($x = 0; $x < mb_strlen($string, $in_encoding); $x++) {
                $str = mb_substr($string, $x, 1, $in_encoding);
                if (strlen($str) > 1) { // 多字节字符
                    $return .= '%u' . strtoupper(bin2hex(mb_convert_encoding($str, $out_encoding, $in_encoding)));
                } else {
                    $return .= '%' . strtoupper(bin2hex($str));
                }
            }
        }
        return $return;
    }

    /**
     * javascript unescape php 实现
     * @param $str
     * @return string
     */
    public static function unescape($str)
    {
        $ret = '';
        $len = strlen($str);
        for ($i = 0; $i < $len; $i++) {
            if ($str[$i] == '%' && $str[$i + 1] == 'u') {
                $val = hexdec(substr($str, $i + 2, 4));
                if ($val < 0x7f)
                    $ret .= chr($val);
                else
                    if ($val < 0x800)
                        $ret .= chr(0xc0 | ($val >> 6)) .
                            chr(0x80 | ($val & 0x3f));
                    else
                        $ret .= chr(0xe0 | ($val >> 12)) .
                            chr(0x80 | (($val >> 6) & 0x3f)) .
                            chr(0x80 | ($val & 0x3f));
                $i += 5;
            } else
                if ($str[$i] == '%') {
                    $ret .= urldecode(substr($str, $i, 3));
                    $i += 2;
                } else
                    $ret .= $str[$i];
        }
        return $ret;
    }


    /**
     * 分页大小
     * @return  array
     */
    public static function page_and_size(&$filter)
    {
        $filter['page_size'] = self::reqdata('pageSize') ? (int)self::reqdata('pageSize') : 30;
        if ($filter['page_size'] == 0) $filter['page_size'] = 30;
        $filter['page'] = self::reqdata('pageCurrent') ? (int)self::reqdata('pageCurrent') : 1;
        if ($filter['page'] == 0) $filter['page'] = 1;
        $filter['page_count'] = (isset($filter['record_count']) && $filter['record_count'] > 0) ?
            ceil($filter['record_count'] / $filter['page_size']) : 1;

        $filter['orderField'] = self::reqdata('orderField') && self::reqdata('orderField') !== '${param.orderField}' ? trim(self::reqdata('orderField')) : 'id';
        $filter['orderDirection'] = self::reqdata('orderDirection') == 'asc' ? 'ASC' : 'DESC';
        /* 边界处理 */
        if ($filter['page'] > $filter['page_count']) {
            $filter['page'] = $filter['page_count'];
        }
        $filter['start'] = ($filter['page'] - 1) * $filter['page_size'];
        $filter['pagelink'] = self::makePageLink($filter['record_count'], $filter['page_size'],
            $filter['page'], '', $filter['page_count']); // 显示分页
        return $filter;
    }

    /**
     * 首页分页
     * @return  array
     */
    public static function page_and_size2(&$filter)
    {
        $filter['page_size'] = self::reqdata('pageSize') ? (int)self::reqdata('pageSize') : 20;
        if ($filter['page_size'] == 0) $filter['page_size'] = 20;
        $filter['page'] = self::reqdata('pageCurrent') ? (int)self::reqdata('pageCurrent') : 1;
        if ($filter['page'] == 0) $filter['page'] = 1;
        $filter['page_count'] = (isset($filter['record_count']) && $filter['record_count'] > 0) ?
            ceil($filter['record_count'] / $filter['page_size']) : 1;

        $filter['orderField'] = self::reqdata('orderField') && self::reqdata('orderField') !== '${param.orderField}' ? trim(self::reqdata('orderField')) : 'id';
        $filter['orderDirection'] = self::reqdata('orderDirection') == 'asc' ? 'ASC' : 'DESC';
        /* 边界处理 */
        if ($filter['page'] > $filter['page_count']) {
            $filter['page'] = $filter['page_count'];
        }
        $filter['start'] = ($filter['page'] - 1) * $filter['page_size'];
        $filter['pagelink'] = self::makePageLink($filter['record_count'], $filter['page_size'],
            $filter['page'], '', $filter['page_count']); // 显示分页
        return $filter;
    }


    /**
     * GET或post数据
     * @param $key
     */
    public static function reqdata($key)
    {
        $req = Request::initial();
        return $req->query($key) ?: $req->post($key);

    }

    /**
     * 使用listtable.js翻页
     * @param $num         总记录数
     * @param $perpage    每页记录数
     * @param $curpage     当前页数
     * @param int $maxpages 最大页面值
     * @param int $page 一次最多显示几页
     * @return string
     */
    public static function makePageLink($num, $perpage, $curpage, $maxpages = 0, $page = 5)
    {
        $dot = '...';

        $page -= strlen($curpage) - 1;
        if ($page <= 0) {
            $page = 1;
        }
        if ($perpage <= 0 || $perpage >= 1000) {
            $perpage = 5;
        }

        $offset = floor($page * 0.5);

        $realpages = @ceil($num / $perpage);
        $curpage = $curpage > $realpages ? $realpages : $curpage;
        $pages = $maxpages && $maxpages < $realpages ? $maxpages : $realpages;


        if ($page > $pages) {
            $from = 1;
            $to = $pages;
        } else {
            $from = $curpage - $offset;
            $to = $from + $page - 1;

            if ($from < 1) {
                $to = $curpage + 1 - $from;
                $from = 1;
                if ($to - $from < $page) {
                    $to = $page;
                }
            } elseif ($to > $pages) {
                $from = $pages - $page + 1;
                $to = $pages;
            }
        }

        $multipage = ($curpage > 1 ? '<a href="javascript:listTable.gotoPagePrev()" class="prev">上一页</a>' : '') .
            ($curpage - $offset > 1 && $pages > $page ? '<a href="javascript:listTable.gotoPageFirst()" class="first">1 ' . $dot . '</a>' : '');
        for ($i = $from; $i <= $to; $i++) {
            $multipage .= $i == $curpage ? '<strong>' . $i . '</strong>' :
                '<a href="javascript:listTable.gotoPage(' . $i . ')">' . $i . '</a>';
        }
        $multipage .= ($to < $pages ? '<a href="javascript:listTable.gotoPageLast()" class="last">' . $dot . ' ' . $realpages . '</a>' : '') .
            ($curpage < $pages ? '<a href="javascript:listTable.gotoPageNext()" class="nxt">下一页</a>' : '');
        return $multipage;
    }

    /**
     * 根据过滤条件获得排序的标记
     *
     * @param   array $filter
     * @return  array
     */
    public static function sortFlag(array $filter)
    {
        $flag['tag'] = 'sort_' . preg_replace('/^.*\./', '', $filter['sort_by']);
        $flag['img'] = '<img src="/asset/images/' . ($filter['sort_order'] == "DESC" ? 'sort_desc.gif' :
                'sort_asc.gif') . '"/>';

        return $flag;
    }

    /**
     * 多币种格式化价格
     *
     * @access  public
     * @param   float $price 价格
     * @param   string $currency 货币名称简写字母（三个大小字母）
     * @return  string
     */
    public static function currency_price_format($price, $currency = 'CNY')
    {
        if ($price === '') {
            $price = 0;
        }
        $code = '';
        switch (strtoupper($currency)) {
            case 'USD': //美元
                $code = 'USD:%s';
                break;
            case 'EUR': //欧元
                $code = 'EUR:%s';
                break;
            case 'GBP': //英磅
                $code = 'GBP:%s';
                break;
            case 'HKD': //港币
                $code = 'HKD:%s';
                break;
            case 'TWD': //台币
                $code = 'TWD:%s';
                break;
            case 'AUD': //澳元
                $code = 'AUD:%s';
                break;
            case 'JPY': //日元
                $code = 'JPY:%s';
                break;
            case 'KRW': //韩元
                $code = 'KRW:%s';
                break;
            case 'CAD': //加拿大元
                $code = 'CAD:%s';
                break;
            case 'MOP': //澳门元
                $code = 'MOP:%s';
                break;
            case 'CNY': //人民币
            default:
                $code = '￥%s';
                break;

        }
        $price = number_format($price, 2, '.', '');

        return sprintf($code, $price);
    }


    /**
     * 获取文件扩展名
     *
     * @param    string    文件名
     *
     * @return    string    扩展名
     */
    public static function fileExtension($filename)
    {
        return substr(strrchr($filename, '.'), 1);
    }

    /**
     *  将一个字串中含有全角的数字字符、字母、空格或'%+-()'字符转换为相应半角字符
     *
     * @access  public
     * @param   string $str 待转换字串
     *
     * @return  string       $str         处理后字串
     */
    public static function makeSemiangle($str)
    {
        $arr = array('０' => '0', '１' => '1', '２' => '2', '３' => '3', '４' => '4',
            '５' => '5', '６' => '6', '７' => '7', '８' => '8', '９' => '9',
            'Ａ' => 'A', 'Ｂ' => 'B', 'Ｃ' => 'C', 'Ｄ' => 'D', 'Ｅ' => 'E',
            'Ｆ' => 'F', 'Ｇ' => 'G', 'Ｈ' => 'H', 'Ｉ' => 'I', 'Ｊ' => 'J',
            'Ｋ' => 'K', 'Ｌ' => 'L', 'Ｍ' => 'M', 'Ｎ' => 'N', 'Ｏ' => 'O',
            'Ｐ' => 'P', 'Ｑ' => 'Q', 'Ｒ' => 'R', 'Ｓ' => 'S', 'Ｔ' => 'T',
            'Ｕ' => 'U', 'Ｖ' => 'V', 'Ｗ' => 'W', 'Ｘ' => 'X', 'Ｙ' => 'Y',
            'Ｚ' => 'Z', 'ａ' => 'a', 'ｂ' => 'b', 'ｃ' => 'c', 'ｄ' => 'd',
            'ｅ' => 'e', 'ｆ' => 'f', 'ｇ' => 'g', 'ｈ' => 'h', 'ｉ' => 'i',
            'ｊ' => 'j', 'ｋ' => 'k', 'ｌ' => 'l', 'ｍ' => 'm', 'ｎ' => 'n',
            'ｏ' => 'o', 'ｐ' => 'p', 'ｑ' => 'q', 'ｒ' => 'r', 'ｓ' => 's',
            'ｔ' => 't', 'ｕ' => 'u', 'ｖ' => 'v', 'ｗ' => 'w', 'ｘ' => 'x',
            'ｙ' => 'y', 'ｚ' => 'z',
            '（' => '(', '）' => ')', '〔' => '[', '〕' => ']', '【' => '[',
            '】' => ']', '〖' => '[', '〗' => ']', '“' => '[', '”' => ']',
            '‘' => '[', '’' => ']', '｛' => '{', '｝' => '}', '《' => '<',
            '》' => '>',
            '％' => '%', '＋' => '+', '—' => '-', '－' => '-', '～' => '-',
            '：' => ':', '。' => '.', '、' => ',', '，' => '.', '、' => '.',
            '；' => ',', '？' => '?', '！' => '!', '…' => '-', '‖' => '|',
            '”' => '"', '’' => '`', '‘' => '`', '｜' => '|', '〃' => '"',
            '　' => ' ');

        return strtr($str, $arr);
    }

    /**
     * @param $multiarr 二维数组
     * @param $index 要保留的键值
     * @return array 一维数组
     */
    public static function arrayToSingleByIndex($multiarr, $index)
    {
        $data = array();
        if (!empty($multiarr) && is_array($multiarr)) {

            foreach ($multiarr as $k => $v) {

                $data[] = $v[$index];

            }
        }
        return array_unique($data);
    }

    /**
     * 将秒转换为H:i:s
     * @param $time
     * @return string
     */
    public static function Transformation($time)
    {
        if ($time >= 3600) {
            $hours = floor($time / 3600);
            $time = $time % 3600;
            if ($hours < 10) {
                $last = '0' . $hours . ':';
            } else {
                $last = $hours . ':';
            }
        } else {
            $last = '00:';
        }
        if ($time >= 60) {
            $minutes = floor($time / 60);
            $time = $time % 60;
            if ($minutes < 10) {
                $last = $last . '0' . $minutes . ':';
            } else {
                $last = $last . $minutes . ':';
            }
        } else {
            $last = $last . '00:';
        }
        if ($time == 0) {
            $last = $last . '00';
        } elseif ($time < 10) {
            $last = $last . '0' . $time;
        } else {
            $last = $last . $time;
        }
        return $last;
    }

    /**
     * 验证用户名
     * @param string $value
     * @param int $length
     * @return boolean
     */
    public static function isNames($value, $minLen = 2, $maxLen = 20, $charset = 'ALL')
    {
        if (empty($value))
            return false;
        switch ($charset) {
            case 'EN':
                $match = '/^[_\w\d]{' . $minLen . ',' . $maxLen . '}$/iu';
                break;
            case 'CN':
                $match = '/^[_\x{4e00}-\x{9fa5}\d]{' . $minLen . ',' . $maxLen . '}$/iu';
                break;
            default:
                $match = '/^[_\w\d\x{4e00}-\x{9fa5}]{' . $minLen . ',' . $maxLen . '}$/iu';
        }
        return preg_match($match, $value);
    }

    /**
     * 验证密码
     * @param string $value
     * @param int $length
     * @return boolean
     */
    public static function isPWD($value, $minLen = 5, $maxLen = 16)
    {
        //密码必须 由6-16位数字、大写字母、小写字母组成
        $match = '/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])[a-zA-Z\d]{' . $minLen . ',' . $maxLen . '}$/';
//        $match = '/^[\\~!@#$%^&*()-_=+|{}\[\],.?\/:;\'\"\d\w]{' . $minLen . ',' . $maxLen . '}$/';
        $v = trim($value);
        if (empty($v))
            return false;
        return preg_match($match, $v);
    }

    /**
     * 验证eamil
     * @param string $value
     * @param int $length
     * @return boolean
     */
    public static function isEmail($value, $match = '/^[\w\d]+[\w\d-.]*@[\w\d-.]+\.[\w\d]{2,10}$/i')
    {
        $v = trim($value);
        if (empty($v))
            return false;
        return preg_match($match, $v);
    }

    /**
     * 验证电话号码
     * @param string $value
     * @return boolean
     */
    public static function isTelephone($value, $match = '/^0[0-9]{2,3}[-]?\d{7,8}$/')
    {
        $v = trim($value);
        if (empty($v))
            return false;
        return preg_match($match, $v);
    }

    /**
     * 验证手机
     * @param string $value
     * @param string $match
     * @return boolean
     */
    public static function isMobile($value, $match = '#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,1,2,3,4,5,6,7,8,9]{1}\d{8}$|^18[\d]{9}$#')
    {
        $v = trim($value);
        if (empty($v))
            return false;
        return preg_match($match, $v);
    }

    /**
     * 验证邮政编码
     * @param string $value
     * @param string $match
     * @return boolean
     */
    public static function isPostcode($value, $match = '/\d{6}/')
    {
        $v = trim($value);
        if (empty($v))
            return false;
        return preg_match($match, $v);
    }

    /**
     * 验证IP
     * @param string $value
     * @param string $match
     * @return boolean
     */
    public static function isIP($value, $match = '/^(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9])\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[0-9])$/')
    {
        $v = trim($value);
        if (empty($v))
            return false;
        return preg_match($match, $v);
    }

    /**
     * 验证纳税人识别号
     * @param $value
     * @param string $match
     * @return bool|int
     */
    public static function isCusCode($value, $match = '/[a-zA-Z]|[0-9]/')
    {
        $v = trim($value);
        if (empty($v))
            return false;
        return preg_match($match, $v);
    }

    /**
     * 验证身份证号码
     * @param string $value
     * @param string $match
     * @return boolean
     */
    public static function isIDcard($value, $match = '/^\d{6}((1[89])|(2\d))\d{2}((0\d)|(1[0-2]))((3[01])|([0-2]\d))\d{3}(\d|X)$/i')
    {
        $v = trim($value);
        if (empty($v))
            return false;
        else if (strlen($v) > 18)
            return false;
        return preg_match($match, $v);
    }

    /**
     * *
     * 验证URL
     * @param string $value
     * @param string $match
     * @return boolean
     */
    public static function isURL($value, $match = '/^(http:\/\/)?(https:\/\/)?([\w\d-]+\.)+[\w-]+(\/[\d\w-.\/?%&=]*)?$/')
    {
        $v = strtolower(trim($value));
        if (empty($v))
            return false;
        return preg_match($match, $v);
    }

    /**
     * *
     * 验证域名
     * @param string $value
     * @param string $match
     * @return boolean
     */
    public static function isDomain($value, $match = '/^(?:[a-z0-9](?:[-a-z0-9]*[a-z0-9])?\\.)+(?:com|net|edu|biz|gov|org|asia|in(?:t|fo)|(?-i:[a-z][a-z]))$/')
    {
        $v = strtolower(trim($value));
        if (empty($v))
            return false;
        return preg_match($match, $v);
    }

    /**
     * 邮件发送
     *
     * @param: $name[string]        接收人姓名
     * @param: $email[string]       接收人邮件地址
     * @param: $subject[string]     邮件标题
     * @param: $content[string]     邮件内容
     * @param: $type[int]           0 普通邮件， 1 HTML邮件
     * @param: $notification[bool]  true 要求回执， false 不用回执
     *
     * @return boolean
     */
    public static function sendMail($name, $email, $subject, $content, $type = 0, $notification = false)
    {
        $config = Kohana::$config->load('mail');
        if (empty($config) || !isset($config['mail_service'], $config['smtp_ssl'], $config['smtp_host'], $config['smtp_port']
                , $config['smtp_user'], $config['smtp_pass'], $config['smtp_mail'], $config['mail_charset'], $config['smtp_username'])
        ) {
            return false;
        }

        $charset = strtolower($config['mail_charset']);
        /* 如果邮件编码不是OA_CHARSET，创建字符集转换对象，转换编码 */
        if ($charset != 'utf8') {
            $name = iconv($charset, 'utf8//IGNORE', $name);
            $subject = iconv($charset, 'utf8//IGNORE', $subject);
            $content = iconv($charset, 'utf8//IGNORE', $content);
        }
        if ($charset == 'utf8') $charset = 'utf-8';
        /**
         * 使用mail函数发送邮件
         */
        if ($config['mail_service'] == 0 && function_exists('mail')) {
            /* 邮件的头部信息 */
            $content_type = ($type == 0) ? 'Content-Type: text/plain; charset=' . $charset : 'Content-Type: text/html; charset=' . $charset;
            $headers = array();
            $headers[] = 'From: "' . '=?' . $charset . '?B?' . base64_encode($config['smtp_username']) . '?=' . '" <' .
                $config['smtp_mail'] . '>';
            $headers[] = $content_type . '; format=flowed';
            if ($notification) {
                $headers[] = 'Disposition-Notification-To: ' . '=?' . $charset . '?B?' . base64_encode($config['smtp_username']) . '?='
                    . '" <' . $config['smtp_mail'] . '>';
            }

            $res = @mail($email, '=?' . $charset . '?B?' . base64_encode($subject) . '?=', $content, implode("\r\n", $headers));

            if (!$res) {
                return false;
            } else {
                return true;
            }
        } /**
         * 使用smtp服务发送邮件
         */
        else {
            /* 邮件的头部信息 */
            $content_type = ($type == 0) ?
                'Content-Type: text/plain; charset=' . $charset : 'Content-Type: text/html; charset=' . $charset;
            $content = base64_encode($content);
            $headers = array();
            $headers[] = 'Date: ' . date('D, j M Y H:i:s', TIMENOW);//东八区时间
            $headers[] = 'To: "' . '=?' . $charset . '?B?' . base64_encode($name) . '?=' . '" <' . $email . '>';
            $headers[] = 'From: "' . '=?' . $charset . '?B?' . base64_encode($config['smtp_username']) . '?=' . '" <'
                .
                $config['smtp_mail'] . '>';
            $headers[] = 'Subject: ' . '=?' . $charset . '?B?' . base64_encode($subject) . '?=';
            $headers[] = $content_type . '; format=flowed';
            $headers[] = 'Content-Transfer-Encoding: base64';
            $headers[] = 'Content-Disposition: inline';
            if ($notification) {
                $headers[] = 'Disposition-Notification-To: ' . '=?' . $charset . '?B?' . base64_encode($config['smtp_username']) . '?='
                    . '" <' . $config['smtp_mail'] . '>';
            }
            /* 获得邮件服务器的参数设置 */
            $params['host'] = $config['smtp_host'];
            $params['port'] = $config['smtp_port'];
            $params['user'] = $config['smtp_user'];
            $params['pass'] = $config['smtp_pass'];

            if (empty($params['host']) || empty($params['port'])) {
                // 如果没有设置主机和端口直接返回 false
                return false;
            } else {
                // 发送邮件
                if (!function_exists('fsockopen')) {
                    //如果fsockopen被禁用，直接返回
                    return false;
                }

                static $smtp;

                $send_params['recipients'] = $email;
                $send_params['headers'] = $headers;
                $send_params['from'] = $config['smtp_mail'];
                $send_params['body'] = $content;


                if (!isset($smtp)) {
                    $smtp = new Library_Smtpmail($params);
                }
                if ($smtp->connect() && $smtp->send($send_params)) {
                    return true;
                } else {
                    $err_msg = $smtp->error_msg();
                    return false;
                }
            }
        }
    }


    /**
     * 获取客户端IP
     *
     * @access      public
     *
     * @return string
     **/
    public static function getClientIP()
    {
        if (!empty($_SERVER["HTTP_CLIENT_IP"])) $cip = $_SERVER["HTTP_CLIENT_IP"];
        else if (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) $cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        else if (!empty($_SERVER["REMOTE_ADDR"])) $cip = $_SERVER["REMOTE_ADDR"];
        else $cip = '0.0.0.0';
        return $cip;
    }

    /**
     * 根据当前时间和随机数生成16位的流水号
     * @param $loginid
     * @return string
     */
    public static function createSn()
    {
        $a = TIMENOW;
        return $a . Text::random('numeric', 16 - strlen($a));
    }


    /**
     * 匹配字母、数字、下划线，检测合同编号等是否正确
     * 返回值为0或1
     */
    public static function checkSn($sn)
    {
        return preg_match('#^[0-9a-zA-Z_-]+$#', $sn);
    }

    /**
     * 获取客服座机号后8位（不含区号）
     * @param $CallerNo
     * @return string
     */
    public static function checkCallerNo($CallerNo)
    {
        if ($CallerNo >= 8) {
            $CallerNo = substr($CallerNo, -8);
        }
        return $CallerNo;
    }

    /**
     * 汉字转为拼音的函数
     * @param $_String
     * @param string $_Code
     * @return mixed
     */
    public static function Pinyin($_String, $_Code = 'UTF8')
    { //GBK页面可改为gb2312，其他随意填写为UTF8
        $_DataKey = "a|ai|an|ang|ao|ba|bai|ban|bang|bao|bei|ben|beng|bi|bian|biao|bie|bin|bing|bo|bu|ca|cai|can|cang|cao|ce|ceng|cha" .
            "|chai|chan|chang|chao|che|chen|cheng|chi|chong|chou|chu|chuai|chuan|chuang|chui|chun|chuo|ci|cong|cou|cu|" .
            "cuan|cui|cun|cuo|da|dai|dan|dang|dao|de|deng|di|dian|diao|die|ding|diu|dong|dou|du|duan|dui|dun|duo|e|en|er" .
            "|fa|fan|fang|fei|fen|feng|fo|fou|fu|ga|gai|gan|gang|gao|ge|gei|gen|geng|gong|gou|gu|gua|guai|guan|guang|gui" .
            "|gun|guo|ha|hai|han|hang|hao|he|hei|hen|heng|hong|hou|hu|hua|huai|huan|huang|hui|hun|huo|ji|jia|jian|jiang" .
            "|jiao|jie|jin|jing|jiong|jiu|ju|juan|jue|jun|ka|kai|kan|kang|kao|ke|ken|keng|kong|kou|ku|kua|kuai|kuan|kuang" .
            "|kui|kun|kuo|la|lai|lan|lang|lao|le|lei|leng|li|lia|lian|liang|liao|lie|lin|ling|liu|long|lou|lu|lv|luan|lue" .
            "|lun|luo|ma|mai|man|mang|mao|me|mei|men|meng|mi|mian|miao|mie|min|ming|miu|mo|mou|mu|na|nai|nan|nang|nao|ne" .
            "|nei|nen|neng|ni|nian|niang|niao|nie|nin|ning|niu|nong|nu|nv|nuan|nue|nuo|o|ou|pa|pai|pan|pang|pao|pei|pen" .
            "|peng|pi|pian|piao|pie|pin|ping|po|pu|qi|qia|qian|qiang|qiao|qie|qin|qing|qiong|qiu|qu|quan|que|qun|ran|rang" .
            "|rao|re|ren|reng|ri|rong|rou|ru|ruan|rui|run|ruo|sa|sai|san|sang|sao|se|sen|seng|sha|shai|shan|shang|shao|" .
            "she|shen|sheng|shi|shou|shu|shua|shuai|shuan|shuang|shui|shun|shuo|si|song|sou|su|suan|sui|sun|suo|ta|tai|" .
            "tan|tang|tao|te|teng|ti|tian|tiao|tie|ting|tong|tou|tu|tuan|tui|tun|tuo|wa|wai|wan|wang|wei|wen|weng|wo|wu" .
            "|xi|xia|xian|xiang|xiao|xie|xin|xing|xiong|xiu|xu|xuan|xue|xun|ya|yan|yang|yao|ye|yi|yin|ying|yo|yong|you" .
            "|yu|yuan|yue|yun|za|zai|zan|zang|zao|ze|zei|zen|zeng|zha|zhai|zhan|zhang|zhao|zhe|zhen|zheng|zhi|zhong|" .
            "zhou|zhu|zhua|zhuai|zhuan|zhuang|zhui|zhun|zhuo|zi|zong|zou|zu|zuan|zui|zun|zuo";
        $_DataValue = "-20319|-20317|-20304|-20295|-20292|-20283|-20265|-20257|-20242|-20230|-20051|-20036|-20032|-20026|-20002|-19990" .
            "|-19986|-19982|-19976|-19805|-19784|-19775|-19774|-19763|-19756|-19751|-19746|-19741|-19739|-19728|-19725" .
            "|-19715|-19540|-19531|-19525|-19515|-19500|-19484|-19479|-19467|-19289|-19288|-19281|-19275|-19270|-19263" .
            "|-19261|-19249|-19243|-19242|-19238|-19235|-19227|-19224|-19218|-19212|-19038|-19023|-19018|-19006|-19003" .
            "|-18996|-18977|-18961|-18952|-18783|-18774|-18773|-18763|-18756|-18741|-18735|-18731|-18722|-18710|-18697" .
            "|-18696|-18526|-18518|-18501|-18490|-18478|-18463|-18448|-18447|-18446|-18239|-18237|-18231|-18220|-18211" .
            "|-18201|-18184|-18183|-18181|-18012|-17997|-17988|-17970|-17964|-17961|-17950|-17947|-17931|-17928|-17922" .
            "|-17759|-17752|-17733|-17730|-17721|-17703|-17701|-17697|-17692|-17683|-17676|-17496|-17487|-17482|-17468" .
            "|-17454|-17433|-17427|-17417|-17202|-17185|-16983|-16970|-16942|-16915|-16733|-16708|-16706|-16689|-16664" .
            "|-16657|-16647|-16474|-16470|-16465|-16459|-16452|-16448|-16433|-16429|-16427|-16423|-16419|-16412|-16407" .
            "|-16403|-16401|-16393|-16220|-16216|-16212|-16205|-16202|-16187|-16180|-16171|-16169|-16158|-16155|-15959" .
            "|-15958|-15944|-15933|-15920|-15915|-15903|-15889|-15878|-15707|-15701|-15681|-15667|-15661|-15659|-15652" .
            "|-15640|-15631|-15625|-15454|-15448|-15436|-15435|-15419|-15416|-15408|-15394|-15385|-15377|-15375|-15369" .
            "|-15363|-15362|-15183|-15180|-15165|-15158|-15153|-15150|-15149|-15144|-15143|-15141|-15140|-15139|-15128" .
            "|-15121|-15119|-15117|-15110|-15109|-14941|-14937|-14933|-14930|-14929|-14928|-14926|-14922|-14921|-14914" .
            "|-14908|-14902|-14894|-14889|-14882|-14873|-14871|-14857|-14678|-14674|-14670|-14668|-14663|-14654|-14645" .
            "|-14630|-14594|-14429|-14407|-14399|-14384|-14379|-14368|-14355|-14353|-14345|-14170|-14159|-14151|-14149" .
            "|-14145|-14140|-14137|-14135|-14125|-14123|-14122|-14112|-14109|-14099|-14097|-14094|-14092|-14090|-14087" .
            "|-14083|-13917|-13914|-13910|-13907|-13906|-13905|-13896|-13894|-13878|-13870|-13859|-13847|-13831|-13658" .
            "|-13611|-13601|-13406|-13404|-13400|-13398|-13395|-13391|-13387|-13383|-13367|-13359|-13356|-13343|-13340" .
            "|-13329|-13326|-13318|-13147|-13138|-13120|-13107|-13096|-13095|-13091|-13076|-13068|-13063|-13060|-12888" .
            "|-12875|-12871|-12860|-12858|-12852|-12849|-12838|-12831|-12829|-12812|-12802|-12607|-12597|-12594|-12585" .
            "|-12556|-12359|-12346|-12320|-12300|-12120|-12099|-12089|-12074|-12067|-12058|-12039|-11867|-11861|-11847" .
            "|-11831|-11798|-11781|-11604|-11589|-11536|-11358|-11340|-11339|-11324|-11303|-11097|-11077|-11067|-11055" .
            "|-11052|-11045|-11041|-11038|-11024|-11020|-11019|-11018|-11014|-10838|-10832|-10815|-10800|-10790|-10780" .
            "|-10764|-10587|-10544|-10533|-10519|-10331|-10329|-10328|-10322|-10315|-10309|-10307|-10296|-10281|-10274" .
            "|-10270|-10262|-10260|-10256|-10254";
        $_TDataKey = explode('|', $_DataKey);
        $_TDataValue = explode('|', $_DataValue);
        $_Data = array_combine($_TDataKey, $_TDataValue);
        arsort($_Data);
        reset($_Data);
        if ($_Code != 'gb2312') $_String = self::_U2_Utf8_Gb($_String);
        $_Res = '';
        for ($i = 0; $i < strlen($_String); $i++) {
            $_P = ord(substr($_String, $i, 1));
            if ($_P > 160) {
                $_Q = ord(substr($_String, ++$i, 1));
                $_P = $_P * 256 + $_Q - 65536;
            }
            $_Res .= self::_Pinyin($_P, $_Data);
        }
        return preg_replace("/[^a-z0-9]*/", '', $_Res);
    }

    public static function _Pinyin($_Num, $_Data)
    {
        if ($_Num > 0 && $_Num < 160) {
            return chr($_Num);
        } elseif ($_Num < -20319 || $_Num > -10247) {
            return '';
        } else {
            foreach ($_Data as $k => $v) {
                if ($v <= $_Num) break;
            }
            return $k;
        }
    }

    public static function _U2_Utf8_Gb($_C)
    {
        $_String = '';
        if ($_C < 0x80) {
            $_String .= $_C;
        } elseif ($_C < 0x800) {
            $_String .= chr(0xC0 | $_C >> 6);
            $_String .= chr(0x80 | $_C & 0x3F);
        } elseif ($_C < 0x10000) {
            $_String .= chr(0xE0 | $_C >> 12);
            $_String .= chr(0x80 | $_C >> 6 & 0x3F);
            $_String .= chr(0x80 | $_C & 0x3F);
        } elseif ($_C < 0x200000) {
            $_String .= chr(0xF0 | $_C >> 18);
            $_String .= chr(0x80 | $_C >> 12 & 0x3F);
            $_String .= chr(0x80 | $_C >> 6 & 0x3F);
            $_String .= chr(0x80 | $_C & 0x3F);
        }
        return iconv('UTF-8', 'GB2312', $_String);
    }

    /*
   函数名称：ipCity
   参数说明：$userip——用户IP地址
   函数功能：PHP通过IP地址判断用户所在城市
   author:hush
   */
    public static function ipCity($userip, $charset = 'utf-8')
    {
        //IP数据库路径，这里用的是QQ IP数据库 20110405 纯真版
        $dat_path = APPPATH . 'classes' . DIRECTORY_SEPARATOR . 'Library' . DIRECTORY_SEPARATOR . 'qqwry.dat';
        //判断IP地址是否有效
        if (!preg_match("/^([0-9]{1,3}.){3}[0-9]{1,3}$/", $userip)) {
            //return 'IP Address Invalid';
            return false;
        }
        //打开IP数据库
        if (!$fd = @fopen($dat_path, 'rb')) {
            //return 'IP data file not exists or access denied';
            return false;
        }
        //explode函数分解IP地址，运算得出整数形结果
        $userip = explode('.', $userip);
        $useripNum = $userip[0] * 16777216 + $userip[1] * 65536 + $userip[2] * 256 + $userip[3];
        //获取IP地址索引开始和结束位置
        $DataBegin = fread($fd, 4);
        $DataEnd = fread($fd, 4);
        $useripbegin = implode('', unpack('L', $DataBegin));
        if ($useripbegin < 0) $useripbegin += pow(2, 32);
        $useripend = implode('', unpack('L', $DataEnd));
        if ($useripend < 0) $useripend += pow(2, 32);
        $useripAllNum = ($useripend - $useripbegin) / 7 + 1;
        $BeginNum = 0;
        $EndNum = $useripAllNum;
        $userip1num = 0;
        $userip2num = 0;
        $useripAddr2 = '';
        $useripAddr1 = '';
        //使用二分查找法从索引记录中搜索匹配的IP地址记录
        while ($userip1num > $useripNum || $userip2num < $useripNum) {
            $Middle = intval(($EndNum + $BeginNum) / 2);
            //偏移指针到索引位置读取4个字节
            fseek($fd, $useripbegin + 7 * $Middle);
            $useripData1 = fread($fd, 4);
            if (strlen($useripData1) < 4) {
                fclose($fd);
                //return 'File Error';
                return false;
            }
            //提取出来的数据转换成长整形，如果数据是负数则加上2的32次幂
            $userip1num = implode('', unpack('L', $useripData1));
            if ($userip1num < 0) $userip1num += pow(2, 32);
            //提取的长整型数大于我们IP地址则修改结束位置进行下一次循环
            if ($userip1num > $useripNum) {
                $EndNum = $Middle;
                continue;
            }
            //取完上一个索引后取下一个索引
            $DataSeek = fread($fd, 3);
            if (strlen($DataSeek) < 3) {
                fclose($fd);
                //return 'File Error';
                return false;
            }
            $DataSeek = implode('', unpack('L', $DataSeek . chr(0)));
            fseek($fd, $DataSeek);
            $useripData2 = fread($fd, 4);
            if (strlen($useripData2) < 4) {
                fclose($fd);
                //return 'File Error';
                return false;
            }
            $userip2num = implode('', unpack('L', $useripData2));
            if ($userip2num < 0) $userip2num += pow(2, 32);
            //找不到IP地址对应城市
            if ($userip2num < $useripNum) {
                if ($Middle == $BeginNum) {
                    fclose($fd);
                    // return 'No Data';
                    return false;
                }
                $BeginNum = $Middle;
            }
        }
        $useripFlag = fread($fd, 1);
        if ($useripFlag == chr(1)) {
            $useripSeek = fread($fd, 3);
            if (strlen($useripSeek) < 3) {
                fclose($fd);
                //return 'System Error';
                return false;
            }
            $useripSeek = implode('', unpack('L', $useripSeek . chr(0)));
            fseek($fd, $useripSeek);
            $useripFlag = fread($fd, 1);
        }
        if ($useripFlag == chr(2)) {
            $AddrSeek = fread($fd, 3);
            if (strlen($AddrSeek) < 3) {
                fclose($fd);
                // return 'System Error';
                return false;
            }
            $useripFlag = fread($fd, 1);
            if ($useripFlag == chr(2)) {
                $AddrSeek2 = fread($fd, 3);
                if (strlen($AddrSeek2) < 3) {
                    fclose($fd);
                    //return 'System Error';
                    return false;
                }
                $AddrSeek2 = implode('', unpack('L', $AddrSeek2 . chr(0)));
                fseek($fd, $AddrSeek2);
            } else {
                fseek($fd, -1, SEEK_CUR);
            }
            while (($char = fread($fd, 1)) != chr(0))
                $useripAddr2 .= $char;
            $AddrSeek = implode('', unpack('L', $AddrSeek . chr(0)));
            fseek($fd, $AddrSeek);
            while (($char = fread($fd, 1)) != chr(0))
                $useripAddr1 .= $char;
        } else {
            fseek($fd, -1, SEEK_CUR);
            while (($char = fread($fd, 1)) != chr(0))
                $useripAddr1 .= $char;
            $useripFlag = fread($fd, 1);
            if ($useripFlag == chr(2)) {
                $AddrSeek2 = fread($fd, 3);
                if (strlen($AddrSeek2) < 3) {
                    fclose($fd);
                    // return 'System Error';
                    return false;
                }
                $AddrSeek2 = implode('', unpack('L', $AddrSeek2 . chr(0)));
                fseek($fd, $AddrSeek2);
            } else {
                fseek($fd, -1, SEEK_CUR);
            }
            while (($char = fread($fd, 1)) != chr(0)) {
                $useripAddr2 .= $char;
            }
        }
        fclose($fd);
        //返回IP地址对应的城市结果
//        if (preg_match('/http/i', $useripAddr2)) {
//            $useripAddr2 = '';
//        }
//        $useripaddr = "$useripAddr1 $useripAddr2";
//        $useripaddr = preg_replace('/CZ88.Net/is', '', $useripaddr);
//        $useripaddr = preg_replace('/^s*/is', '', $useripaddr);
//        $useripaddr = preg_replace('/s*$/is', '', $useripaddr);
//        if (preg_match('/http/i', $useripaddr) || $useripaddr == '') {
//            $useripaddr = '暂无对应城市';
//        }
        // return $useripaddr;
        if ($charset != 'gbk') {
            $useripAddr1 = iconv('gbk', $charset, $useripAddr1);
        }
        return $useripAddr1;


    }

    /**
     * curl传送数据
     * @param $url
     * @param $data
     * @param bool $post
     * @return mixed
     */
    public static function curl_send($url, $data = array(), $post = true, $userpass = '')
    {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
        curl_setopt($ch, CURLOPT_TIMEOUT, 5000);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($post) {
            curl_setopt($ch, CURLOPT_POST, true);
            if (is_array($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
        }
        if (!empty($userpass)) {
            curl_setopt($ch, CURLOPT_USERPWD, $userpass);
        }
        curl_setopt($ch, CURLOPT_HEADER, false);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    /**
     * 保存两位小数
     * @param $float
     * @param int $perm
     * @return string
     */
    public static function formatfloat($float, $perm = 2)
    {
        return bcadd($float, 0.00, $perm);
    }

    /**
     * 比较数字大小
     * @param $leftnum
     * @param $rightnum
     * @param $scale
     */
    public static function bccomp($leftnum, $rightnum, $scale = 2)
    {

        $leftnum = sprintf("%0.$scale" . "f", $leftnum);
        $rightnum = sprintf("%0.$scale" . "f", $rightnum);
        return bccomp($leftnum, $rightnum, $scale);
    }

    /**
     * 获取当前URL，含GET参数
     * @param Request $req
     * @param null $protocol
     * @return string
     */
    public static function fullUrl(Request $req, $protocol = NULL)
    {
        return URL::site($req->url(), $protocol) . '?' . $_SERVER['QUERY_STRING'];
    }

    public static function gmt_iso8601($time)
    {
        $dtStr = date("c", $time);
        $mydatetime = new DateTime($dtStr);
        $expiration = $mydatetime->format(DateTime::ISO8601);
        $pos = strpos($expiration, '+');
        $expiration = substr($expiration, 0, $pos);
        return $expiration . "Z";
    }

    /**
     * 获取部门下拉菜单
     * @param int $selectid
     * @return string
     */
    public static function getDepartmentOptions($selectid = 0, $pid = 0, $deletid = 0)
    {
        $department_mode = new Model_System_Department();
        $cate_tree = $department_mode->getDepartCate($pid, 1, false);
        $options = '<option value="0">---------</option>';
        foreach ($cate_tree as $cat) {
            if ($deletid > 0 && $deletid == $cat['id']) continue;
            if ($selectid > 0 && $selectid == $cat['id']) {
                $selected = 'selected="true"';
            } else {
                $selected = '';
            }
            if ($cat['depath'] == 1) {
                $options .= sprintf('<option value="%d" %s>├ %s</option>', $cat['id'], $selected, $cat['name']);
            } else {
                $options .= sprintf('<option value="%d" %s>%s├ %s</option>', $cat['id'], $selected,
                    str_repeat('&nbsp;', $cat['depath'] * 2), $cat['name']);
            }
        }
        return $options;
    }

    /**
     * 生成全球唯一标识(32位)
     */
    public static function uuid()
    {
        // The field names refer to RFC 4122 section 4.1.2

        return sprintf('%04x%04x%04x%03x4%04x%04x%04x%04x',
            mt_rand(0, 65535), mt_rand(0, 65535), // 32 bits for "time_low"
            mt_rand(0, 65535), // 16 bits for "time_mid"
            mt_rand(0, 4095), // 12 bits before the 0100 of (version) 4 for "time_hi_and_version"
            bindec(substr_replace(sprintf('%016b', mt_rand(0, 65535)), '01', 6, 2)),
            // 8 bits, the last two of which (positions 6 and 7) are 01, for "clk_seq_hi_res"
            // (hence, the 2nd hex digit after the 3rd hyphen can only be 1, 5, 9 or d)
            // 8 bits for "clk_seq_low"
            mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535) // 48 bits for "node"
        );
    }

    /**
     * 比较两个日期相差天数,进一法(只取正整数)
     */
    public static function diffBetweenTwoDays($day1, $day2)
    {
        $second1 = strtotime($day1);
        $second2 = strtotime($day2);

//        if ($second1 < $second2) {
//            $tmp = $second2;
//            $second2 = $second1;
//            $second1 = $tmp;
//        }
        return ceil(($second1 - $second2) / 86400);
    }

    /**
     * 判断字符串是否为自然数
     */
    public static function isInt($str)
    {
        return preg_match('/^[0-9]+$/', $str);
    }

    /**
     * 判断字符串是否为浮点数(不小于0)
     */
    public static function isFloat($str)
    {
        return preg_match('/^([0-9]|([1-9][0-9]*))(.[0-9]+)?$/', $str);
    }

    /*原生态sql转化*/
    public static function createsql(&$sql, $colum, $arrids, $type = 0)
    {
        $curareaids = self::arridssort($arrids);
        if ($curareaids) {
            if ($type > 0) {
                $sql .= ' or (';
                foreach ($curareaids as $key => $val) {
                    if ($key > 0) {
                        $sql .= ' OR ';
                    }
                    if (isset($val['startnum']) && isset($val['endnum'])) {
                        if ($val['startnum'] == $val['endnum']) {
                            $sql .= ' (' . $colum . '=' . $val['startnum'] . ' )';
                        } else {
                            $sql .= ' (' . $colum . ' between ' . $val['startnum'] . ' AND ' . $val['endnum'] . ' )';
                        }
                    }
                }
                $sql .= '  )';
            } else {
                $sql .= ' and (';
                foreach ($curareaids as $key => $val) {
                    if ($key > 0) {
                        $sql .= ' OR ';
                    }
                    if (isset($val['startnum']) && isset($val['endnum'])) {
                        if ($val['startnum'] == $val['endnum']) {
                            $sql .= ' (' . $colum . '=' . $val['startnum'] . ' )';
                        } else {
                            $sql .= ' (' . $colum . ' between ' . $val['startnum'] . ' AND ' . $val['endnum'] . ' )';
                        }
                    }
                }
                $sql .= '  )';
            }
        }
        return $sql;
    }

    /*原生态sql转化(NOT BETWEEN)*/
    public static function createsqlr(&$sql, $colum, $arrids, $type = 0)
    {
        $curareaids = self::arridssort($arrids);
        if ($curareaids) {
            if ($type > 0) {
                $sql .= ' or (';
                foreach ($curareaids as $key => $val) {
                    if ($key > 0) {
                        $sql .= ' OR ';
                    }
                    if (isset($val['startnum']) && isset($val['endnum'])) {
                        if ($val['startnum'] == $val['endnum']) {
                            $sql .= ' (' . $colum . '!=' . $val['startnum'] . ' )';
                        } else {
                            $sql .= ' (' . $colum . ' not between ' . $val['startnum'] . ' AND ' . $val['endnum'] . ' )';
                        }

                    }
                }
                $sql .= '  )';
            } else {
                $sql .= ' and (';
                foreach ($curareaids as $key => $val) {
                    if ($key > 0) {
                        $sql .= ' OR ';
                    }
                    if (isset($val['startnum']) && isset($val['endnum'])) {
                        if ($val['startnum'] == $val['endnum']) {
                            $sql .= ' (' . $colum . '!=' . $val['startnum'] . ' )';
                        } else {
                            $sql .= ' (' . $colum . '  not between ' . $val['startnum'] . ' AND ' . $val['endnum'] . ' )';
                        }

                    }
                }
                $sql .= '  )';
            }
        }
        return $sql;
    }

    /*转化数组排序方法*/
    public static function arridssort($arrids)
    {
        if ($arrids) {
            $arrids = array_unique($arrids);
            asort($arrids);
            $arrids = array_merge($arrids);
            $newarrids = array();
            $j = 0;
            for ($i = 0; $i < count($arrids); $i++) {
                if (isset($arrids[$i])) {
                    if (!isset($newarrids[$j])) {
                        $newarrids[$j]['startnum'] = $arrids[$i];
                        $newarrids[$j]['endnum'] = $arrids[$i];
                    } else {
                        if ((int)$arrids[$i] - 1 != (int)$arrids[$i - 1]) {
                            $j++;
                            $newarrids[$j]['startnum'] = $arrids[$i];
                            $newarrids[$j]['endnum'] = $arrids[$i];
                        } else {
                            $newarrids[$j]['endnum'] = $arrids[$i];
                        }
                    }
                }
            }
            return $newarrids;
        } else {
            return '';
        }
    }

    /**
     * array_column 用于获取二维数组中的元素(PHP 5.5新增函数)
     * 自定义用于兼容5.5以下的版本
     *
     **/
    public static function i_array_column($input, $columnKey, $indexKey = null)
    {
        if (!function_exists('array_column')) {
            $columnKeyIsNumber = (is_numeric($columnKey)) ? true : false;
            $indexKeyIsNull = (is_null($indexKey)) ? true : false;
            $indexKeyIsNumber = (is_numeric($indexKey)) ? true : false;
            $result = array();
            foreach ((array)$input as $key => $row) {
                if ($columnKeyIsNumber) {
                    $tmp = array_slice($row, $columnKey, 1);
                    $tmp = (is_array($tmp) && !empty($tmp)) ? current($tmp) : null;
                } else {
                    $tmp = isset($row[$columnKey]) ? $row[$columnKey] : null;
                }
                if (!$indexKeyIsNull) {
                    if ($indexKeyIsNumber) {
                        $key = array_slice($row, $indexKey, 1);
                        $key = (is_array($key) && !empty($key)) ? current($key) : null;
                        $key = is_null($key) ? 0 : $key;
                    } else {
                        $key = isset($row[$indexKey]) ? $row[$indexKey] : 0;
                    }
                }
                $result[$key] = $tmp;
            }
            return $result;
        } else {
            return array_column($input, $columnKey, $indexKey);
        }
    }

    /**
     * 生成10位随机字符串
     * @param int $length
     * @return string
     * @author wdy
     */
    public static function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * 替换一段字符串中两个及以上空格为一个空格
     * @param string $str
     * @return string
     * @author wdy
     */
    public static function getSingleSpaceStr($str = '')
    {
        return preg_replace("/\s+/", ' ', $str);
    }

    /**
     * 获取起止日期内的所有日期
     * @param $startdate
     * @param $enddate
     * @return array
     */
    public static function getDateFromRange($startdate, $enddate)
    {
        $dt_start = strtotime($startdate);
        $dt_end = strtotime($enddate);
        $date = [];
        while ($dt_start <= $dt_end) {
            $date[] = date('Y-m-d', $dt_start);
            $dt_start = strtotime('+1 day', $dt_start);
        }
        return $date;
    }

    /**
     * 将字符串转换成二进制
     * @param $str
     * @return string
     */
    public static function StrToBin($str)
    {
        //1.列出每个字符
        $arr = preg_split('/(?<!^)(?!$)/u', $str);
        //2.unpack字符
        foreach ($arr as &$v) {
            $temp = unpack('H*', $v);
            $v = base_convert($temp[1], 16, 2);
            unset($temp);
        }
        return join(' ', $arr);
    }

    /**
     * 将二进制转换成字符串
     * @param $str
     * @return string
     */
    public static function BinToStr($str){
        $arr = explode(' ', $str);
        foreach ($arr as &$v) {
            $v = pack("H" . strlen(base_convert($v, 2, 16)), base_convert($v, 2, 16));
        }
        return join('', $arr);
    }
}
