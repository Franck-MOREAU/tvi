<?php
/* Copyright (C) 2005-2008 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2015 Regis Houssin        <regis.houssin@capnetworks.com>
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
 * \file htdocs/core/modules/facture/mod_facture_terre.php
 * \ingroup facture
 * \brief File containing class for numbering module Terre
 */
require_once DOL_DOCUMENT_ROOT . '/core/modules/facture/modules_facture.php';

/**
 * \class mod_facture_terre
 * \brief Classe du modele de numerotation de reference de facture tvi
 */
class mod_facture_tvi extends ModeleNumRefFactures
{
	var $version = 'dolibarr'; // 'development', 'experimental', 'dolibarr'
	var $prefixinvoiceCD = 'CD';
	var $prefixinvoiceVE = 'VE';
	var $prefixinvoiceLD = 'LD';
	var $prefixinvoiceDV = 'DV';
	var $prefixcreditnote = 'AV';
	var $prefixdeposit = 'AC';
	var $error = '';

	/**
	 * Renvoi la description du modele de numerotation
	 *
	 * @return string Texte descripif
	 */
	function info() {
		global $langs;
		$langs->load("bills");
		return $langs->trans('Modéle pour TVI en fonction du contrat ou vehicule CD, VE, LD, DV');
	}

	/**
	 * Renvoi un exemple de numerotation
	 *
	 * @return string Example
	 */
	function getExample() {
		$str = $this->prefixinvoiceCD . ' ' . dol_print_date(dol_now(), '%y') . ' ' . dol_print_date(dol_now(), '%m') . ' 0001'.'<BR>';
		$str .= $this->prefixinvoiceVE . ' '.  dol_print_date(dol_now(), '%y') . ' '.  dol_print_date(dol_now(), '%m').' 0001'.'<BR>';
		$str .= $this->prefixinvoiceLD . ' ' . dol_print_date(dol_now(), '%y') . ' ' . dol_print_date(dol_now(), '%m').' 0001'.'<BR>';
		$str .= $this->prefixinvoiceDV . ' ' . dol_print_date(dol_now(), '%y') . ' ' . dol_print_date(dol_now(), '%m').' 0001'.'<BR>';
		return $str;
	}

