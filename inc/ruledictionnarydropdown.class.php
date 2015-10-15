<?php
/*
 * @version $Id: ruledictionnarydropdown.class.php 20580 2013-03-29 18:09:43Z moyo $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2013 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

/** @file
* @brief 
*/

class RuleDictionnaryDropdown extends Rule {

   // From Rule
   static public $right  = 'rule_dictionnary_dropdown';
   public $can_sort      = true;
   public $show_cache    = false;


   /**
    * @see Rule::maxActionsCount()
   **/
   function maxActionsCount() {
      return 1;
   }


//    /**
//     * @see RuleCached::showCacheRuleHeader()
//    **/
//    function showCacheRuleHeader() {
// 
//       if ($this->show_cache) {
//          echo "<tr><th colspan='2'>".__('Cache information')."</th><th>".$this->fields["name"].
//               "</th></tr>";
//          echo "<tr><td class='tab_bg_1 b'>".__('Original value')."</td>";
//          echo "<td class='tab_bg_1 b'>".__('Manufacturer')."</td>";
//          echo "<td class='tab_bg_1 b'>".__('Modified value')."</td></tr>";
//       } else {
//          parent::showCacheRuleHeader();
//       }
//    }


//    /**
//     * @see RuleCached::showCacheRuleDetail()
//    **/
//    function showCacheRuleDetail($fields) {
// 
//       if ($this->show_cache) {
//          echo "<td class='tab_bg_2'>".$fields["old_value"]."</td>";
//          echo "<td class='tab_bg_2'>".(($fields["manufacturer"] != '')?$fields["manufacturer"]:'').
//               "</td>";
//          echo "<td class='tab_bg_2'>".(($fields["new_value"] != '') ? $fields["new_value"]
//                                                                     : __('Unchanged'))."</td>";
//       } else {
//          parent::showCacheRuleDetail($fields);
//       }
//    }

}
?>
