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
		if (in_array('loancard', $current_context)){
			$out = '<script type="text/javascript">' . "\n";
			$out .= '  	$(document).ready(function() {' . "\n";
			$out .= '		$a = $(\'<a href="javascript:popEcheancier()" class="butAction">Créer / Modifier échéancier de pret</a>\');' . "\n";
			$out .= '		$(\'div.fiche div.tabsAction\').first().prepend($a);' . "\n";
			$out .= '  	});' . "\n";
			$out .= '' . "\n";
			$out .= '  	function popEcheancier() {' . "\n";
			$out .= '  		$div = $(\'<div id="popCalendar"><iframe width="100%" height="100%" frameborder="0" src="' . dol_buildpath('/tvi/tvi/popup/createecheancier.php?loanid=' . $object->id, 1) . '"></iframe></div>\');' . "\n";
			$out .= '' . "\n";
			$out .= '  		$div.dialog({' . "\n";
			$out .= '  			modal:true' . "\n";
			$out .= '  			,width:"90%"' . "\n";
			$out .= '  			,height:$(window).height() - 150' . "\n";
			$out .= '  		});' . "\n";
			$out .= '' . "\n";
			$out .= '  	}' . "\n";
			$out .= '' . "\n";
			$out .= '</script>';
			print $out;
		}

		if (in_array('contractcard', $current_context)){
			$out = '<script type="text/javascript">' . "\n";
			$out .= '  	$(document).ready(function() {' . "\n";

			//Count nb file associated to contract to validated it
			require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
			$upload_dir = $conf->contrat->dir_output . "/" . dol_sanitizeFileName($object->ref);
			$nbFiles = count(dol_dir_list($upload_dir,'files',0,'','(\.meta|_preview\.png)$'));
			if ($nbFiles<2) {
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


		if (in_array('invoicesuppliercard', $current_context)){

			 if (empty($object->array_options['options_origin']) && $object->fk_statut !=0) $out='<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=gen_recur">'.$langs->trans('Générer les Factures récurentes').'</a>';
			 if (!empty($object->array_options['options_origin']) && $object->fk_statut ==0) $out='<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=mod_recur">'.$langs->trans('Modifier les Factures récurentes suivantes').'</a>';
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

		$current_context = explode(':', $parameters['context']);
		if (in_array('loancard', $current_context)) {


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

		if (in_array('projectcard', $current_context) && $action != 'create') {

//  			header("Location: ".DOL_URL_ROOT.'/tvi/tvi/project_card.php?id=' . $object->id);
//  			exit;


		} elseif (in_array('contractcard', $current_context) && $action=='activall') {

			foreach($object->lines as $line) {

				if ($line->date_ouverture_prevue<=dol_now() && dol_now()<=$line->date_fin_validite) {
					$result = $object->active_line($user, $line->id, dol_now(), $line->date_fin_validite, '');
					if ($result<0) {
						setEventMessages($object->error, $object->errors,'errors');
					}
				}
			}
		} elseif (in_array('invoicesuppliercard', $current_context) && $action=='gen_recur_confirm' && $confirm == 'yes') {

			if($object->array_options['options_recur']==true && !empty($object->array_options['options_period']) && !empty($object->array_options['options_freq']) && !empty($object->array_options['options_nb_gen']) && !empty($object->array_options['options_nb_gen'])){
				for ($i = 1; $i <= $object->array_options['options_nb_gen']-1; $i++) {
					$new = new FactureFournisseur($db);
					$org = new FactureFournisseur($db);
					$org = clone($object);
					$newid = $org->createFromClone($org->id);
					$res = $new->fetch($newid);
					$new->date = dol_time_plus_duree($org->date, $i*$org->array_options['options_freq'], $org->array_options['options_period']);
					$new->date_echeance = dol_time_plus_duree($org->date_echeance, $i*$org->array_options['options_freq'], $org->array_options['options_period']);
					$new->ref_supplier = $org->ref_supplier . '-' . ($i+1);
					$res = $new->update($user);
					$new->array_options['options_origin'] = $org->id;
					$new->insertExtraFields();
					$new->add_object_linked('invoice_supplier',$org->id);
				}
				$object->array_options['options_origin'] = $object->id;
				$object->insertExtraFields();
			}
			$action = '';

		} elseif (in_array('invoicesuppliercard', $current_context) && $action=='mod_recur_confirm' && $confirm == 'yes') {

		if($object->array_options['options_recur']==true && !empty($object->array_options['options_period']) && !empty($object->array_options['options_freq']) && !empty($object->array_options['options_nb_gen'])){
				require_once DOL_DOCUMENT_ROOT . '/tvi/class/tvi.class.php';

				$org = new FactureFournisseur($db);
				$org = clone($object);

				$tvi = new Tvi($db);
				$filter= array('f.datef>='=>$org->date, 'ef.recur'=>1, 'ef.origin' => $org->array_options['options_origin']);
				$fac =array();
				$fac = $tvi->fetchallrecursivesupplier_invoices('','','','',$filter);
				foreach($fac as $f){
					if($f != $object->id){
						$ff = new FactureFournisseur($db);
						$ff->fetch($f);
						$ref = $ff->ref_supplier;
						$datef = $ff->date;
						$datee = $ff->date_echeance;
						$ff->delete($f);
						$ffid = $org->createFromClone($org->id);
						echo $ffid .'</br>';
						$ff->fetch($ffid);
						$ff->date = $datef;
						$ff->date_echeance = $datee;
						$ff->ref_supplier = $ref;
						$ff->update($user);
// 						$ff->array_options['options_origin'] = $object->array_options['origin'];
// 						$ff->insertExtraFields();
						$ff->add_object_linked('invoice_supplier',$org->array_options['options_origin']);
					}
				}
			}
			$action = '';

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
		if (in_array('invoicesuppliercard', $current_context) && $action == 'gen_recur') {
			$form = new Form($db);

			$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('DeleteBill'), 'Etes vous sur de vouloir générer les factures récurrentes ?', 'gen_recur_confirm', '', 0, 1);
			print $formconfirm;
		}

		if (in_array('invoicesuppliercard', $current_context) && $action == 'mod_recur') {
			$form = new Form($db);

			$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('DeleteBill'), 'Etes vous sur de vouloir modifier les factures récurrentes suivantes ?', 'mod_recur_confirm', '', 0, 1);
			print $formconfirm;
		}

		return 0;
	}
}

