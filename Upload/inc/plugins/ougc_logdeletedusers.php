<?php

/***************************************************************************
 *
 *   OUGC Log Deleted Users plugin (/inc/plugins/ougc_logdeletedusers.php)
 *	 Author: Omar Gonzalez
 *   Copyright: © 2016 Omar Gonzalez
 *   
 *   Website: http://omarg.me
 *
 *   Logs deleted users information into threads.
 *
 ***************************************************************************
 
****************************************************************************
	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	
	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
****************************************************************************/

// Die if IN_MYBB is not defined, for security reasons.
defined('IN_MYBB') or die('This file cannot be accessed directly.');

// Run/Add Hooks
if(defined('IN_ADMINCP'))
{
	$plugins->add_hook('admin_config_settings_begin', 'ougc_logdeletedusers_lang_load');
}
$plugins->add_hook('datahandler_user_delete_start', 'ougc_logdeletedusers_run');

// PLUGINLIBRARY
defined('PLUGINLIBRARY') or define('PLUGINLIBRARY', MYBB_ROOT.'inc/plugins/pluginlibrary.php');

// Plugin API
function ougc_logdeletedusers_info()
{
	global $lang;
	ougc_logdeletedusers_lang_load();

	return array(
		'name'			=> 'OUGC Log Deleted Users plugin',
		'description'	=> $lang->setting_group_ougc_logdeletedusers_desc,
		'website'		=> 'http://omarg.me',
		'author'		=> 'Omar G.',
		'authorsite'	=> 'http://omarg.me',
		'version'		=> '1.8',
		'versioncode'	=> 1800,
		'compatibility'	=> '18*',
		'pl'			=> array(
			'version'	=> 12,
			'url'		=> 'http://mods.mybb.com/view/pluginlibrary'
		)
	);
}

// _activate() routine
function ougc_logdeletedusers_activate()
{
	global $PL, $cache, $lang;
	ougc_logdeletedusers_lang_load();
	ougc_logdeletedusers_pl_check();

	// Add settings group
	$PL->settings('ougc_logdeletedusers', $lang->setting_group_ougc_logdeletedusers, $lang->setting_group_ougc_logdeletedusers_desc, array(
		'forum'	=> array(
		   'title'			=> $lang->setting_ougc_logdeletedusers_forum,
		   'description'	=> $lang->setting_ougc_logdeletedusers_forum_desc,
		   'optionscode'	=> 'forumselectsingle',
			'value'			=>	-1,
		)
	));

	// Insert/update version into cache
	$plugins = $cache->read('ougc_plugins');
	if(!$plugins)
	{
		$plugins = array();
	}

	$info = ougc_logdeletedusers_info();

	if(!isset($plugins['logdeletedusers']))
	{
		$plugins['logdeletedusers'] = $info['versioncode'];
	}

	/*~*~* RUN UPDATES START *~*~*/

	/*~*~* RUN UPDATES END *~*~*/

	$plugins['logdeletedusers'] = $info['versioncode'];
	$cache->update('ougc_plugins', $plugins);
}

// _is_installed() routine
function ougc_logdeletedusers_is_installed()
{
	global $cache;

	$plugins = (array)$cache->read('ougc_plugins');

	return !empty($plugins['logdeletedusers']);
}

// _uninstall() routine
function ougc_logdeletedusers_uninstall()
{
	global $PL, $cache;
	ougc_logdeletedusers_pl_check();

	$PL->settings_delete('ougc_logdeletedusers');

	// Delete version from cache
	$plugins = (array)$cache->read('ougc_plugins');

	if(isset($plugins['logdeletedusers']))
	{
		unset($plugins['logdeletedusers']);
	}

	if(!empty($plugins))
	{
		$cache->update('ougc_plugins', $plugins);
	}
	else
	{
		$PL->cache_delete('ougc_plugins');
	}
}

