<?php
/*
 * @version $Id: HEADER 14684 2011-06-11 06:32:40Z remi $
 LICENSE

 This file is part of the datainjection plugin.

 Datainjection plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Datainjection plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with datainjection. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   datainjection
 @author    the datainjection plugin team
 @copyright Copyright (c) 2010-2013 Datainjection plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://forge.indepnet.net/projects/datainjection
 @link      http://www.glpi-project.org/
 @since     2009
 ---------------------------------------------------------------------- */

if (!defined("PLUGIN_DATAINJECTION_UPLOAD_DIR")) {
   define("PLUGIN_DATAINJECTION_UPLOAD_DIR", GLPI_PLUGIN_DOC_DIR."/datainjection/");
}

function plugin_init_datainjection() {
   global $PLUGIN_HOOKS, $CFG_GLPI, $INJECTABLE_TYPES;

   $PLUGIN_HOOKS['csrf_compliant']['datainjection'] = true;

   Plugin::registerClass('PluginDatainjectionProfile',
                         array('addtabon' => array('Profile')));

   $PLUGIN_HOOKS['change_profile']['datainjection'] = array('PluginDatainjectionProfile',
                                                            'changeProfile');

   $PLUGIN_HOOKS['migratetypes']['datainjection'] = 'plugin_datainjection_migratetypes_datainjection';

   $plugin = new Plugin();
   if ($plugin->isActivated("datainjection")) {
      if (!plugin_datainjection_checkDirectories()) {
         Toolbox::logDebug("[Datainjection plugin] ".
                           sprintf(__('%s must exists and be writable for web server user',
                                      'datainjection'), PLUGIN_DATAINJECTION_UPLOAD_DIR));
         return false;
      }

      $image_import  = "<img src='".$CFG_GLPI["root_doc"]."/pics/actualiser.png' title='";
      $image_import .= __s('Injection of the file', 'datainjection');
      $image_import .= "' alt='".__s('Injection of the file', 'datainjection')."'>";

      $PLUGIN_HOOKS['menu_entry']['datainjection'] = 'front/clientinjection.form.php';

      if (plugin_datainjection_haveRight("model", "r")) {
         $PLUGIN_HOOKS['submenu_entry']['datainjection']['options']['model']['title']
                                                   = PluginDatainjectionModel::getTypeName();
         $PLUGIN_HOOKS['submenu_entry']['datainjection']['options']['model']['page']
                                                   = '/plugins/datainjection/front/model.php';
         $PLUGIN_HOOKS['submenu_entry']['datainjection']['options']['model']['links']['search']
                                                   = '/plugins/datainjection/front/model.php';
         $PLUGIN_HOOKS['submenu_entry']['datainjection']['options']['model']['links']['add']
                                                   = '/plugins/datainjection/front/model.form.php';

         $image_model  = "<img src='".$CFG_GLPI["root_doc"]."/pics/rdv.png' title='";
         $image_model .= PluginDatainjectionModel::getTypeName();
         $image_model .= "' alt='".PluginDatainjectionModel::getTypeName()."'>";

         $PLUGIN_HOOKS['submenu_entry']['datainjection'][$image_model] = 'front/model.php';
         $PLUGIN_HOOKS['submenu_entry']['datainjection']['options']['model']['links'][$image_import]
                                       = '/plugins/datainjection/front/clientinjection.form.php';
      }

      $PLUGIN_HOOKS['submenu_entry']['datainjection']['add'] = 'front/clientinjection.form.php';

      $PLUGIN_HOOKS['pre_item_purge']['datainjection']
            = array('Profile' => array('PluginDatainjectionProfile', 'purgeProfiles'));

      // Css file
      $PLUGIN_HOOKS['add_css']['datainjection'] = 'css/datainjection.css';

      // Javascript file
      $PLUGIN_HOOKS['add_javascript']['datainjection'] = 'javascript/datainjection.js';

      // Inbtegration with Webservices plugin
      $PLUGIN_HOOKS['webservices']['datainjection'] = 'plugin_datainjection_registerMethods';
      $INJECTABLE_TYPES = array();

   }
}


function plugin_version_datainjection() {

   return array('name'           => __('File injection', 'datainjection'),
                'minGlpiVersion' => '0.84',
                'author'         => 'Walid Nouh, Remi Collet, Nelly Mahu-Lasson, Xavier Caillaud',
                'homepage'       => 'https://forge.indepnet.net/projects/datainjection',
                'license'        => 'GPLv2+',
                'version'        => '2.3.0'
   );
}


