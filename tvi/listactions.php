<?php
/* Copyright (C) 2001-2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2003      Eric Seigne          <erics@rycks.com>
 * Copyright (C) 2004-2016 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@capnetworks.com>
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
 *	    \file       htdocs/comm/action/listactions.php
 *      \ingroup    agenda
 *		\brief      Page to list actions
 */

require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/comm/action/class/actioncomm.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/tvi/lib/tvi.lib.php';
require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';

$langs->load("agenda");
$langs->load("commercial");

$action=GETPOST('action','alpha');
$resourceid=GETPOST("resourceid","int");
$pid=GETPOST("projectid",'int',3);
$status=GETPOST("status",'alpha');
$actioncode=GETPOST('actioncode');
$actionid = GETPOST('actionid','int');

/*
 *	Actions
 */

// valide et replannifie l'événement

if($action == 'validaction_confirm' && !empty($actionid)){
	$date_valid = dol_mktime(0, 0, 0, GETPOST('date_validmonth','int'), GETPOST('date_validday','int'), GETPOST('date_validyear','int'));
	$event = new ActionComm($db);
	$event->fetch($actionid);
	$projet = new Project($db);
	$projet->fetch($event->fk_project);
	require_once(DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php');
	$extrafields=new ExtraFields($db);
	$extralabels=$extrafields->fetch_name_optionals_label($projet->table_element,true);
	$projet->fetch_optionals($projet->id,$extralabels);
	$newdate = get_next_date($event->type_id,$projet->array_options['options_type'],$date_valid);
	$event->createFromClone($fuser, 0);
	$event->datep = $newdate;
	$event->datef = '';
	$event->percentage = 0;
	$event->update($user,1);
 	$event->fetch($actionid);
 	$event->datef = $event->datep;
 	$event->percentage = 100;
 	$event->update($user,1);
}



// Set actioncode (this code must be same for setting actioncode into peruser, listacton and index)
if (GETPOST('actioncode','array'))
{
    $actioncode=GETPOST('actioncode','array',3);
    if (! count($actioncode)) $actioncode='0';
}
else
{
    $actioncode=GETPOST("actioncode","alpha",3)?GETPOST("actioncode","alpha",3):(GETPOST("actioncode")=='0'?'0':(empty($conf->global->AGENDA_DEFAULT_FILTER_TYPE)?'':$conf->global->AGENDA_DEFAULT_FILTER_TYPE));
}
if ($actioncode == '' && empty($actioncodearray)) $actioncode=(empty($conf->global->AGENDA_DEFAULT_FILTER_TYPE)?'':$conf->global->AGENDA_DEFAULT_FILTER_TYPE);

$datestart=dol_mktime(0, 0, 0, GETPOST('datestartmonth'), GETPOST('datestartday'), GETPOST('datestartyear'));
$dateend=dol_mktime(0, 0, 0, GETPOST('dateendmonth'), GETPOST('dateendday'), GETPOST('dateendyear'));
if ($status == ''   && ! isset($_GET['status']) && ! isset($_POST['status'])) $status=(empty($conf->global->AGENDA_DEFAULT_FILTER_STATUS)?'':$conf->global->AGENDA_DEFAULT_FILTER_STATUS);
if (empty($action) && ! isset($_GET['action']) && ! isset($_POST['action'])) $action=(empty($conf->global->AGENDA_DEFAULT_VIEW)?'show_month':$conf->global->AGENDA_DEFAULT_VIEW);

$filter=GETPOST("filter",'',3);
$filtert = GETPOST("usertodo","int",3)?GETPOST("usertodo","int",3):GETPOST("filtert","int",3);

// If not choice done on calendar owner, we filter on user.
if (empty($filtert) && empty($conf->global->AGENDA_ALL_CALENDARS))
{
	$filtert=$user->id;
}

$limit = GETPOST("limit")?GETPOST("limit","int"):$conf->liste_limit;
$sortfield = GETPOST("sortfield",'alpha');
$sortorder = GETPOST("sortorder",'alpha');
$page = GETPOST("page",'int');
if ($page == -1) { $page = 0 ; }
$offset = $limit * $page ;
if (! $sortorder)
{
	$sortorder="ASC";
	if ($status == 'todo') $sortorder="DESC";
}
if (! $sortfield)
{
	$sortfield="a.datep";
	if ($status == 'todo') $sortfield="a.datep";
}

// Security check
$socid = GETPOST("socid",'int');
if ($user->societe_id) $socid=$user->societe_id;
$result = restrictedArea($user, 'agenda', 0, '', 'myactions');
if ($socid < 0) $socid='';

$canedit=1;
if (! $user->rights->agenda->myactions->read) accessforbidden();
if (! $user->rights->agenda->allactions->read) $canedit=0;
if (! $user->rights->agenda->allactions->read || $filter=='mine')	// If no permission to see all, we show only affected to me
{
	$filtert=$user->id;
}

// Purge search criteria


/*
 *  View
 */

$form=new Form($db);
$userstatic=new User($db);

$now=dol_now();

llxHeader('','Controles','');






// Define list of all external calendars
$listofextcals=array();

$param='';
if (! empty($contextpage) && $contextpage != $_SERVER["PHP_SELF"]) $param.='&contextpage='.$contextpage;
if ($limit > 0 && $limit != $conf->liste_limit) $param.='&limit='.$limit;
if ($status || isset($_GET['status']) || isset($_POST['status'])) $param.="&status=".$status;
if ($filter) $param.="&filter=".$filter;
if ($filtert) $param.="&filtert=".$filtert;
if ($pid) $param.="&projectid=".$pid;
if ($actioncode) $param.="&actioncode=".$actioncode;
if ($datestart) $param.="&datestartmonth=".dol_print_date($datestart,'%m') . "&datestartday=".dol_print_date($datestart,'%d')."&datestartyear=".dol_print_date($datestart,'%Y');
if ($dateend) $param.="&dateendmonth=".dol_print_date($dateend,'%m')."&dateendday=".dol_print_date($dateend,'%d')."&dateendyear=".dol_print_date($dateend,'%Y');

if($action == "validaction" && !empty($actionid)){

	$formquestion = array(
		'type' => 'date',
		'name' => 'date_valid',
		'label'=> 'date');

	$url_ret = $_SERVER["PHP_SELF"] . '?actionid=' . $actionid . $param;

	$formconfirm = $form->formconfirm($url_ret, 'Date de validité du controle', 'Veuillez saisir la de début de validité de la nouvelle période', 'validaction_confirm', array($formquestion), '', 1);

	print $formconfirm;
}

$sql = "SELECT";
$sql.= " a.id, a.label, a.datep as dp, a.datep2 as dp2,";
$sql.= " a.percent as percent,";
$sql.= " c.code as type_code, c.libelle as type_label ";
$sql.= " FROM ".MAIN_DB_PREFIX."c_actioncomm as c INNER JOIN ".MAIN_DB_PREFIX."actioncomm as a ON c.id = a.fk_action";
$sql.= " WHERE c.type NOT LIKE '%system%' ";
if ($actioncode) $sql.=" AND c.id IN ('".$db->escape($actioncode)."')";
if ($pid) $sql.=" AND a.fk_project=".$db->escape($pid);
// We must filter on assignement table
if ($type) $sql.= " AND c.id = ".$type;
if ($status == '0') { $sql.= " AND a.percent = 0"; }
if ($status == '-1') { $sql.= " AND a.percent = -1"; }	// Not applicable
if ($status == '50') { $sql.= " AND (a.percent > 0 AND a.percent < 100)"; }	// Running already started
if ($status == 'done' || $status == '100') { $sql.= " AND (a.percent = 100 OR (a.percent = -1 AND a.datep2 <= '".$db->idate($now)."'))"; }
if ($status == 'todo') { $sql.= " AND ((a.percent >= 0 AND a.percent < 100) OR (a.percent = -1 AND a.datep2 > '".$db->idate($now)."'))"; }

// The second or of next test is to take event with no end date (we suppose duration is 1 hour in such case)
if ($datestart > 0 && $dateend > 0) $sql.= " AND a.datep BETWEEN '".$db->idate($datestart)."' AND '".$db->idate($dateend)."'";
$sql.= $db->order($sortfield,$sortorder);

$nbtotalofrecords = 0;
if (empty($conf->global->MAIN_DISABLE_FULL_SCANLIST))
{
    $result = $db->query($sql);
    $nbtotalofrecords = $db->num_rows($result);
}

$sql.= $db->plimit($limit + 1, $offset);
//print $sql;

dol_syslog("tvi/tvi/listactions.php", LOG_DEBUG);
$resql=$db->query($sql);
if ($resql)
{
	$actionstatic=new ActionComm($db);

	$num = $db->num_rows($resql);

	$title=$langs->trans("ListOfEvents");

	$newtitle=$langs->trans($title);

	print_fiche_titre('Liste des controles périodiques');

	print_actions_filter_list($form,$canedit,$status,$pid,$actioncode,$datestart,$dateend);

    $s=$newtitle;

	// Calendars from hooks
    $parameters=array(); $object=null;

    print '<form method="POST" id="searchFormList" action="'.$_SERVER["PHP_SELF"].'">'."\n";

    print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
    print '<input type="hidden" name="action" value="list">';
    print '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';
    print '<input type="hidden" name="sortfield" value="'.$sortfield.'">';
    print '<input type="hidden" name="sortorder" value="'.$sortorder.'">';
    print '<input type="hidden" name="type" value="'.$type.'">';
    $nav='';

    if ($actioncode) $nav.='<input type="hidden" name="actioncode" value="'.$actioncode.'">';
    if ($resourceid) $nav.='<input type="hidden" name="resourceid" value="'.$resourceid.'">';
    if ($status || isset($_GET['status']) || isset($_POST['status']))  $nav.='<input type="hidden" name="status" value="'.$status.'">';
    if ($filter)  $nav.='<input type="hidden" name="filter" value="'.$filter.'">';
    if ($filtert) $nav.='<input type="hidden" name="filtert" value="'.$filtert.'">';
    if ($socid)   $nav.='<input type="hidden" name="socid" value="'.$socid.'">';
    if ($showbirthday)  $nav.='<input type="hidden" name="showbirthday" value="1">';
    if ($pid)    $nav.='<input type="hidden" name="projectid" value="'.$pid.'">';
    if ($usergroup) $nav.='<input type="hidden" name="usergroup" value="'.$usergroup.'">';
    print $nav;

    print_barre_liste('', $page, $_SERVER["PHP_SELF"], $param, $sortfield, $sortorder, $link, $num, -1 * $nbtotalofrecords, '', 0, $nav, '', $limit);

    $i = 0;
	print '<table class="liste" width="100%">';
	print '<tr class="liste_titre">';
	if (! empty($conf->global->AGENDA_USE_EVENT_TYPE)) print_liste_field_titre("Controle",$_SERVER["PHP_SELF"],"c.libelle",$param,"","",$sortfield,$sortorder);
	print_liste_field_titre("Véhicule",$_SERVER["PHP_SELF"],"a.label",$param,"","",$sortfield,$sortorder);
	print_liste_field_titre($langs->trans("DateStart"),$_SERVER["PHP_SELF"],"a.datep",$param,'','align="center"',$sortfield,$sortorder);
	print_liste_field_titre($langs->trans("DateEnd"),$_SERVER["PHP_SELF"],"a.datep2",$param,'','align="center"',$sortfield,$sortorder);
	print_liste_field_titre($langs->trans("Status"),$_SERVER["PHP_SELF"],"a.percent",$param,"",'align="center"',$sortfield,$sortorder);
	print_liste_field_titre("");
	print "</tr>\n";

	$now=dol_now();
	$delay_warning=$conf->global->MAIN_DELAY_ACTIONS_TODO*24*60*60;

	require_once DOL_DOCUMENT_ROOT.'/comm/action/class/cactioncomm.class.php';
	$caction=new CActionComm($db);
	$arraylist=$caction->liste_array(1, 'code', '', (empty($conf->global->AGENDA_USE_EVENT_TYPE)?1:0));

	$var=true;
	while ($i < min($num,$limit))
	{
		$obj = $db->fetch_object($resql);

        // Discard auto action if option is on
        if (! empty($conf->global->AGENDA_ALWAYS_HIDE_AUTO) && $obj->type_code == 'AC_OTH_AUTO')
        {
        	$i++;
        	continue;
        }

		$var=!$var;

		print "<tr ".$bc[$var].">";

		// Action (type)
		if (! empty($conf->global->AGENDA_USE_EVENT_TYPE))
		{
			$labeltype=$obj->type_code;
			if (! empty($arraylist[$labeltype])) $labeltype=$arraylist[$labeltype];
			print '<td>'.dol_trunc($labeltype,28).'</td>';
		}


		print '<td>';
		$actionstatic->id=$obj->id;
		$actionstatic->type_code=$obj->type_code;
		$actionstatic->type_label=$obj->type_label;
		$actionstatic->label=$obj->label;
		print $actionstatic->getNomUrl(1,36);
		print '</td>';

		// Start date
		print '<td align="center" class="nowrap">';
		print dol_print_date($db->jdate($obj->dp),"day");
		$late=0;
		if ($obj->percent == 0 && $obj->dp && $db->jdate($obj->dp) < ($now - $delay_warning)) $late=1;
		if ($obj->percent == 0 && ! $obj->dp && $obj->dp2 && $db->jdate($obj->dp) < ($now - $delay_warning)) $late=1;
		if ($obj->percent > 0 && $obj->percent < 100 && $obj->dp2 && $db->jdate($obj->dp2) < ($now - $delay_warning)) $late=1;
		if ($obj->percent > 0 && $obj->percent < 100 && ! $obj->dp2 && $obj->dp && $db->jdate($obj->dp) < ($now - $delay_warning)) $late=1;
		if ($late) print img_warning($langs->trans("Late")).' ';
		print '</td>';

		// End dateection
		print '<td align="center" class="nowrap">';
		print dol_print_date($db->jdate($obj->dp2),"day");
		print '</td>';


		// Status/Percent
		print '<td align="center" class="nowrap">'.$actionstatic->LibStatut($obj->percent,2).'</td>';

		print '<td>';
		if($obj->percent==0) {print print_button_donne($obj->id, $param);}
		print '</td>';

		print "</tr>\n";
		$i++;
	}
	print "</table>";

	print '</form>';

	$db->free($resql);

}
else
{
	dol_print_error($db);
}

dol_fiche_end();

llxFooter();

$db->close();
