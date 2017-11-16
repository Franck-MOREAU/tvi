<?php
/* Copyright (C) 2001-2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2015 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2010 Regis Houssin        <regis.houssin@capnetworks.com>
 * Copyright (C) 2012-2016 Juanjo Menent        <jmenent@2byte.es>
 * Copyright (C) 2015      Alexandre Spangaro	<aspangaro.dolibarr@gmail.com>
 * Copyright (C) 2015      Marcos García        <marcosgdf@gmail.com>
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
 *      \file       htdocs/projet/element.php
 *      \ingroup    projet facture
 *		\brief      Page of project referrers
 */

require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/project.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/tvi/class/echeanceloan.class.php';
require_once DOL_DOCUMENT_ROOT.'/tvi/class/tvi.class.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
if (! empty($conf->facture->enabled))     require_once DOL_DOCUMENT_ROOT.'/compta/facture/class/facture.class.php';
if (! empty($conf->fournisseur->enabled)) require_once DOL_DOCUMENT_ROOT.'/fourn/class/fournisseur.facture.class.php';
if (! empty($conf->contrat->enabled))     require_once DOL_DOCUMENT_ROOT.'/contrat/class/contrat.class.php';
if (! empty($conf->loan->enabled))     	  require_once DOL_DOCUMENT_ROOT.'/loan/class/loan.class.php';
if (! empty($conf->loan->enabled))     	  require_once DOL_DOCUMENT_ROOT.'/loan/class/paymentloan.class.php';


$langs->load("projects");
$langs->load("companies");
$langs->load("suppliers");
if (! empty($conf->facture->enabled))  	    $langs->load("bills");

$id=GETPOST('id','int');
$action=GETPOST('action','alpha');
$datesrfc=GETPOST('datesrfc');
$dateerfc=GETPOST('dateerfc');
$dates=dol_mktime(0, 0, 0, GETPOST('datesmonth'), GETPOST('datesday'), GETPOST('datesyear'));
$datee=dol_mktime(23, 59, 59, GETPOST('dateemonth'), GETPOST('dateeday'), GETPOST('dateeyear'));
if (empty($dates) && ! empty($datesrfc)) $dates=dol_stringtotime($datesrfc);
if (empty($datee) && ! empty($dateerfc)) $datee=dol_stringtotime($dateerfc);
if (! isset($_POST['datesrfc']) && ! isset($_POST['datesday']) && ! empty($conf->global->PROJECT_LINKED_ELEMENT_DEFAULT_FILTER_YEAR))
{
	$new=dol_now();
	$tmp=dol_getdate($new);
	$dates=dol_get_first_day($tmp['year'],1);
}
if ($id == '' && $projectid == '' && $ref == '')
{
	dol_print_error('','Bad parameter');
	exit;
}

$mine = $_REQUEST['mode']=='mine' ? 1 : 0;
$object = new Project($db);
$object->fetch($id);

$tvi = New Tvi($db);