function plugin_datainjection_haveRight($module, $right) {

   $matches = array(""  => array ("", "r", "w"), // ne doit pas arriver normalement
                    "r" => array ("r", "w"),
                    "w" => array ("w"),
                    "1" => array ("1"),
                    "0" => array ("0", "1")); // ne doit pas arriver non plus

   if (isset ($_SESSION["glpi_plugin_datainjection_profile"][$module])
       && in_array($_SESSION["glpi_plugin_datainjection_profile"][$module], $matches[$right])) {
      return true;
   }
   return false;
}


function plugin_datainjection_check_prerequisites() {

  if (!plugin_datainjection_checkDirectories()) {
      printf(__('%s must exists and be writable for web server user', 'datainjection'),
             PLUGIN_DATAINJECTION_UPLOAD_DIR);
      return false;
   }
   if (version_compare(GLPI_VERSION,'0.84','lt') || version_compare(GLPI_VERSION,'0.85','ge')) {
      _e('This plugin requires GLPI >= 0.84', 'datainjection');
      return false;
   }
   return true;
}


function plugin_datainjection_check_config($verbose=false) {
   return true;
}


/**
 * Return all types that can be injected using datainjection
 *
 * @return an array of injection class => plugin
 */
function getTypesToInject() {
   global $INJECTABLE_TYPES,$PLUGIN_HOOKS;

   if (count($INJECTABLE_TYPES)) {
      // already populated
      return;
   }

   $INJECTABLE_TYPES = array('PluginDatainjectionCartridgeItemInjection'               => 'datainjection',
                             'PluginDatainjectionBudgetInjection'                      => 'datainjection',
                             'PluginDatainjectionComputerInjection'                    => 'datainjection',
                             'PluginDatainjectionComputer_ItemInjection'               => 'datainjection',
                             'PluginDatainjectionConsumableItemInjection'              => 'datainjection',
                             'PluginDatainjectionContactInjection'                     => 'datainjection',
                             'PluginDatainjectionContact_SupplierInjection'            => 'datainjection',
                             'PluginDatainjectionContractInjection'                    => 'datainjection',
                             'PluginDatainjectionContract_ItemInjection'               => 'datainjection',
                             'PluginDatainjectionContract_SupplierInjection'           => 'datainjection',
                                //'PluginDatainjectionDocumentInjection'               => 'datainjection',
                             'PluginDatainjectionEntityInjection'                      => 'datainjection',
                             'PluginDatainjectionGroupInjection'                       => 'datainjection',
                             'PluginDatainjectionGroup_UserInjection'                  => 'datainjection',
                             'PluginDatainjectionInfocomInjection'                     => 'datainjection',
                             'PluginDatainjectionLocationInjection'                    => 'datainjection',
                             'PluginDatainjectionStateInjection'                       => 'datainjection',
                             'PluginDatainjectionManufacturerInjection'                => 'datainjection',
                             'PluginDatainjectionMonitorInjection'                     => 'datainjection',
                             'PluginDatainjectionNetworkequipmentInjection'            => 'datainjection',
                             'PluginDatainjectionPeripheralInjection'                  => 'datainjection',
                             'PluginDatainjectionPhoneInjection'                       => 'datainjection',
                             'PluginDatainjectionPrinterInjection'                     => 'datainjection',
                             'PluginDatainjectionProfileInjection'                     => 'datainjection',
                             'PluginDatainjectionProfile_UserInjection'                => 'datainjection',
                             'PluginDatainjectionSoftwareInjection'                    => 'datainjection',
                             'PluginDatainjectionComputer_SoftwareVersionInjection'    => 'datainjection',
                             'PluginDatainjectionComputer_SoftwareLicenseInjection'    => 'datainjection',
                             'PluginDatainjectionSoftwareLicenseInjection'             => 'datainjection',
                             'PluginDatainjectionSoftwareVersionInjection'             => 'datainjection',
                             'PluginDatainjectionSupplierInjection'                    => 'datainjection',
                             'PluginDatainjectionUserInjection'                        => 'datainjection',
                             'PluginDatainjectionNetworkportInjection'                 => 'datainjection',
                             'PluginDatainjectionVlanInjection'                        => 'datainjection',
                             'PluginDatainjectionNetworkport_VlanInjection'            => 'datainjection',
                             'PluginDatainjectionNetworkNameInjection'                 => 'datainjection',
                             'PluginDatainjectionNetpointInjection'                    => 'datainjection',
                             'PluginDatainjectionKnowbaseItemCategoryInjection'        => 'datainjection',
                             'PluginDatainjectionKnowbaseItemInjection'                => 'datainjection',
                             'PluginDatainjectionITILCategoryInjection'                => 'datainjection',
                             'PluginDatainjectionTaskCategoryInjection'                => 'datainjection',
                             'PluginDatainjectionSolutionTypeInjection'                => 'datainjection',
                             'PluginDatainjectionRequestTypeInjection'                 => 'datainjection',
                             'PluginDatainjectionSolutionTemplateInjection'            => 'datainjection',
                             'PluginDatainjectionComputerTypeInjection'                => 'datainjection',
                             'PluginDatainjectionMonitorTypeInjection'                 => 'datainjection',
                             'PluginDatainjectionNetworkEquipmentTypeInjection'        => 'datainjection',
                             'PluginDatainjectionPeripheralTypeInjection'              => 'datainjection',
                             'PluginDatainjectionPrinterTypeInjection'                 => 'datainjection',
                             'PluginDatainjectionPhoneTypeInjection'                   => 'datainjection',
                             'PluginDatainjectionSoftwareLicenseTypeInjection'         => 'datainjection',
                             'PluginDatainjectionContractTypeInjection'                => 'datainjection',
                             'PluginDatainjectionContactTypeInjection'                 => 'datainjection',
                             'PluginDatainjectionSupplierTypeInjection'                => 'datainjection',
                             'PluginDatainjectionDeviceMemoryTypeInjection'            => 'datainjection',
                             'PluginDatainjectionInterfaceTypeInjection'               => 'datainjection',
                             'PluginDatainjectionPhonePowerSupplyTypeInjection'        => 'datainjection',
                             'PluginDatainjectionFilesystemTypeInjection'              => 'datainjection',
                             'PluginDatainjectionComputerModelInjection'               => 'datainjection',
                             'PluginDatainjectionMonitorModelInjection'                => 'datainjection',
                             'PluginDatainjectionPhoneModelInjection'                  => 'datainjection',
                             'PluginDatainjectionPrinterModelInjection'                => 'datainjection',
                             'PluginDatainjectionPeripheralModelInjection'             => 'datainjection',
                             'PluginDatainjectionNetworkEquipmentModelInjection'       => 'datainjection',
                             'PluginDatainjectionNetworkEquipmentFirmwareInjection'    => 'datainjection',
                             'PluginDatainjectionVirtualMachineTypeInjection'          => 'datainjection',
                             'PluginDatainjectionVirtualMachineSystemInjection'        => 'datainjection',
                             'PluginDatainjectionVirtualMachineStateInjection'         => 'datainjection',
                             'PluginDatainjectionDocumentTypeInjection'                => 'datainjection',
                             'PluginDatainjectionAutoUpdateSystemInjection'            => 'datainjection',
                             'PluginDatainjectionOperatingSystemInjection'             => 'datainjection',
                             'PluginDatainjectionOperatingSystemVersionInjection'      => 'datainjection',
                             'PluginDatainjectionOperatingSystemServicePackInjection'  => 'datainjection',
                             'PluginDatainjectionNetworkInterfaceInjection'            => 'datainjection',
                             'PluginDatainjectionDomainInjection'                      => 'datainjection',
                             'PluginDatainjectionNetworkInjection'                     => 'datainjection',
                             'PluginDatainjectionDeviceCaseInjection'                  => 'datainjection',
                             'PluginDatainjectionDeviceCaseTypeInjection'              => 'datainjection',
                             'PluginDatainjectionDeviceControlInjection'               => 'datainjection',
                             'PluginDatainjectionDeviceProcessorInjection'             => 'datainjection',
                             'PluginDatainjectionDeviceMemoryInjection'                => 'datainjection',
                             'PluginDatainjectionDeviceHardDriveInjection'             => 'datainjection',
                             'PluginDatainjectionDeviceMotherboardInjection'           => 'datainjection',
                             'PluginDatainjectionDeviceDriveInjection'                 => 'datainjection',
                             'PluginDatainjectionDeviceNetworkCardInjection'           => 'datainjection'
                             );
   //Add plugins
   Plugin::doHook('plugin_datainjection_populate');
}


function plugin_datainjection_migratetypes_datainjection($types) {

   $types[996] = 'NetworkPort';
   $types[999] = 'NetworkPort';
   return $types;
}


function plugin_datainjection_checkDirectories() {
   $plugin = new Plugin();

   if ($plugin->isInstalled('datainjection')
       && (!file_exists(PLUGIN_DATAINJECTION_UPLOAD_DIR)
           || !is_writable(PLUGIN_DATAINJECTION_UPLOAD_DIR))) {
      return false;
   }
   return true;
}
?>