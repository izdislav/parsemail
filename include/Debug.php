<?php
/**
 * @author Christian Reinecke <reinecke@bajoodoo.com>
 * @version 2.0
 * @since 2007-05-16
 * @license no
 */
abstract class Debug
{
    /**
     * @desc call with as much parameters as you like; script will exit after output
     * @param mixed [optional]
     * @return null
     */
    public static function stop()
    {
        $glue   = PHP_EOL . PHP_EOL;
        $output = array();
        for ($i = 0, $k = 1, $x = func_num_args(); $i < $x; ++$i, ++$k) {
            $arg = func_get_arg($i);
            $output[] = "DEBUG ARG $k:";
            $output[] = self::_getVariable($arg);
        }
        $output[] = self::_getBacktrace($glue, 1);
        $output[] = self::_getMemoryUsage();
        $output[] = self::_getTimestamp();
        $output[] = "DEBUG STOP";
        $output   = implode($glue, $output);
        self::_flush($output);
        exit;
    }

    /**
     * @desc call with as much parameters as you like
     * @param mixed [optional]
     * @return null
     */
    public static function write()
    {
        $glue   = PHP_EOL . PHP_EOL;
        $output = array();
        for ($i = 0, $k = 1, $x = func_num_args(); $i < $x; ++$i, ++$k) {
            $arg = func_get_arg($i);
            $output[] = "DEBUG ARG $k:";
            $output[] = self::_getVariable($arg);
        }
        $output[] = self::_getBacktrace($glue, 1);
        $output[] = self::_getMemoryUsage();
        $output[] = self::_getTimestamp();
        $output   = implode($glue, $output);
        self::_flush($output);
    }

    /**
     * @desc call with as much parameters as you like, arguments will be passed to error_log()
     * @see http://de.php.net/manual/en/function.error-log.php
     * @param mixed [optional]
     * @return bool error_log
     */
    public static function log()
    {
        $glue   = "; ";
        $output = array();
        for ($i = 0, $k = 1, $x = func_num_args(); $i < $x; ++$i, ++$k) {
            $arg = func_get_arg($i);
            $output[] = "DEBUG ARG $k:";
            $output[] = self::_getVariable($arg);
        }
        $output[] = self::_getBacktrace($glue, 1);
        $output[] = self::_getMemoryUsage();
        $output[] = self::_getTimestamp();
        $output   = implode($glue, $output);
        return error_log($output);
    }

    protected static function _getTimestamp()
    {
        list ($usec, $sec) = explode(" ", microtime());
        $usec = substr($usec, 2);
        return "DEBUG TIMESTAMP $sec.$usec";
    }

    protected static function _getMemoryUsage()
    {
        $memoryEmalloc = number_format(memory_get_usage(false));
        $memoryReal    = number_format(memory_get_usage(true));
        return "DEBUG MEMORY $memoryEmalloc of $memoryReal";
    }

    protected static function _getVariable($variable)
    {
        ob_start();
        var_dump($variable);
        return ob_get_clean();
    }

    protected static function _getBacktrace($glue, $slice)
    {
        foreach (debug_backtrace() as $i => $trace) {
            $file     = isset($trace["file"])     ? $trace["file"]     : "null";
            $line     = isset($trace["line"])     ? $trace["line"]     : "null";
            $class    = isset($trace["class"])    ? $trace["class"]    :  null;
            $function = isset($trace["function"]) ? $trace["function"] : "null";
            $type     = isset($trace["type"])     ? $trace["type"]     :  null;
            $args     = isset($trace["args"])     ? implode(", ", array_map(array(__CLASS__, "_getBeautifiedArgument"), $trace["args"])) : null;
            $output[] = sprintf("[%2s] %s:%s\n     %s%s%s(%s)",
                        $i, $file, $line, $class, $type, $function, $args);
        }
        $output = array_slice($output, $slice);
        array_unshift($output, "DEBUG BACKTRACE");
        return implode($glue, $output);
    }

    protected static function _flush($string)
    {
        echo "<pre>", $string, "</pre>";
        flush();
    }

    protected static function _getBeautifiedArgument($arg)
    {
        if (is_int($arg) || is_double($arg)) {
            return $arg;
        }
        if (is_string($arg)) {
            if (mb_strlen($arg) > 15) {
                return '"' . mb_substr($arg, 0, 15) . '"[..]';
            }
            return '"' . $arg . '"';
        }
        if (is_bool($arg)) {
            return $arg ? "true" : "false";
        }
        if (is_array($arg)) {
            return "array(" . count($arg) . ")";
        }
        if (is_object($arg)) {
            return get_class($arg);
        }
        return gettype($arg);
    }
}
?>