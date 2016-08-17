<?php
/**
 * Description: Log class
 * License:
 * User: carey
 * Date: 2016/8/10
 * Time: 15:07
 */
namespace logger\fileLogger;

/**
 * 日志处理类
 */
class Log
{

    // 日志级别 从上到下，由低到高
    const EMERG = 'EMERG';  // 严重错误: 导致系统崩溃无法使用
    const ALERT = 'ALERT';  // 警戒性错误: 必须被立即修改的错误
    const CRIT = 'CRIT';  // 临界值错误: 超过临界值的错误，例如一天24小时，而输入的是25小时这样
    const ERR = 'ERR';  // 一般错误: 一般性错误
    const WARN = 'WARN';  // 警告性错误: 需要发出警告的错误
    const NOTICE = 'NOTIC';  // 通知: 程序可以运行但是还不够完美的错误
    const INFO = 'INFO';  // 信息: 程序输出信息
    const DEBUG = 'DEBUG';  // 调试: 调试信息
    const SQL = 'SQL';  // SQL：SQL语句 注意只在调试模式开启时有效

    //下面选项应该放到项目中去配置
    const LOG_PATH = '/';
    const LOG_TYPE = 3;
    const LOG_ALL_IN_ONE_FILE = false;

    // 日志存储
    static protected $storage = null;

    // 日志记录方式
    const SYSTEM = 0;
    const MAIL = 1;
    const FILE = 3;
    const SAPI = 4;

    // 日志信息
    static $log = array();

    //业务日志
    static $bizLogs = array();

    // 日期格式
    static $format = 'Y/m/d H:i:s';

    //目标路径
    static $destPath = '';

    //按等级存放
    static $archiveByLevel = false;

    //日志缓存最大数量
    static $_maxLogQueue = 1000;

    //日志缓存数量
    static $_numLogQueue = 0;

    // 日志初始化
    static public function init($config = array())
    {
        $type = isset($config['type']) ? $config['type'] : 'File';
        $class = strpos($type, '\\') ? $type : 'logger\\fileLogger\\' . ucwords(strtolower($type));
        unset($config['type']);
        self::$storage = new $class($config);
    }

    /**
     * 记录日志 并且会过滤未经设置的级别
     * @static
     * @access public
     * @param string $message 日志信息
     * @param string $level 日志级别
     * @param boolean $record 是否强制记录
     * @return void
     */
    private static function record($message, $level = self::ERR, $record = false)
    {
        if ($record || false !== strpos('EMERG,ALERT,CRIT,ERR', $level)) {
            self::$log[] = "{$level}: {$message}\r\n";
        }
    }

    /**
     * 日志保存
     * @static
     * @access public
     * @param integer $type 日志记录方式
     * @param string $destination  写入目标
     * @param string $extra 额外参数
     * @return void
     */
    private static function save($type='', $level=self::ERR, $destination='', $extra='') {
        self::_MultiSave($type, $level=self::ERR, $destination, $extra);
        //计数器重置
        self::$_numLogQueue = 0;
    }

    /**
     * 日志直接写入
     * @static
     * @access public
     * @param string $message 日志信息
     * @param string $level  日志级别
     * @param integer $type 日志记录方式
     * @param string $destination  写入目标
     * @param string $extra 额外参数
     * @return void
     */
    private static function write($message,$level=self::ERR,$type='',$destination='',$extra='') {
        $now = date(self::$format);
        $type = $type?$type:C('LOG_TYPE');
        if(self::FILE == $type) { // 文件方式记录日志
            if(empty($destination))
                $destination = (C('LOG_PATH') ? C('LOG_PATH') : LOG_PATH) .date('y_m_d').'.log';
            //检测日志文件大小，超过配置大小则备份日志文件重新生成
            if(is_file($destination) && floor(C('LOG_FILE_SIZE')) <= filesize($destination) )
                rename($destination,dirname($destination).'/'.time().'-'.basename($destination));
        }else{
            $destination   =   $destination?$destination:C('LOG_DEST');
            $extra   =  $extra?$extra:C('LOG_EXTRA');
        }
        if (!is_file($destination)) {
            touch($destination);
            chmod($destination, 0775);
        }
        error_log("{$now} {$level}: {$message}\r\n", $type,$destination,$extra );
        //clearstatcache();
    }