	/**
	 * Test si les numeros deja en vigueur dans la base ne provoquent pas de
	 * de conflits qui empechera cette numerotation de fonctionner.
	 *
	 * @return boolean false si conflit, true si ok
	 */
	function canBeActivated() {
		global $langs, $conf, $db;

		$langs->load("bills");

		// Check invoice num
		$fayymm = '';
		$max = '';

		$posindice = 10;
		// CD
		$fayymm = '';
		$sql = "SELECT MAX(CAST(SUBSTRING(facnumber FROM " . $posindice . ") AS SIGNED)) as max";
		$sql .= " FROM " . MAIN_DB_PREFIX . "facture";
		$sql .= " WHERE facnumber LIKE '" . $this->prefixinvoiceCD . "____-%'";
		$sql .= " AND entity = " . $conf->entity;

		$resql = $db->query($sql);
		if ($resql) {
			$row = $db->fetch_row($resql);
			if ($row) {
				$fayymm = substr($row[0], 0, 6);
				$max = $row[0];
			}
		}
		if ($fayymm && ! preg_match('/' . $this->prefixinvoiceCD . '[0-9][0-9][0-9][0-9]/i', $fayymm)) {
			$langs->load("errors");
			$this->error = $langs->trans('ErrorNumRefModel', $max);
			return false;
		}

		// VE
		$fayymm = '';
		$sql = "SELECT MAX(CAST(SUBSTRING(facnumber FROM " . $posindice . ") AS SIGNED)) as max";
		$sql .= " FROM " . MAIN_DB_PREFIX . "facture";
		$sql .= " WHERE facnumber LIKE '" . $this->prefixinvoiceVE . "____-%'";
		$sql .= " AND entity = " . $conf->entity;

		$resql = $db->query($sql);
		if ($resql) {
			$row = $db->fetch_row($resql);
			if ($row) {
				$fayymm = substr($row[0], 0, 6);
				$max = $row[0];
			}
		}
		if ($fayymm && ! preg_match('/' . $this->prefixinvoiceVE . '[0-9][0-9][0-9][0-9]/i', $fayymm)) {
			$langs->load("errors");
			$this->error = $langs->trans('ErrorNumRefModel', $max);
			return false;
		}

		// VE
		$fayymm = '';
		$sql = "SELECT MAX(CAST(SUBSTRING(facnumber FROM " . $posindice . ") AS SIGNED)) as max";
		$sql .= " FROM " . MAIN_DB_PREFIX . "facture";
		$sql .= " WHERE facnumber LIKE '" . $this->prefixinvoiceVE . "____-%'";
		$sql .= " AND entity = " . $conf->entity;

		$resql = $db->query($sql);
		if ($resql) {
			$row = $db->fetch_row($resql);
			if ($row) {
				$fayymm = substr($row[0], 0, 6);
				$max = $row[0];
			}
		}
		if ($fayymm && ! preg_match('/' . $this->prefixinvoiceVE . '[0-9][0-9][0-9][0-9]/i', $fayymm)) {
			$langs->load("errors");
			$this->error = $langs->trans('ErrorNumRefModel', $max);
			return false;
		}

		// DV
		$fayymm = '';
		$sql = "SELECT MAX(CAST(SUBSTRING(facnumber FROM " . $posindice . ") AS SIGNED)) as max";
		$sql .= " FROM " . MAIN_DB_PREFIX . "facture";
		$sql .= " WHERE facnumber LIKE '" . $this->prefixinvoiceDV . "____-%'";
		$sql .= " AND entity = " . $conf->entity;

		$resql = $db->query($sql);
		if ($resql) {
			$row = $db->fetch_row($resql);
			if ($row) {
				$fayymm = substr($row[0], 0, 6);
				$max = $row[0];
			}
		}
		if ($fayymm && ! preg_match('/' . $this->prefixinvoiceDV . '[0-9][0-9][0-9][0-9]/i', $fayymm)) {
			$langs->load("errors");
			$this->error = $langs->trans('ErrorNumRefModel', $max);
			return false;
		}

		// Check credit note num
		$fayymm = '';

		$posindice = 10;
		$sql = "SELECT MAX(CAST(SUBSTRING(facnumber FROM " . $posindice . ") AS SIGNED)) as max"; // This is standard SQL
		$sql .= " FROM " . MAIN_DB_PREFIX . "facture";
		$sql .= " WHERE facnumber LIKE '" . $this->prefixcreditnote . "____-%'";
		$sql .= " AND entity = " . $conf->entity;

		$resql = $db->query($sql);
		if ($resql) {
			$row = $db->fetch_row($resql);
			if ($row) {
				$fayymm = substr($row[0], 0, 6);
				$max = $row[0];
			}
		}
		if ($fayymm && ! preg_match('/' . $this->prefixcreditnote . '[0-9][0-9][0-9][0-9]/i', $fayymm)) {
			$this->error = $langs->trans('ErrorNumRefModel', $max);
			return false;
		}

		// Check deposit num
		$fayymm = '';

		$posindice = 10;
		$sql = "SELECT MAX(CAST(SUBSTRING(facnumber FROM " . $posindice . ") AS SIGNED)) as max"; // This is standard SQL
		$sql .= " FROM " . MAIN_DB_PREFIX . "facture";
		$sql .= " WHERE facnumber LIKE '" . $this->prefixdeposit . "____-%'";
		$sql .= " AND entity = " . $conf->entity;

		$resql = $db->query($sql);
		if ($resql) {
			$row = $db->fetch_row($resql);
			if ($row) {
				$fayymm = substr($row[0], 0, 6);
				$max = $row[0];
			}
		}
		if ($fayymm && ! preg_match('/' . $this->prefixdeposit . '[0-9][0-9][0-9][0-9]/i', $fayymm)) {
			$this->error = $langs->trans('ErrorNumRefModel', $max);
			return false;
		}

		return true;
	}

