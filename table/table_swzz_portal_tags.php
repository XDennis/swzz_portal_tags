<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_swzz_portal_tags.php Powered by www.siwenzaizi.cn.
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_swzz_portal_tags extends discuz_table {

	public function __construct() {
		$this->_table = 'swzz_portal_tags';
		$this->_pk = 'aid';

		parent::__construct();
	}
	
	public function fetch_tags_by_aid($aid) {
		return DB::result_first("SELECT tags FROM %t WHERE aid=%d", array($this->_table, $aid));
	}

	public function count_by_aid($aid) {
		return DB::result_first("SELECT COUNT(*) FROM %t WHERE aid=%d", array($this->_table, $aid));
	}

	public function update_by_aid($aid, $tags) {

		return DB::update($this->_table, $tags, DB::field('aid', $aid));
	}


	
}