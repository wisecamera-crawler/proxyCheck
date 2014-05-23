<?php
/**
 * Proxy's check, a Proxy server class
 *
 * PHP version 5
 *
 * LICENSE: none
 *
 * @category  Utility
 * @package   PackageName
 * @author    Patrick Her <zivhsiao@gmail.com>
 * @copyright 1997-2005 The PHP Group
 * @license   none <none>
 * @version   GIT: <id>
 * @link      none
 */

namespace dispatch;

/**
 * Proxy's check
 *
 * PHP version 5
 *
 * LICENSE: none
 *
 * @category  Utility
 * @package   PackageName
 * @author    Patrick Her <zivhsiao@gmail.com>
 * @copyright 1997-2005 The PHP Group
 * @license   none <none>
 * @link      none
 */

class ProxyCheck
{

    public static $chkAllTime = "";
    public static $extraProgram = "";
    public static $chkType = "";
    /**
     * Proxy's check
     *
     * PHP version 5
     *
     * LICENSE: none
     *
     * @param string $proxy Proxy's address
     *
     * @category  Utility
     * @return    none
     */
    public function check($proxy)
    {
        $url_array = array(
            'http://www.google.com',
            'http://tw.yahoo.com',
            'http://www.pchome.com.tw'
        );

        $url = $url_array[rand(0, count($url_array) - 1)];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
        curl_setopt($ch, CURLOPT_PROXY, $proxy);

        $response = curl_exec($ch);

        if ($response === false) {
            return "connection error!";
        } else {
            return "worked";
        }
    }

    /**
     * ps making
     *
     * PHP version 5
     *
     * LICENSE: none
     *
     * @param string $param  1st param
     * @param string $param2 2nd param
     *
     * @category  Utility
     * @return    none
     */
    public function makePs($param, $param2)
    {
        $cmd = "ps aux | grep '$param' | awk '{print $param2}' | xargs";

        return $cmd;
    }

}