// Loads language strings
function ougc_logdeletedusers_lang_load()
{
	global $lang;

	isset($lang->setting_group_ougc_logdeletedusers) or $lang->load('ougc_logdeletedusers', true);

	if(!isset($lang->setting_group_ougc_logdeletedusers))
	{
		$lang->setting_group_ougc_logdeletedusers = 'OUGC Log Deleted Users plugin';
		$lang->setting_group_ougc_logdeletedusers_desc = 'Logs deleted users information into threads.';

		$lang->setting_ougc_logdeletedusers_forum = 'Log Forum';
		$lang->setting_ougc_logdeletedusers_forum_desc = 'Please select the forum where threads should be created.';

		$lang->ougc_logdeletedusers_thread_subject = '{1} was deleted by {2}.';
		$lang->ougc_logdeletedusers_thread_message = '[list]
[*][b]Username:[/b] {1}
[*][b]E-mail:[/b] {2}
[*][b]Registration Date:[/b] {3}, {4}
[*][b]IP Address:[/b] {5}
[/list]';
		
		$lang->ougc_logdeletedusers_pluginlibrary = 'This plugin requires <a href="{1}">PluginLibrary</a> version {2} or later to be uploaded to your forum. Please install or update this library before continuing.';
	}
}

// PluginLibrary dependency check & load
function ougc_logdeletedusers_pl_check()
{
	global $lang;
	ougc_logdeletedusers_lang_load();
	$info = ougc_logdeletedusers_info();

	if(!file_exists(PLUGINLIBRARY))
	{
		flash_message($lang->sprintf($lang->ougc_logdeletedusers_pluginlibrary, $info['pl']['url'], $info['pl']['version']), 'error');
		admin_redirect('index.php?module=config-plugins');
		exit;
	}

	global $PL;

	$PL or require_once PLUGINLIBRARY;

	if($PL->version < $info['pl']['version'])
	{
		flash_message($lang->sprintf($lang->ougc_logdeletedusers_pluginlibrary, $info['pl']['url'], $info['pl']['version']), 'error');
		admin_redirect('index.php?module=config-plugins');
		exit;
	}
}

// Show additional group images routine
function ougc_logdeletedusers_run(&$dh)
{
	global $db, $mybb, $lang, $forum_cache;
	ougc_logdeletedusers_lang_load();

	$forum_cache or cache_forums();

	if(empty($forum_cache[($fid = (int)$mybb->settings['ougc_logdeletedusers_forum'])]))
	{
		return false;
	}

	require_once MYBB_ROOT.'inc/datahandlers/post.php';
	$posthandler = new PostDataHandler('insert');
	$posthandler->action = 'thread';

	$new_thread = array(
		'fid'			=> $fid,
		'icon'			=> -1,
		'uid'			=> $mybb->user['uid'] ? (int)$mybb->user['uid'] : 0,
		'username'		=> $mybb->user['uid'] ? $mybb->user['username'] : $lang->guest,
		'ipaddress'		=> $mybb->session->packedip,
		'savedraft'		=> 0,
		'options'		=> array(
			'signature'				=> 0,
			'subscriptionmethod'	=> 0,
			'disablesmilies'		=> 0
		),
	);

	require_once MYBB_ROOT.'inc/functions_indicators.php';

	$uids = implode("','", $dh->delete_uids);

	$query = $db->simple_select('users', 'username, email, regdate, regip', "uid IN ('{$uids}')");

	while($user = $db->fetch_array($query))
	{
		// IIRC we don't need to htmlspecialchars_uni'd here
		$new_thread['subject'] = $lang->sprintf($lang->ougc_logdeletedusers_thread_subject, $user['username'], $mybb->user['username']);
		$new_thread['message'] = $lang->sprintf($lang->ougc_logdeletedusers_thread_message, $user['username'], $user['email'], my_date($mybb->settings['dateformat'], $user['regdate'], '', false), my_date($mybb->settings['timeformat'], $user['regdate']), my_inet_ntop($db->unescape_binary($user['regip'])));
	
		$posthandler->set_data($new_thread);

		if($posthandler->validate_thread())
		{
			$thread_info = $posthandler->insert_thread();

			mark_thread_read($thread_info['tid'], $fid);
		}
	}
}












