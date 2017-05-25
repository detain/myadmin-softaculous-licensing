<?php
/* TODO:
 - service type, category, and services  adding
 - dealing with the SERVICE_TYPES_softaculous define
 - add way to call/hook into install/uninstall
*/
return [
	'name' => 'Softaculous Licensing',
	'description' => 'Allows selling of Softaculous Server and VPS License Types.  More info at https://www.netenberg.com/softaculous.php',
	'help' => 'It provides more than one million end users the ability to quickly install dozens of the leading open source content management systems into their web space.  	Must have a pre-existing cPanel license with cPanelDirect to purchase a softaculous license. Allow 10 minutes for activation.',
	'module' => 'licenses',
	'author' => 'detain@interserver.net',
	'home' => 'https://github.com/detain/myadmin-softaculous',
	'repo' => 'https://github.com/detain/myadmin-softaculous',
	'version' => '1.0.0',
	'type' => 'licenses',
	'hooks' => [
		'function.requirements' => ['Detain\MyAdminSoftaculous\Plugin', 'Requirements'],
		'licenses.settings' => ['Detain\MyAdminSoftaculous\Plugin', 'Settings'],
		'licenses.activate' => ['Detain\MyAdminSoftaculous\Plugin', 'Activate'],
		'licenses.change_ip' => ['Detain\MyAdminSoftaculous\Plugin', 'ChangeIp'],
		'ui.menu' => ['Detain\MyAdminSoftaculous\Plugin', 'Menu']
	],
];
