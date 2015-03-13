<?php
/**
 *	[斯文在兹门户标签(swzz_portal_tags.{modulename})] (C)2014-2099 Powered by www.siwenzaizi.cn.
 *	Version: 1.0.0
 *	Date: 2014-10-16 17:22
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
class plugin_swzz_portal_tags {

	function plugin_swzz_portal_tags() {
		global $_G;
		if(!$_G['uid']) {
			return;
		}

		if(submitcheck("articlesubmit")) {
			$tags = $_POST['tags'];
			$aid = intval($_GET['aid']);
			if(!$aid) {
				$aid = DB::result_first("SELECT aid FROM %t ORDER BY aid DESC LIMIT 1", array('portal_article_title'));
				$aid += 1;
			}

			if(C::t('#swzz_portal_tags#swzz_portal_tags')->count_by_aid($aid)) {
				C::t('#swzz_portal_tags#swzz_portal_tags')->update_by_aid($aid, array('tags' => $tags));
				$this->update_field($tags, $aid, 'aid');
			}
			else {
				C::t('#swzz_portal_tags#swzz_portal_tags')->insert(array('aid' => $aid, 'tags' => $tags));
				$this->add_tag($tags, $aid, 'aid');
			}
			
		}

	}

	function add_tag($tags, $itemid, $idtype = 'aid', $returnarray = 0) {
		if($tags == '') {
			return;
		}

		$tags = str_replace(array(chr(0xa3).chr(0xac), chr(0xa1).chr(0x41), chr(0xef).chr(0xbc).chr(0x8c)), ',', censor($tags));
		if(strexists($tags, ',')) {
			$tagarray = array_unique(explode(',', $tags));
		} else {
			$langcore = lang('core');
			$tags = str_replace($langcore['fullblankspace'], ' ', $tags);
			$tagarray = array_unique(explode(' ', $tags));
		}
		$tagcount = 0;
		foreach($tagarray as $tagname) {
			$tagname = trim($tagname);
			if(preg_match('/^([\x7f-\xff_-]|\w|\s){2,20}$/', $tagname)) {
				$status = $idtype != 'uid' ? 0 : 3;
				$result = C::t('#swzz_portal_tags#swzz_common_tag')->get_bytagname($tagname, $idtype);
				if($result['tagid']) {
					if($result['status'] == $status) {
						$tagid = $result['tagid'];
					}
				} else {
					$tagid = C::t('#swzz_portal_tags#swzz_common_tag')->insert($tagname,$status);
				}
				if($tagid) {
					if($itemid) {
						C::t('#swzz_portal_tags#swzz_common_tagitem')->replace($tagid,$itemid,$idtype);
					}
					$tagcount++;
					if(!$returnarray) {
						$return .= $tagid.','.$tagname."\t";
					} else {
						$return[$tagid] = $tagname;
					}

				}

			}
		}
		return $return;
	}

	function update_field($tags, $itemid, $idtype = 'aid', $typeinfo = array()) {

		if($idtype == 'aid') {
			$tagstr = C::t('#swzz_portal_tags#swzz_portal_tags')->fetch_tags_by_aid($itemid);

		} else {
			return '';
		}

		$tagarray = $tagidarray = $tagarraynew = array();
		$results = C::t('#swzz_portal_tags#swzz_common_tagitem')->select(0, $itemid, $idtype);
		foreach($results as $result) {
			$tagidarray[] = $result['tagid'];
		}
		if($tagidarray) {
			$results = C::t('#swzz_portal_tags#swzz_common_tag')->get_byids($tagidarray);
			foreach($results as $result) {
				$tagarray[$result[tagid]] = $result['tagname'];
			}
		}
		$tags = $this->add_tag($tags, $itemid, $idtype, 1);
		foreach($tags as $tagid => $tagname) {
			$tagarraynew[] = $tagname;
			if(empty($tagarray[$tagid])) {
				$tagstr = $tagstr.$tagid.','.$tagname."\t";
			}
		}
		foreach($tagarray as $tagid => $tagname) {
			if(!in_array($tagname, $tagarraynew)) {
				C::t('#swzz_portal_tags#swzz_common_tagitem')->delete($tagid, $itemid, $idtype);
				$tagstr = str_replace("$tagid,$tagname\t", '', $tagstr);
			}
		}
		return $tagstr;
	}

}

class plugin_swzz_portal_tags_portal extends plugin_swzz_portal_tags {

	/**
	 * @Methods describe
	 * @return string type
	 */
	public function portalcp_middle() {
		$aid = intval($_GET['aid']);
		$showtags=	C::t('#swzz_portal_tags#swzz_portal_tags')->fetch_tags_by_aid($aid);

		return '<div class="bm bml">'.
		'<div class="bm_h cl"><h2>Tag 标签</h2></div>'.
		'<div class="bm_c"><input type="text" id="tags" name="tags"  class="px" maxlength="120" size="80" value="'.$showtags.'"></div>'.
		'<div class="bm_h cl">多个Tag 用空格隔开，例如：书房 问卷 斯文在兹</div>';
	}

	/**
	 * @Methods describe
	 * @return string type
	 */
	public function view_article_content() {
		$aid = intval($_GET['aid']);
		$showtags=	C::t('#swzz_portal_tags#swzz_portal_tags')->fetch_tags_by_aid($aid);
		

		$tagids = $taglist = array();
		$query = C::t('#swzz_portal_tags#swzz_common_tagitem')->select(0 , $aid, 'aid', '', '', 0);
		foreach($query as $result) {
			$tagids[$result['tagid']] = $result['tagid'];
		}
		if(!empty($tagids)) {
			foreach(C::t('#swzz_portal_tags#swzz_common_tag')->get_byids($tagids) as$tags) {
				$articlelist[$tags['tagid']] = $tags;
				$htmltags .= '<li><a href="tag.php?id='.$tags['tagid'].'" class="list-group-item" target="_blank">'.$tags['tagname'].'</a></li>';
			}
			return '<div class="taglist"><ul class="list-inline"><li><a class="list-group-item active">标签：</a></li>'.$htmltags.'</ul></div>';		
		}
			return '<p><br /><br /></p>';
    }

}

?>