#!/usr/bin/php -q
<?php
/**
* Updates our data with whats in cpanels db
* @author Joe Huss <detain@corpmail.interserver.net>
* @package MyAdmin
* @subpackage Scripts
* @subpackage update_cpanel_data
* @copyright 2017
*/

	require_once(__DIR__ . '/../../include/functions.inc.php');
	$webpage = false;
	define('VERBOSE_MODE', false);
	$show_help = false;
	$endprog = false;
	$module = 'licenses';
	$GLOBALS['tf']->accounts->set_db_module($module);
	$GLOBALS['tf']->history->set_db_module($module);
	$GLOBALS['tf']->session->create(160307, 'services');
	$GLOBALS['tf']->session->verify();
	$db = get_module_db($module);
	$softaculous_type = SERVICE_TYPES_SOFTACULOUS;
	$hostdates = 0;
	$good = 0;
	$cancels = 0;
	$unknowns = 0;
	$noc = new \Detain\MyAdminSoftaculous\SOFT_NOC(SOFTACULOUS_USERNAME, SOFTACULOUS_PASSWORD);
	$licenses = $noc->licenses();
	foreach ($licenses['licenses'] as $lid => $license)
	{
		$email = $license['authemail'];
		$key = $license['license'];
		$ip = $license['ip'];
		$custid = $GLOBALS['tf']->accounts->cross_reference($email);
		if ($custid === false)
		{
			echo "Couldnt match up {$email} for license ip {$ip} key {$key} to customer id\n";
			continue;
		}
		$esc_email = $db->real_escape($email);
		if (isset($license['hostname']) && trim($license['hostname']) != '')
		{
			$hostname = trim($license['hostname']);
			$esc_hostname = $db->real_escape($hostname);
			$query = "update licenses set license_hostname='{$hostname}' where license_ip='{$ip}'";
			if ($custid !== false)
			{
				$query .= " and license_custid={$custid}";
			}
			else
			{
				$query .= " and license_hostname=''";
			}
			$hostdates++;
			$db->query($query);
		}
		$db->query("select * from licenses where license_custid={$custid} and license_ip='{$ip}' and license_type in (select services_id from services where services_category={$softaculous_type} and services_module='{$module}')", __LINE__, __FILE__);
		$status = 'unknown';
		while ($db->next_record(MYSQL_ASSOC))
		{
			if ($db->Record['license_status'] == 'active')
			{
				$status = 'active';
			}
			elseif ($status != 'active')
			{
				$status = $db->Record['license_status'];
			}
		}
		if ($status == 'unknown')
		{
			echo "Couldnt find any order for Softaculous License {$ip}\n";
			$unknowns++;
		}
		elseif ($status != 'active')
		{
			echo "I wanted to cancel with refund {$ip} {$key}\n";
			deactivate_softaculous($ip);
			$cancels++;
		}
		else
		{
			$good++;
		}
	}
echo "
Hostname Updates {$hostdates}
Good Softaculous Licensese {$good}
Cancable Softaculous Licenses {$cancels}
Unknown Licenses {$unknowns}
";
	$GLOBALS['tf']->session->destroy();