<?php
// Local lab file for zbx01. Contains a password: do not upload to Git.
// Install as: /etc/zabbix/web/zabbix.conf.php   (owner apache:apache, mode 600)
// Then: sudo systemctl restart httpd php-fpm

$DB['TYPE']       = 'MYSQL';
$DB['SERVER']     = '192.168.118.129';
$DB['PORT']       = '3306';
$DB['DATABASE']   = 'zabbix';
$DB['USER']       = 'zabbix';
$DB['PASSWORD']   = 'Zbx#Lab2026';
$DB['SCHEMA']     = '';
$DB['ENCRYPTION'] = false;

$ZBX_SERVER      = 'localhost';
$ZBX_SERVER_PORT = '10051';
$ZBX_SERVER_NAME = 'zbx01';

$IMAGE_FORMAT_DEFAULT = IMAGE_FORMAT_PNG;