	/**
	 * Return next value not used or last value used
	 *
	 * @param Societe $objsoc Object third party
	 * @param Facture $facture Object invoice
	 * @param string $mode 'next' for next value or 'last' for last value
	 * @return string Value
	 */
	function getNextValue($objsoc, $facture, $mode = 'next') {
		global $db;

		// Find if contraxt is link to invoice
		
		$invoice_type = $facture->array_options['options_typ_contract'];
		if(empty($invoice_type)) $invoice_type='DV';


		if ($facture->type == 2)
			$prefix = $this->prefixcreditnote;
		else if ($facture->type == 3)
			$prefix = $this->prefixdeposit;
		else
			$prefix = $this->{'prefixinvoice'.$invoice_type};
		
		$date = $facture->date; // This is invoice date (not creation date)
		$yymm = strftime("%y %m", $date);

			// D'abord on recupere la valeur max
		$posindice = 10;
		$sql = "SELECT MAX(CAST(SUBSTRING(facnumber FROM " . $posindice . ") AS SIGNED)) as max"; // This is standard SQL
		$sql .= " FROM " . MAIN_DB_PREFIX . "facture";
		$sql .= " WHERE facnumber LIKE '" . $prefix . ' ' . $yymm ." %'";
		$sql .= " AND entity IN (" . getEntity('facture', 1) . ")";

		$resql = $db->query($sql);
		dol_syslog(get_class($this) . "::getNextValue", LOG_DEBUG);
		if ($resql) {
			$obj = $db->fetch_object($resql);
			if ($obj)
				$max = intval($obj->max);
			else
				$max = 0;
		} else {
			return - 1;
		}

		if ($mode == 'last') {
			if ($max >= (pow(10, 4) - 1))
				$num = $max; // If counter > 9999, we do not format on 4 chars, we take number as it is
			else
				$num = sprintf("%04s", $max);

			$ref = '';
			$sql = "SELECT facnumber as ref";
			$sql .= " FROM " . MAIN_DB_PREFIX . "facture";
			$sql .= " WHERE facnumber LIKE '" . $prefix . ' ' . $yymm . " " . $num . "'";
			$sql .= " AND entity IN (" . getEntity('facture', 1) . ")";

			dol_syslog(get_class($this) . "::getNextValue", LOG_DEBUG);
			$resql = $db->query($sql);
			if ($resql) {
				$obj = $db->fetch_object($resql);
				if ($obj)
					$ref = $obj->ref;
			} else
				dol_print_error($db);

			return $ref;
		} else if ($mode == 'next') {
			$date = $facture->date; // This is invoice date (not creation date)
			$yymm = strftime("%y %m", $date);

			if ($max >= (pow(10, 4) - 1))
				$num = $max + 1; // If counter > 9999, we do not format on 4 chars, we take number as it is
			else
				$num = sprintf("%04s", $max + 1);

			dol_syslog(get_class($this) . "::getNextValue return " . $prefix . $yymm . "-" . $num);
			return $prefix . ' ' . $yymm . " " . $num;
		} else
			dol_print_error('', 'Bad parameter for getNextValue');
	}

	/**
	 * Return next free value
	 *
	 * @param Societe $objsoc Object third party
	 * @param string $objforref Object for number to search
	 * @param string $mode 'next' for next value or 'last' for last value
	 * @return string Next free value
	 */
	function getNumRef($objsoc, $objforref, $mode = 'next') {
		return $this->getNextValue($objsoc, $objforref, $mode);
	}
}

