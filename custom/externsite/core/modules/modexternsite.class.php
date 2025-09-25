<?php
/* Copyright (C) 2003      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2015 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2016 Regis Houssin        <regis.houssin@capnetworks.com>
 * Copyright (C) 2016-2017 Quentin Sutkowski for HdF industrilab	<quentin.sutkowski@gmail.com>
 *
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
 * 	\defgroup   mymodule     Module MyModule
 *  \brief      Example of a module descriptor.
 *				Such a file must be copied into htdocs/mymodule/core/modules directory.
 *  \file       htdocs/mymodule/core/modules/modMyModule.class.php
 *  \ingroup    mymodule
 *  \brief      Description and activation file for module MyModule
 */
include_once DOL_DOCUMENT_ROOT .'/core/modules/DolibarrModules.class.php';


/**
 *  Description and activation class for module externsite
 */
class modexternsite extends DolibarrModules
{
	/**
	 * Constructor. Define names, constants, directories, boxes, permissions
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct($db)
	{
        global $langs,$conf;

        $this->db = $db;

		$this->numero = 429003;

		$this->rights_class = 'externsite';

		$this->family = "IndustriLAB";

		$this->module_position = 500;
		// Gives the possibility to the module, to provide his own family info and position of this family (Overwrite $this->family and $this->module_position. Avoid this)
		//$this->familyinfo = array('myownfamily' => array('position' => '001', 'label' => $langs->trans("MyOwnFamily")));

		$this->name = preg_replace('/^mod/i','',get_class($this));
		// Module description, used if translation string 'ModuleXXXDesc' not found (where XXX is value of numeric property 'numero' of module)
		$this->description = "Module de redirection vers sites externes";
		$this->descriptionlong = "Module de redirection vers les sites externes (autant que désirés)";
		$this->editor_name = 'twister';
		$this->editor_url = 'http://www.dolibarr.org';
		
		// Possible values for version are: 'development', 'experimental', 'dolibarr', 'dolibarr_deprecated' or a version string like 'x.y.z'
		$this->version = '1.0';
		// Key used in llx_const table to save module status enabled/disabled (where MYMODULE is value of property name of module in uppercase)
		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);

		$this->picto='externsite@externsite';

// Defined all module parts (triggers, login, substitutions, menus, css, etc...)
		// for default path (eg: /mymodule/core/xxxxx) (0=disable, 1=enable)
		// for specific path of parts (eg: /mymodule/core/modules/barcode)
		// for specific css file (eg: /mymodule/css/mymodule.css.php)
		$this->module_parts = array(
		//                        	
									'css' => array('/custom/externsite/css/externsite.css.php')	// Set this to relative path of css 
		                        );


		$this->dirs = array("/custom/externsite/temp");

		$this->config_page_url = array("setup.php@externsite");

		// Dependencies
		$this->hidden = false;			// A condition to hide module
		$this->depends = array();		// List of modules id that must be enabled if this module is enabled
		$this->requiredby = array();	// List of modules id to disable if this one is disabled
		$this->conflictwith = array();	// List of modules id this module is in conflict with
		$this->phpmin = array(5,0);					// Minimum version of PHP required by module
		$this->need_dolibarr_version = array(3,0);	// Minimum version of Dolibarr required by module
		$this->langfiles = array("myTrans");

		$this->const = array();

        $this->tabs = array();

		if (! isset($conf->externsite) || ! isset($conf->externsite->enabled))
        {
        	$conf->externsite=new stdClass();
        	$conf->externsite->enabled=0;
        }
        
        // Dictionaries
		$this->dictionaries=array();

        // Boxes
		// Add here list of php file(s) stored in core/boxes that contains class to show a box.
        $this->boxes = array();			// List of boxes

		// Cronjobs
		$this->cronjobs = array();			// List of cron jobs entries to add

		// Permissions
		$this->rights = array();		// Permission array used by this module
		$r=0;

		// Add here list of permission defined by an id, a label, a boolean and two constant strings.
		// Example:
		$this->rights[$r][0] = 17954988;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'acces';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'acces';				// In php code, permission will be checked by test if ($user->rights->permkey->level1->level2)
		// $this->rights[$r][5] = 'level2';				// In php code, permission will be checked by test if ($user->rights->permkey->level1->level2)
		$r++;

		// Main menu entries
		$this->menu = array();			// List of menus to add
		$r=0;

		// Add here entries to declare new menus
		//

		// Exports
		$r=1;

	}

	/**
	 *		Function called when module is enabled.
	 *		The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
	 *		It also creates data directories
	 *
     *      @param      string	$options    Options when enabling module ('', 'noboxes')
	 *      @return     int             	1 if OK, 0 if KO
	 */
	public function init($options='')
	{
		$sql = array();

		$this->_load_tables('/externsite/sql/');
//"re"création des menus pour les sites externes activés en base (appelée à l'activation du module)
		$menus_extern=$this->db->query("SELECT * from llx_externsite");
		while ($donnees = $this->db->fetch_object($menus_extern))
		{
			$nom=str_replace(" ","_",$donnees->nom);
			$nom=str_replace("(","_",$nom);
			$nom=str_replace(")","_",$nom);

			$menu = new Menubase($this->db);
			$menu->menu_handler='all';
			$menu->module='externsite';
			$menu->type='top';
			$menu->mainmenu="exts_".$nom."";
			$menu->fk_menu='0';
			$menu->fk_mainmenu=substr("exts_".$nom, 0, 23);
			$menu->titre=$donnees->nom;
			$menu->url="custom/externsite/pages/frames.php?page=".$donnees->url."&mainmenu=externsite&idmenu=".$idmenu.($theme?'&theme='.$theme:'').($codelang?'&lang='.$codelang:'')."";
			$menu->position=150;
			$menu->perms=1;
			$menu->target="";
			$menu->user=2;
			$menu->enabled='$conf->externsite->enabled';

			$result=$menu->create($user);
		}

		return $this->_init($sql, $options);
	}

	/**
	 * Function called when module is disabled.
	 * Remove from database constants, boxes and permissions from Dolibarr database.
	 * Data directories are not deleted
	 *
	 * @param      string	$options    Options when enabling module ('', 'noboxes')
	 * @return     int             	1 if OK, 0 if KO
	 */
	public function remove($options = '')
	{
		$sql = array();

		return $this->_remove($sql, $options);
		
	}

}

