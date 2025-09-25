<?php
/* 
 * Copyright (C) 2025 Massaoud Bouzenad    <massaoud@dzprod.net>
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

 /**
  * @file       modDolidiag.class.php
  * @brief      DoliDiag module class file
  * @ingroup    dolidiag
  * @author     Massaoud Bouzenad <massaoud@dzprod.net>
  */

  include_once DOL_DOCUMENT_ROOT .'/core/modules/DolibarrModules.class.php';


/**
 * DoliDiag Module Descriptor
 */
class modDoliDiag extends DolibarrModules
{
    public function __construct($db)
    {
        global $langs, $conf;

        $this->db = $db;
        $this->numero = 207213;
        $this->rights_class = 'dolidiag';
        $this->family = "other";
        $this->module_position = '90';
        $this->name = preg_replace('/^mod/i', '', get_class($this));
        $this->editor_name		= '<b>DZPROD - Massaoud Bouzenad</b>';
        $this->editor_web		= 'https://www.dzprod.net/';
        $this->description = "Diagnostic tool for Dolibarr environment";
        $this->version = '1.0.0';
        $this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
        $this->picto = 'generic@dolidiag';
        $this->module_parts = array(
            'hooks' => array('admin'),
        );
        $this->dirs = array('/dolidiag/temp');
        $this->config_page_url = array("dolidiag_setup.php@dolidiag");
        $this->depends = array();
        $this->requiredby = array();
        $this->phpmin = array(7, 1);
        $this->need_dolibarr_version = array(14, 0);
        $this->langfiles = array("dolidiag@dolidiag");

        // Permissions
        $this->rights = array();
        $r = 0;
        $this->rights[$r][0] = 500001;
        $this->rights[$r][1] = 'Generate and view diagnostic reports';
        $this->rights[$r][3] = 0;
        $this->rights[$r][4] = 'read';
        $r++;

        // Menu entries
        $this->menu = array();
        $r = 0;

        // Main menu entry
        $this->menu[$r++] = array(
            'fk_menu' => 'fk_mainmenu=tools', // Parent menu = Tools
            'type' => 'left',                // This is a left menu entry
            'titre' => 'DoliDiag',           // Menu title
            'prefix' => '<i class="fa fa-stethoscope"></i> ', // Font Awesome icon for diagnostics
            'mainmenu' => 'tools',           // Main menu (top)
            'leftmenu' => 'dolidiag',        // Left menu name
            'url' => '/dolidiag/dolidiag.php', // URL to the main page
            'langs' => 'dolidiag@dolidiag',  // Lang file
            'position' => 100,               // Position in the menu
            'enabled' => '$conf->dolidiag->enabled', // Condition for menu activation
            'perms' => '$user->rights->dolidiag->read', // Permission needed
            'target' => '',                  // Target attribute
            'user' => 2                      // 0=Menu for internal users, 1=external users, 2=both
        );
    }

    public function init($options = '')
    {
        $sql = array();
        return $this->_init($sql, $options);
    }

    public function remove($options = '')
    {
        $sql = array();
        return $this->_remove($sql, $options);
    }
}