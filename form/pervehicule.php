<?php
/* Copyright (C) 2001-2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2003      Eric Seigne          <erics@rycks.com>
 * Copyright (C) 2004-2014 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@capnetworks.com>
 * Copyright (C) 2011      Juanjo Menent        <jmenent@2byte.es>
 * Copyright (C) 2014      Cedric GROSS         <c.gross@kreiz-it.fr>
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
 * \file htdocs/comm/action/peruser.php
 * \ingroup agenda
 * \brief Tab of calendar events per user
 */
// For root directory
$res = @include '../main.inc.php';
// For "custom" directory
if (! $res) {
	$res = @include '../../main.inc.php';
}
if (! $res) {
	die("Include of main fails");
}

require_once DOL_DOCUMENT_ROOT . '/comm/action/class/actioncomm.class.php';
require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
require_once DOL_DOCUMENT_ROOT . '/user/class/usergroup.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/extrafields.class.php';
dol_include_once('/tvi/lib/tvi.lib.php');
dol_include_once('/tvi/class/tvi.class.php');
if (! empty($conf->projet->enabled))
	require_once DOL_DOCUMENT_ROOT . '/core/class/html.formprojet.class.php';

if (! isset($conf->global->AGENDA_MAX_EVENTS_DAY_VIEW))
	$conf->global->AGENDA_MAX_EVENTS_DAY_VIEW = 3;

	// Security check
if ($socid < 0)
	$socid = '';

$canedit = 1;
if (! $user->rights->tvi->read)
	accessforbidden();

$resourceid = GETPOST("resourceid", "int");
$year = GETPOST("year", "int") ? GETPOST("year", "int") : date("Y");
$month = GETPOST("month", "int") ? GETPOST("month", "int") : date("m");
$week = GETPOST("week", "int") ? GETPOST("week", "int") : date("W");
$day = GETPOST("day", "int") ? GETPOST("day", "int") : date("d");
$pid = GETPOST("projectid", "int", 3);
$genre=GETPOST('genre');
$carrosserie=GETPOST('carrosserie');
$marque=GETPOST('marque');
$modele=GETPOST('modele');
$puissance=GETPOST('puissance');
$ptc=GETPOST('ptc');
$chassis=GETPOST('chassis');
$immat=GETPOST('immat');
$columns_array_vis=GETPOST('columns_array_vis');
$action = GETPOST('action');
$vhresaid = GETPOST('vhresaid');
$datedebresa = dol_mktime(0, 0, 0, GETPOST('date_start'.$vhresaid.'month'), GETPOST('date_start'.$vhresaid.'day'), GETPOST('date_start'.$vhresaid.'year'));
$datefinresa = dol_mktime(0, 0, 0, GETPOST('date_end'.$vhresaid.'month'), GETPOST('date_end'.$vhresaid.'day'), GETPOST('date_end'.$vhresaid.'year'));
$resatitle = GETPOST('libelle'.$vhresaid);


if (!is_array($columns_array_vis)) {
	$columns_array_vis=array();
}
$categorie=GETPOST('categorie');
if (empty($categorie)) {
	$categorie=1;
}

$dttest=GETPOST('dmcfromday');
if (!empty($dttest)) {
	$dmcfrom=dol_mktime(0,0,0,GETPOST('dmcfrommonth'),GETPOST('dmcfromday'),GETPOST('dmcfromyear'));
} else {
	$dmcfrom='';
}

$dttest=GETPOST('dmctoday');
if (!empty($dttest)) {
	$dmcto=dol_mktime(23,59,59,GETPOST('dmctomonth'),GETPOST('dmctoday'),GETPOST('dmctoyear'));
} else {
	$dmcto='';
}
unset($dttest);

$dateselect = dol_mktime(0, 0, 0, GETPOST('dateselectmonth'), GETPOST('dateselectday'), GETPOST('dateselectyear'));
if ($dateselect > 0) {
	$day = GETPOST('dateselectday');
	$month = GETPOST('dateselectmonth');
	$year = GETPOST('dateselectyear');
}

$tmp = empty($conf->global->MAIN_DEFAULT_WORKING_DAYS) ? '1-5' : $conf->global->MAIN_DEFAULT_WORKING_DAYS;
$tmparray = explode('-', $tmp);

