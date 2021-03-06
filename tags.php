<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: tag.php 32232 2012-12-03 08:57:08Z zhangjie $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$id = intval($_GET['id']);
$type = trim($_GET['type']);//虽然代码按照type来编写，但未做check，只是确保article是正确的
$name = trim($_GET['name']);
$page = intval($_GET['page']);
if($type == 'countitem') {
	//获取tagid对应的文章数目
	$num = 0;
	if($id) {
		$num = C::t('#swzz_portal_tags#swzz_common_tagitem')->count_by_tagid($id);
	}
	include_once template('tag/tag');
	exit();
}
$taglang = lang('tag/template', 'tag');
if($id || $name) {
	//有参数tagid，或者tagname的情况

	//分页信息在文章分类未启用
	$tpp = 20;
	$page = max(1, intval($page));
	$start_limit = ($page - 1) * $tpp;
	if($id) {
		//按照tagid，获得这个tag的tagid，tagname，status
		$tag = C::t('#swzz_portal_tags#swzz_common_tag')->fetch_info($id);
	} else {
		if(!preg_match('/^([\x7f-\xff_-]|\w|\s)+$/', $name) || strlen($name) > 20) {
			showmessage('parameters_error');
		}
		//按照tagname，获得这个tag的tagid，tagname，status
		$name = addslashes($name);
		$tag = C::t('#swzz_portal_tags#swzz_common_tag')->fetch_info(0, $name);
	}

	if($tag['status'] == 1) {
		showmessage('tag_closed');
	}
	$tagname = $tag['tagname'];
	$id = $tag['tagid'];
	$searchtagname = $name;
	$navtitle = $tagname ? $taglang.' - '.$tagname : $taglang;
	$metakeywords = $tagname ? $taglang.' - '.$tagname : $taglang;
	$metadescription = $tagname ? $taglang.' - '.$tagname : $taglang;


	$showtype = '';
	$count = '';
	$summarylen = 300;

	if($type == 'thread') {
		$showtype = 'thread';
		$tidarray = $threadlist = array();
		$count = C::t('common_tagitem')->select($id, 0, 'tid', '', '', 0, 0, 0, 1);
		if($count) {
			$query = C::t('common_tagitem')->select($id, 0, 'tid', '', '', $start_limit, $tpp);
			foreach($query as $result) {
				$tidarray[$result['itemid']] = $result['itemid'];
			}
			$threadlist = getthreadsbytids($tidarray);
			$multipage = multi($count, $tpp, $page, "misc.php?mod=tag&id=$tag[tagid]&type=thread");
		}
	} elseif($type == 'blog') {
		$showtype = 'blog';
		$blogidarray = $bloglist = array();
		$count = C::t('common_tagitem')->select($id, 0, 'blogid', '', '', 0, 0, 0, 1);
		if($count) {
			$query = C::t('common_tagitem')->select($id, 0, 'blogid', '', '', $start_limit, $tpp);
			foreach($query as $result) {
				$blogidarray[$result['itemid']] = $result['itemid'];
			}
			$bloglist = getblogbyid($blogidarray);

			$multipage = multi($count, $tpp, $page, "misc.php?mod=tag&id=$tag[tagid]&type=blog");
		}
	} else {
		$shownum = 0;

		$tidarray = $threadlist = array();
		$query = C::t('#swzz_portal_tags#swzz_common_tagitem')->select($id, 0, 'tid', '', '', $shownum);
		foreach($query as $result) {
			$tidarray[$result['itemid']] = $result['itemid'];
		}
		$threadlist = getthreadsbytids($tidarray);

		$aidarray = $articlelist = array();
		$query = C::t('#swzz_portal_tags#swzz_common_tagitem')->select($id, 0, 'aid', '', '', $shownum);
		foreach($query as $result) {
			$aidarray[$result['itemid']] = $result['itemid'];
		}
		$articlelist = getarticlesbyaids($aidarray);

		if(helper_access::check_module('blog')) {
			$blogidarray = $bloglist = array();
			$query = C::t('#swzz_portal_tags#swzz_common_tagitem')->select($id, 0, 'blogid', '', '', $shownum);
			foreach($query as $result) {
				$blogidarray[$result['itemid']] = $result['itemid'];
			}
			$bloglist = getblogbyid($blogidarray);
		}

	}

	//具体的tag对应的内容列表页
	include_once template('tag/tagitem');

} else {
	//获取所有的tag列表
	$navtitle = $metakeywords = $metadescription = $taglang;
	$viewthreadtags = 100;
	$tagarray = array();
	//$query = C::t('#swzz_portal_tags#swzz_common_tag')->fetch_all_by_status(0, '', $viewthreadtags, 0, 0, '');
	//$query = DB::fetch_all("SELECT * FROM ".DB::table("pre_swzz_common_tag")." WHERE tagid in (select tagid from ".DB::table("pre_swzz_common_tagitem").") ORDER BY tagname");
	//the above codes can't be allowed by dz, this like select * (select *)	

	$tagidarray = array();
	$query = DB::fetch_all("SELECT distinct tagid FROM ".DB::table("swzz_common_tagitem"));
	foreach($query as $result) {
			$tagidarray[$result['tagid']] = $result['tagid'];
	}

	$query = DB::fetch_all("SELECT * FROM ".DB::table("swzz_common_tag")." where tagid in (".dimplode($tagidarray).") ORDER BY tagname");

	foreach($query as $result) {
		$tagarray[] = $result;
	}
	include_once template('tag/tag');
}

