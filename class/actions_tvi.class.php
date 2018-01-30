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
		$current_context = explode(':', $parameters['context']);
		
		if (in_array('contractcard', $current_context)) {
			

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
		$current_context = explode(':', $parameters['context']);

		if (in_array('contractcard', $current_context)) {
			$url = dol_buildpath('/tvi/form/contract/card.php?id=' .$object->id,2);
			header("Location: ". $url);
			exit;
		}
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

