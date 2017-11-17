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
	$var = GETPOST('TVI_INVOICE_AUTO_STATUS', 'int');
	if (! empty($var)) {
		$res = dolibarr_set_const($db, 'TVI_INVOICE_AUTO_STATUS', $var, 'chaine', 0, '', $conf->entity);
	}
	if (! $res > 0) {
		$error ++;
	}
	
	$var = GETPOST('TVI_CONTRACT_LOC_PRODUCT', 'int');
	if (! empty($var)) {
		$res = dolibarr_set_const($db, 'TVI_CONTRACT_LOC_PRODUCT', $var, 'chaine', 0, '', $conf->entity);
	}
	if (! $res > 0) {
		$error ++;
	}
	
	$var = GETPOST('TVI_CONTRACT_ASS_PRODUCT', 'int');
	if (! empty($var)) {
		$res = dolibarr_set_const($db, 'TVI_CONTRACT_ASS_PRODUCT',$var, 'chaine', 0, '', $conf->entity);
	}
	if (! $res > 0) {
		$error ++;
	}
	
	$var = GETPOST('TVI_CONTRACT_ASSDOM_PRODUCT', 'int');
	if (! empty($var)) {
		$res = dolibarr_set_const($db, 'TVI_CONTRACT_ASSDOM_PRODUCT', $var, 'chaine', 0, '', $conf->entity);
	}
	if (! $res > 0) {
		$error ++;
	}
	
	$var = GETPOST('TVI_CONTRACT_KM_PRODUCT', 'int');
	if (! empty($var)) {
		$res = dolibarr_set_const($db, 'TVI_CONTRACT_KM_PRODUCT', $var, 'chaine', 0, '', $conf->entity);
	}
	if (! $res > 0) {
		$error ++;
	}
	
	$var = GETPOST('TVI_CONTRACT_TAXES_PRODUCT', 'int');
	if (! empty($var)) {
		$res = dolibarr_set_const($db, 'TVI_CONTRACT_TAXES_PRODUCT', $var, 'chaine', 0, '', $conf->entity);
	}
	if (! $res > 0) {
		$error ++;
	}
	
	$var = GETPOST('TVI_CONTRACT_CT_PRODUCT', 'int');
	if (! empty($var)) {
		$res = dolibarr_set_const($db, 'TVI_CONTRACT_CT_PRODUCT', $var, 'chaine', 0, '', $conf->entity);
	}
	if (! $res > 0) {
		$error ++;
	}
	
	$var = GETPOST('TVI_CONTRACT_ENTREP_PRODUCT', 'int');
	if (! empty($var)) {
		$res = dolibarr_set_const($db, 'TVI_CONTRACT_ENTREP_PRODUCT', $var, 'chaine', 0, '', $conf->entity);
	}
	if (! $res > 0) {
		$error ++;
	}
	
	$var = GETPOST('TVI_CONTRACT_DEPREM_PRODUCT', 'int');
	if (! empty($var)) {
		$res = dolibarr_set_const($db, 'TVI_CONTRACT_DEPREM_PRODUCT', $var, 'chaine', 0, '', $conf->entity);
	}
	if (! $res > 0) {
		$error ++;
	}
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

print '<tr class="pair"><td>Produit Location Terme Fixe</td>';
print '<td align="left">';
print $form->select_produits($conf->global->TVI_CONTRACT_LOC_PRODUCT,'TVI_CONTRACT_LOC_PRODUCT');
print '</td>';
print '</tr>';

print '<tr class="pair"><td>Produit Location Terme variable</td>';
print '<td align="left">';
print $form->select_produits($conf->global->TVI_CONTRACT_KM_PRODUCT,'TVI_CONTRACT_KM_PRODUCT');
print '</td>';
print '</tr>';

print '<tr class="pair"><td>Produit Assurance RC</td>';
print '<td align="left">';
print $form->select_produits($conf->global->TVI_CONTRACT_ASS_PRODUCT,'TVI_CONTRACT_ASS_PRODUCT');
print '</td>';
print '</tr>';

print '<tr class="pair"><td>Produit Dommages</td>';
print '<td align="left">';
print $form->select_produits($conf->global->TVI_CONTRACT_ASSDOM_PRODUCT,'TVI_CONTRACT_ASSDOM_PRODUCT');
print '</td>';
print '</tr>';

print '<tr class="pair"><td>Produit Controle Technique</td>';
print '<td align="left">';
print $form->select_produits($conf->global->TVI_CONTRACT_CT_PRODUCT,'TVI_CONTRACT_CT_PRODUCT');
print '</td>';
print '</tr>';

print '<tr class="pair"><td>Produit Entretien Pneumatique</td>';
print '<td align="left">';
print $form->select_produits($conf->global->TVI_CONTRACT_ENTREP_PRODUCT,'TVI_CONTRACT_ENTREP_PRODUCT');
print '</td>';
print '</tr>';

print '<tr class="pair"><td>Produit D�panage remorquage</td>';
print '<td align="left">';
print $form->select_produits($conf->global->TVI_CONTRACT_DEPREM_PRODUCT,'TVI_CONTRACT_DEPREM_PRODUCT');
print '</td>';
print '</tr>';

print '<tr class="pair"><td>'."Produit taxe a l'essieu".'</td>';
print '<td align="left">';
print $form->select_produits($conf->global->TVI_CONTRACT_TAXES_PRODUCT,'TVI_CONTRACT_TAXES_PRODUCT');
print '</td>';

print '</tr>';
print '</table>';

print '<input type="submit" class="button" value="' . $langs->trans("Save") . '">';
print '</form>';


dol_fiche_end();

llxFooter();

$db->close();