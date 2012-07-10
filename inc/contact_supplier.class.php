<?php
/*
 * @version $Id$
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2012 by the INDEPNET Development Team.

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

// ----------------------------------------------------------------------
// Original Author of file: Julien Dombre
// Purpose of file:
// ----------------------------------------------------------------------

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class Contact_Supplier extends CommonDBRelation{

   // From CommonDBRelation
   public $itemtype_1 = 'Contact';
   public $items_id_1 = 'contacts_id';

   public $itemtype_2 = 'Supplier';
   public $items_id_2 = 'suppliers_id';


   static function getTypeName($nb=0) {
      return _n('Link Contact/Supplier','Links Contact/Supplier',$nb);
   }


   /**
    * Get search function for the class
    *
    * @return array of search option
   **/
   function getSearchOptions() {
      return parent::getSearchOptions();
   }


   function doSpecificMassiveActions($input = array()) {
      $res = array('ok'      => 0,
                   'ko'      => 0,
                   'noright' => 0);
      switch ($input['action']) {
         case "add_contact_supplier" :
            $contactsupplier = new Contact_Supplier();
            foreach ($input["item"] as $key => $val) {
               if (isset($input['contacts_id'])) {
                  $input = array('suppliers_id' => $key,
                                 'contacts_id'  => $input['contacts_id']);
               } else if (isset($input['suppliers_id'])) {
                $input = array('suppliers_id' => $input['suppliers_id'],
                               'contacts_id'  => $key);
               } else {
                  return false;
               }
               if ($contactsupplier->can(-1, 'w', $input)) {
                  if ($contactsupplier->add($input)) {
                     $res['ok']++;
                  } else {
                     $res['ko']++;
                  }
               } else {
                  $res['noright']++;
               }
            }
            break;
         default :
            return parent::doSpecificMassiveActions($input);
      }
      return $res;
   }

   
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if (!$withtemplate && Session::haveRight("contact_enterprise","r")) {
         switch ($item->getType()) {
            case 'Supplier' :
               if ($_SESSION['glpishow_count_on_tabs']) {
                  return self::createTabEntry(_n('Contact', 'Contacts', 2),
                                              self::countForSupplier($item));
               }
               return _n('Contact', 'Contacts', 2);

            case 'Contact' :
               if ($_SESSION['glpishow_count_on_tabs']) {
                  return self::createTabEntry(_n('Supplier', 'Suppliers', 2),
                                              self::countForContact($item));
               }
               return _n('Supplier', 'Suppliers', 2);
         }
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      switch ($item->getType()) {
         case 'Supplier' :
            $item->showContacts();
            break;

         case 'Contact' :
            $item->showSuppliers();
            break;
      }
      return true;
   }


   /**
    * @param $item   string   Supplier object
   **/
   static function countForSupplier(Supplier $item) {

      $restrict = "`glpi_contacts_suppliers`.`suppliers_id` = '".$item->getField('id') ."'
                    AND `glpi_contacts_suppliers`.`contacts_id` = `glpi_contacts`.`id` ".
                    getEntitiesRestrictRequest(" AND ", "glpi_contacts", '',
                                               $_SESSION['glpiactiveentities'], true);

      return countElementsInTable(array('glpi_contacts_suppliers', 'glpi_contacts'), $restrict);
   }


   /**
    * @param $item   string   Contact object
   **/
   static function countForContact(Contact $item) {

      $restrict = "`glpi_contacts_suppliers`.`contacts_id` = '".$item->getField('id') ."'
                    AND `glpi_contacts_suppliers`.`suppliers_id` = `glpi_suppliers`.`id` ".
                    getEntitiesRestrictRequest(" AND ", "glpi_suppliers", '',
                                               $_SESSION['glpiactiveentities'], true);

      return countElementsInTable(array('glpi_contacts_suppliers', 'glpi_suppliers'), $restrict);
   }

}
?>
