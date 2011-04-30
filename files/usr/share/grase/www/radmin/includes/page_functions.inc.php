<?php

/* Copyright 2008 Timothy White */

/*  This file is part of GRASE Hotspot.

    http://hotspot.purewhite.id.au/

    GRASE Hotspot is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    GRASE Hotspot is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with GRASE Hotspot.  If not, see <http://www.gnu.org/licenses/>.
*/
require_once('php-gettext/gettext.inc');


require_once 'includes/database_functions.inc.php';
require_once 'includes/load_settings.inc.php';

require_once 'smarty/Smarty.class.php';
require_once 'smarty_sortby.php';

require_once 'includes/smarty-gettext.php';
/*ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . '/usr/share/grase/i18n/');

include_once 'library/piins.smarty/prefilter.i18N.php';
include_once 'library/piins.smarty/postfilter.i18n.php';
include_once 'library/piins.smarty/prefilter.smartyTags.php';
//include_once 'library/smarty/Smarty.class.php';
require_once 'library/piins.utils/FileUtils.php';*/




function css_file_version()
{
	//reading stream
	$handle = fopen("radmin.css", "r");
	//read first line, TODO:  check if it's not empty, etc.
	$first_line = fgets ($handle);
	$second_line = fgets ($handle);
	fclose($handle);
	//extract revision number, chosen format: "/* $Rev: 1424314 $ */"
	//$cssrevid = substr($first_line, 14, -3);
	$resourcefiles = array(
	    '/usr/share/grase/www/hotspot.css',
	    '/usr/share/grase/www/radmin/radmin.css',
	    '/usr/share/grase/www/js/grase.js');
	foreach($resourcefiles as $file)
	{
	    $fileversions[basename($file)] = date("YmdHis",filemtime($file));	
	}
    //	$cssrevid = date("YmdHis",filemtime("radmin.css"));	
	$application_version = substr($second_line, 13, -3);
	return array($fileversions, $application_version);
}

function createmenuitems()
{
	//	$menubar['id'] = array("href" => , "label" => );
	$menubar['main'] = array("href" => "./", "label" => T_("Status"));
	$menubar['users'] = array("href" => "display", "label" => T_("List Users"));
	$menubar['createuser'] = array("href" => "newuser", "label" => T_("New User"));
	$menubar['createtickets'] = array("href" => "newtickets", "label" => T_("Mass New Users"));	
	$menubar['createmachine'] = array("href" => "newmachine", "label" => T_("Computer Account"));	
	$menubar['sessions'] = array("href" => "sessions", "label" => T_("Monitor Sessions"));
    $menubar['reports'] = array("href" => "reports", "label" => T_("Reports"));
    //$menubar['monthly_accounts'] = array("href" => "datausage", "label" => "Monthly Reports"); // Not working atm TODO:
	$menubar['settings'] = array("href" => "settings", "label" => T_("Site Settings") );
	$menubar['uploadlogo'] = array("href" => "uploadlogo", "label" => T_("Site Logo") );	
	$menubar['links'] = array("href" => "links", "label" => T_("Useful Links"));	
	$menubar['passwd'] = array("href" => "passwd", "label" => T_("Admin Users") );
	$menubar['adminlog'] = array("href" => "adminlog", "label" => T_("Admin Log") );	
	
	$menubar['logout'] = array("href" => "./?logoff", "label" => T_("Logoff") );
	return $menubar;
}

function createusefullinks()
{
	#$links['radmin'] = array("href" => "/radmin", "label" => "Internet User Administration (Radmin, RADIUS Administration)");
	#$links['dglog'] = array("href" => "/cgi-bin/dglog.pl", "label" => "Dansguardian Log Viewer, for checking logs for attempts to view blocked pages");
	#$links['munin'] = array("href" => "/munin", "label" => "Munin, System Monitor Graphs");	
	$links['sysstatus'] = array("href" => "/grase/radmin/sysstatus", "label" => T_("System Status"));		
	return $links;
}

function datacosts()
{
	global $datacosts, $pricemb, $currency, $CurrencySymbols;
	$disp_currency = $CurrencySymbols[$currency];
	$datacosts[''] = '';
	$money_options = array($pricemb, 5, 10, 15, 20, 25, 30, 40, 50, 75, 100);
	foreach($money_options as $money)
	{
		$disp_money = number_format($money, 2);
		$data = round($money/$pricemb, 2);
		$disp_data = Formatting::formatBytes($data*1024*1024);
		$datacosts["$data"] = "$disp_currency$disp_money ($disp_data)";
	}
	return $datacosts;
}

function timecosts()
{
	global $timecosts, $pricetime, $currency, $CurrencySymbols;
	$disp_currency = $CurrencySymbols[$currency];
	$timecosts[''] = '';
	//$pricemb = $price; // 60c/Mb
	$time_options = array(5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 60, 90, 120, 180);
	foreach($time_options as $time)
	{
		$cost = displayLocales(number_format(round($pricetime*$time, 2),2), TRUE);
		$timecosts["$time"] = "$disp_currency$cost ($time mins)";
	}
	return $timecosts;
}

