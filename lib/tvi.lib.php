<?php
/* Copyright (C) 2008-2014 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2009 Regis Houssin        <regis.houssin@capnetworks.com>
 * Copyright (C) 2011	   Juanjo Menent        <jmenent@2byte.es>
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
 * or see http://www.gnu.org/
 */

/**
 * \file tvi/lib/tvi.lib.php
 * \brief Set of function for the agenda module
 */

/**
 * Show filter form in agenda view
 *
 * @param Object $form Form object
 * @param int $canedit Can edit filter fields
 * @param int $status Status
 * @param int $year Year
 * @param int $month Month
 * @param int $day Day
 * @param int $showbirthday Show birthday
 * @param string $filtera Filter on create by user
 * @param string $filtert Filter on assigned to user
 * @param string $filterd Filter of done by user
 * @param int $pid Product id
 * @param int $socid Third party id
 * @param string $action Action string
 * @param array $showextcals Array with list of external calendars (used to show links to select calendar), or -1 to show no legend
 * @param string|array $actioncode Preselected value(s) of actioncode for filter on event type
 * @param int $usergroupid Id of group to filter on users
 * @param string $excludetype A type to exclude ('systemauto', 'system', '')
 * @param int $resourceid Preselected value of resource for filter on resource
 * @return void
 */
