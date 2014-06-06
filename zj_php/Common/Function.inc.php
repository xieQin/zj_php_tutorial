<?php

function my_log($obj, $tag = "") {
    print_r("<br/>-----LOG({$tag})-----------------<br/>");
    print_r($obj);
    print_r("<br/>---------------------------------<br/>");
}

/**
 * 截取字符
 * */
function get_word($content, $length, $more = 1) {
    if (WEB_LANG == 'utf-8') {
        $content = get_utf8_word($content, $length, $more);
        return $content;
    }

    if (WEB_LANG == 'big5') {
        $more = 1; //不这样的话.截取字符容易使用页面乱码
    }

    if (!$more) {
        $length = $length + 2;
    }
    if ($length > 10) {
        $length = $length - 2;
    }
    if ($length && strlen($content) > $length) {
        $num = 0;
        for ($i = 0; $i < $length - 1; $i++) {
            if (ord($content[$i]) > 127) {
                $num++;
            }
        }
        $num % 2 == 1 ? $content = substr($content, 0, $length - 2) : $content = substr($content, 0, $length - 1);
        $more && $content.='..';
    }
    return $content;
}

/**
 * UTF8截取字符
 * */
function get_utf8_word($string, $length = 80, $more = 1, $etc = '..') {
    $strcut = '';
    $strLength = 0;
    $width = 0;
    if (strlen($string) > $length) {
        //将$length换算成实际UTF8格式编码下字符串的长度
        for ($i = 0; $i < $length; $i++) {
            if ($strLength >= strlen($string)) {
                break;
            }
            if ($width >= $length) {
                break;
            }
            //当检测到一个中文字符时
            if (ord($string[$strLength]) > 127) {
                $strLength += 3;
                $width += 2;              //大概按一个汉字宽度相当于两个英文字符的宽度
            } else {
                $strLength += 1;
                $width += 1;
            }
        }
        return substr($string, 0, $strLength) . $etc;
    } else {
        return $string;
    }
}

/**
 * 过滤安全字符
 * */
function filtrate($msg) {
    //$msg = str_replace('&','&amp;',$msg);
    //$msg = str_replace(' ','&nbsp;',$msg);
    $msg = str_replace('"', '&quot;', $msg);
    $msg = str_replace("'", '&#39;', $msg);
    $msg = str_replace("<", "&lt;", $msg);
    $msg = str_replace(">", "&gt;", $msg);
    $msg = str_replace("\t", "   &nbsp;  &nbsp;", $msg);
    //$msg = str_replace("\r","",$msg);
    $msg = str_replace("   ", " &nbsp; ", $msg);
    return $msg;
}

/* 过滤不健康的字 */

function replace_bad_word($str) {
    global $Limitword;
    @include_once(ZJ_PHP_PATH . "Resource/limitword.php");
    foreach ($Limitword AS $old => $new) {
        strlen($old) > 2 && $str = str_replace($old, trim($new), $str);
    }
    return $str;
}

/**
 * 取得随机字符
 * */
function rands($length, $strtolower = 1) {
    $hash = '';
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
    $max = strlen($chars) - 1;
    mt_srand((double) microtime() * 1000000);
    for ($i = 0; $i < $length; $i++) {
        $hash .= $chars[mt_rand(0, $max)];
    }
    if ($strtolower == 1) {
        $hash = strtolower($hash);
    }
    return $hash;
}

/**
 * 简体中文转UTF8编码
 * */
function gbk2utf8($text) {
    $fp = fopen(ZJ_PHP_PATH . "Resource/gbkcode/gbk2utf8.table", "r");
    while (!feof($fp)) {
        list($gb, $utf8) = fgetcsv($fp, 10);
        $charset[$gb] = $utf8;
    }
    fclose($fp);  //以上读取对照表到数组备用wl__hd_sg2_02.gif
    //提取文本中的成分，汉字为一个元素，连续的非汉字为一个元素
    preg_match_all("/(?:[\x80-\xff].)|[\x01-\x7f]+/", $text, $tmp);
    $tmp = $tmp[0];
    //分离出汉字
    $ar = array_intersect($tmp, array_keys($charset));
    //替换汉字编码
    foreach ($ar as $k => $v)
        $tmp[$k] = $charset[$v];
    //返回换码后的串
    return join('', $tmp);
}