function getthreadsbytids($tidarray) {
	global $_G;

	$threadlist = array();
	if(!empty($tidarray)) {
		loadcache('forums');
		include_once libfile('function_misc', 'function');
		$fids = array();
		foreach(C::t('forum_thread')->fetch_all_by_tid($tidarray) as $result) {
			if(!isset($_G['cache']['forums'][$result['fid']]['name'])) {
				$fids[$result['fid']] = $result['tid'];
			} else {
				$result['name'] = $_G['cache']['forums'][$result['fid']]['name'];
			}
			$threadlist[$result['tid']] = procthread($result);
		}
		if(!empty($fids)) {
			foreach(C::t('forum_forum')->fetch_all_by_fid(array_keys($fids)) as $fid => $forum) {
				$_G['cache']['forums'][$fid]['forumname'] = $forum['name'];
				$threadlist[$fids[$fid]]['forumname'] = $forum['name'];
			}
		}
	}
	return $threadlist;
}

function getblogbyid($blogidarray) {
	global $_G;

	$bloglist = array();
	if(!empty($blogidarray)) {
		$data_blog = C::t('home_blog')->fetch_all($blogidarray, 'dateline', 'DESC');
		$data_blogfield = C::t('home_blogfield')->fetch_all($blogidarray);

		require_once libfile('function/spacecp');
		require_once libfile('function/home');
		$classarr = array();
		foreach($data_blog as $curblogid => $result) {
			$result = array_merge($result, (array)$data_blogfield[$curblogid]);
			$result['dateline'] = dgmdate($result['dateline']);
			$classarr = getclassarr($result['uid']);
			$result['classname'] = $classarr[$result[classid]]['classname'];
			if($result['friend'] == 4) {
				$result['message'] = $result['pic'] = '';
			} else {
				$result['message'] = getstr($result['message'], $summarylen, 0, 0, 0, -1);
			}
			$result['message'] = preg_replace("/&[a-z]+\;/i", '', $result['message']);
			if($result['pic']) {
				$result['pic'] = pic_cover_get($result['pic'], $result['picflag']);
			}
			$bloglist[] = $result;
		}
	}
	return $bloglist;
}

function getarticlesbyaids($aidarray) {
	global $_G;

	$articlelist = array();
	if(!empty($aidarray)) {
		$wheresql = 'aid IN ('.dimplode($aidarray).')';
		$query = C::t('portal_article_title')->fetch_all_by_sql($wheresql, '', 0, 0, 0, 'at');
		foreach($query as $result) {
			$articlelist[$result['aid']] = $result;
		}
	}
	return $articlelist;
}
?>