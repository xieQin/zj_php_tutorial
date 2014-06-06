<?php

/**
 * 接口文档处理类
 *
 * @author joy
 */
class ApiDocHandler {

    static function generateDoc($apiFullPath) {

        $handle = fopen($apiFullPath, 'r');

        $names = array();
        $tmpDoc = "";
        $state = 0; //0:查找注释开始,1:查找注释结尾,2:操作注释定义对象
        while (!feof($handle)) {
            $line = fgets($handle, 1024);
            $line = trim($line);

            if (!$line) {
                continue;
            }
            switch ($state) {
                case 0:
                    if (strpos($line, "/**") !== false) {
                        $nodes = self::_formatNodesStart($line);
                        $nodesName = "";
                        $nodesTitle = "";
                        $nodesType = "";
                        $state = 1;
                    }
                    break;
                case 1:
                    if (strpos($line, "*/") !== false) {
                        $nodes .= self::_formatNodeEnd($line);
                        $state = 2;
                    } else {
                        if (!$nodesTitle) {
                            $nodesTitle = self::_getNodeTitle($line);
                        }

                        $nodes .= self::_formatNodesContent($line);
                    }
                    break;
                case 2:
                    if (strpos($line, "class ") !== false) {
                        $nodes .= self::_formatClassDef($line);
                        $nodesName = self::_getNodesName($line);
                        $nodesType = "class";
                        $state = 0;
                    } else if (strpos($line, "function ") !== false) {
                        $nodes .= self::_formatFunDef($line);
                        $nodesName = self::_getNodesName($line);
                        $nodesType = "function";
                        $state = 0;
                    }
                    break;
            }

            if ($nodes && $nodesName) {
                $tmpDoc .= "<a name='$nodesName'></a>" . $nodes;
                $names[] = array("name" => $nodesName, "title" => $nodesTitle, "type" => $nodesType);

                $nodes = null;
                $nodesName = null;
            }
        }

        fclose($handle);
        return self::_formatDocHtml($names, $tmpDoc);
    }

    static function _formatDocHtml(&$names, &$tmpDoc) {
        $tmpName = "<ul class='index'>";
        
        foreach ($names as $obj) {
            $name = $obj["name"];
            $title = $obj["title"];
            $type = $obj["type"];
            $tag = "";
            if($obj["type"] == "function"){
                $tag = '[F]';
            } else if($obj["type"] == "class") {
                $tag = '[C]';
            }
            $tmpName .= "<li><a class='name {$type}' href='#{$name}'>{$name}</a><span class='tag'>{$tag}</span><span class='title'>{$title}</span></li>";
        }
        $tmpName .= "</ul>";

        $html = "<html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8' />\n";
        $html .= "<style>\n";
        $html .= "*{font-family:'微软雅黑'; font-size:14px;}\n";
        $html .= "body {text-align:center;}\n";
        $html .= ".wrap {width:980px; text-align:left; margin-left:auto;margin-right:auto;margin-top:30px;}\n";
        $html .= ".nodes {border:1px solid #999;background:#cfcfcf;padding:10px;}\n";
        $html .= ".classdef,.classfun{border:1px dotted #785;background:#f5f5f5; padding:10px;margin-bottom:20px;}\n";
        $html .= "strong{color:#BBBB00;}\n";
        $html .= ".index .name{font-size:16px; line-height:2em;} \n";
        $html .= ".index .tag{margin-left:1em;font-style:italic;color:#999;} \n";
        $html .= ".index .title{margin-left:0.5em;font-style:italic;color:#999;} \n";
        $html .= ".index .class{font-weight:bold;color:#009fcc} \n";
        $html .= ".index .function{margin-left:1em;color:#00aaaa;} \n";
        $html .= "</style></head>\n";
        $html .= "<body><div class='wrap'>{$tmpName}{$tmpDoc}<div style='height:100px;'></div></div></body></html>";

        return $html;
    }

    static function _getNodesName($line) {
        $regex = '/(?:class|function)[\s]+([\w]+)/';
        $matches = array();
        if (preg_match($regex, $line, $matches)) {
            //print_r($matches);
            return $matches[1];
        }
        return false;
    }

    static function _getNodeTitle($line) {
        $regex = '/\*[\s]+([^@].*)/';
        $matches = array();
        if (preg_match($regex, $line, $matches)) {
            //标题要求长度 > 3
            return strlen($matches[1]) > 3 ? $matches[1] : false;
        }
        return false;
    }

    static function _formatNodesStart($line) {
        return "<div style='height:5px;'></div><div class='nodes'><div>{$line}</div>";
    }

    static function _formatNodesContent($line) {
        return "<div style='text-indent:.5em;'>{$line}</div>";
    }

    static function _formatNodeEnd($line) {
        return "<div style='text-indent:.5em;'>{$line}</div></div>";
    }

    static function _formatClassDef($line) {
        $arr = explode('{', $line);
        $tmp = $arr[0];
        $nodesName = self::_getNodesName($line);
        $tmp = str_replace($nodesName, "<strong>$nodesName</strong>", $tmp);
        return "<div class='classdef'>{$tmp}</div>";
    }

    static function _formatFunDef($line) {
        $arr = explode('{', $line);
        $tmp = $arr[0];
        $nodesName = self::_getNodesName($line);
        $tmp = str_replace($nodesName, "<strong>$nodesName</strong>", $tmp);
        return "<div class='classfun'>{$tmp}</div>";
    }

}