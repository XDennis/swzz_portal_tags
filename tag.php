<?php

/**
 *      This file located at dz root path
 *		copyed from system file misc.php
 *		deleted some codes
 *		edited at 20141026 by Dennis
 *
 *      $Id: tag.php 34264 2013-11-27 03:14:58Z nemohou $
 */

if(isset($_GET['css'])) {
	$css = explode('|', $_GET['css']);
	$string = '';
	$size = 0;
	foreach($css as $file) {
		if(preg_match('/^\w+$/', $file)) {
			$file = './data/cache/style_'.$file.'.css';
			$string .= @implode('', file($file));
		}
	}
	ob_start('ob_gzhandler');
	header('Content-Type: text/css');
	header('Expires: '.gmdate('D, d M Y H:i:s', time() + 2592000).' GMT');
	header('Last-Modified: '.gmdate('D, d M Y H:i:s', time()).' GMT');
	echo $string;
	exit;
}
if(isset($_GET['js'])) {
	$js = explode('|', $_GET['js']);
	$string = '';
	$size = 0;
	foreach($js as $file) {
		$file = substr($file, 0, strpos($file, '.'));
		if(preg_match('/^\w+$/', $file)) {
			$file = './data/cache/'.$file.'.js';
			$string .= @implode('', file($file));
		}
	}
	ob_start('ob_gzhandler');
	header('Content-Type: text/javascript');
	header('Expires: '.gmdate('D, d M Y H:i:s', time() + 2592000).' GMT');
	header('Last-Modified: '.gmdate('D, d M Y H:i:s', time()).' GMT');
	echo $string;
	exit;
}

define('APPTYPEID', 100);
define('CURSCRIPT', 'tag');


require './source/class/class_core.php';

$discuz = C::app();

$discuz->reject_robot();
$modarray = array('seccode', 'secqaa', 'initsys', 'invite', 'faq', 'report',
				'swfupload', 'manyou', 'stat', 'ranklist', 'buyinvitecode',
				'tag', 'diyhelp', 'mobile', 'patch', 'getatuser', 'imgcropper',
				'userstatus', 'signin');

$modcachelist = array(
	'ranklist' => array('forums', 'diytemplatename'),
);

$mod = getgpc('mod');
$mod = (empty($mod) || !in_array($mod, $modarray)) ? 'error' : $mod;

if(in_array($mod, array('seccode', 'secqaa', 'initsys', 'faq', 'swfupload', 'mobile'))) {
	define('ALLOWGUEST', 1);
}

$cachelist = array();
if(isset($modcachelist[$mod])) {
	$cachelist = $modcachelist[$mod];
}

$discuz->cachelist = $cachelist;

$discuz->init();

define('CURMODULE', $mod);
runhooks();

require DISCUZ_ROOT.'./source/plugin/swzz_portal_tags/tags.php';

?>