require_once(DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php');
$extrafields=new ExtraFields($db);
$extralabels=$extrafields->fetch_name_optionals_label($object->table_element,true);
$object->fetch_optionals($object->id,$extralabels);

// Security check
if (empty($user->rights->tvi->renta_voir)) accessforbidden();

/*
 *	View
 */

$title=$langs->trans("ProjectReferers").' - '.$object->ref.' '.$object->name;
if (! empty($conf->global->MAIN_HTML_TITLE) && preg_match('/projectnameonly/',$conf->global->MAIN_HTML_TITLE) && $object->name) $title=$object->ref.' '.$object->name.' - '.$langs->trans("ProjectReferers");
$help_url="EN:Module_Projects|FR:Module_Projets|ES:M&oacute;dulo_Proyectos";
llxHeader("",$langs->trans("Referers"),$help_url);

$form = new Form($db);
$formproject=new FormProjets($db);
$formfile = new FormFile($db);

$userstatic=new User($db);

// To verify role of users
$userAccess = $object->restrictedProjectArea($user);

$head=project_prepare_head($object);
dol_fiche_head($head, 'rentabilite', $langs->trans("Project"),0,($object->public?'projectpub':'project'));

print '<table class="border" width="100%">';

$linkback = '<a href="'.DOL_URL_ROOT.'/projet/list.php">'.$langs->trans("BackToList").'</a>';

print '<tr><td>'.$langs->trans("Ref").'</td><td colspan="7">';
// Define a complementary filter for search of next/prev ref.
if (! $user->rights->projet->all->lire)
{
    $projectsListId = $object->getProjectsAuthorizedForUser($user,0,0);
    $object->next_prev_filter=" rowid in (".(count($projectsListId)?join(',',array_keys($projectsListId)):'0').")";
}
print $form->showrefnav($object, 'ref', $linkback, 1, 'ref', 'ref');
print '</td></tr>';

print '<tr><td>'.$langs->trans("Label").'</td><td colspan="7">'.$object->title.'</td></tr>';

// Statut
print '<tr><td>'.$langs->trans("Status").'</td><td colspan="3">'.$object->getLibStatut(4).'</td>';
print '<td>'.$langs->trans("OpportunityStatus").'</td><td colspan="3">';
$code = dol_getIdFromCode($db, $object->opp_status, 'c_lead_status', 'rowid', 'code');
if ($code) print $langs->trans("OppStatus".$code);
print '</td></tr>';
Print '<tr>';
Print '<td width="15%">'.$extralabels['immat'] . '</td><td width="10%">'.$extrafields->showOutputField('immat', $object->array_options['options_immat']).'</td>';
Print '<td width="10%">'.$extralabels['carrosserie'] . '</td><td width="15%">'.$extrafields->showOutputField('carrosserie', $object->array_options['options_carrosserie']).'</td>';
Print '<td width="15%">'.$extralabels['silouhette'] . '</td><td width="10%">'.$extrafields->showOutputField('silouhette', $object->array_options['options_silouhette']).'</td>';
Print '<td width="12%">'.$extralabels['site'] . '</td><td width="13%">'.$extrafields->showOutputField('site', $object->array_options['options_site']).'</td>';
print '</tr>';
print '</table>';
print '<br>';

print '<table class="border" width="100%">';
print '<tr class="liste_titre">';
Print '<th width = "40%" align="center">Catégorie</th>';
Print '<th width = "20%" align="center">Dépense</th>';
Print '<th width = "20%" align="center">Recette</th>';
Print '<th width = "20%" align="center">Solde</th>';
print '</tr>';

$soldetotal = 0;

$articles = array();
$articles = $tvi->fetchallloan($object->id);
foreach ($articles as $article){
	$loan = New Loan($db);
	$loan->fetch($article);
	$immo = $tvi->gettotalloan($object->id, $loan->id);
	$solde-=$immo;
	if(empty($immo)){
		$immo = "-";
		$solde = "-";
	}else{
		$immo = price($immo) . ' €';
		$solde = "- " . $immo;
	}

	print '<tr>';
	print '<td align="left">' . $loan->getLibStatut(3) . ' ' . $loan->getLinkUrl() .' - '.$loan->label .  '</td>';
	print '<td align="center">' . $immo . '</td>';
	print '<td align="center"> - </td>';
	print '<td align="center">' . $solde. '</td>';
	print '</tr>';
}

$articles = array();
$articles = $tvi->fetchallproduct();
foreach ($articles as $article){
	$product = New Product($db);
	$product->fetch($article);
	$achats = $tvi->gettotalachat($object->id, $product->id);
	$ventes = $tvi->gettotalvente($object->id, $product->id);
	$solde = $ventes - $achats;
	$soldetotal+= $solde;

	if(!empty($achats)||!empty($ventes)){

		if($solde > 0){
			$solde = '+ ' . price($solde) . ' €';
		}elseif($solde < 0){
			$solde = '- ' . price(abs($solde)) . ' €';
		}else{
			$solde = '-';
		}
		if(!empty($achats)){
			$achats = price($achats) . ' €';
		}Else{
			$achats = "-";
		}
		if(!empty($ventes)){
			$ventes = price($ventes) . ' €';
		}Else{
			$ventes = "-";
		}


		print '<tr>';
		print '<td align="left">'. $product->getNomUrl(1) .' - '.$product->label .  '</td>';
		print '<td align="center">' . $achats .'</td>';
		print '<td align="center">' . $ventes . '</td>';
		print '<td align="center">' . $solde . '</td>';
		print '</tr>';
	}
}

if($soldetotal > 0){
	$soldetotal = '+ ' . price($soldetotal) . ' €';
}elseif($soldetotal < 0){
	$soldetotal = '- ' . price(abs($soldetotal)) . ' €';
}else{
	$soldetotal = '-';
}


print '<tr class="liste_titre">';
Print '<th align="center"></th>';
Print '<th align="center"></th>';
Print '<th align="center" Colspan="2">Rentabilité = '. $soldetotal . '</th>';
print '</tr>';
print '</table>';
dol_fiche_end();



llxFooter();

$db->close();