$begin_d = $day;
$end_d = dol_print_date(dol_get_last_day($year, $month), '%d');

if ($status == '' && ! isset($_GET['status']) && ! isset($_POST['status']))
	$status = (empty($conf->global->AGENDA_DEFAULT_FILTER_STATUS) ? '' : $conf->global->AGENDA_DEFAULT_FILTER_STATUS);

$langs->load("users");
$langs->load("agenda");
$langs->load("other");
$langs->load("commercial");

$form = new Form($db);
$companystatic = new Societe($db);
$extrafields = new ExtraFields($db);
$extralabels = $extrafields->fetch_name_optionals_label('projet', true);

$now = dol_now();
$nowarray = dol_getdate($now);
$nowyear = $nowarray['year'];
$nowmonth = $nowarray['mon'];
$nowday = $nowarray['mday'];

$prev = dol_get_first_day($year, $month);
$first_day = 1;
$first_month = dol_print_date($prev, '%m');
$first_year = dol_print_date($prev, '%Y');

$next = dol_get_next_month($month, $year);
$next_year = $next['year'];
$next_month = $next['month'];
$next_day = 1;

$prev = dol_get_prev_month($month, $year);
$prev_year = $prev['year'];
$prev_month = $prev['month'];
$prev_day = 1;

$param = '';
$param_filter=array();
$array_filter=array();
if (! empty($pid)) {
	$param_filter['pid'] = $pid;
	$array_filter['t.rowid'] = $pid;
	$param .= "&projectid=" . $pid;
} else {
	$param_filter['pid'] = 0;
}
if (! empty($resourceid)) {
	$param_filter['resourceid'] = $resourceid;
	$param .= "&resourceid=" . $resourceid;
}
if (! empty($socid)) {
	$param_filter['socid'] = $socid;
	$param .= "&socid=" . $socid;
} else {
	$param_filter['socid'] = '';
}
if (! empty($genre)) {
	$param_filter['genre'] = $genre;
	$array_filter['pextra.type'] = $genre;
	$param .= "&genre=" . $genre;
} else {
	$param_filter['genre'] = 0;
}
if (! empty($carrosserie)) {
	$param_filter['carrosserie'] = $carrosserie;
	$array_filter['pextra.carrosserie'] = $carrosserie;
	$param .= "&carrosserie=" . $carrosserie;
} else {
	$param_filter['carrosserie'] = 0;
}
if (! empty($marque)) {
	$param_filter['marque'] = $marque;
	$array_filter['pextra.marque'] = $marque;
	$param .= "&marque=" . $marque;
} else {
	$param_filter['marque'] = 0;
}
if (! empty($modele)) {
	$param_filter['modele'] = $modele;
	$array_filter['pextra.modele'] = $modele;
	$param .= "&modele=" . $modele;
}else {
	$param_filter['modele'] = '';
}
if (! empty($puissance)) {
	$param_filter['puissance'] = $puissance;
	$array_filter['pextra.puissance'] = $puissance;
	$param .= "&puissance=" . $puissance;
}else {
	$param_filter['puissance'] = '';
}
if (! empty($ptc)) {
	$param_filter['ptc'] = $ptc;
	$array_filter['pextra.ptc'] = $ptc;
	$param .= "&ptc=" . $ptc;
}else {
	$param_filter['ptc'] = '';
}
if (! empty($chassis)) {
	$param_filter['chassis'] = $chassis;
	$array_filter['pextra.chassis'] = $chassis;
	$param .= "&chassis=" . $chassis;
}else {
	$param_filter['chassis'] = '';
}
if (! empty($immat)) {
	$param_filter['immat'] = $immat;
	$array_filter['pextra.immat'] = $immat;
	$param .= "&immat=" . $immat;
}else {
	$param_filter['immat'] = '';
}
if (! empty($categorie) && $categorie!=-1) {
	$param_filter['categorie'] = $categorie;
	$array_filter['pextra.categorie'] = $categorie;
	$param .= "&categorie=" . $categorie;
}else {
	$param_filter['categorie'] = '';
	$param .= "&categorie=" . $categorie;
}
if (! empty($dmcfrom)) {
	$param_filter['dmcfrom'] = $dmcfrom;
	$array_filter['pextra.dmc<='] = $dmcfrom;
	$param .= "&dmcfromday=" . dol_print_date($dmcfrom,'%d')."&dmcfrommonth=" . dol_print_date($dmcfrom,'%m')."&dmcfromyear=" . dol_print_date($dmcfrom,'%Y');
}else {
	$param_filter['dmcfrom'] = '';
}
if (! empty($dmcto)) {
	$param_filter['dmcto'] = $dmcto;
	$array_filter['pextra.dmc>='] = $dmcto;
	$param .= "&dmctoday=" . dol_print_date($dmcto,'%d')."&dmctomonth=" . dol_print_date($dmcto,'%m')."&dmctoyear=" . dol_print_date($dmcto,'%Y');
}else {
	$param_filter['dmcto'] = '';
}

