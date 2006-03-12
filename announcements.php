<?php
/**
 * MyBB 1.0
 * Copyright � 2005 MyBulletinBoard Group, All Rights Reserved
 *
 * Website: http://www.mybboard.com
 * License: http://www.mybboard.com/eula.html
 *
 * $Id$
 */

$templatelist = "announcement";
require "./global.php";
require "./inc/functions_post.php";

// Load global language phrases
$lang->load("announcements");

$aid = intval($mybb->input['aid']);

$plugins->run_hooks("announcements_start");

// Get announcement fid
$query = $db->simple_select(TABLE_PREFIX."announcements", "fid", "aid='$aid'");
$announcement = $db->fetch_array($query);

if(!$announcement)
{
	error($lang->error_invalidannouncement);
}

// Get forum info
$fid = $announcement['fid'];
$forum = get_forum($fid);

if(!$forum)
{
	error($lang->error_invalidforum);
}

// Make navigation
makeforumnav($forum['fid']);
addnav($lang->nav_announcements);

// Permissions
$forumpermissions = forum_permissions($forum['fid']);
$parentlist = $forum['parentlist'];

if($forumpermissions['canview'] == "no" || $forumpermissions['canviewthreads'] == "no")
{
	nopermission();
}

// Get announcement info
$time = time();
$query = $db->query("
	SELECT u.*, a.*, f.*, g.title AS grouptitle, g.usertitle AS groupusertitle, g.stars AS groupstars, g.starimage AS groupstarimage, g.image AS groupimage, g.namestyle, g.usereputationsystem
	FROM ".TABLE_PREFIX."announcements a
	LEFT JOIN ".TABLE_PREFIX."users u ON (u.uid=a.uid)
	LEFT JOIN ".TABLE_PREFIX."userfields f ON (f.ufid=u.uid)
	LEFT JOIN ".TABLE_PREFIX."usergroups g ON (g.gid=u.usergroup)
	WHERE a.startdate<='$time' AND a.enddate>='$time' AND a.aid='$aid'
");
$announcementarray = $db->fetch_array($query);

$announcementarray['dateline'] = $announcementarray['startdate'];
$announcementarray['userusername'] = $announcementarray['username'];
$announcement = makepostbit($announcementarray, 3);
$lang->forum_announcement = sprintf($lang->forum_announcement, $announcementarray['subject']);

$plugins->run_hooks("announcements_end");

eval("\$forumannouncement = \"".$templates->get("announcement")."\";");
outputpage($forumannouncement);
?>