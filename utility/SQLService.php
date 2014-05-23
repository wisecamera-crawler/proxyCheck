<?php
/**
 * SQL service
 *
 * PHP version 5
 *
 * LICENSE: none
 *
 * @category Utility
 * @package  PackageName
 * @author   Patrick Her <zivhsiao@gmail.com>
 * @license  none <none>
 * @version  GIT: <git_id>
 * @link     none
 */

namespace dispatch;

use \PDO;

/**
 * SQL service
 *
 * PHP version 5
 *
 * LICENSE: none
 *
 * @category Utility
 * @package  PackageName
 * @author   Patrick Her <zivhsiao@gmail.com>
 * @license  none <none>
 * @link     none
 */
class SQLService
{
    public static $conn;

    public static $host = "";
    public static $user = "";
    public static $password = "";
    public static $dbname = "";

    /**
     * SQL connection
     *
     * PHP version 5
     *
     * LICENSE: none
     *
     * @category  Utility
     * @package   PackageName
     * @author    Patrick Her <zivhsiao@gmail.com>
     * @license   none <none>
     * @version   SVN: $Id$
     * @link      none
     */
    public function __construct()
    {
        $dsn = "mysql:host=" . SQLService::$host . ";dbname=" . SQLService::$dbname;
        $this->conn = new PDO($dsn, SQLService::$user, SQLService::$password);
        $this->conn->query("SET CHARACTER SET utf8 ");
        $this->conn->query("SET NAMES utf8");
    }

    /**
     * Get Proxy's server ip, port
     *
     * @category  Utility
     * @return    proxy's address
     */
    public function getProxyId()
    {
        $proxy_svr = array();
        $result = $this->conn->query(
            "SELECT `proxy_ip`, `proxy_port`
               FROM `proxy`
              WHERE `status` IN ('on-line', 'off-line')"
        );

        while ($rows = $result->fetch()) {
            array_push($proxy_svr, $rows['proxy_ip'] . ":" . $rows['proxy_port']);
        }

        return $proxy_svr;
    }

    /**
     * Proxy's status by address
     *
     * @category  Utility
     * @return    proxy's nice or error
     */
    public function getAllProxyStatus()
    {
        $result_01 = $this->conn->query(
            "SELECT count(*) FROM `proxy`"
        );

        $result_02 = $this->conn->query(
            "SELECT `proxy_ip`,
                    `proxy_port`,
                    `status`
               FROM `proxy`"
        );

        $rows = $result_01->fetchAll();
        $rowsCount = count($rows);

        while ($row = $result_02->fetch()) {
            if ($row['status'] == 'on-line') {
                break;
            }
        }

        if (count($row) == $rowsCount && $row['status'] == 'off-line') {
            return 'proxy_error';
        } else {
            return 'proxy_nice';
        }

    }
    /**
     * Get Proxy's status
     *
     * @param string $proxy_ip   Proxy's address
     * @param string $proxy_port Proxy's port
     *
     * @category  Utility
     * @return    the last status
     */
    public function getProxyStatus($proxy_ip, $proxy_port)
    {
        $result = $this->conn->query(
            "SELECT `status`
               FROM `proxy`
              WHERE `proxy_ip` = '$proxy_ip'
                AND `proxy_port` = '$proxy_port'"
        );

        $rows = $result->fetch(PDO::FETCH_ASSOC);
        return $rows['status'];
    }
    /**
     * Get Proxy's last status
     *
     * @param string $proxy_ip   Proxy's address
     * @param string $proxy_port Proxy's port
     *
     * @category  Utility
     * @return    the last status
     */
    public function getProxyLastStatus($proxy_ip, $proxy_port)
    {
        $result = $this->conn->query(
            "SELECT `last_status`
               FROM `proxy`
              WHERE `proxy_ip` = '$proxy_ip'
                AND `proxy_port` = '$proxy_port'"
        );

        $rows = $result->fetch(PDO::FETCH_ASSOC);
        return $rows['last_status'];
    }

    /**
     * Get Proxy's current status
     *
     * @param string $proxy_ip    Proxy's address
     * @param string $proxy_port  Proxy's port
     * @param string $last_status Proxy's last status
     *
     * @category  Utility
     * @return    none
     */
    public function updateProxyLastStatus($proxy_ip, $proxy_port, $last_status)
    {
        $this->conn->query(
            "UPDATE `proxy`
                SET `last_status` = '$last_status'
              WHERE `proxy_ip` = '$proxy_ip'
                AND `proxy_port` = '$proxy_port'"
        );
    }