$firstdaytoshow = dol_mktime(0, 0, 0, $first_month, $first_day, $first_year);
$lastdaytoshow = dol_get_last_day($next_year, $next_month);

$color_contractline_status = array(
		'0' => '007fff',
		'4' => '00ff00',
		'5' => '9e9292'
);

$color_event_status = array(
		'-100' => 'ff0000',
		'101' => '9e9292',
		'0' => 'ffff00',
		'50' => '007fff',
		'100' => '00ff00'
);

$varpage='tvi_pervehicule';
$confusercolumn="MAIN_SELECTEDFIELDS_".$varpage;

if (! empty($user->conf->$confusercolumn)) {
	$columns_array=json_decode($user->conf->$confusercolumn,true);
} else {
	$columns_array[]=array('name'=>'dossier','label'=>'N° de parc','visible'=>1);
	$columns_array[]=array('name'=>'type','label'=>'Type','visible'=>0);
	$columns_array[]=array('name'=>'carrosserie','label'=>'Carrosserie','visible'=>0);
	$columns_array[]=array('name'=>'marque','label'=>'Marque','visible'=>0);
	$columns_array[]=array('name'=>'modele','label'=>'Modèle','visible'=>0);
	$columns_array[]=array('name'=>'puissance','label'=>'Puissance','visible'=>0);
	$columns_array[]=array('name'=>'ptc','label'=>'PTC','visible'=>0);
	$columns_array[]=array('name'=>'chassis','label'=>'Châssis','visible'=>0);
	$columns_array[]=array('name'=>'dmc','label'=>'DMC','visible'=>0);
	$columns_array[]=array('name'=>'immat','label'=>'Immat.','visible'=>0);
}

if (count($columns_array_vis)>0) {
	foreach($columns_array as $key=>$col) {
		if (in_array($col['name'],$columns_array_vis)) {
			$columns_array[$key]['visible']=1;

		}elseif ($col['name']!='dossier') {
			$columns_array[$key]['visible']=0;
		}

		if ($col['name']=='dossier') {
			$columns_array[$key]['visible']=1;
		}
	}
} else {
	foreach($columns_array as $key=>$col) {
		if ($col['name']!='dossier') {
			$columns_array[$key]['visible']=0;
		}
	}
}

foreach($columns_array as $key=>$col) {
	if ($columns_array[$key]['visible']==1){
		$cnt_colvis++;
	}
}

$tabparam[$confusercolumn]=json_encode($columns_array);
//var_dump($tabparam[$confusercolumn]);
include_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
$result=dol_set_user_param($db, $conf, $user, $tabparam);

$array_day_to_show = array();
$array_week_to_show = array();
$array_colspan = array();
$cnt_colspan = 1;