    /**
     * 文件路径
     * @param $path
     */
    public static function setDestPath($path)
    {
        self::$destPath = $path;
    }

    /**
     * 安日志等级单独存放日志
     */
    public static function setArchiveByLevel()
    {
        self::$archiveByLevel = true;
    }

    /**
     * 获取客户端IP地址
     * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
     * @return mixed
     */
    public static function get_client_ip($type = 0)
    {
        $type = $type ? 1 : 0;
        static $ip = NULL;
        if ($ip !== NULL) return $ip[$type];
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos = array_search('unknown', $arr);
            if (false !== $pos) unset($arr[$pos]);
            $ip = trim($arr[0]);
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        // IP地址合法验证
        $long = sprintf("%u", ip2long($ip));
        $ip = $long ? array($ip, $long) : array('0.0.0.0', 0);
        return $ip[$type];
    }

    /**
     * 业务日志
     *
     * @param string $msg
     * @param string $level
     */
    static function business($message, $level, $tag = 'Common', array $context = array(), $traceInfo = null)
    {
        static $ip = null;
        if (null === $ip) {
            $ip = self::get_client_ip();
        }
        if (false !== strpos('ERR,WARN,INFO,NOTICE,DEBUG', $level)) {
            $now = date(self::$format);
            $tag = ucfirst($tag);
            $context = $context ? json_encode($context) : '';

            $message = $now . ' ' . $ip . ' ' . $level . ": [$tag] " . $message . ' ||| [CONTEXT] ' . $context . ' ||| [TRACE] ' . $traceInfo . ' ||| [URI] ' . $_SERVER['REQUEST_URI'];

            $message = "{$message}\r\n";
            self::_OneSave($message, $level);
        }
    }

    /**
     * 保存业务日志
     * @static
     * @access public
     * @param integer $type 日志记录方式
     * @param string $destination  写入目标
     * @param string $extra 额外参数
     * @return void
     */
    protected static function _MultiSave($type='',$level=self::ERR,$destination='',$extra='') {
        if(empty(self::$bizLogs)) return ;

        foreach (self::$bizLogs as $lv => $logs) {
            $type = $type?$type:self::LOG_TYPE;
            if(self::FILE == $type) { // 文件方式记录日志信息
                if (!empty(self::$destPath)) {
                    if (self::LOG_ALL_IN_ONE_FILE) {
                        $destination = self::$destPath . '/' . 'all-level.log';
                    } else {
                        $destination = self::$destPath . '/' .date('y_m_d') . '_' . strtolower($lv) . '.log';
                    }
                } elseif (empty($destination)) {
                    //cli下LOG_PATH可能不存在
                    $destination = self::$destPath . '/' . date('y_m_d') . '_' . strtolower($lv) . '.log';
                }
                //检测日志文件大小，超过配置大小则备份日志文件重新生成
                if(is_file($destination) && floor(self::$_maxLogQueue) <= filesize($destination) )
                    rename($destination,dirname($destination).'/'.time().'-'.basename($destination));
            }else{
//                $destination   =   $destination?$destination:C('LOG_DEST');
//                $extra   =  $extra?$extra:C('LOG_EXTRA');
            }
            if (!is_file($destination)) {
                touch($destination);
                chmod($destination, 0775);
            }
            error_log(implode('',$logs), $type,$destination ,$extra);
        }

        // 保存后清空日志缓存
        self::$bizLogs = array();
        //clearstatcache();
    }

    /**
     * 写入业务日志（单条直接写入）
     * @param string $log 日志内容
     * @param string $level 日记登记
     * @param integer $type 日志记录方式
     * @param string $destination  写入目标
     * @param string $extra 额外参数
     * @return void
     */
    protected static function _OneSave($log, $level, $type='',$destination='',$extra='') {
        $type = $type?$type:self::LOG_TYPE;
        if(self::FILE == $type) { // 文件方式记录日志信息
            if (!empty(self::$destPath)) {
                if (self::LOG_ALL_IN_ONE_FILE) {
                    $destination = self::$destPath . '/' . 'all-level.log';
                } else {
                    $destination = self::$destPath . '/' .date('y_m_d') . '_' . strtolower($level) . '.log';
                }
            } elseif (empty($destination)) {
                //cli下LOG_PATH可能不存在
                $destination = self::$destPath . '/' . date('y_m_d') . '_' . strtolower($level) . '.log';
            }

            //检测日志文件大小，超过配置大小则备份日志文件重新生成
            if(is_file($destination) && floor(self::$_maxLogQueue) <= filesize($destination) )
                rename($destination,dirname($destination).'/'.time().'-'.basename($destination));
        }else{
//            $destination   =   $destination?$destination:C('LOG_DEST');
//            $extra   =  $extra?$extra:C('LOG_EXTRA');
        }
        if (!is_file($destination)) {
            touch($destination);
            chmod($destination, 0775);
        }
        error_log($log, $type, $destination, $extra);
    }

}