    /**
     * update Proxy's status
     *
     * @param string $proxy_ip   Proxy's address
     * @param string $proxy_port Proxy's port
     * @param string $status     Proxy's current status
     *
     * @category  Utility
     * @return    none
     */
    public function updateProxyStatus($proxy_ip, $proxy_port, $status)
    {
        $this->conn->query(
            "UPDATE `proxy`
                set `status` = '$status'
              WHERE `proxy_ip` = '$proxy_ip'
                AND `proxy_port` = '$proxy_port'"
        );
    }

    /**
     * Get mailer
     *
     * @category  Utility
     * @return    email
     */
    public function getMailer()
    {
        $result = $this->conn->query(
            "SELECT `email` FROM `email`"
        );

        return $result;
    }

    /**
     * Get schedule
     *
     * @category  Utility
     * @return    email
     */
    public function getSchedule()
    {
        $res = $this->conn->query(
            "SELECT *
               FROM `schedule`"
        );

        return $res;
    }

    /**
     * Get mailer
     *
     * @param string $param give a param
     *
     * @category  Utility
     * @return    email
     */
    public function getScheduleParam($param)
    {

        $res = $this->conn->query(
            "SELECT *
               FROM `schedule` WHERE " . $param
        );

        return $res;
    }

    /**
     * Get schedule_group for schedule
     *
     * @param string $status     Schedule's status
     * @param string $sch_id     Schedule's id
     * @param string $start_time Start time
     *
     * @category  Utility
     * @return    sch_type
     */
    public function updateSchedule($status, $sch_id, $start_time = '')
    {
        $this->conn->query(
            "UPDATE `schedule`
                SET `status` = '$status',
                    `start_time` = '$start_time'
              WHERE `schedule_id` = $sch_id"
        );

    }

    /**
     * Get schedule_group for schedule
     *
     * @param string $sch_id Schedule group's id
     *
     * @category  Utility
     * @return    sch_type
     */
    public function getScheduleGroup($sch_id)
    {
        $result = $this->conn->query(
            "SELECT `schedule_group_id`,
                    `schedule_id`,
                    `member`,
                    `type`
               FROM `schedule_group`
              WHERE `schedule_id` = $sch_id
           ORDER BY `type`"
        );

        return $result;
    }

    /**
     * Get schedule_group for schedule
     *
     * @param string $project_id Schedule group's id
     * @param string $year       Schedule group's id
     * @param string $type       Schedule group's id
     *
     * @category  Utility
     * @return    sch_type
     */
    public function getProject($project_id = '', $year = '', $type = '')
    {

        $result = $this->conn->query(
            "SELECT `project_id`,
                    `url`
               FROM `project`"
        );

        if ($project_id != '') {
            $result = $this->conn->query(
                "SELECT `project_id`,
                        `url`
                  FROM `project`
                  WHERE `project_id` = '" . $project_id . "'"
            );

        }

        if ($year != "" && $type != "") {
            if ($year == 'all' && $type == 'all') {
                $result = $this->conn->query(
                    "SELECT `project_id`,
                            `url`
                       FROM `project`"
                );
            }

            if ($year == 'all' && $type != 'all') {
                $result = $this->conn->query(
                    "SELECT `project_id`,
                            `url`
                       FROM `project`
                      WHERE `type` = '$type'"
                );
            }

            if ($year != 'all' && $type == 'all') {
                $result = $this->conn->query(
                    "SELECT `project_id`,
                            `url`
                       FROM `project`
                      WHERE `year` = '$year'"
                );
            }

            if ($year != 'all' && $type != 'all') {
                $result = $this->conn->query(
                    "SELECT `project_id`,
                            `url`
                       FROM `project`
                      WHERE `year` = '$year'
                        AND `type` = '$type'"
                );
            }

        }

        return $result->fetch();

    }

    /**
     * Get schedule_group for schedule
     *
     * @param string $param Schedule group's id
     *
     * @category  Utility
     * @return    sch_type
     */
    public function getProjectParam($param = "")
    {

        $result = $this->conn->query(
            "SELECT `project_id`,
                    `url`
               FROM `project`
              WHERE " . $param
        );

        return $result;
    }

    /**
     * Get schedule_group for schedule
     *
     * @category  Utility
     * @return    sch_type
     */
    public function getProjectNoParam()
    {
        $result = $this->conn->query(
            "SELECT `project_id`,
                    `url`
               FROM `project`"
        );

        return $result;
    }

    /**
     * Update log
     *
     * @param string $msg some word
     *
     * @category  Utility
     * @return    sch_type
     */
    public function updateLog($msg)
    {
        $this->conn->query(
            "INSERT INTO `log`
                (`type`, `action`)
             VALUES ('project','$msg')"
        );

    }
}