// Build date arry to show on two month
for($i = $firstdaytoshow; $i <= $lastdaytoshow; $i = dol_time_plus_duree($i, 1, 'd')) {

	$monthshort = dol_print_date($i, '%b');

	// Find week of the day sunday=0
	$arraytimestamp = dol_getdate($i);

	$class = 'cal_current_month cal_peruser';
	if ($arraytimestamp['wday'] == 0) {

		// Is week half on month
		if (count($array_week_to_show) > 0) {
			$calc_date = dol_time_plus_duree($i, - 7, 'd');
			$calc_monthshort = dol_print_date($calc_date, '%b');
			if ($calc_monthshort != $monthshort) {
				$monthshort = $calc_monthshort . '/' . $monthshort;
			}
		}

		// Add colspan value
		$array_colspan[] = $cnt_colspan;
		$cnt_colspan = 0;

		// Add week array
		$array_week_to_show[] = array(
				'dayofweek' => $arraytimestamp['wday'],
				'monthshort' => $monthshort,
				'weeknumber' => date("W", $i),
				'daynumber' => dol_print_date($i, 'day'),
				'timestamp' => $i
		);

		$class = 'cal_current_month_peruserright';
	}

	$array_day_to_show[] = array(
			'dayofweek' => $arraytimestamp['wday'],
			'monthshort' => $monthshort,
			'weeknumber' => date("W", $i),
			'daynumber' => dol_print_date($i, 'day'),
			'timestamp' => $i,
			'class' => $class
	);

	$cnt_colspan ++;
}

// Complete last week
$lastdatefromweektoshow = end($array_week_to_show);
$lastdatefromdaytoshow = end($array_day_to_show);
$cnt_colspan = 0;
if ($lastdatefromweektoshow['timestamp'] != $lastdatefromdaytoshow['timestamp']) {
	$calc_array_day_to_show = array_reverse($array_day_to_show);
	foreach ( $calc_array_day_to_show as $key => $datetocalc ) {
		if ($datetocalc['timestamp'] == $lastdatefromweektoshow['timestamp']) {

			$nextday = dol_time_plus_duree($datetocalc['timestamp'], 1, 'd');

			$monthshort = dol_print_date($nextday, '%b');
			$arraytimestamp = dol_getdate($nextday);

			// Is week half on month
			$calc_monthshort = dol_print_date($lastdatefromdaytoshow['timestamp'], '%b');
			if ($calc_monthshort != $monthshort) {
				$monthshort = $monthshort . '/' . $calc_monthshort;
			}

			$array_colspan[] = $cnt_colspan;
			$array_week_to_show[] = array(
					'dayofweek' => $arraytimestamp['wday'],
					'monthshort' => $monthshort,
					'weeknumber' => date("W", $nextday),
					'daynumber' => dol_print_date($nextday, 'day'),
					'timestamp' => $nextday
			);
			break;
		}
		$cnt_colspan ++;
	}
}

if($action=='addresa_confirm' && !empty($datedebresa) && !empty($datefinresa) && ! empty($resatitle) && !empty($vhresaid)){
	$resa = new ActionComm($db);
	$resa->type_id = 50;
	$resa->userownerid = $user->id;
	$resa->label = $resatitle;
	$resa->fulldayevent = 1;
	$resa->datep = $datedebresa;
	$resa->datef = $datefinresa;
	$resa->percentage = -1;
	$resa->fk_project = $vhresaid;
	$result = $resa->create($user);
	if($result<0){
		setEventMessage($resa->error,'errors');
	}else{
		setEventMessage('evenement créé','mesgs');
		header("Location: ".DOL_URL_ROOT.'/tvi/tvi/pervehicule.php?' . $param);
		exit;
	}
}

/*
 * View
 */

llxHeader('', $langs->trans("Agenda TVI"), '', '', 0, 0, array(), array('/tvi/css/style.css'));

$tmpday = $first_day;

$nav = "<a href=\"?year=" . $prev_year . "&amp;month=" . $prev_month . "&amp;day=" . $prev_day . $param . "\">" . img_previous($langs->trans("Previous"), 'class="valignbottom"') . "</a>\n";
$nav .= " <span id=\"month_name\">" . dol_print_date(dol_mktime(0, 0, 0, $first_month, $first_day, $first_year), "%Y") . ", " . $langs->trans("Month") . " " . $month;
$nav .= " </span>\n";
$nav .= "<a href=\"?year=" . $next_year . "&amp;month=" . $next_month . "&amp;day=" . $next_day . $param . "\">" . img_next($langs->trans("Next"), 'class="valignbottom"') . "</a>\n";
$nav .= " &nbsp; (<a href=\"?year=" . $nowyear . "&amp;month=" . $nowmonth . "&amp;day=" . $nowday . $param . "\">" . $langs->trans("Today") . "</a>)";
$picto = 'calendarweek';

