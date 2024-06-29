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

// Init the hooks of the plugins -Needed
function plugin_init_webhook() {
	global $PLUGIN_HOOKS, $DB;

	$PLUGIN_HOOKS['csrf_compliant']['webhook'] = true;
	$PLUGIN_HOOKS['change_profile']['webhook'] = ['PluginWebhookProfile', 'initProfile'];
	$PLUGIN_HOOKS['item_add_targets']['webhook'] = [
		'NotificationTarget' => 'plugin_webhook_add_targets',
		'NotificationTargetTicket' => 'plugin_webhook_add_targets',
		'NotificationTargetUser' => 'plugin_webhook_add_targets',
		'NotificationTargetReservation' => 'plugin_webhook_add_targets',
		'NotificationTargetDBConnection' => 'plugin_webhook_add_targets',
		'NotificationTargetSavedSearch_Alert' => 'plugin_webhook_add_targets',
		'NotificationTargetCartridgeItem' => 'plugin_webhook_add_targets',
		'NotificationTargetCertificate' => 'plugin_webhook_add_targets',
		'NotificationTargetChange' => 'plugin_webhook_add_targets',
		'NotificationTargetConsumableItem' => 'plugin_webhook_add_targets',
		'NotificationTargetContract' => 'plugin_webhook_add_targets',
		'NotificationTargetDomain' => 'plugin_webhook_add_targets',
		'NotificationTargetFieldUnicity' => 'plugin_webhook_add_targets',
		'NotificationTargetInfocom' => 'plugin_webhook_add_targets',
		'NotificationTargetSoftwareLicense' => 'plugin_webhook_add_targets',
		'Glpi\Marketplace\NotificationTargetController' => 'plugin_webhook_add_targets',
		'NotificationTargetObjectLock' => 'plugin_webhook_add_targets',
		'NotificationTargetPlanningRecall' => 'plugin_webhook_add_targets',
		'NotificationTargetProblem' => 'plugin_webhook_add_targets',
		'NotificationTargetProject' => 'plugin_webhook_add_targets',
		'NotificationTargetProjectTask' => 'plugin_webhook_add_targets',
		'NotificationTargetMailCollector' => 'plugin_webhook_add_targets',
		'NotificationTargetCommonITILObject' => 'plugin_webhook_add_targets',
		'PluginFormcreatorNotificationTargetFormAnswer' => 'plugin_webhook_add_targets',
		'PluginStatecheckNotificationTargetRule' => 'plugin_webhook_add_targets'];
	$PLUGIN_HOOKS['item_action_targets']['webhook'] = [
		'NotificationTarget' => 'plugin_webhook_action_targets',
		'NotificationTargetTicket' => 'plugin_webhook_action_targets',
		'NotificationTargetUser' => 'plugin_webhook_action_targets',
		'NotificationTargetReservation' => 'plugin_webhook_action_targets',
		'NotificationTargetDBConnection' => 'plugin_webhook_action_targets',
		'NotificationTargetSavedSearch_Alert' => 'plugin_webhook_action_targets',
		'NotificationTargetCartridgeItem' => 'plugin_webhook_action_targets',
		'NotificationTargetCertificate' => 'plugin_webhook_action_targets',
		'NotificationTargetChange' => 'plugin_webhook_action_targets',
		'NotificationTargetConsumableItem' => 'plugin_webhook_action_targets',
		'NotificationTargetContract' => 'plugin_webhook_action_targets',
		'NotificationTargetDomain' => 'plugin_webhook_action_targets',
		'NotificationTargetFieldUnicity' => 'plugin_webhook_action_targets',
		'NotificationTargetInfocom' => 'plugin_webhook_action_targets',
		'NotificationTargetSoftwareLicense' => 'plugin_webhook_action_targets',
		'Glpi\Marketplace\NotificationTargetController' => 'plugin_webhook_action_targets',
		'NotificationTargetObjectLock' => 'plugin_webhook_action_targets',
		'NotificationTargetPlanningRecall' => 'plugin_webhook_action_targets',
		'NotificationTargetProblem' => 'plugin_webhook_action_targets',
		'NotificationTargetProject' => 'plugin_webhook_action_targets',
		'NotificationTargetProjectTask' => 'plugin_webhook_action_targets',
		'NotificationTargetMailCollector' => 'plugin_webhook_action_targets',
		'NotificationTargetCommonITILObject' => 'plugin_webhook_action_targets',
		'PluginFormcreatorNotificationTargetFormAnswer' => 'plugin_webhook_action_targets',
		'PluginStatecheckNotificationTargetRule' => 'plugin_webhook_action_targets'];
	$plugin = new Plugin();
	if ($plugin->isActivated('webhook')) {
		Notification_NotificationTemplate::registerMode(
         'webhook',							//mode itself
         __('Webhook', 'webhook'),			//label
         'webhook'							//plugin name
		);
		Plugin::registerClass('PluginWebhookProfile',
                         ['addtabon' => 'Profile']);
                         
   //add menu to config form
		if (Session::getLoginUserID()) {

			if (Session::haveRight("plugin_webhook", READ)
			|| Session::haveRight("config", UPDATE)) {
				$PLUGIN_HOOKS['config_page']['webhook'] = 'front/config.php';
			}
			if (Session::haveRight("plugin_webhook_configuration", READ)) {
				$PLUGIN_HOOKS['menu_toadd']['webhook'] = ["config" => 'PluginWebhookConfigMenu'];
			}
		}
	}
}

