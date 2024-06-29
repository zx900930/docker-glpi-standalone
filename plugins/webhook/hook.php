<?php
/*
 -------------------------------------------------------------------------
 Webhook plugin for GLPI
 Copyright (C) 2020-2022 by Eric Feron.
 -------------------------------------------------------------------------

 LICENSE
      
 This file is part of Webhook.

 Webhook is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 at your option any later version.

 Webhook is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Webhook. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

function plugin_webhook_install() {
   global $DB;

//   include_once (Plugin::getPhpDir("webhook")."/inc/profile.class.php");

	if (!$DB->TableExists("glpi_plugin_webhook_configs")) {

		$DB->runFile(Plugin::getPhpDir("webhook")."/sql/empty-1.0.4.sql");
	}
	else {
		if ($DB->TableExists("glpi_plugin_webhook_secrettypes")) {
			$query = "SELECT name FROM glpi_plugin_webhook_secrettypes WHERE id=2";
			$result = $DB->query($query);
			while ($data = $DB->fetchRow($result)) {
				if ($data[0] == "Encoded Basic Authentication") {
					$DB->runFile(Plugin::getPhpDir("webhook")."/sql/update-1.0.2.sql");
					Session::addMessageAfterRedirect('<font color="red"><b>'.__('!! Authentication types have been modified !!<br/>!! You should review them in Webhooks configuration !!').'</b></font>');
				}
			}
		}
		if (!$DB->FieldExists("glpi_plugin_webhook_configs","debug")) {
			$DB->runFile(Plugin::getPhpDir("webhook")."/sql/update-1.0.3.sql");
		}
	}


	Config::setConfigurationValues('core', ['notifications_webhook' => 0]);
	Config::setConfigurationValues('plugin:webhook', ['webhook_max_retries' => '6',
														'webhook_retry_time'   => '5'
													]
									);
	PluginWebhookProfile::initProfile();
	PluginWebhookProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);
	return true;
}

function plugin_webhook_uninstall() {
	global $DB;
   
	$config = new Config();
	$config->deleteConfigurationValues('core', ['notifications_webhook']);
   
	$tables = ["glpi_plugin_webhook_configs", "glpi_plugin_webhook_secrettypes", "glpi_plugin_webhook_operationtypes"];

	foreach($tables as $table)
		$DB->query("DROP TABLE IF EXISTS `$table`;");

	$tables_glpi = ["glpi_displaypreferences",
               "glpi_documents_items",
               "glpi_savedsearches",
               "glpi_logs",
               "glpi_items_tickets",
               "glpi_notepads",
               "glpi_dropdowntranslations"];

	foreach($tables_glpi as $table_glpi)
		$DB->query("DELETE FROM `$table_glpi` WHERE `itemtype` LIKE 'PluginWebhook%' ;");

	return true;
}


// Define Dropdown tables to be managed in GLPI :
function plugin_webhook_getDropdown() {

   $plugin = new Plugin();
   if ($plugin->isActivated("webhook"))
		return ["PluginWebhookSecrettype"=>PluginWebhookSecrettype::getTypeName(2)];
   else
      return [];
}

////// SEARCH FUNCTIONS ///////() {

function plugin_webhook_getAddSearchOptions($itemtype) {

   $sopt=[];

   return $sopt;
}

?>