function gboctects()
{
    $gb_options = array(1, 2, 4, 5, 10, 100);
    foreach($gb_options as $gb)
    {
        $octects = $gb*1024*1048576;
        $label = "$gb GiB";
        $options[$octects] = $label;
    }
    return $options;
}

function usergroups()
{
	global $Usergroups;
	// TODO:  Move this stuff into database??
	$Usergroups["Visitors"] = T_("Visitors");
	$Usergroups["Students"] = T_("Students");
	$Usergroups["Staff"] = T_("Staff");
	$Usergroups["Ministry"] = T_("Ministry");
//	$Usergroups[MACHINE_GROUP_NAME] = "Machine (Locked)";
	return $Usergroups;
}

function groupexpirys()
{
	global $Expiry;
	// TODO: Move this stuff into database??
	$Expiry["Staff"] = "+6 months";
	$Expiry["Ministry"] = "+6 months";
	$Expiry["Students"] = "+3 months";
	$Expiry["Visitors"] = "+1 months";
//	$Expiry[MACHINE_GROUP_NAME] = "--";
	$Expiry[DEFAULT_GROUP_NAME] = "+1 months";
	return $Expiry;
}

function currency_symbols()
{
	global $CurrencySymbols;
	// TODO: install more locales and automate this?
	$CurrencySymbols['$'] = "$";
	$CurrencySymbols['¢'] = "&#162;";
	$CurrencySymbols['R'] = "R";
	$CurrencySymbols['£'] = "&pound;";
	$CurrencySymbols['€'] = "&euro;";
	$CurrencySymbols['¥'] = "&#165;";
	$CurrencySymbols['¤'] = "&#164;";
	return $CurrencySymbols;
}

function display_page($template)
{
	global $smarty;
	assign_vars();
	return $smarty->display($template);
}





$smarty = new Smarty;

$smarty->compile_check = true;
//$smarty->register_outputfilter('smarty_outputfilter_strip');
$smarty->register_modifier('bytes', array("Formatting", "formatBytes"));
$smarty->register_modifier('seconds', array("Formatting", "formatSec"));

// i18n
//$locale = (!isset($_GET["l"]))?"en_GB":$_GET["l"];  
$smarty->register_block('t', 'smarty_translate');
// TODO: Move this stuff to somewhere else?
$locale = $Settings->getSetting('locale');
//$locale = locale_accept_from_http("en_ZA");
//echo $locale;

if($locale == '') $locale = "en_AU";

Locale::setDefault($locale);
//echo Locale::getDefault();
$language =  locale_get_display_language($locale);
$region = locale_get_display_region($locale);
//echo "$language $region<br/>";
//print_r(displayLocales("-10000.11", TRUE)); 

//putenv("LC_ALL=$locale");
//$language = "Leet";
T_setlocale(LC_MESSAGES, $language);
//print_r(setlocale(LC_MESSAGES, NULL));
T_bindtextdomain("grase", "/usr/share/grase/locale");
T_textdomain("grase");


list($fileversions, $application_version)=css_file_version();
$smarty->assign("radmincssversion", $fileversions['radmin.css']);
$smarty->assign("hotspotcssversion", $fileversions['hotspot.css']);
$smarty->assign("grasejsversion", $fileversions['grase.js']);
$smarty->assign("application_version", $application_version);
$smarty->assign("Application", APPLICATION_NAME);

$smarty->assign("RealHostname", $realhostname);

// Setup Menus
$smarty->assign("MenuItems", createmenuitems());
$smarty->assign("Usergroups", usergroups());


// Costs
$smarty->assign("CurrencySymbols", currency_symbols());
$smarty->assign("Datacosts", datacosts());
$smarty->assign("Timecosts", timecosts());

$smarty->assign('gbvalues', gboctects());



function assign_vars()
{
	global $smarty, $sellable_data, $useable_data, $used_data, $sold_data;
	global $location, $website_name, $website_link;

	// Data
	$total_sellable_data = $sellable_data; 
	$smarty->assign("TotalSellableData", $total_sellable_data);
	$sold_data =  getSoldData();
	$smarty->assign("SoldOctets", $sold_data);
	$smarty->assign("SellableOctets", $total_sellable_data - $sold_data);
	$smarty->assign("SoldOctetsPercent", $sold_data/($total_sellable_data)*100);

	$total_useable_data = $useable_data; 
	$smarty->assign("TotalUseableData", $total_useable_data);
	$used_data =  getUsedData();
	$smarty->assign("DataUsageOctets", $used_data);
	$smarty->assign("DataRemainingOctets", $total_useable_data - $used_data);
	$smarty->assign("DataUsagePercent", $used_data/($total_useable_data)*100);

    // Settings
    $smarty->assign("Title", $location . " - " . APPLICATION_NAME);
    $smarty->assign("website_name", $website_name);
    $smarty->assign("website_link", $website_link);
    

	// last months usage
	$used_data =  getMonthUsedData(); // TODO: make it get last month that data is for?
	$smarty->assign("LastM_DataUsageOctets", $used_data);
	$smarty->assign("LastM_DataRemainingOctets", $total_useable_data - $used_data);
	$smarty->assign("LastM_DataUsagePercent", $used_data/($total_useable_data)*100);
}


groupexpirys();
?>
