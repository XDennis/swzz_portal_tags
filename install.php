<?php
/**
 *	[斯文在兹门户标签(swzz_portal_tags.{modulename})] (C)2014-2099 Powered by www.siwenzaizi.cn.
 *	Version: 1.0.0
 *	Date: 2014-10-16 17:22
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = <<<EOF

CREATE TABLE IF NOT EXISTS pre_swzz_portal_tags (
  `aid` mediumint(8) unsigned NOT NULL,
  `tags` varchar(255) NULL,
  PRIMARY KEY (`aid`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS pre_swzz_common_tag (
  `tagid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `tagname` char(20) NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`tagid`),
  KEY `tagname` (`tagname`),
  KEY `status` (`status`,`tagid`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS pre_swzz_common_tagitem (
  `tagid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `itemid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `idtype` char(10) NOT NULL DEFAULT '',
  UNIQUE KEY `item` (`tagid`,`itemid`,`idtype`),
  KEY `idtype` (`idtype`,`itemid`)
) ENGINE=MyISAM;

EOF;

runquery($sql);

$finish = TRUE;

?>