/**
 * 加密与解密函数
 * */
function mymd5($string, $action = "EN", $rand = '') { //字符串加密和解密 
    global $webdb;
    $secret_string = "mydb5" . $rand . '5*j,.^&;?.%#@!'; //绝密字符串,可以任意设定 
    if (!is_string($string)) {
        $string = strval($string);
    }
    if ($string === "")
        return "";
    if ($action == "EN")
        $md5code = substr(md5($string), 8, 10);
    else {
        $md5code = substr($string, -10);
        $string = substr($string, 0, strlen($string) - 10);
    }
    //$key = md5($md5code.$_SERVER["HTTP_USER_AGENT"].$secret_string);
    $key = md5($md5code . $secret_string);
    $string = ($action == "EN" ? $string : base64_decode($string));
    $len = strlen($key);
    $code = "";
    for ($i = 0; $i < strlen($string); $i++) {
        $k = $i % $len;
        $code .= $string[$i] ^ $key[$k];
    }
    $code = ($action == "DE" ? (substr(md5($code), 8, 10) == $md5code ? $code : NULL) : base64_encode($code) . "$md5code");
    return $code;
}

function pwd_md5($code) {
    //return md5($code);
    return $code;
}

function set_cookie($name, $value, $cktime = 0) {
    global $webdb, $timestamp;
    if ($cktime != 0) {
        $cktime = $timestamp + $cktime;
    }
    if ($value == '') {
        $cktime = $timestamp - 31536000;
    }
    $S = $_SERVER['SERVER_PORT'] == '443' ? 1 : 0;
    if ($webdb[cookiePath]) {
        $path = $webdb[cookiePath];
    } else {
        $path = "/";
    }
    $domain = $webdb[cookieDomain];
    setCookie("$webdb[cookiePre]$name", $value, $cktime, $path, $domain, $S);
}

function get_cookie($name) {
    global $webdb;
    return $_COOKIE["$webdb[cookiePre]$name"];
}

//sock方式打开远程文件
function sockOpenUrl($url, $method = 'GET', $postValue = '') {
    $method = strtoupper($method);
    if (!$url) {
        return '';
    } elseif (!ereg("://", $url)) {
        $url = "http://$url";
    }
    $urldb = parse_url($url);
    $port = $urldb[port] ? $urldb[port] : 80;
    $host = $urldb[host];
    $query = '?' . $urldb[query];
    $path = $urldb[path] ? $urldb[path] : '/';
    $method = $method == 'GET' ? "GET" : 'POST';

    $fp = fsockopen($host, 80, $errno, $errstr, 30);
    if (!$fp) {
        echo "$errstr ($errno)<br />\n";
    } else {
        $out = "$method $path$query HTTP/1.1\r\n";
        $out .= "Host: $host\r\n";
        $out .= "Cookie: c=1;c2=2\r\n";
        $out .= "Referer: $url\r\n";
        $out .= "Accept: */*\r\n";
        $out .= "Connection: Close\r\n";
        if ($method == "POST") {
            $out .= "Content-Type: application/x-www-form-urlencoded\r\n";
            $length = strlen($postValue);
            $out .= "Content-Length: $length\r\n";
            $out .= "\r\n";
            $out .= $postValue;
        } else {
            $out .= "\r\n";
        }
        fwrite($fp, $out);
        while (!feof($fp)) {
            $file.= fgets($fp, 256);
        }
        fclose($fp);
        if (!$file) {
            return '';
        }
        $ck = 0;
        $string = '';
        $detail = explode("\r\n", $file);
        foreach ($detail AS $key => $value) {
            if ($value == '') {
                $ck++;
                if ($ck == 1) {
                    continue;
                }
            }
            if ($ck) {
                $stringdb[] = $value;
            }
        }
        $string = implode("\r\n", $stringdb);
        //$string=preg_replace("/([\d]+)(.*)0/is","\\2",$string);
        return $string;
    }
}

/* 页面显示,强制过滤关键字 */

