<?php

if ( ! defined('RSVP_NAME'))
{
	define('RSVP_NAME', 'RSVP');
	define('RSVP_CLASS', 'Rsvp');
	define('RSVP_VERSION', '1.2.2');
	define('RSVP_DOCS', 'https://github.com/expressodev/rsvp');
}

$config['name'] = RSVP_NAME;
$config['version'] = RSVP_VERSION;
$config['nsm_addon_updater']['versions_xml'] = 'http://exp-resso.com/rss/rsvp/versions.rss';
