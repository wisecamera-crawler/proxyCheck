<?php
/**
 * Make Config
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
namespace utility;

    /**
     * Make config class
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
class MakeConfig
{
    function Make() {
        exec("env | grep 'HOME='", $homeOutput);
        for ($i = 0; $i < count($homeOutput); $i++) {
            $selfHome = explode("=", $homeOutput[$i]);
            if ($selfHome[0] == 'HOME') {
                break;
            }
        }
        $defHome = $selfHome[1] . "/crawler_conf";

        $dataWrite[0] = "\$host = '127.0.0.1'";
        $dataWrite[1] = "\$dbname = 'NSC'";
        $dataWrite[2] = "\$user = 'root'";
        $dataWrite[3] = "\$password = 'openfoundry'";
        $dataWrite[4] = "\$chkAllTime = 10";
        $dataWrite[5] = "\$chkProxyTime = 10";
        $dataWrite[6] = "\$extraProgram = 'php ./crawler/main.php '";
        $dataWrite[7] = "\$chkType = 'project'";
        $dataWrite[8] = "\$chkTime = 240";

        if (!file_exists($defHome)) {
            $fileOpen = fopen($defHome, "w");
            fwrite($fileOpen, implode(chr(10), $dataWrite));
            fclose($fileOpen);
        }

        for ($i=0; $i < count($dataWrite); $i++) {
            $fileOpen = fopen($defHome, "r");
            while (!feof($fileOpen)) {
                $lineRead = fgets($fileOpen);
                $explode = explode("=", $dataWrite[$i]);
                $forRead = explode("=", $lineRead);
                if ($explode[0] == $forRead[0]) {
                    eval($explode[0] . "=" . $forRead[1] . ";");
                }
            }
            fclose($fileOpen);
        }
        ProxySQLService::$dbname = $dbname;
        ProxySQLService::$host = $host;
        ProxySQLService::$password = $password;
        ProxySQLService::$user = $user;
        ProxyCheck::$chkAllTime = $chkAllTime;
        ProxyCheck::$chkProxyTime = $chkProxyTime;
        ProxyCheck::$chkTime = $chkTime;
        ProxyCheck::$chkType = $chkType;
        ProxyCheck::$extraProgram = $extraProgram;
    }
}