function kill_badword($content) {
    global $webdb, $Limitword;
    if ($webdb[kill_badword]) {
        if (!$content) {
            $content = @ob_get_contents();
            $ck++;
        }

        @include_once(ZJ_PHP_PATH . "Resource/limitword.php");

        foreach ($Limitword AS $key => $value) {
            $content = str_replace($key, $value, $content);
        }
        if ($ck) {
            ob_end_clean();
            ob_start();
            echo $content;
        } else {
            return $content;
        }
    } else {
        return $content;
    }
}

//根据IP获取来源地
function ipfrom($ip) {
    if (!preg_match("/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/", $ip)) {
        return '';
    }
    if (!is_file(ZJ_PHP_PATH . 'Resource/ip.dat')) {
        return '<a title><A HREF="http://down.qibosoft.com/ip.rar" title="点击下载后,解压放到整站/Resource/目录即可">IP库不存在,请点击下载一个!</A></a>';
    }
    if ($fd = @fopen(ZJ_PHP_PATH . 'Resource/ip.dat', 'rb')) {

        $ip = explode('.', $ip);
        $ipNum = $ip[0] * 16777216 + $ip[1] * 65536 + $ip[2] * 256 + $ip[3];

        $DataBegin = fread($fd, 4);
        $DataEnd = fread($fd, 4);
        $ipbegin = implode('', unpack('L', $DataBegin));
        if ($ipbegin < 0)
            $ipbegin += pow(2, 32);
        $ipend = implode('', unpack('L', $DataEnd));
        if ($ipend < 0)
            $ipend += pow(2, 32);
        $ipAllNum = ($ipend - $ipbegin) / 7 + 1;

        $BeginNum = 0;
        $EndNum = $ipAllNum;

        while ($ip1num > $ipNum || $ip2num < $ipNum) {
            $Middle = intval(($EndNum + $BeginNum) / 2);

            fseek($fd, $ipbegin + 7 * $Middle);
            $ipData1 = fread($fd, 4);
            if (strlen($ipData1) < 4) {
                fclose($fd);
                return '- System Error';
            }
            $ip1num = implode('', unpack('L', $ipData1));
            if ($ip1num < 0)
                $ip1num += pow(2, 32);

            if ($ip1num > $ipNum) {
                $EndNum = $Middle;
                continue;
            }

            $DataSeek = fread($fd, 3);
            if (strlen($DataSeek) < 3) {
                fclose($fd);
                return '- System Error';
            }
            $DataSeek = implode('', unpack('L', $DataSeek . chr(0)));
            fseek($fd, $DataSeek);
            $ipData2 = fread($fd, 4);
            if (strlen($ipData2) < 4) {
                fclose($fd);
                return '- System Error';
            }
            $ip2num = implode('', unpack('L', $ipData2));
            if ($ip2num < 0)
                $ip2num += pow(2, 32);

            if ($ip2num < $ipNum) {
                if ($Middle == $BeginNum) {
                    fclose($fd);
                    return '- Unknown';
                }
                $BeginNum = $Middle;
            }
        }

        $ipFlag = fread($fd, 1);
        if ($ipFlag == chr(1)) {
            $ipSeek = fread($fd, 3);
            if (strlen($ipSeek) < 3) {
                fclose($fd);
                return '- System Error';
            }
            $ipSeek = implode('', unpack('L', $ipSeek . chr(0)));
            fseek($fd, $ipSeek);
            $ipFlag = fread($fd, 1);
        }

        if ($ipFlag == chr(2)) {
            $AddrSeek = fread($fd, 3);
            if (strlen($AddrSeek) < 3) {
                fclose($fd);
                return '- System Error';
            }
            $ipFlag = fread($fd, 1);
            if ($ipFlag == chr(2)) {
                $AddrSeek2 = fread($fd, 3);
                if (strlen($AddrSeek2) < 3) {
                    fclose($fd);
                    return '- System Error';
                }
                $AddrSeek2 = implode('', unpack('L', $AddrSeek2 . chr(0)));
                fseek($fd, $AddrSeek2);
            } else {
                fseek($fd, -1, SEEK_CUR);
            }

            while (($char = fread($fd, 1)) != chr(0))
                $ipAddr2 .= $char;

            $AddrSeek = implode('', unpack('L', $AddrSeek . chr(0)));
            fseek($fd, $AddrSeek);

            while (($char = fread($fd, 1)) != chr(0))
                $ipAddr1 .= $char;
        } else {
            fseek($fd, -1, SEEK_CUR);
            while (($char = fread($fd, 1)) != chr(0))
                $ipAddr1 .= $char;

            $ipFlag = fread($fd, 1);
            if ($ipFlag == chr(2)) {
                $AddrSeek2 = fread($fd, 3);
                if (strlen($AddrSeek2) < 3) {
                    fclose($fd);
                    return '- System Error';
                }
                $AddrSeek2 = implode('', unpack('L', $AddrSeek2 . chr(0)));
                fseek($fd, $AddrSeek2);
            } else {
                fseek($fd, -1, SEEK_CUR);
            }
            while (($char = fread($fd, 1)) != chr(0))
                $ipAddr2 .= $char;
        }
        fclose($fd);

        if (preg_match('/http/i', $ipAddr2)) {
            $ipAddr2 = '';
        }
        $ipaddr = "$ipAddr1 $ipAddr2";
        $ipaddr = preg_replace('/CZ88\.NET/is', '', $ipaddr);
        $ipaddr = preg_replace('/^\s*/is', '', $ipaddr);
        $ipaddr = preg_replace('/\s*$/is', '', $ipaddr);
        if (preg_match('/http/i', $ipaddr) || $ipaddr == '') {
            $ipaddr = '- Unknown';
        }

        if (WEB_LANG == 'big5') {
            require_once(ZJ_PHP_PATH . "Lib/Chinese.class.php");
            $cnvert = new Chinese("GB2312", "BIG5", $ipaddr, ZJ_PHP_PATH . "Resource/gbkcode/");
            $ipaddr = $cnvert->ConvertIT();
        } elseif (WEB_LANG == 'utf-8') {
            require_once(ZJ_PHP_PATH . "Lib/Chinese.class.php");
            $cnvert = new Chinese("GB2312", "UTF8", $ipaddr, ZJ_PHP_PATH . "Resource/gbkcode/");
            $ipaddr = $cnvert->ConvertIT();
        }

        return $ipaddr;
    }
}