function print_actions_filter($canedit, $year, $month, $day, $param_filter = array(), $columns_array = array()) {
	global $conf, $user, $langs, $db, $hookmanager;

	include_once DOL_DOCUMENT_ROOT . '/core/class/html.form.class.php';
	$form = new Form($db);

	$langs->load("companies");

	// Filters
	print '<form name="listactionsfilter" class="listactionsfilter" action="' . $_SERVER["PHP_SELF"] . '" method="get">';
	print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
	print '<input type="hidden" name="year" value="' . $year . '">';
	print '<input type="hidden" name="month" value="' . $month . '">';
	print '<input type="hidden" name="day" value="' . $day . '">';

	if ($canedit) {


		if (! empty($conf->projet->enabled) && $user->rights->projet->lire) {

			print '<table class="nobordernopadding" width="100%">';
				print '<tr>';
					print '<td style="padding-bottom: 2px; padding-right: 4px;" width="70px">';
					print $langs->trans("ThirdParty") . ': ';
					print '</td>';

					print '<td style="padding-bottom: 2px; padding-right: 4px;" width="300px">';
					print $form->select_company($param_filter['socid'], 'socid', '', 1);
					print '</td>';

					print '<td style="padding-bottom: 2px; padding-right: 4px;" width="80px">';
					print $langs->trans("Categorie") . ': ';
					print '</td>';

					print '<td style="padding-bottom: 2px; padding-right: 4px;" width="200px">';
					$arrval = array(
							'1' => 'Courte durée',
							'2' => 'Longue Durée',
					);
					print $form->selectarray('categorie', $arrval, $param_filter['categorie'], 1);
					print '</td>';

					print '<th width="200px">';
					print 'Colonnes à affichées';
					print '</th>';

					print '<td rowspan="6">';
					print '<div class="formleftzone">';
					print '<input type="submit" class="button" name="refresh" value="' . $langs->trans("Refresh") . '">';
					print '</div>';
					print '</td>';
				print '</tr>';

			require_once DOL_DOCUMENT_ROOT . '/core/class/html.formprojet.class.php';
			$formproject = new FormProjets($db);

				print '<tr>';
				print '<td style="padding-bottom: 2px;">';
				print $langs->trans("Project") . ': ';
				print '</td>';

				print '<td style="padding-bottom: 2px; padding-right: 4px;">';
				$formproject->select_projects(! empty($param_filter['socid']) ? $param_filter['socid'] : - 1, $param_filter['pid'], 'projectid', 0);
				print '</td>';

				dol_include_once('/tvi/class/html.formtvi.class.php');
				$formtvi = new FormTvi($db);

				print '<td style="padding-bottom: 2px;">';
				print $langs->trans("Type") . ': ';
				print '</td>';

				print '<td style="padding-bottom: 2px;">';
				print $formtvi->select_dict('genre', $param_filter['genre'], 'c_tvi_genre', array('genre'));
				print '</td>';

				print '<td rowspan="5">';
				print '<table>';
				foreach ( $columns_array as $col ) {
					if ($col['name'] != 'dossier') {
						print '<tr><td>';
						print '<input type="checkbox" name="columns_array_vis[]" value="' . $col['name'] . '" ' . (empty($col['visible']) ? '' : ' checked ') . '>' . $col['label'];
						print '</td></tr>';
					}
				}
				print '</table>';
				print'</td>';

			print '</tr>';

			print '<tr>';
				print '<td style="padding-bottom: 2px;">';
				print $langs->trans("Carroserie") . ': ';
				print '</td>';

				print '<td style="padding-bottom: 2px; padding-right: 4px;">';
				print $formtvi->select_dict('carrosserie', $param_filter['carrosserie'], 'c_tvi_carrosserie', array('carrosserie'));
				print '</td>';

				print '<td style="padding-bottom: 2px;">';
				print $langs->trans("Marque") . ': ';
				print '</td>';

				print '<td style="padding-bottom: 2px; padding-right: 4px;">';
				print $formtvi->select_dict('marque', $param_filter['marque'], 'c_tvi_marques', array('marque'));
				print '</td>';
			print '</tr>';

			print '<tr>';
				print '<td style="padding-bottom: 2px;">';
				print $langs->trans("Modèle") . ': ';
				print '</td>';

				print '<td style="padding-bottom: 2px; padding-right: 4px;">';
				print '<input type="text" class="flat" size="10" name="modele" id="modele" value="' . $param_filter['modele'] . '">';
				print '</td>';

				print '<td style="padding-bottom: 2px;">';
				print $langs->trans("Puissance") . ': ';
				print '</td>';

				print '<td style="padding-bottom: 2px; padding-right: 4px;">';
				print '<input type="text" class="flat" size="10" name="puissance" id="puissance" value="' . $param_filter['puissance'] . '">';
				print '</td>';
			print '</tr>';

			print '<tr>';
				print '<td style="padding-bottom: 2px;">';
				print $langs->trans("PTC") . ': ';
				print '</td>';

				print '<td style="padding-bottom: 2px; padding-right: 4px;">';
				print '<input type="text" class="flat" size="10" name="ptc" id="ptc" value="' . $param_filter['ptc'] . '">';
				print '</td>';

				print '<td style="padding-bottom: 2px;">';
				print $langs->trans("Chassis") . ': ';
				print '</td>';

				print '<td style="padding-bottom: 2px; padding-right: 4px;">';
				print '<input type="text" class="flat" size="10" name="ptc" id="ptc" value="' . $param_filter['chassis'] . '">';
				print '</td>';
			print '</tr>';

			print '<tr>';
				print '<td style="padding-bottom: 2px;">';
				print $langs->trans("DMC") . ' entre: ';
				print '</td>';

				print '<td style="padding-bottom: 2px; padding-right: 4px;">';
				print $form->select_date($param_filter['dmcfrom'], 'dmcfrom', 0, 0, 1, '', 1, 0, 1);
				print 'Et ';
				print $form->select_date($param_filter['dmcto'], 'dmcto', 0, 0, 1, '', 1, 0, 1);
				print '</td>';

				print '<td style="padding-bottom: 2px;">';
				print $langs->trans("Immatriculation") . ': ';
				print '</td>';

				print '<td style="padding-bottom: 2px; padding-right: 4px;">';
				print '<input type="text" class="flat" size="10" name="immat" id="immat" value="' . $param_filter['immat'] . '">';
				print '</td>';
			print '</tr>';
		print '</table>';
		}
	}
	print '</form>';
}

/**
 * Define head array for tabs of agenda setup pages
 *
 * @param string $param Parameters to add to url
 * @return array Array of head
 */
function tvicalendars_prepare_head($param) {
	global $langs, $conf, $user;

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath('/tvi/tvi/pervehicule.php', 1) . ($param ? '?' . $param : '');
	$head[$h][1] = $langs->trans("Vue Par Vehicule");
	$head[$h][2] = 'cardperuser';
	$h ++;

	return $head;
}


