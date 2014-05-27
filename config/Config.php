<?php
/**
 * Config File
 *
 * PHP version 5
 *
 * LICENSE: none
 *
 * @category  Config
 * @package   PackageName
 * @author    Patrick Her <zivhsiao@gmail.com>
 * @copyright 1997-2005 The PHP Group
 * @license   none <none>
 * @version   SVN: $Id$
 * @link      none
 */
namespace dispatch;

SQLService::$host = '127.0.0.1';
SQLService::$dbname = 'NSC';
SQLService::$user = 'root';
SQLService::$password = 'openfoundry';

// 檢查時間
ProxyCheck::$chkAllTime = 10;

// 額外的程式
ProxyCheck::$extraProgram = "php ./crawler/main.php ";

// 檢查方式 project , url
ProxyCheck::$chkType = "project";

// 檢查時間
ProxyCheck::$chkTime = "240";
// 日期
date_default_timezone_set('Asia/Taipei');

