<?php
/*
 * Copyright (C) 2014 Florian HENRY <florian.henry@open-concept.pro>
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
 * \defgroup tvi tvimodule
 * \brief TVI module descriptor.
 * \file core/modules/tvi.class.php
 * \ingroup lead
 * \brief Description and activation file for module Lead
 */
include_once DOL_DOCUMENT_ROOT . "/core/modules/DolibarrModules.class.php";

/**
 * Description and activation class for module Lead
 */
class modtvi extends DolibarrModules
{

	/**
	 * Constructor.
	 * Define names, constants, directories, boxes, permissions
	 *
	 * @param DoliDB $db
	 */
	public function __construct($db) {
		global $langs, $conf;

		$this->db = $db;

		// Id for module (must be unique).
		// Use a free id here
		// (See in Home -> System information -> Dolibarr for list of used modules id).
		$this->numero = 103571;
		// Key text used to identify module (for permissions, menus, etc...)
		$this->rights_class = 'tvi';

		// Family can be 'crm','financial','hr','projects','products','ecm','technic','other'
		// It is used to group modules in module setup page
		$this->family = "other";
		// Module label (no space allowed)
		// used if translation string 'ModuleXXXName' not found
		// (where XXX is value of numeric property 'numero' of module)
		$this->name = preg_replace('/^mod/i', '', get_class($this));
		// Module description
		// used if translation string 'ModuleXXXDesc' not found
		// (where XXX is value of numeric property 'numero' of module)
		$this->description = "Module Spécifique TVI LOCATIONS";
		// Possible values for version are: 'development', 'experimental' or version
		$this->version = '1.0';
		// Key used in llx_const table to save module status enabled/disabled
		// (where MYMODULE is value of property name of module in uppercase)
		$this->const_name = 'MAIN_MODULE_' . strtoupper($this->name);
		// Where to store the module in setup page
		// (0=common,1=interface,2=others,3=very specific)
		$this->special = 3;
		// Name of image file used for this module.
		// If file is in theme/yourtheme/img directory under name object_pictovalue.png
		// use this->picto='pictovalue'
		// If file is in module/img directory under name object_pictovalue.png
		// use this->picto='pictovalue@module'
		$this->picto = 'iron02@volvo'; // mypicto@lead
		                               // Defined all module parts (triggers, login, substitutions, menus, css, etc...)
		                               // for default path (eg: /lead/core/xxxxx) (0=disable, 1=enable)
		                               // for specific path of parts (eg: /lead/core/modules/barcode)
		                               // for specific css file (eg: /lead/css/lead.css.php)
		$this->module_parts = array(
				// Set this to 1 if module has its own trigger directory
				// 'triggers' => 1,
				// Set this to 1 if module has its own login method directory
				// 'login' => 0,
				// Set this to 1 if module has its own substitution function file
				// 'substitutions' => 0,
				// Set this to 1 if module has its own menus handler directory
				// 'menus' => 0,
				// Set this to 1 if module has its own barcode directory
				// 'barcode' => 0,
				// Set this to 1 if module has its own models directory
				'models' => 1,
				'tpl' => 1,
				// Set this to relative path of css if module has its own css file
				// 'css' => '/lead/css/mycss.css.php',
				// Set here all hooks context managed by module
				'hooks' => array(
						'contractcard',
						)
		)
		;

		// Set here all workflow context managed by module
		// 'workflow' => array('order' => array('WORKFLOW_ORDER_AUTOCREATE_INVOICE'))

		// Data directories to create when module is enabled.
		// Example: this->dirs = array("/lead/temp");
		$this->dirs = array(
			'/tvi',
			'/tvi/modelpdf'
		);

		// Config pages. Put here list of php pages
		// stored into lead/admin directory, used to setup module.
		$this->config_page_url = array(
		 "admin_tvi.php@tvi"
		 );

		// Dependencies
		// List of modules id that must be enabled if this module is enabled
		$this->depends = array();
		// List of modules id to disable if this one is disabled
		$this->requiredby = array();
		// Minimum version of PHP required by module
		$this->phpmin = array(
				5,
				4
		);
		// Minimum version of Dolibarr required by module
		$this->need_dolibarr_version = array(
				4,
				0
		);
		// $this->langfiles = array(
		// "lead@lead"
		// ); // langfiles@lead
		// Constants
		// List of particular constants to add when module is enabled
		// (key, 'chaine', value, desc, visible, 'current' or 'allentities', deleteonunactive)
		// Example:
		$this->const = array(
			0 => array(
				'TVI_INVOICE_AUTO_STATUS',
				'chaine',
				'0',
				'status des facture auto générér : 0 brouillon ,1 valider',
				1,
				'current',
				1
			),
			1 => array(
				'CONTRACT_ADDON_PDF',
				'chaine',
				'contrattvi',
				'',
				1,
				'current',
				1
			),
			2 => array(
				'FACTURE_ADDON',
				'chaine',
				'mod_facture_tvi',
				'',
				1,
				'current',
				1
			)
		);
		

		// Array to add new pages in new tabs
		// Example:
		$this->tabs = array(
				
		);

		// 'thirdparty:+tabLead:Module103111Name:lead@lead:$user->rights->lead->read && ($object->client > 0 || $soc->client > 0):/lead/lead/list.php?socid=__ID__',
		// 'invoice:+tabAgefodd:AgfMenuSess:agefodd@agefodd:/lead/lead/list.php?search_invoiceid=__ID__',
		// 'propal:+tabAgefodd:AgfMenuSess:agefodd@agefodd:/lead/lead/list.php?search_propalid=__ID__',
		// // To add a new tab identified by code tabname1
		// 'lead:+reprise:Reprise::$user->rights->volvo->lire:/volvo/reprise/card.php?&action=&repid=&id=__ID__',
		// // To add another new tab identified by code tabname2
		// 'objecttype:+tabname2:Title2:langfile@lead:$user->rights->othermodule->read:/lead/mynewtab2.php?id=__ID__',
		// // To remove an existing tab identified by code tabname
		// 'objecttype:-tabname'
		// 'thirdparty:-customer',
		// 'thirdparty:-price'
		// where objecttype can be
		// 'thirdparty' to add a tab in third party view
		// 'intervention' to add a tab in intervention view
		// 'order_supplier' to add a tab in supplier order view
		// 'invoice_supplier' to add a tab in supplier invoice view
		// 'invoice' to add a tab in customer invoice view
		// 'order' to add a tab in customer order view
		// 'product' to add a tab in product view
		// 'stock' to add a tab in stock view
		// 'propal' to add a tab in propal view
		// 'member' to add a tab in fundation member view
		// 'contract' to add a tab in contract view
		// 'user' to add a tab in user view
		// 'group' to add a tab in group view
		// 'contact' to add a tab in contact view
		// 'categories_x' to add a tab in category view
		// (replace 'x' by type of category (0=product, 1=supplier, 2=customer, 3=member)
		// Dictionnaries
		if (! isset($conf->tvi->enabled)) {
			$conf->tvi = ( object ) array();
			$conf->tvi->enabled = 0;
		}

		$this->dictionnaries = array(
				'langs' => 'tvi@tvi',
				'tabname' => array(
						MAIN_DB_PREFIX . "c_tvi_vehicules",
						),
				'tablib' => array(
						"TVI -- Parc de V�hicules",
						),
				'tabsql' => array(
						'SELECT f.rowid as rowid, f.parc as parc, f.type as type, f.immat as immat, f.chassis as chassis, f.active FROM ' . MAIN_DB_PREFIX . 'c_tvi_vehicules as f',
						),
				'tabsqlsort' => array(
						'parc ASC',
				),
				'tabfield' => array(
						"parc,type,immat,chassis",
						),
				'tabfieldvalue' => array(
						"parc,type,immat,chassis",
						),
				'tabfieldinsert' => array(
						"parc,type,immat,chassis",
						),
				'tabrowid' => array(
						"rowid",
						),
				'tabcond' => array(
						'$conf->tvi->enabled',
						)
		);

		// Boxes
		// Add here list of php file(s) stored in core/boxes that contains class to show a box.
		$this->boxes = array(); // Boxes list
		$r = 0;
		// Example:

		// $this->boxes[$r][1] = "box_pay_late@volvo";
		// $r ++;
		/*
		 * $this->boxes[$r][1] = "myboxb.php"; $r++;
		 */

		// Permissions
		$this->rights = array(); // Permission array used by this module
		$r = 0;
		$this->rights[$r][0] = 1035711;
		$this->rights[$r][1] = 'Mettre a jour le parc';
		$this->rights[$r][3] = 1;
		$this->rights[$r][4] = 'parc';
		$r ++;

		$this->rights[$r][0] = 1035712;
		$this->rights[$r][1] = 'exporter les ecritures comptables';
		$this->rights[$r][3] = 1;
		$this->rights[$r][4] = 'compta';
		$r ++;

		$this->rights[$r][0] = 1035713;
		$this->rights[$r][1] = 'lancer la facturation en masse';
		$this->rights[$r][3] = 1;
		$this->rights[$r][4] = 'facturation';
		$r ++;

		// Main menu entries
		$this->menus = array(); // List of menus to add
		$r = 0;

		$this->menu[$r] = array(
				'fk_menu' => 0,
				'type' => 'top',
				'titre' => 'TVI',
				'mainmenu' => 'tvi',
				'url' => '/tvi/form/parc.php',
				'langs' => '',
				'position' => 100,
				'enabled' => '$conf->tvi->enabled',
				'perms' => '$conf->tvi->enabled',
				'target' => '',
				'user' => 0
		);
		$r ++;

		$this->menu[$r] = array(
				'fk_menu' => 'fk_mainmenu=tvi',
				'type' => 'left',
				'titre' => "TVI",
				'mainmenu' => 'tvi',
				'leftmenu' => 'tvi_loc',
				'url' => '/tvi/form/parc.php',
				'langs' => '',
				'position' => 100 + $r,
				'enabled' => '$conf->tvi->enabled',
				'perms' => '$conf->tvi->enabled',
				'target' => '',
				'user' => 0
		);
		$r ++;

		$this->menu[$r] = array(
				'fk_menu' => 'fk_mainmenu=tvi,fk_leftmenu=tvi_loc',
				'type' => 'left',
				'titre' => 'Facturation en masse',
				'mainmenu' => 'tvi',
				'url' => '/tvi/form/facturation.php',
				'langs' => '',
				'position' => 100 + $r,
				'enabled' => '$user->rights->facturation',
				'perms' => '$user->rights->facturation',
				'target' => '',
				'user' => 0
		);
		$r ++;
		
		$this->menu[$r] = array(
				'fk_menu' => 'fk_mainmenu=tvi,fk_leftmenu=tvi_loc',
				'type' => 'left',
				'titre' => 'Journal de vente',
				'mainmenu' => 'tvi',
				'url' => '/tvi/form/journal_sell.php',
				'langs' => '',
				'position' => 100 + $r,
				'enabled' => '$user->rights->compta',
				'perms' => '$user->rights->compta',
				'target' => '',
				'user' => 0
		);
		$r ++;

		
	}