//自动补全一些不对称的TABLE,TD,DIV标签
function check_html_format($string) {
    preg_match_all("/<div([^>]*)>/", $string, $array0);
    preg_match_all("/<\/div>/", $string, $array1);
    $num0 = count($array0[0]);
    $num1 = count($array1[0]);
    $divNUM = abs($num0 - $num1);
    for ($i = 0; $i < $divNUM; $i++) {
        if ($num0 > $num1) {
            $string.="</div>";
        } else {
            $string = "<div>$string";
        }
        break;
    }
    preg_match_all("/<td([^>]*)>/", $string, $array0);
    preg_match_all("/<\/td>/", $string, $array1);
    $num0 = count($array0[0]);
    $num1 = count($array1[0]);
    $tdNUM = abs($num0 - $num1);
    for ($i = 0; $i < $tdNUM; $i++) {
        if ($num0 > $num1) {
            $string.="</td>";
        } else {
            $string = "<td>$string";
        }
        break;
    }
    preg_match_all("/<table([^>]*)>/", $string, $array0);
    preg_match_all("/<\/table>/", $string, $array1);
    $num0 = count($array0[0]);
    $num1 = count($array1[0]);
    $tableNUM = abs($num0 - $num1);
    for ($i = 0; $i < $tableNUM; $i++) {
        if ($num0 > $num1) {
            $string.="</table>";
        } else {
            $string = "<table>$string";
        }
        break;
    }
    if ($tdNUM > 1 || $tdNUM > 1 || $tableNUM > 1) {
        $string = check_html_format($string);
    }
    return $string;
}

/**
 * 用POST（中转）上传文件。（文件服务器请用 add-tools/UploadService 服务）
 * 
 * @param $serviceUrl 上传服务URL
 * @param $fileData $_FILES接收到的图片数据
 * @param $type 文件类型(文件扩展名)
 * @return $sign 上传服务签名
 */
function upload_file($serviceUrl, $fileData, $type, $sign = "") {
    $snoopy = new Snoopy;
    $param["sign"] = $sign;
    $param["file"] = $fileData;
    $param["type"] = $type;
    $snoopy->_submit_type = "multipart/form-data";
    $snoopy->submit($serviceUrl, $param);
    $str = $snoopy->results;
    return $str;
}