$nav .= ' &nbsp; <form name="dateselect" action="' . $_SERVER["PHP_SELF"] . '?' . $param . '">';
$nav .= '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
$nav .= '<input type="hidden" name="resourceid" value="' . $resourceid . '">';
$nav .= '<input type="hidden" name="socid" value="' . $socid . '">';
$nav .= '<input type="hidden" name="projectid" value="' . $pid . '">';

//$nav .= '<input type="hidden" name="genre[]" value="' . $genre . '">';

$nav .= $form->select_date($dateselect, 'dateselect', 0, 0, 1, '', 1, 0, 1);
$nav .= ' <input type="submit" name="submitdateselect" class="button" value="' . $langs->trans("Refresh") . '">';
$nav .= '</form>';

// Must be after the nav definition
$param .= '&year=' . $year . '&month=' . $month . ($day ? '&day=' . $day : '');
// print 'x'.$param;

$head = tvicalendars_prepare_head($param);

dol_fiche_head($head, 'cardperuser', $langs->trans('Agenda Location'), 0, 'action');
print_actions_filter($canedit, $year, $month, $day, $param_filter,$columns_array);
dol_fiche_end();

$link = '';
print load_fiche_titre($s, $link . ' &nbsp; &nbsp; ' . $nav, '');

// Get all project/vehicule list
$tvi = new Tvi($db);
$array_filter['t.fk_statut'] = array(
		0,
		1
);
$result = $tvi->fetchAllVehicules('t.ref', '', 0, 0, $array_filter);
if ($result < 0) {
	setEventMessages(null, $tvi->errors, 'errors');
}

echo '<table width="100%" class="noborder nocellnopadd cal_month">';

echo '<tr class="liste_titre">';
echo '<td class="cal_current_month" colspan="'.$cnt_colvis.'"></td>';

// Print week
foreach ( $array_week_to_show as $key => $dateshow ) {

	echo '<td align="center" class="cal_current_month" colspan="' . $array_colspan[$key] . '">';
	echo $dateshow['monthshort'];
	echo '<BR>';
	echo $langs->trans('Week') . ':' . $dateshow['weeknumber'];
	echo "</td>\n";
}
echo "</tr>\n";

// column title
echo '<tr class="liste_titre">' . "\n";
foreach($columns_array as $col) {
	if ($col['visible']) {
		echo '<td>'.$col['label'].'</td>';
	}
}
// Print day
foreach ( $array_day_to_show as $key => $dateshow ) {
	echo '<td align="center" >';
	echo $langs->trans("Day" . $dateshow['dayofweek'])[0] . '<BR>';
	echo dol_print_date($dateshow['timestamp'], '%d');
	echo "</td>\n";
}
echo "</tr>\n";