// Get the name and the version of the plugin - Needed
function plugin_version_webhook() {

   return [
      'name' => _n('Webhook', 'Webhooks', 2, 'webhook'),
      'version' => '1.0.18',
      'author'  => "Eric Feron",
      'license' => 'GPLv2+',
      'homepage'=> 'https://github.com/ericferon/glpi-webhook',
      'requirements' => [
         'glpi' => [
            'min' => '10.0.3',
            'dev' => false
         ]
      ]
   ];

}

// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_webhook_check_prerequisites() {
   if (version_compare(GLPI_VERSION, '10.0.3', 'lt')
       || version_compare(GLPI_VERSION, '10.1', 'ge')) {
      if (method_exists('Plugin', 'messageIncompatible')) {
         echo Plugin::messageIncompatible('core', '10.0');
      }
      return false;
   }
   return true;
}

// Uninstall process for plugin : need to return true if succeeded : may display messages or add to message after redirect
function plugin_webhook_check_config() {
   return true;
}

   /**
    * @param $entity
   **/
function plugin_webhook_add_targets($notificationtarget) {
      global $DB;

      // Filter webhooks which can be notified

	  $query = "SELECT id, name FROM ".PluginWebhookConfig::getTable()." ORDER BY name";
      $result = $DB->query($query);
      while ($data = $DB->fetchRow($result)) {
         //Add webhook
         $notificationtarget->addTarget($data[0], sprintf(__('%1$s: %2$s'), __('Webhook', 'webhook'), $data[1]),
                          PluginWebhookConfig::WEBHOOK_TYPE);
      }
   }

function plugin_webhook_action_targets($notificationtarget) {
	global $DB, $CFG_GLPI;

	if (isset($notificationtarget->data) && isset($notificationtarget->data['type']) && $notificationtarget->data['type'] == PluginWebhookConfig::WEBHOOK_TYPE)
	{
      $new_lang = '';

	  $query = "SELECT * FROM ".PluginWebhookConfig::getTable()." WHERE id like '".(isset($notificationtarget->data['items_id'])?$notificationtarget->data['items_id']:'%')."' ORDER BY name";
      $result = $DB->query($query);

      while ($data = $DB->fetchRow($result)) {
			$notificationoption = ['usertype' => PluginWebhookConfig::WEBHOOK_TYPE];
			foreach ($data as $nb => $field) {
				$fieldname = $DB->fieldName($result, $nb);
				if ($fieldname)
					$notificationoption[$fieldname] = $field;
			}
			//Add webhook
			if (isset($notificationoption['language'])) {
				$new_lang = trim($notificationoption['language']);
			}
			$target_field = PluginWebhookNotificationEventWebhook::getTargetField($notificationoption);
			$param = [
				'language'				=> (empty($new_lang) ? $CFG_GLPI["language"] : $new_lang),
				'additionnaloption' 	=> $notificationoption,
				'id'					=> isset($notificationoption['id']) ? $notificationoption['id'] : 0,
				'name'					=> isset($notificationoption['name']) ? $notificationoption['name'] : "",
				'username'				=> isset($notificationoption['user']) ? $notificationoption['user'] : "",
				'address'				=> isset($notificationoption['address']) ? $notificationoption['address'] : ""
			];
//			$notificationtarget->notification_targets[$notificationoption[$target_field]] = $param;
			$notificationtarget->target[$notificationoption[$target_field]] = $param;
		}
	}
}

?>