/**
 * 外部调用类
 * Class Logger
 * @package logger\fileLogger
 */
class Logger
{

    public static function debug($msg, $tag = null, array $context = array())
    {
        return self::_log($msg, Log::DEBUG, $tag, $context);
    }

    public static function info($msg, $tag = null, array $context = array())
    {
        return self::_log($msg, Log::INFO, $tag, $context);
    }

    public static function notice($msg, $tag = null, array $context = array())
    {
        return self::_log($msg, Log::NOTICE, $tag, $context);
    }

    public static function warn($msg, $tag = null, array $context = array())
    {
        return self::_log($msg, Log::WARN, $tag, $context);
    }

    public static function error($msg, $tag = null, array $context = array())
    {
        return self::_log($msg, Log::ERR, $tag, $context);
    }

    /**
     * 记录业务日志
     *
     * @param string $msg
     * @param int $level
     * @param string $tag
     * @param array $context 上下文信息，便于定位问题
     */
    private static function _log($msg, $level, $tag, array $context = array())
    {
        static $init = false;
        if (!$init) {
            Log::setArchiveByLevel();
            Log::setDestPath(self::_getDestPath($level));
            $init = true;
        }

        if (!$tag) {
            $tag = 'common';
        }

        $traceInfo = '';
        if (APP_DEBUG) {
            list($file, $line, $func, $class) = self::_getBacktraceInfo(1);
            $traceInfo = (!empty($class) ? "$class::" : '') . "$func() $file line: $line";
        }

        return Log::business($msg, $level, $tag, $context, $traceInfo);
    }

    /**
     * 取类名与方法
     * @param $depth
     * @return array
     */
    private static function _getBacktraceInfo($depth)
    {
        $bt = debug_backtrace();
        $bt0 = isset($bt[$depth]) ? $bt[$depth] : null;
        $bt1 = isset($bt[$depth + 1]) ? $bt[$depth + 1] : null;

        $class = isset($bt1['class']) ? $bt1['class'] : null;
        if ($class !== null && strcasecmp($class, 'Log_composite') == 0) {
            $depth++;
            $bt0 = isset($bt[$depth]) ? $bt[$depth] : null;
            $bt1 = isset($bt[$depth + 1]) ? $bt[$depth + 1] : null;
            $class = isset($bt1['class']) ? $bt1['class'] : null;
        }

        $file = isset($bt0) ? $bt0['file'] : null;
        $line = isset($bt0) ? $bt0['line'] : 0;
        $func = isset($bt1) ? $bt1['function'] : null;

        if (in_array($func, array('error', 'warn', 'notice', 'info', 'debug'))) {
            $bt2 = isset($bt[$depth + 2]) ? $bt[$depth + 2] : null;

            $file = is_array($bt1) ? $bt1['file'] : null;
            $line = is_array($bt1) ? $bt1['line'] : 0;
            $func = is_array($bt2) ? $bt2['function'] : null;
            $class = isset($bt2['class']) ? $bt2['class'] : null;
        }

        if ($func === null) {
            $func = '(none)';
        }
        return array($file, $line, $func, $class);
    }

    /**
     * 路径过滤
     * @return string
     */
    private static function _getDestPath()
    {
        $domain = $_SERVER['HTTP_HOST'];
        $domain = $domain ? $domain : 'test.com';
        $logDir = dirname($_SERVER['SCRIPT_FILENAME']) . '/Logs/' . $domain . '/' . date('Ymd');
        is_dir($logDir) || mkdir($logDir, 0775, true);
        return $logDir;
    }

}