/**
 * 把数组序列化成字符串
 * @param type $array
 * @return type
 */
function array_to_string($array) {
    return json_encode($array);
}

/**
 * 把字符串反序列化成数组
 * @param type $string
 * @return type
 */
function string_to_array($string) {
    $obj = json_decode($string);
    if (is_object($obj)) {
        return (array) $obj;
    }

    return $obj;
}

function get_unix_time() {
    $time = explode(" ", microtime());
    $time = $time [1] . ($time [0] * 1000);
    $time2 = explode(".", $time);
    $time = $time2 [0];
    return $time;
}

function create_guid() {
    $charid = strtoupper(md5(uniqid(mt_rand(), true)));
    $hyphen = chr(45);
    $uuid = substr($charid, 0, 8) . $hyphen
            . substr($charid, 8, 4) . $hyphen
            . substr($charid, 12, 4) . $hyphen
            . substr($charid, 16, 4) . $hyphen
            . substr($charid, 20, 12);
    return $uuid;
}

/**
 * 取指定目录下的所有目录（包括子目录）
 * @param type $dir
 * @return type
 */
function sub_dir_ls($dir) {
    $dir = trim($dir, "/\\");
    $files = Array();
    $d = opendir($dir);
    while ($file = readdir($d)) {
        if ($file == '.' || $file == '..')
            continue;

        if (is_dir($dir . DIRECTORY_SEPARATOR . $file)) {
            $files[] = $dir . DIRECTORY_SEPARATOR . $file;
            $files = array_merge($files, sub_dir_ls($dir . DIRECTORY_SEPARATOR . $file));
        }
    }
    closedir($d);
    return $files;
}

/**
 * 分页
 * @param int $page 当前页
 * @param int $count 总记录数
 * @param int $pagesize 页长
 * @param array $del_key URL中忽略的参数key，格式　array("a","b") 
 * @param array $add_item URL中添加的参数，格式 array("a"=>1,"b"=>2);
 * @param string $add_string URL后添加的字符串
 * @return string
 */
function list_page($page, $count, $pagesize = 10, $del_key = array(), $add_item = array(), $add_string = "") {
    $page = floor($page);
    $page = $page < 1 ? 1 : $page;

    $count_page = ceil($count / $pagesize);
    $count_page = $count_page < 1 ? 1 : $count_page;

    if ($page > $count_page) {
        $page = $count_page;
    }

    if (!is_array($del_key)) {
        $del_key = array();
    }

    $params = $_GET;
    if (is_array($add_item)) {
        $params = array_merge($params, $add_item);
    }

    $query = "?";
    $del_key[] = "page";
    $fg = "";

    $pkey = "page=";
    foreach ($params as $key => $value) {
        if (in_array($key, $del_key)) {
            continue;
        }
        $query .= $fg . $key . "=" . $value;
        $pkey = "&page=";
        $fg = "&";
    }

    $pre_no = $page > 1 ? $page - 1 : 1;
    $aft_no = $page < $count_page ? $page + 1 : $count_page;

    $numLen = strlen("$count_page" . "$page");

    $str = "<table class='list_page'><tr>";
    $str .= "<td style='width:2.2em;text-align:center;'><a style='color:#333;' href='{$query}{$pkey}1{$add_string}'>首页</a></td>";
    $str .= "<td style='width:2.2em;text-align:center;'><a style='color:#333;' href='{$query}{$pkey}{$pre_no}{$add_string}'><</a></td>";
    $str .= "<td style='width:{$numLen}em;text-align:center; color:#333;'>{$page}&nbsp;/&nbsp;{$count_page}</td>";
    $str .= "<td style='width:2.2em;text-align:center;'><a style='color:#333;' href='{$query}{$pkey}{$aft_no}{$add_string}'>></a></td>";
    $str .= "<td style='width:2.2em;text-align:center;'><a style='color:#333;' href='{$query}{$pkey}{$count_page}{$add_string}'>末页</a></td>";
    $str .= "</tr></table>";

    return $str;
}

function redirect($url) {
    Header("HTTP/1.1 301 Moved Permanently");
    Header("Location:{$url}");
}

