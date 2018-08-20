<?php
define('DIR_ROOT', realpath(dirname(__FILE__) . '/../'));

define('DIR_LOG', DIR_ROOT . '/log');
define('DIR_CSS', DIR_ROOT . '/css');
define('DIR_CFG', DIR_ROOT . '/cfg');
define('DIR_IMG', DIR_ROOT . '/img');
define('DIR_INC', DIR_ROOT . '/inc');
	define('DIR_DB', DIR_INC . '/db');
	define('DIR_JS', DIR_INC . '/js');
	define('DIR_PHP', DIR_INC . '/php');
define('DIR_LIB', DIR_ROOT . '/lib');
define('DIR_TPL', DIR_ROOT . '/tpl');
define('DIR_TMP', DIR_ROOT . '/tmp');


define('NOT_FOUND', 404);
define('OK', 200);
define('FORBIDDEN', 403);
define('BAD_REQUEST', 400);
define('KO', 400);
define('NO_CONTENT', 204);
define('CONFLICT', 409);
define('NOT_ACCEPTABLE', 406);