	/**
	 * Function called when module is enabled.
	 * The init function add constants, boxes, permissions and menus
	 * (defined in constructor) into Dolibarr database.
	 * It also creates data directories
	 *
	 * @param string $options Enabling module ('', 'noboxes')
	 * @return int if OK, 0 if KO
	 */
	public function init($options = '') {
		global $conf;

		$sql = array();

		dol_include_once('/core/class/extrafields.class.php');
		$extrafields = new ExtraFields($this->db);
		

		// Extrafields contract fact
		$res = $extrafields->addExtraField('frequency', 'Fréquence de facturation', 'int', 0, '', 'contrat', 0, 0, '1', array(
				'options' => ''
		), 1, '', 1);
		$res = $extrafields->addExtraField('unit_frequency', 'Unité de fréquence de facturation', 'select', 1, '', 'contrat', 0, 0, '', array(
				'options' => array(
						'd' => 'Jour',
						'm' => ' Mois',
						'y' => 'Année'
				)
		), 1, '', 1);
		$res = $extrafields->addExtraField('date_when', 'Date pour la prochaine génération de facture', 'date', 2, '', 'contrat', 0, 0, '', array(
				'options' => ''
		), 1, '', 1);
		$res = $extrafields->addExtraField('kmsup', 'Kilométrage mensuel inclus dans le contrat', 'int', 3, '', 'contrat', 0, 0, '', array(
				'options' => ''
		), 1, '', 1);
		$res = $extrafields->addExtraField('typ_contract', 'Type de contrat', 'select', 4, '', 'contrat', 0, 0, '', array(
				'options' => array(
						'CD' => 'Courte durée',
						'LD' => 'Longue durée',
				)
		), 1, '', 1);

		$result = $this->loadTables();

		$result=$this->_init($sql, $options);
		if ($result>0) {
			require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
			$result=dol_copy(dol_buildpath('/tvi/core/doctemplate/modelcontract.pdf'),DOL_DATA_ROOT.'/tvi/modelpdf/modelcontract.pdf',0,0);
		}
		return $result;
	}

	/**
	 * Function called when module is disabled.
	 * Remove from database constants, boxes and permissions from Dolibarr database.
	 * Data directories are not deleted
	 *
	 * @param string $options Enabling module ('', 'noboxes')
	 * @return int if OK, 0 if KO
	 */
	public function remove($options = '') {
		$sql = array();

		return $this->_remove($sql, $options);
	}

	/**
	 * Create tables, keys and data required by module
	 * Files llx_table1.sql, llx_table1.key.sql llx_data.sql with create table, create keys
	 * and create data commands must be stored in directory /lead/sql/
	 * This function is called by this->init
	 *
	 * @return int if KO, >0 if OK
	 */
	private function loadTables() {
		return $this->_load_tables('/tvi/sql/');
	}
}