// Loop on each project/vehicule to show calendar
$var = false;
if (is_array($tvi->lines) && count($tvi->lines) > 0) {
	foreach ( $tvi->lines as $keyveh => $veh ) {
		$var = ! $var;
		echo "<tr>";
		echo '<td class="cal_current_month_peruserright cal_peruserviewname' . ($var ? ' cal_impair' : '') . '" style="white-space:nowrap" rowspan="2">';
		echo $veh->getNomUrl();
		echo ' ';
		echo print_button_resa($veh->id,$param);
		$url_ret = $_SERVER["PHP_SELF"] . '?vhresaid=' . $veh->id . $param;
		$formquestion = array(
				array(
						'type' => 'date',
						'name' => 'date_start'.$veh->id,
						'label'=> 'date début'),
				array(
						'type' => 'date',
						'name' => 'date_end'.$veh->id,
						'label'=> 'date début'),
				array(
						'type' => 'text',
						'name' => 'libelle'.$veh->id,
						'label'=> 'Motif'),
		);
		echo $form->formconfirm($url_ret, 'Nouvelle resa', '', 'addresa_confirm', $formquestion, '', 'resa'.$veh->id);
		echo '</td>';
		foreach($columns_array as $col) {

			if ($col['visible'] && $col['name']!='dossier') {
				echo '<td class="cal_current_month_peruserright cal_peruserviewname' . ($var ? ' cal_impair' : '') . '" rowspan="2">';
				echo $extrafields->showOutputField($col['name'], $veh->array_options['options_'.$col['name']]);
				echo '</td>';
			}
		}

		// Find contract lines
		$array_filter = array();
		$array_filter['t.fk_projet'] = array(
				$veh->id
		);
		$array_filter['p.ref'] = 'LOC';
		$array_filter['t.statut'] = array(
				1
		);
		$array_filter['dateinrange'] = array('from'=>	$firstdaytoshow,'to'=>$lastdaytoshow);
		if (! empty($socid)) {
			$array_filter['t.fk_soc'] = $socid;
		}

		$result = $tvi->fetchContractLines('', '', 0, 0, $array_filter);
		if ($result < 0) {
			setEventMessages(null, $tvi->errors, 'errors');
		}
		// Find if contract lines is in range displayed
		$events = array();
		$date_booked_or_already_printed = array();
		if (is_array($tvi->lines_contract) && count($tvi->lines_contract) > 0) {

			foreach ( $tvi->lines_contract as $keycont => $contratline ) {
				$date_cnt_start = empty($contratline->date_ouverture) ? $contratline->date_ouverture_prevue : $contratline->date_ouverture;
				$date_cnt_end = empty($contratline->date_cloture) ? $contratline->date_fin_validite : $contratline->date_cloture;

				// Normalized time with 00:00:00 as time
				$date_cnt_start = dol_mktime(0, 0, 0, dol_print_date($date_cnt_start, '%m'), dol_print_date($date_cnt_start, '%d'), dol_print_date($date_cnt_start, '%Y'));
				$date_cnt_end = dol_mktime(0, 0, 0, dol_print_date($date_cnt_end, '%m'), dol_print_date($date_cnt_end, '%d'), dol_print_date($date_cnt_end, '%Y'));
				// Do not display revert displayed
				if ($date_cnt_end < $date_cnt_start) {
					continue;
				}

				$date_start_draw = max($date_cnt_start, $firstdaytoshow);
				$date_end_draw = min($date_cnt_end, $lastdaytoshow);
				/*print '$firstdaytoshow='.dol_print_date($firstdaytoshow).'<br>';
				print '$lastdaytoshow='.dol_print_date($lastdaytoshow).'<br>';
				print '$date_cnt_start='.dol_print_date($date_cnt_start).'<br>';
				print '$date_cnt_end='.dol_print_date($date_cnt_end).'<br>';*/
				if ($date_cnt_start <= $firstdaytoshow || $date_cnt_end <= $lastdaytoshow) {
					$colspan = num_between_day($date_start_draw, $date_end_draw);
				}
				$desc = dol_trunc($contratline->socname,15) . ': ';
				$desc .= dol_print_date($date_cnt_start, 'day') . ' Au ' . dol_print_date($date_cnt_end, 'day');
				$dtst = dol_mktime(0, 0, 0, dol_print_date($date_start_draw, '%m'), dol_print_date($date_start_draw, '%d'), dol_print_date($date_start_draw, '%Y'));
				$dtend = dol_mktime(0, 0, 0, dol_print_date($date_end_draw, '%m'), dol_print_date($date_end_draw, '%d'), dol_print_date($date_end_draw, '%Y'));
				$events[] = array(
						'datestart' => $dtst,
						'dateend' => $dtend,
						'realdatestart' => dol_print_date($date_start_draw, 'standard') . '-' . dol_print_date($date_end_draw, 'standard'),
						'colspan' => $colspan + 1,
						'desc' => $desc,
						'contract_id' => $contratline->fk_contrat,
						'thirdparty' => $contratline->socname,
						'status' => $contratline->statut
				);
				for($dt = $dtst; $dt <= $dtend; $dt = dol_time_plus_duree($dt, 1, 'd')) {
					$date_booked_or_already_printed[] = $dt;
				}
			}
		}
		//var_dump($events);
		foreach ( $array_day_to_show as $key => $dateshow ) {
			if (count($events) > 0) {
				foreach ( $events as $event ) {
					if ($event['datestart'] == $dateshow['timestamp']) {
						echo '<td class="cal_current_month" colspan="' . $event['colspan'] . '">';

						echo '<table class="nobordernopadding" width="100%">';
						echo '<tbody>';
						echo '<tr>';
						echo '<td style="background: #' . $color_contractline_status[$event['status']] . ';" class="onclickopenref cursorpointer cal_autoshrimp" ';
						echo '  title="' . $event['desc'] . '" ref="' . $event['contract_id'] . '">' . (($event['colspan'] > 15) ? $event['desc'] : '&nbsp;') . '</td>';
						echo '</tr>';
						echo '</tbody>';
						echo '</table>';

						echo "</td>\n";
					} elseif (! in_array($dateshow['timestamp'], $date_booked_or_already_printed)) {
						$date_booked_or_already_printed[] = $dateshow['timestamp'];
						echo '<td class="' . $dateshow['class'] . ' cal_' . ($var ? 'impair' : '') . ' cal_current_month_' . ($var ? 'impair' : '') . '">';
						echo "</td>\n";
					}
				}
			} elseif (! in_array($dateshow['timestamp'], $date_booked_or_already_printed)) {
				$date_booked_or_already_printed[] = $dateshow['timestamp'];
				echo '<td class="' . $dateshow['class'] . ' cal_' . ($var ? 'impair' : '') . ' cal_current_month_' . ($var ? 'impair' : '') . '">';
				echo '&nbsp;';
				echo "</td>\n";
			}
		}
		echo "</tr>";

		// Find events
		$array_filter = array();
		$array_filter['a.fk_project'] = array(
				$veh->id
		);
		$array_filter['a.datep[]'] = array('from'=>	$firstdaytoshow,'to'=>$lastdaytoshow);

		$array_filter['!c.type'] = "systemauto";
		if (! empty($socid)) {
			$array_filter['a.fk_soc'] = $socid;
		}

		$result = $tvi->fetchEventsLines('', '', 0, 0, $array_filter);
		if ($result < 0) {
			setEventMessages(null, $tvi->errors, 'errors');
		}
		// Find if event is in range displayed
		$events = array();
		$date_booked_or_already_printed = array();
		$date_booked_only = array();
		$date_exists = array();
		if (is_array($tvi->lines_events) && count($tvi->lines_events) > 0) {

			foreach ( $tvi->lines_events as $keyevt => $eventline ) {
				$date_evt_start = $eventline->datep;
				$date_evt_end = empty($eventline->datef) ? $eventline->datep : $eventline->datef;

				// Normalized time with 00:00:00 as time
				$date_evt_start = dol_mktime(0, 0, 0, dol_print_date($date_evt_start, '%m'), dol_print_date($date_evt_start, '%d'), dol_print_date($date_evt_start, '%Y'));
				$date_evt_end = dol_mktime(0, 0, 0, dol_print_date($date_evt_end, '%m'), dol_print_date($date_evt_end, '%d'), dol_print_date($date_evt_end, '%Y'));
				// Do not display revert displayed
				if ($date_evt_end < $date_evt_start) {
					continue;
				}

				$date_start_draw = max($date_evt_start, $firstdaytoshow);
				$date_endt_draw = min($date_evt_end, $lastdaytoshow);

				if ($date_evt_start <= $firstdaytoshow || $date_evt_end <= $lastdaytoshow) {
					$colspan = num_between_day($date_start_draw, $date_endt_draw);
				}
				$desc = $eventline->type . ' ' . $eventline->label . ': ';
				$desc .= dol_print_date($date_evt_start, 'daytext');
				if(!empty($eventline->datef) && $date_evt_end !=$date_evt_start ){
					$desc.= '-' . dol_print_date($date_evt_end, 'daytext');
				}
				$desc.=  "\n";
				$dtst = dol_mktime(0, 0, 0, dol_print_date($date_start_draw, '%m'), dol_print_date($date_start_draw, '%d'), dol_print_date($date_start_draw, '%Y'));
				$dtend = dol_mktime(0, 0, 0, dol_print_date($date_endt_draw, '%m'), dol_print_date($date_endt_draw, '%d'), dol_print_date($date_endt_draw, '%Y'));
				if($eventline->percentage<100 && $eventline->percentage>-1 && $date_evt_start<dol_now()){
					$eventline->percentage = -100;
				}elseif ($eventline->percentage==-1){
					$eventline->percentage = 101;
				}

				$events[] = array(
						'datestart' => $dtst,
						'realdatestart' => $date_evt_start,
						'dateend' => $dtend,
						'colspan' => $colspan + 1,
						'desc' => $desc,
						'event_id' => $eventline->id,
						'thirdparty' => $eventline->thirdparty->name,
						'projectid' => $veh->id,
						'percentage'=> $eventline->percentage
				);
				for($dt = $dtst; $dt <= $dtend; $dt = dol_time_plus_duree($dt, 1, 'd')) {
					$date_booked_or_already_printed[] = $dt;
					$date_exists[$dt] = (array_key_exists($dt, $date_exists) ? $date_exists[$dt] + 1 : 1);
				}


			}
		}
		$mincolor=array();
		$descarray=array();
		foreach ( $array_day_to_show as $key => $dateshow ) {
			$mincolor[$dateshow['timestamp']] = 101;
			foreach($events as $selected){
				if($selected['realdatestart'] == $dateshow['timestamp'] && $selected['percentage']< $mincolor[$dateshow['timestamp']]) {
					$mincolor[$dateshow['timestamp']]= $selected['percentage'];
				}
				if($selected['realdatestart'] == $dateshow['timestamp']) {
					$descarray[$dateshow['timestamp']].= $selected['desc'];
				}
			}
		}

		echo "<tr>";
		foreach ( $array_day_to_show as $key => $dateshow ) {
			if (count($events) > 0) {
				foreach ( $events as $event ) {
					if ($event['datestart'] == $dateshow['timestamp'] && ! in_array($dateshow['timestamp'], $date_booked_only)) {
						echo '<td class="cal_current_month" colspan="' . $event['colspan'] . '">';

						echo '<table class="nobordernopadding" width="100%">';
						echo '<tbody>';
						echo '<tr>';
						echo '<td style="background: #' . $color_event_status[$mincolor[$dateshow['timestamp']]] . ';" class="onclickopenrefevent cursorpointer cal_autoshrimp" ';
						echo '  title="' . $descarray[$dateshow['timestamp']] . '" ref="' . $event['projectid'] . '" ';
						echo '  ref_day="' . dol_print_date($event['realdatestart'], '%d') . '" ';
						echo '  ref_month="' . dol_print_date($event['realdatestart'], '%m') . '" ';
						echo '  ref_year="' . dol_print_date($event['realdatestart'], '%Y') . '" ';
						echo '  align="center">' . $date_exists[$dtst] . '</td>';
						echo '</tr>';
						echo '</tbody>';
						echo '</table>';

						echo "</td>\n";
						$date_booked_only[] = $dateshow['timestamp'];
					} elseif (! in_array($dateshow['timestamp'], $date_booked_or_already_printed)) {
						$date_booked_or_already_printed[] = $dateshow['timestamp'];
						echo '<td class="' . $dateshow['class'] . ' cal_' . ($var ? 'impair' : '') . ' cal_current_month_' . ($var ? 'impair' : '') . '">';
						echo "</td>\n";
					}
				}
			} elseif (! in_array($dateshow['timestamp'], $date_booked_or_already_printed)) {
				$date_booked_or_already_printed[] = $dateshow['timestamp'];
				echo '<td class="' . $dateshow['class'] . ' cal_' . ($var ? 'impair' : '') . ' cal_current_month_' . ($var ? 'impair' : '') . '">';
				echo '&nbsp;';
				echo "</td>\n";
			}
		}
		echo "</tr>";
	}
}

echo "</table>\n";

// Add js code to manage click on a box
print
		'<script type="text/javascript" language="javascript">
$(document).ready(function() {
	$(".onclickopenref").click(function() {
		var ref=$(this).attr(\'ref\');
		url = "' . DOL_URL_ROOT . '/contrat/card.php?id="+ref
		window.location.href = url;
	});
	$(".onclickopenrefevent").click(function() {
		var ref=$(this).attr(\'ref\');
		var ref_day=$(this).attr(\'ref_day\');
		var ref_month=$(this).attr(\'ref_month\');
		var ref_year=$(this).attr(\'ref_year\');
		url = "' . DOL_URL_ROOT . '/tvi/tvi/listactions.php?projectid="+ref+"&datestartday="+ref_day+"&datestartmonth="+ref_month+"&datestartyear="+ref_year
		window.location.href = url;
	});
});
</script>';

llxFooter();

$db->close();
