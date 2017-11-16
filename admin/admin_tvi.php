<?php
/*
 * Copyright (C) 2014-2016 Florian HENRY <florian.henry@atm-consulting.fr>
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
 * \file admin/lead.php
 * \ingroup lead
 * \brief This file is an example module setup page
 * Put some comments here
 */
// Dolibarr environment
$res = @include '../../main.inc.php'; // From htdocs directory
if (! $res) {
	$res = @include '../../../main.inc.php'; // From "custom" directory
}

// Libraries
dol_include_once( '/core/lib/admin.lib.php');
dol_include_once('/core/class/html.form.class.php');
dol_include_once('/tvi/class/tvi.class.php');

// Translations
$langs->load("admin");

// Access control
if (! $user->admin) {
	accessforbidden();
}

$form = new Form($db);
$tvi = new Tvi($db);

// Parameters
$action = GETPOST('action', 'alpha');
$periodeid = GETPOST('periodeid','int');

/*
 * Actions
 */

if ($action == 'setvar') {

	$nb_day = GETPOST('TVI_INVOICE_AUTO_STATUS', 'int');
	if (! empty($nb_day)) {
		$res = dolibarr_set_const($db, 'TVI_INVOICE_AUTO_STATUS', $nb_day, 'chaine', 0, '', $conf->entity);
	}
	if (! $res > 0) {
		$error ++;
	}


}elseif($action=='adnewperiode'&&empty($periodeid)){
	$fk_genre=GETPOST('fk_genre','int');
	$fk_typeevent=GETPOST('fk_typeevent','int');
	$franchise=GETPOST('franchise','int');
	$periode=GETPOST('periode','int');

	if(!empty($fk_genre)&&!empty($fk_typeevent)&&!empty($franchise)&&!empty($periode)){
		$tvi->addnewperiode($fk_genre, $fk_typeevent, $franchise, $periode);
	}
	header("Location: ".DOL_URL_ROOT.'/tvi/admin/admin_tvi.php');
	exit;
}elseif ($action=='editline'&&!empty($periodeid)){
	$obj=$tvi->fetchperiode($periodeid);
	$genre = $obj->fk_genre;
	$event = $obj->fk_typeevent;
	$franch = $obj->franchise;
	$per = $obj->periode;
}elseif($action=='adnewperiode'&&!empty($periodeid)){
	$fk_genre=GETPOST('fk_genre','int');
	$fk_typeevent=GETPOST('fk_typeevent','int');
	$franchise=GETPOST('franchise','int');
	$periode=GETPOST('periode','int');

	if(!empty($fk_genre)&&!empty($fk_typeevent)&&!empty($franchise)&&!empty($periode)){
		$tvi->updateperiode($periodeid,$fk_genre, $fk_typeevent, $franchise, $periode);
	}
	header("Location: ".DOL_URL_ROOT.'/tvi/admin/admin_tvi.php');
	exit;
}elseif($action=='deleteline'&&!empty($periodeid)){
	$tvi->deleteperiode($periodeid);
	header("Location: ".DOL_URL_ROOT.'/tvi/admin/admin_tvi.php');
	exit;
}

/*
 * View
 */
$page_name = "Tvi Setup";
llxHeader('', $langs->trans($page_name));

// Subheader
$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php">' . $langs->trans("BackToModuleList") . '</a>';

// Admin var of module
print_fiche_titre('Gestion des constantes du module',$linkback);

print '<form method="post" action="' . $_SERVER['PHP_SELF'] . '" >';
print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
print '<input type="hidden" name="action" value="setvar">';

print '<table class="noborder" width="100%">';

print '<tr class="liste_titre">';
print '<td>Nom</td>';
print '<td width="400px">Valeur</td>';
print "</tr>\n";

// Statut Facture
print '<tr class="pair"><td>Status des factures auto générées</td>';
print '<td align="left">';
print '<SELECT name="TVI_INVOICE_AUTO_STATUS">';
print '<OPTION value="0" ' . ($conf->global->TVI_INVOICE_AUTO_STATUS==0?'selected':'') . '>Brouillons</OPTION>';
print '<OPTION value="1" ' . ($conf->global->TVI_INVOICE_AUTO_STATUS==1?'selected':'') . '>Validées</OPTION>';
print '</SELECT></td>';
print '</tr>';

print '</table>';

print '<input type="submit" class="button" value="' . $langs->trans("Save") . '">';
print '</form>';

print_fiche_titre('Paramétrage des entretiens périodiques');



print '<form method="post" action="' . $_SERVER['PHP_SELF'] . '" >';
print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
print '<input type="hidden" name="action" value="adnewperiode">';

print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
Print '<th colspan="5" align="center">Ajouter un nouvel entretien périodique</th></tr>';
print '<tr class="pair">';
print '<td align="center">';
$dico =array();
$dico = $tvi->loaddicogenre();
print $form->selectarray('fk_genre', $dico,$genre);
print '</td>';
print '<td align="center">';
$tvi->select_type_actions($event,'fk_typeevent','system',0,1);
print '</td>';
print '<td align="center"><input name="franchise" size="5" value="' . $franch . '"> Mois</td>';
print '<td align="center"><input name="periode" size="5" value="' . $per . '"> Mois</td>';
print '<td align="center"><input type="hidden" name="periodeid" value="' . $periodeid . '"><input type="submit" class="button" value="Ajouter"></td>';
print '</tr>';
print '<tr class="liste_titre">';
Print '<th align="center" width="23%">Type de Véhicule</th>';
Print '<th align="center" width="23%">Type d\'entretien</th>';
Print '<th align="center" width="23%">Période de franchise (mois)</th>';
Print '<th align="center" width="23%">périodicité de l\'entretien(mois)</th>';
Print '<th align="center"></th>';
print '</tr>';
$liste = $tvi->fetchallperiode();
foreach($liste as $obj){
 	print '<tr>';
 	print '<td>' . $obj->genre . '</td>';
 	print '<td align="center">' . $obj->libelle . '</td>';
 	print '<td align="center">' . $obj->franchise . ' Mois</td>';
 	print '<td align="center">' . $obj->periode . ' Mois</td>';
 	print '<td align="right">';
 	print '<a style="padding-left: 5px;" href="'. DOL_URL_ROOT .'/tvi/admin/admin_tvi.php?action=editline&periodeid='.$obj->rowid . '"><img src="' . DOL_URL_ROOT . '/theme/eldy/img/edit.png" border="0" alt="" title="Modifier"></a>';
 	print '<a style="padding-left: 5px;" href="'. DOL_URL_ROOT .'/tvi/admin/admin_tvi.php?action=deleteline&periodeid='.$obj->rowid . '"><img src="' . DOL_URL_ROOT . '/theme/eldy/img/delete.png" border="0" alt="" title="Modifier"></a>';
 	print '</td>';
 	print '</tr>';
}


print '</table>';
print '</form>';
dol_fiche_end();

llxFooter();

$db->close();
