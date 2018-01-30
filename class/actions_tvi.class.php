<?php

/* Copyright (C) 2015		Florian HENRY	<florian.henry@atm-consulting.fr>
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
 * \file htdocs/lead/class/actions_lead.class.php
* \ingroup lead
* \brief Fichier de la classe des actions/hooks des lead
*/
class ActionsTvi // extends CommonObject
{

	/**
	 * addMoreActionsButtons Method Hook Call
	 *
	 * @param string[] $parameters parameters
	 * @param CommonObject $object Object to use hooks on
	 * @param string $action Action code on calling page ('create', 'edit', 'view', 'add', 'update', 'delete'...)
	 * @param HookManager $hookmanager class instance
	 * @return int Hook status
	 */
	function addMoreActionsButtons($parameters, &$object, &$action, $hookmanager) {
		global $langs, $conf, $user, $db, $bc;

		$current_context = explode(':', $parameters['context']);
		if (in_array('contractcard', $current_context)){
			$out = '<script type="text/javascript">' . "\n";
			$out .= '  	$(document).ready(function() {' . "\n";

			//Count nb file associated to contract to validated it
			require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
			$upload_dir = $conf->contrat->dir_output . "/" . dol_sanitizeFileName($object->ref);
			$nbFiles = count(dol_dir_list($upload_dir,'files',0,'','(\.meta|_preview\.png)$'));
			if ($nbFiles<0) {
				$out .= '		$(\'a[href*="action=valid"].butAction\').hide();';
			}

			if ($user->rights->contrat->activer && $nbFiles<2) {
				$out .= '		$a = $(\'<div class="inline-block divButAction"><a class="butAction" href="'.dol_buildpath('/contrat/card.php',2).'?id='.$object->id.'&amp;action=activall">'.$langs->trans("Activer tout les services (dates prévues)").'</a></div>\');' . "\n";
				$out .= '		$(\'div.fiche div.tabsAction\').first().prepend($a);' . "\n";
			}

			$out .= '  	});' . "\n";
			$out .= '</script>';

			print $out;
		}


		// Always OK
		return 0;
	}

	/**
	 * formObjectOptions Method Hook Call
	 *
	 * @param string[] $parameters parameters
	 * @param CommonObject $object Object to use hooks on
	 * @param string $action Action code on calling page ('create', 'edit', 'view', 'add', 'update', 'delete'...)
	 * @param HookManager $hookmanager class instance
	 * @return int Hook status
	 */
	function formObjectOptions($parameters, &$object, &$action, $hookmanager) {
		global $langs, $conf, $user, $db, $bc;
		$this->resprint= 'insertion hook';
		$current_context = explode(':', $parameters['context']);
		if (in_array('contractcard', $current_context)) {
			$this->resprint= 'insertion hook';

		}

		return 0;
	}

	/**
	 * doActions Method Hook Call
	 *
	 * @param string[] $parameters parameters
	 * @param CommonObject $object Object to use hooks on
	 * @param string $action Action code on calling page ('create', 'edit', 'view', 'add', 'update', 'delete'...)
	 * @param HookManager $hookmanager class instance
	 * @return int Hook status
	 */
	function doActions($parameters, &$object, &$action, $hookmanager) {
		global $langs, $conf, $user, $db, $bc;

		require_once DOL_DOCUMENT_ROOT . '/core/lib/date.lib.php';

		$confirm = GETPOST('confirm','alpha');
		$current_context = explode(':', $parameters['context']);

		if (in_array('contractcard', $current_context)) {
			$url = dol_buildpath('/tvi/form/contract/card.php?id=' .$object->id);
			header("Location: ". $url);
			exit;
		}
		
// 		if (in_array('contractcard', $current_context) && $action=='activall') {

// 			foreach($object->lines as $line) {

// 				if ($line->date_ouverture_prevue<=dol_now() && dol_now()<=$line->date_fin_validite) {
// 					$result = $object->active_line($user, $line->id, $line->date_ouverture_prevue, $line->date_fin_validite, '');
// 					if ($result<0) {
// 						setEventMessages($object->error, $object->errors,'errors');
// 					}
// 				}
// 			}
// 		} 
		return 0;
	}

	
	/**
	 * formConfirm Method Hook Call
	 *
	 * @param string[] $parameters parameters
	 * @param CommonObject $object Object to use hooks on
	 * @param string $action Action code on calling page ('create', 'edit', 'view', 'add', 'update', 'delete'...)
	 * @param HookManager $hookmanager class instance
	 * @return int Hook status
	 */
	function formConfirm($parameters, &$object, &$action, $hookmanager) {
		global $langs, $conf, $user, $db, $bc;

		$current_context = explode(':', $parameters['context']);
		
		return 0;
	}
}