function print_actions_filter_list($form, $canedit, $status, $pid, $actioncode='',$datestart,$dateend)
{
	global $conf, $user, $langs, $db, $hookmanager;
	global $begin_h, $end_h, $begin_d, $end_d;

	$langs->load("companies");

	// Filters
	print '<form name="listactionsfilter" class="listactionsfilter" action="' . $_SERVER["PHP_SELF"] . '" method="get">';
	print '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">';

	print '<div class="fichecenter">';

	if (! empty($conf->browser->phone)) print '<div class="fichehalfleft">';
	else print '<table class="nobordernopadding" width="100%"><tr><td class="borderright">';

	print '<table class="nobordernopadding">';

	if ($canedit)
	{
		include_once DOL_DOCUMENT_ROOT . '/tvi/class/tvi.class.php';
		$formactions=new Tvi($db);

		// Type
		print '<tr>';
		print '<td class="nowrap" style="padding-bottom: 2px; padding-right: 4px;">';
		print $langs->trans("Type");
		print ' &nbsp;</td><td class="nowrap maxwidthonsmartphone" style="padding-bottom: 2px; padding-right: 4px;">';
		$multiselect=0;
		if (! empty($conf->global->MAIN_ENABLE_MULTISELECT_TYPE))     // We use an option here because it adds bugs when used on agenda page "peruser" and "list"
		{
		$multiselect=(!empty($conf->global->AGENDA_USE_EVENT_TYPE));
		}
		print $formactions->select_type_actions($actioncode, "actioncode", 'system', (empty($conf->global->AGENDA_USE_EVENT_TYPE)?1:0), 0, $multiselect);
		print '</td></tr>';
	}

	if (! empty($conf->projet->enabled) && $user->rights->projet->lire)
	{
		require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';
		$formproject=new FormProjets($db);

		print '<tr>';
		print '<td class="nowrap" style="padding-bottom: 2px;">';
		print $langs->trans("Project").' &nbsp; ';
		print '</td><td class="nowrap maxwidthonsmartphone" style="padding-bottom: 2px;">';
		$formproject->select_projects($socid?$socid:-1, $pid, 'projectid', 0);
		print '</td></tr>';
	}

	if ($canedit)
	 {
		// Status
		print '<tr>';
		print '<td class="nowrap" style="padding-bottom: 2px; padding-right: 4px;">';
		print $langs->trans("Status");
		print ' &nbsp;</td><td class="nowrap maxwidthonsmartphone" style="padding-bottom: 2px; padding-right: 4px;">';
		$formactions->form_select_status_action('formaction',$status,1,'status',1,2);
		print '</td></tr>';

		//date de début
		print '<tr>';
		print '<td class="nowrap" style="padding-bottom: 2px; padding-right: 4px;">';
		print 'Controles du :';
		print ' &nbsp;</td><td class="nowrap maxwidthonsmartphone" style="padding-bottom: 2px; padding-right: 4px;">';
		print $form->select_date($datestart, 'datestart', 0, 0, 1, '', 1, 0, 1);
		print ' au :';
		print $form->select_date($dateend, 'dateend', 0, 0, 1, '', 1, 0, 1);
		print '</td></tr>';

		}

	print '</table>';

	if (! empty($conf->browser->phone)) print '</div>';
	else print '</td>';

	if (! empty($conf->browser->phone)) print '<div class="fichehalfright">';
	else print '<td align="center" valign="middle" class="nowrap">';

	print '<table><tr><td align="center">';
	print '<div class="formleftzone">';
	print '<input type="submit" class="button" style="min-width:120px" name="refresh" value="' . $langs->trans("Refresh") . '">';
	print '</div>';
	print '</td></tr>';
	print '</table>';

	if (! empty($conf->browser->phone)) print '</div>';
	else print '</td></tr></table>';

	print '</div>';	// Close fichecenter
	print '<div style="clear:both"></div>';

	print '</form>';
}
Function print_button_donne($rowid,$param)
{
	$but = '<a style="padding-left: 5px;" href="'. DOL_URL_ROOT .'/tvi/tvi/listactions.php?action=validaction&actionid='.$rowid .$param . '"><img src="' . DOL_URL_ROOT . '/theme/eldy/img/tick.png" border="0" alt="" title="valider et replannifier"></a>';
	return $but;
}

Function print_button_resa($rowid,$param)
{
	$but = '<a style="padding-left: 5px;" id="resa'.$rowid.'" href="#"><img src="' . DOL_URL_ROOT . '/theme/eldy/img/edit.png" border="0" alt="" title="ajouter une réservation"></a>';
	return $but;
}


Function get_next_date($fk_type,$fk_genre,$date_valid)
{
	global $db;

	$sql = "SELECT periode FROM " . MAIN_DB_PREFIX . "event_period WHERE fk_genre = " . $fk_genre . " AND fk_typeevent = " . $fk_type;
	$resql = $db->query($sql);
	if($resql){
		$obj=$db->fetch_object($resql);
	}

	return dol_time_plus_duree($date_valid, $obj->periode, 'm');
}

