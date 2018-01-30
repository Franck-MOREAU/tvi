<?php

/* Copyright (C) 2016		Florian HENRY	<florian.henry@atm-consulting.fr>
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
 * \file tvi/class/tvi.class.php
 * \ingroup tvi
 */
require_once DOL_DOCUMENT_ROOT . "/core/class/commonobject.class.php";
class Tvi extends CommonObject
{
	public $lines = array();
	public $lines_contract = array();
	public $lines_events = array();

	/**
	 * Constructor
	 *
	 * @param DoliDB $db Database handler
	 */
	function __construct($db) {
		$this->db = $db;
	}

	/**
	 *
	 * @param string $sortorder
	 * @param string $sortfield
	 * @param number $limit
	 * @param number $offset
	 * @param array $filter
	 * @return unknown|number
	 */
	public function fetchAllVehicules($sortorder = '', $sortfield = '', $limit = 0, $offset = 0, $filter = array()) {
		require_once DOL_DOCUMENT_ROOT . '/projet/class/project.class.php';

		global $langs;

		$sql = "SELECT ";
		$sql .= "t.rowid, ";
		$sql .= "t.ref,";
		$sql .= "t.title, ";
		$sql .= "t.description, ";
		$sql .= "t.public, ";
		$sql .= "t.datec, ";
		$sql .= "t.opp_amount, ";
		$sql .= "t.budget_amount,";
		$sql .= "t.tms, ";
		$sql .= "t.dateo, ";
		$sql .= "t.datee, ";
		$sql .= "t.date_close, ";
		$sql .= "t.fk_soc, ";
		$sql .= "t.fk_user_creat, ";
		$sql .= "t.fk_user_close, ";
		$sql .= "t.fk_statut, ";
		$sql .= "t.fk_opp_status, ";
		$sql .= "t.opp_percent, ";
		$sql .= "t.note_private, ";
		$sql .= "t.note_public, ";
		$sql .= "t.model_pdf";

		$sql .= " FROM " . MAIN_DB_PREFIX . "projet as t";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "projet_extrafields as pextra ON pextra.fk_object=t.rowid";

		$sql .= " WHERE t.entity IN (" . getEntity('projet') . ")";

		if (is_array($filter)) {
			foreach ( $filter as $key => $value ) {
				if ($key == 't.rowid' || $key == 'pextra.puissance' || $key == 'pextra.ptc' || $key == 'pextra.categorie') {
					$sql .= ' AND ' . $key . ' = ' . $value;
				} elseif ($key == 't.fk_statut') {
					$sql .= ' AND ' . $key . ' IN (' . implode(',', $value) . ')';
				} elseif ($key == 'pextra.type' || $key == 'pextra.carrosserie' || $key == 'pextra.marque' ) {
					$sql .= ' AND ' . $key . ' IN (' . implode(',', $value) . ')';
				} elseif ($key == 'pextra.dmc<=' ) {
					$sql .= ' AND ' . $key . '\''.$this->db->idate($value).'\'';
				} elseif ($key == 'pextra.dmc>=' ) {
					$sql .= ' AND ' . $key . '\''.$this->db->idate($value).'\'';
				} else {
					$sql .= ' AND ' . $key . ' LIKE \'%' . $this->db->escape($value) . '%\'';
				}
			}
		}
		if (! empty($sortfield)) {
			$sql .= $this->db->order($sortfield, $sortorder);
		}

		if (! empty($limit)) {
			$sql .= ' ' . $this->db->plimit($limit + 1, $offset);
		}

		dol_syslog(get_class($this) . "::" . __METHOD__, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			$this->lines = array();

			$num = $this->db->num_rows($resql);

			while ( $obj = $this->db->fetch_object($resql) ) {

				$line = new Project($this->db);

				$line->id = $obj->rowid;
				$line->ref = $obj->ref;
				$line->title = $obj->title;
				$line->description = $obj->description;
				$line->date_c = $this->db->jdate($obj->datec);
				$line->date_m = $this->db->jdate($obj->tms);
				$line->date_start = $this->db->jdate($obj->dateo);
				$line->date_end = $this->db->jdate($obj->datee);
				$line->date_close = $this->db->jdate($obj->date_close);
				$line->note_private = $obj->note_private;
				$line->note_public = $obj->note_public;
				$line->socid = $obj->fk_soc;
				$line->user_author_id = $obj->fk_user_creat;
				$line->user_close_id = $obj->fk_user_close;
				$line->public = $obj->public;
				$line->statut = $obj->fk_statut;
				$line->opp_status = $obj->fk_opp_status;
				$line->opp_amount = $obj->opp_amount;
				$line->opp_percent = $obj->opp_percent;
				$line->budget_amount = $obj->budget_amount;
				$line->modelpdf = $obj->model_pdf;

				$extrafields = new ExtraFields($this->db);
				$extralabels = $extrafields->fetch_name_optionals_label($line->table_element, true);
				if (count($extralabels) > 0) {
					$line->fetch_optionals($line->id, $extralabels);
				}

				$this->lines[] = $line;
			}
			$this->db->free($resql);

			return $num;
		} else {
			$this->errors[] = "Error " . $this->db->lasterror();
			dol_syslog(get_class($this) . "::" . __METHOD__ . " Error " . $this->db->lasterror(), LOG_ERR);
			return - 1;
		}
	}

	/**
	 *
	 * @param string $sortorder
	 * @param string $sortfield
	 * @param number $limit
	 * @param number $offset
	 * @param array $filter
	 * @return number
	 */
	public function fetchContractLines($sortorder = '', $sortfield = '', $limit = 0, $offset = 0, $filter = array()) {
		require_once DOL_DOCUMENT_ROOT . '/contrat/class/contrat.class.php';

		$this->nbofserviceswait = 0;
		$this->nbofservicesopened = 0;
		$this->nbofservicesexpired = 0;
		$this->nbofservicesclosed = 0;
		$this->lines_contract=array();
		
		$total_ttc = 0;
		$total_vat = 0;
		$total_ht = 0;

		$now = dol_now();

		require_once DOL_DOCUMENT_ROOT . '/core/class/extrafields.class.php';
		$extrafieldsline = new ExtraFields($this->db);
		$line = new ContratLigne($this->db);
		$extralabelsline = $extrafieldsline->fetch_name_optionals_label($line->table_element, true);

		$this->lines_contract = array();

		$sql = "SELECT ";
		$sql .= "p.label as product_label, ";
		$sql .= "p.description as product_desc, ";
		$sql .= "p.ref as product_ref,";
		$sql .= "p.rowid as product_id, ";
		$sql .= " d.rowid, ";
		$sql .= "d.fk_contrat,";
		$sql .= "d.statut, ";
		$sql .= "d.description, ";
		$sql .= "d.price_ht, ";
		$sql .= "d.tva_tx, ";
		$sql .= "d.localtax1_tx, ";
		$sql .= "d.localtax2_tx, ";
		$sql .= "d.qty, ";
		$sql .= "d.remise_percent, ";
		$sql .= "d.subprice, ";
		$sql .= "d.fk_product_fournisseur_price as fk_fournprice, ";
		$sql .= "d.buy_price_ht as pa_ht,";
		$sql .= " d.total_ht,";
		$sql .= " d.total_tva,";
		$sql .= " d.total_localtax1,";
		$sql .= " d.total_localtax2,";
		$sql .= " d.total_ttc,";
		$sql .= " d.info_bits, d.fk_product,";
		$sql .= " d.date_ouverture_prevue, ";
		$sql .= " d.date_ouverture,";
		$sql .= " d.date_fin_validite, ";
		$sql .= " d.date_cloture,";
		$sql .= " d.fk_user_author,";
		$sql .= " d.fk_user_ouverture,";
		$sql .= " d.fk_user_cloture,";
		$sql .= " d.fk_unit";
		$sql .= " ,soc.rowid as socid";
		$sql .= " ,soc.nom as socname";
		$sql .= " FROM " . MAIN_DB_PREFIX . "contratdet as d ";
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "contrat as t ON t.rowid=d.fk_contrat ";
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "product as p ON d.fk_product = p.rowid";
		$sql .= " INNER JOIN " . MAIN_DB_PREFIX . "societe as soc ON soc.rowid = t.fk_soc";

		$sql .= " WHERE t.entity IN (" . getEntity('contract') . ")";

		if (is_array($filter)) {
			foreach ( $filter as $key => $value ) {
				if ($key == 't.rowid' || $key == 'd.rowid' || $key == 't.fk_soc' || $key=='p.rowid' || $key=='d.fk_product') {
					$sql .= ' AND ' . $key . ' = ' . $value;
				} elseif ($key == 't.fk_projet' || $key == 'd.statut' || $key == 't.statut') {
					$sql .= ' AND ' . $key . ' IN (' . implode(',', $value) . ')';
				} elseif ($key == 'p.ref') {
					$sql .= ' AND ' . $key . ' LIKE \'' . $this->db->escape($value) . '%\'';
				} elseif ($key == 'date_activ') {
					$sql .= ' AND ((d.date_ouverture <= \'' . $this->db->idate($value) . '\' AND \'' . $this->db->idate($value) . '\' <= d.date_fin_validite)';
					$sql .= ' OR (d.date_ouverture <= \'' . $this->db->idate($value) . '\' AND \'' . $this->db->idate($value) . '\' <= d.date_cloture))';
				} elseif ($key == 'dateinrange') {

					$sql .= 'AND ((LEAST(d.date_ouverture_prevue,IFNULL(d.date_ouverture,d.date_ouverture_prevue)) ';
					$sql .= '		BETWEEN \'' . $this->db->idate($value['from']) . '\' AND \'' . $this->db->idate($value['to']) . '\')';
					$sql .= ' 	OR GREATEST(d.date_fin_validite,IFNULL(d.date_cloture, d.date_fin_validite)) ';
					$sql .= '		BETWEEN \'' . $this->db->idate($value['from']) . '\' AND \'' . $this->db->idate($value['to']) . '\')';
				} else {
					$sql .= ' AND ' . $key . ' LIKE \'%' . $this->db->escape($value) . '%\'';
				}
			}
		}
		if (! empty($sortfield)) {
			$sql .= $this->db->order($sortfield, $sortorder);
		}

		if (! empty($limit)) {
			$sql .= ' ' . $this->db->plimit($limit + 1, $offset);
		}

		dol_syslog(get_class($this) . "::".__METHOD__, LOG_DEBUG);
		$result = $this->db->query($sql);
		if ($result) {
			$num = $this->db->num_rows($result);

			while ( $objp = $this->db->fetch_object($result) ) {

				$line = new ContratLigne($this->db);
				$line->id = $objp->rowid;
				$line->fk_contrat = $objp->fk_contrat;
				$line->desc = $objp->description; // Description ligne
				$line->qty = $objp->qty;
				$line->tva_tx = $objp->tva_tx;
				$line->localtax1_tx = $objp->localtax1_tx;
				$line->localtax2_tx = $objp->localtax2_tx;
				$line->subprice = $objp->subprice;
				$line->statut = $objp->statut;
				$line->remise_percent = $objp->remise_percent;
				$line->price_ht = $objp->price_ht;
				$line->price = $objp->price_ht; // For backward compatibility
				$line->total_ht = $objp->total_ht;
				$line->total_tva = $objp->total_tva;
				$line->total_localtax1 = $objp->total_localtax1;
				$line->total_localtax2 = $objp->total_localtax2;
				$line->total_ttc = $objp->total_ttc;
				$line->fk_product = $objp->fk_product;
				$line->info_bits = $objp->info_bits;

				$line->fk_fournprice = $objp->fk_fournprice;
				$marginInfos = getMarginInfos($objp->subprice, $objp->remise_percent, $objp->tva_tx, $objp->localtax1_tx, $objp->localtax2_tx, $line->fk_fournprice, $objp->pa_ht);
				$line->pa_ht = $marginInfos[0];

				$line->fk_user_author = $objp->fk_user_author;
				$line->fk_user_ouverture = $objp->fk_user_ouverture;
				$line->fk_user_cloture = $objp->fk_user_cloture;
				$line->fk_unit = $objp->fk_unit;

				$line->ref = $objp->product_ref; // deprecated
				$line->label = $objp->product_label; // deprecated
				$line->libelle = $objp->product_label; // deprecated
				$line->product_ref = $objp->product_ref; // Ref product
				$line->product_desc = $objp->product_desc; // Description product
				$line->product_label = $objp->product_label; // Label product

				$line->description = $objp->description;

				$line->date_ouverture_prevue = $this->db->jdate($objp->date_ouverture_prevue);
				$line->date_ouverture = $this->db->jdate($objp->date_ouverture);
				$line->date_fin_validite = $this->db->jdate($objp->date_fin_validite);
				$line->date_cloture = $this->db->jdate($objp->date_cloture);

				// For backward compatibility
				$line->date_debut_prevue = $this->db->jdate($objp->date_ouverture_prevue);
				$line->date_debut_reel = $this->db->jdate($objp->date_ouverture);
				$line->date_fin_prevue = $this->db->jdate($objp->date_fin_validite);
				$line->date_fin_reel = $this->db->jdate($objp->date_cloture);

				$line->socname = $objp->socname;
				$line->socid = $objp->socid;

				// Retreive all extrafield for propal
				// fetch optionals attributes and labels
				$line->fetch_optionals($line->id, $extralabelsline);

				$this->lines_contract[] = $line;

				if ($line->statut == 0)
					$this->nbofserviceswait ++;
				if ($line->statut == 4 && (empty($line->date_fin_prevue) || $line->date_fin_prevue >= $now))
					$this->nbofservicesopened ++;
				if ($line->statut == 4 && (! empty($line->date_fin_prevue) && $line->date_fin_prevue < $now))
					$this->nbofservicesexpired ++;
				if ($line->statut == 5)
					$this->nbofservicesclosed ++;

				$total_ttc += $objp->total_ttc;
				$total_vat += $objp->total_tva;
				$total_ht += $objp->total_ht;
			}
			$this->db->free($result);

			$this->nbofservices = count($this->lines);
			$this->total_ttc = price2num($total_ttc);
			$this->total_vat = price2num($total_vat);
			$this->total_ht = price2num($total_ht);

			return $num;
		} else {
			$this->errors[] = "Error " . $this->db->lasterror();
			dol_syslog(get_class($this) . "::" . __METHOD__ . " Error " . $this->db->lasterror(), LOG_ERR);
			return - 1;
		}
	}

	/**
	 *
	 * @param string $sortorder
	 * @param string $sortfield
	 * @param number $limit
	 * @param number $offset
	 * @param array $filter
	 * @return number
	 */
	public function fetchEventsLines($sortorder = '', $sortfield = '', $limit = 0, $offset = 0, $filter = array()) {
		global $langs;

		$langs->load("agenda");

		require_once DOL_DOCUMENT_ROOT . '/comm/action/class/actioncomm.class.php';

		$sql = "SELECT a.id,";
		$sql .= " a.id as ref,";
		$sql .= " a.ref_ext,";
		$sql .= " a.datep,";
		$sql .= " a.datep2,";
		$sql .= " a.durationp,"; // deprecated
		$sql .= " a.datec,";
		$sql .= " a.tms as datem,";
		$sql .= " a.code, ";
		$sql .= " a.label, ";
		$sql .= " a.note,";
		$sql .= " a.fk_soc,";
		$sql .= " a.fk_project,";
		$sql .= " a.fk_user_author, a.fk_user_mod,";
		$sql .= " a.fk_user_action, a.fk_user_done,";
		$sql .= " a.fk_contact, ";
		$sql .= " a.percent as percentage,";
		$sql .= " a.fk_element, ";
		$sql .= " a.elementtype,";
		$sql .= " a.priority, ";
		$sql .= " a.fulldayevent, ";
		$sql .= " a.location, ";
		$sql .= " a.punctual, ";
		$sql .= " a.transparency,";
		$sql .= " c.id as type_id, ";
		$sql .= " c.code as type_code, ";
		$sql .= " c.type as type_dict, ";
		$sql .= " c.libelle,";
		$sql .= " s.nom as socname,";
		$sql .= " u.firstname, ";
		$sql .= " u.lastname as lastname";
		$sql .= " FROM " . MAIN_DB_PREFIX . "actioncomm as a ";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "c_actioncomm as c ON a.fk_action=c.id ";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "user as u on u.rowid = a.fk_user_author";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "societe as s on s.rowid = a.fk_soc";
		$sql .= " WHERE a.entity IN (" . getEntity('action') . ")";

		if (is_array($filter)) {
			foreach ( $filter as $key => $value ) {
				if ($key == 'a.rowid' || $key == 'a.fk_soc') {
					$sql .= ' AND ' . $key . ' = ' . $value;
				} elseif ($key == 'a.fk_project') {
					$sql .= ' AND ' . $key . ' IN (' . implode(',', $value) . ')';
				} elseif ($key == '!c.type') {
					$sql .= ' AND c.type <> \'' . $this->db->escape($value) . '\'';
				} elseif ($key == 'a.datep[]') {
					$sql .= ' AND (a.datep BETWEEN \'' . $this->db->idate($value['from']) . '\' AND \'' . $this->db->idate($value['to']) . '\')';
				} else {
					$sql .= ' AND ' . $key . ' LIKE \'%' . $this->db->escape($value) . '%\'';
				}
			}
		}
		if (! empty($sortfield)) {
			$sql .= $this->db->order($sortfield, $sortorder);
		}

		if (! empty($limit)) {
			$sql .= ' ' . $this->db->plimit($limit + 1, $offset);
		}

		dol_syslog(get_class($this) . "::", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);

			$this->lines_events = array();

			while ( $obj = $this->db->fetch_object($resql) ) {

				$line = new ActionComm($this->db);

				$line->id = $obj->id;
				$line->ref = $obj->ref;
				$line->ref_ext = $obj->ref_ext;

				// Properties of parent table llx_c_actioncomm (will be deprecated in future)
				$line->type_id = $obj->type_id;
				$line->type_code = $obj->type_code;
				$transcode = $langs->trans("Action" . $obj->type_code);
				$type_libelle = ($transcode != "Action" . $obj->type_code ? $transcode : $obj->libelle);
				$line->type = $type_libelle;

				$line->code = $obj->code;
				$line->label = $obj->label;
				$line->datep = $line->db->jdate($obj->datep);
				$line->datef = $line->db->jdate($obj->datep2);

				$line->datec = $line->db->jdate($obj->datec);
				$line->datem = $line->db->jdate($obj->datem);

				$line->note = $obj->note;
				$line->percentage = $obj->percentage;

				$line->authorid = $obj->fk_user_author;
				$line->usermodid = $obj->fk_user_mod;

				if (! is_object($line->author))
					$line->author = new stdClass(); // For avoid warning
				$line->author->id = $obj->fk_user_author; // deprecated
				$line->author->firstname = $obj->firstname; // deprecated
				$line->author->lastname = $obj->lastname; // deprecated
				if (! is_object($line->usermod))
					$line->usermod = new stdClass(); // For avoid warning
				$line->usermod->id = $obj->fk_user_mod; // deprecated

				$line->userownerid = $obj->fk_user_action;
				$line->userdoneid = $obj->fk_user_done;
				$line->priority = $obj->priority;
				$line->fulldayevent = $obj->fulldayevent;
				$line->location = $obj->location;
				$line->transparency = $obj->transparency;
				$line->punctual = $obj->punctual; // deprecated

				$line->socid = $obj->fk_soc; // To have fetch_thirdparty method working
				$line->contactid = $obj->fk_contact; // To have fetch_contact method working
				$line->fk_project = $obj->fk_project; // To have fetch_project method working

				$line->societe->id = $obj->fk_soc; // deprecated
				$line->contact->id = $obj->fk_contact; // deprecated

				$line->fk_element = $obj->fk_element;
				$line->elementtype = $obj->elementtype;

				$extrafields = new ExtraFields($line->db);
				$extralabels = $extrafields->fetch_name_optionals_label($line->table_element, true);
				if (count($extralabels) > 0) {
					$line->fetch_optionals($line->id, $extralabels);
				}

				if (! empty($line->socid)) {
					$line->fetch_thirdparty();
				}

				$this->lines_events[] = $line;
			}
			$this->db->free($resql);

			return $num;
		} else {
			$this->errors[] = "Error " . $this->db->lasterror();
			dol_syslog(get_class($this) . "::" . __METHOD__ . " Error " . $this->db->lasterror(), LOG_ERR);
			return - 1;
		}
	}

	/**
	 *
	 * @return NULL[]
	 */
	public function fetchallproduct() {
		$result = array();

		$sql = "SELECT rowid FROM " . MAIN_DB_PREFIX . "product ";

		$resql = $this->db->query($sql);

		if ($resql) {
			while ( $obj = $this->db->fetch_object($resql) ) {
				$result[] = $obj->rowid;
			}
		}

		return $result;
	}

	/**
	 *
	 * @param unknown $project
	 * @return NULL[]
	 */
	public function fetchallloan($project) {
		$result = array();

		$sql = "SELECT rowid FROM " . MAIN_DB_PREFIX . "loan ";
		$sql .= "WHERE fk_project = " . $project;

		$resql = $this->db->query($sql);

		if ($resql) {
			while ( $obj = $this->db->fetch_object($resql) ) {
				$result[] = $obj->rowid;
			}
		}

		return $result;
	}

	/**
	 *
	 * @param unknown $project
	 * @param unknown $produit
	 * @return unknown
	 */
	public function gettotalvente($project, $produit) {
		$sql = "SELECT SUM(d.total_ht) AS total_ht ";
		$sql .= "FROM " . MAIN_DB_PREFIX . "facturedet AS d ";
		$sql .= "INNER JOIN " . MAIN_DB_PREFIX . "facture AS f ON f.rowid = d.fk_facture ";
		$sql .= "WHERE f.fk_projet = " . $project . " AND d.fk_product = " . $produit;

		$resql = $this->db->query($sql);

		if ($resql) {
			$obj = $this->db->fetch_object($resql);
			$result = $obj->total_ht;
		}
		return $result;
	}

	/**
	 *
	 * @param unknown $project
	 * @param unknown $produit
	 * @return unknown
	 */
	public function gettotalachat($project, $produit) {
		$sql = "SELECT SUM(d.total_ht) AS total_ht ";
		$sql .= "FROM " . MAIN_DB_PREFIX . "facture_fourn_det AS d ";
		$sql .= "INNER JOIN " . MAIN_DB_PREFIX . "facture_fourn AS f ON f.rowid = d.fk_facture_fourn ";
		$sql .= "WHERE f.fk_projet = " . $project . " AND d.fk_product = " . $produit . ' AND f.fk_statut > 0';

		$resql = $this->db->query($sql);

		if ($resql) {
			$obj = $this->db->fetch_object($resql);
			$result = $obj->total_ht;
		}
		return $result;
	}

	/**
	 *
	 * @param unknown $project
	 * @param unknown $loan
	 * @return unknown
	 */
	public function gettotalloan($project, $loan) {
		$sql = "SELECT SUM(IFNULL(p.amount_capital,0) + IFNULL(p.amount_insurance,0) + IFNULL(p.amount_interest,0)) AS total_ht ";
		$sql .= "FROM " . MAIN_DB_PREFIX . "loan AS l ";
		$sql .= "INNER JOIN " . MAIN_DB_PREFIX . "payment_loan AS p ON p.fk_loan = l.rowid ";
		$sql .= "WHERE l.fk_project = " . $project . " AND l.rowid = " . $loan;

		$resql = $this->db->query($sql);

		if ($resql) {
			$obj = $this->db->fetch_object($resql);
			$result = $obj->total_ht;
		}
		return $result;
	}

	/**
	 *  Call From cron job 	tvi/class/tvi.class.php createInvoiceFromContract
	 *
	 * @param string $date_fact
	 * @return number
	 */
	public function createInvoiceFromContract($date_fact = '') {

		global $conf,$user, $mysoc, $langs;

		require_once DOL_DOCUMENT_ROOT . '/contrat/class/contrat.class.php';
		require_once DOL_DOCUMENT_ROOT . '/compta/facture/class/facture.class.php';
		require_once DOL_DOCUMENT_ROOT . '/core/lib/date.lib.php';
		require_once DOL_DOCUMENT_ROOT . '/core/lib/price.lib.php';
		require_once DOL_DOCUMENT_ROOT . '/core/class/extrafields.class.php';

		$error = 0;

		if (empty($date_fact)) {
			$date_fact = dol_now();
		}

		$array_filter = array();
		$array_filter['d.statut'] = array(
				4
		);
		$array_filter['t.statut'] = array(
				1
		);
		$array_filter['date_activ'] = $date_fact;

		$result = $this->fetchContractLines('ASC', 't.rowid', 0, 0, $array_filter);
		if ($result < 0) {
			return - 1;
		}

		$this->db->begin();
		$already_done = array();
		$created_invoices = array();
		if (is_array($this->lines_contract) && count($this->lines_contract) > 0) {
			foreach ( $this->lines_contract as $linecontract ) {
				if (! in_array($linecontract->fk_contrat, $already_done)) {
					$already_done[] = $linecontract->fk_contrat;

					// Only invoice contract where date next invoice is now or in past
					$contract = new Contrat($this->db);
					$result = $contract->fetch($linecontract->fk_contrat);
					if ($result < 0) {
						$this->errors[] = $contract->error;
						$error ++;
					} else {

						$result = $contract->fetch_thirdparty();
						if ($result < 0) {
							$this->errors[] = $contract->error;
							$error ++;
						}
						$date_gen=$this->db->jdate($contract->array_options['options_date_when']);
						if (empty($date_gen)) {
							$date_gen=dol_mktime(12, 0, 0, dol_print_date(dol_now(), '%m'), dol_print_date(dol_now(), '%d'), dol_print_date(dol_now(), '%Y'));
						}
						if ($date_gen <= dol_now()) {
							$invoice = new Facture($this->db);
							$invoice->ref_client = $contract->ref . '-' . dol_print_date(dol_now());

							if (! empty($contract->thirdparty->cond_reglement_id)) {
								$invoice->cond_reglement_id = $contract->thirdparty->cond_reglement_id;
							} else {
								$invoice->cond_reglement_id = 1;
							}

							if (! empty($contract->thirdparty->mode_reglement_id)) {
								$invoice->mode_reglement_id = $contract->thirdparty->mode_reglement_id;
							}

							$invoice->date = $date_gen;
							$invoice->socid = $contract->thirdparty->id;
							$invoice->modelpdf = $conf->global->FACTURE_ADDON_PDF;
							$invoice->array_options['options_typ_contract'] = $contract->array_options['options_typ_contract'];
							$invoice->array_options['options_vehicule']=$contract->array_options['options_vehicule'];
							$invoice->import_key = dol_now();
							
							if(!empty($invoice->array_options['options_vehicule'])){
								$sql = "SELECT parc, type, immat, chassis FROM " .MAIN_DB_PREFIX . "c_tvi_vehicules WHERE rowid = " . $invoice->array_options['options_vehicule'];
								$resql=$this->db->query($sql);
								$dico = $this->db->fetch_object($resql);
								$invoice->note_public = '<table class="border" width="100%"><tr>';
								$invoice->note_public .='<td align="center">N° Parc</td>';
								$invoice->note_public .='<td align="center">Type</td>';
								$invoice->note_public .='<td align="center">Immat.</td>';
								$invoice->note_public .='<td align="center">N° de Châssis</td>';
								$invoice->note_public .='</tr><tr>';
								$invoice->note_public .='<td align="center">'.$dico->parc.'</td>';
								$invoice->note_public .='<td align="center">'.$dico->type.'</td>';
								$invoice->note_public .='<td align="center">'.$dico->immat.'</td>';
								$invoice->note_public .='<td align="center">'.$dico->chassis.'</td>';
								$invoice->note_public .='</tr></table>';
							}


							$invoice->linked_objects[$contract->element] = $contract->id;

							foreach ( $contract->lines as $key => $line ) {
								if ($line->subprice!=0 && $line->statut == 4) {
									$linerec = new FactureLigne($this->db);
									$prod = new Product($this->db);

									if (! empty($line->fk_product)) {
										$prod->fetch($line->fk_product);
									}

									$linerec->desc = $line->desc;
									$linerec->subprice = $line->subprice;
									$linerec->qty = $line->qty;
									$linerec->tva_tx = $line->tva_tx;
									$linerec->localtax1_tx = $line->localtax1_tx;
									$linerec->localtax2_tx = $line->localtax2_tx;
									$linerec->fk_product = $line->fk_product;
									$linerec->remise_percent = $line->remise_percent;
									$linerec->product_type = (empty($prod->product_type) ? 1 : 0);
									$linerec->rang = $key;
									$linerec->special_code = 0;
									$linerec->label = $line->product_label;
									$linerec->fk_unit = $line->fk_unit;
									$linerec->date_start = dol_time_plus_duree($date_gen, -$contract->array_options['options_frequency'], $contract->array_options['options_unit_frequency']);
									$linerec->date_end = $date_gen;

									$tabprice = calcul_price_total($line->qty, $line->subprice, $line->remise_percent,  $line->tva_tx, $line->localtax1_tx, $line->localtax2_tx, 0, 'HT', 0, (empty($prod->product_type) ? 1 : 0), $mysoc);

									$linerec->total_ht  = $tabprice[0];
									$linerec->total_tva = $tabprice[1];
									$linerec->total_ttc = $tabprice[2];
									$linerec->total_localtax1 = $tabprice[9];
									$linerec->total_localtax2 = $tabprice[10];

									$invoice->lines[] = $linerec;
								}
							}

							if (count($invoice->lines)>0) {
								$result = $invoice->create($user);
								$created_invoices[] = $result;
								if ($result < 0) {
									$this->errors[] = $invoice->error;
									$error ++;
								}

								if (empty($error)) {
									if ($conf->global->TVI_INVOICE_AUTO_STATUS==1) {
										$result=$invoice->validate($user);
										if ($result < 0) {
											$this->errors[] = $invoice->error;
											$error ++;
										}

										$result= $invoice->generateDocument($invoice->modelpdf, $langs, 0, 0, 0);
										if ($result <= 0)
										{
											$this->errors[] = $invoice->error;
											$error ++;
										}
									}
								}

								if (empty($error)) {
									//calc new contract date next gen
									$next_gen=dol_time_plus_duree($date_gen, $contract->array_options['options_frequency'], $contract->array_options['options_unit_frequency']);
									$sql = 'UPDATE '.MAIN_DB_PREFIX.'contrat_extrafields SET date_when=\''.$this->db->idate($next_gen).'\' WHERE fk_object='.$contract->id;
									$result=$this->db->query($sql);
									if (!$result) {
										$this->errors[] = $this->db->lasterror;
										$error ++;
									}
								}
							}

						}
					}
				}
			}
		}

		if (empty($error)) {
			$this->db->commit();
			$this->last_fact_list = $created_invoices;
			$this->last_treated_ct = $already_done;
			//For cron jobs 0 mean OK
			return 0;
		} else {
			foreach ( $this->errors as $mesg ) {
				$this->error .= $mesg . "\n";
			}
			$this->db->rollback();
			return - 1 * $error;
		}
	}

	public function loaddicogenre()
	{
		$result = array();

		$sql = "SELECT rowid, genre FROM ".MAIN_DB_PREFIX."c_tvi_genre ";
		$sql.= "WHERE active = 1";

		$resql = $this->db->query($sql);

		if($resql){
			while($obj=$this->db->fetch_object($resql)){
				$result[$obj->rowid] = $obj->genre;
			}
		}

		return $result;
	}

	public function addnewperiode($fk_genre,$fk_typeevent,$franchise,$periode)
	{
		$sql = "INSERT INTO ".MAIN_DB_PREFIX . "event_period(fk_genre, fk_typeevent, franchise, periode) VALUES (";
		$sql.= $fk_genre . ',' . $fk_typeevent . ',' . $franchise . ',' . $periode .')';

		$resql = $this->db->query($sql);

		if($resql){
			return 1;
		}else{
			return -1;
		}
	}

	public function updateperiode($periodeid,$fk_genre,$fk_typeevent,$franchise,$periode)
	{
		$sql = "UPDATE ".MAIN_DB_PREFIX . "event_period SET ";
		$sql.= "fk_genre=" . $fk_genre . ',fk_typeevent=' . $fk_typeevent . ',franchise=' . $franchise . ',periode=' . $periode .' WHERE rowid=' . $periodeid;

		$resql = $this->db->query($sql);

		if($resql){
			return 1;
		}else{
			return -1;
		}
	}


	public function deleteperiode($periodeid)
	{
		$sql = "DELETE FROM ".MAIN_DB_PREFIX . "event_period WHERE rowid=" . $periodeid;

		$resql = $this->db->query($sql);

		if($resql){
			return 1;
		}else{
			return -1;
		}
	}


	public function fetchallperiode()
	{
		$result = array();
		$sql = "SELECT p.rowid, p.fk_genre, g.genre, p.fk_typeevent,e.libelle,p.franchise,p.periode ";
		$sql.= "FROM " . MAIN_DB_PREFIX . "event_period as p ";
		$sql.= "INNER JOIN " . MAIN_DB_PREFIX . "c_tvi_genre as g on g.rowid = p.fk_genre ";
		$sql.= "INNER JOIN " . MAIN_DB_PREFIX . "c_actioncomm as e on e.id = p.fk_typeevent";

		$resql = $this->db->query($sql);

		if($resql){
			while($obj=$this->db->fetch_object($resql)){
				$result[] = $obj;
			}
		}
		return $result;
	}

	public function fetchperiode($rowid)
	{
		$result = array();
		$sql = "SELECT p.rowid, p.fk_genre, g.genre, p.fk_typeevent,e.libelle,p.franchise,p.periode ";
		$sql.= "FROM " . MAIN_DB_PREFIX . "event_period as p ";
		$sql.= "INNER JOIN " . MAIN_DB_PREFIX . "c_tvi_genre as g on g.rowid = p.fk_genre ";
		$sql.= "INNER JOIN " . MAIN_DB_PREFIX . "c_actioncomm as e on e.id = p.fk_typeevent ";
		$sql.= "WHERE p.rowid = " . $rowid;

		$resql = $this->db->query($sql);

		if($resql){
			$obj=$this->db->fetch_object($resql);
			$result = $obj;
		}
		return $result;
	}

	function select_type_actions($selected='',$htmlname='actioncode',$excludetype='',$onlyautoornot=0, $hideinfohelp=0, $multiselect=0)
	{
		global $langs,$user,$form,$conf;

		if (! is_object($form)) $form=new Form($db);

		require_once DOL_DOCUMENT_ROOT.'/comm/action/class/cactioncomm.class.php';


		// Suggest a list with manual events or all auto events

		$arraylist=$this->liste_array(1, 'id', $excludetype, $onlyautoornot);
		$arraylist[0]='&nbsp;';
		asort($arraylist);

		if ($selected == 'manual') $selected='AC_OTH';
		if ($selected == 'auto')   $selected='AC_OTH_AUTO';

		if (! empty($conf->global->AGENDA_ALWAYS_HIDE_AUTO)) unset($arraylist['AC_OTH_AUTO']);

		if (! empty($multiselect))
		{
			if(!is_array($selected) && !empty($selected)) $selected = explode(',', $selected);
			print $form->multiselectarray($htmlname, $arraylist, $selected, 0, 0, 'centpercent', 0, 0);
		}
		else
		{
			print $form->selectarray($htmlname, $arraylist, $selected);
		}

		if ($user->admin && empty($onlyautoornot) && $hideinfohelp <= 0)
		{
			print info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionarySetup").($hideinfohelp == -1 ? ". ".$langs->trans("YouCanSetDefaultValueInModuleSetup") : ''),1);
		}
	}


	function liste_array($active='',$idorcode='id',$excludetype='',$onlyautoornot=0, $morefilter='')
	{
		global $langs,$conf;
		$langs->load("commercial");

		$repid = array();
		$repcode = array();

		$sql = "SELECT id, code, libelle, module, type, color";
		$sql.= " FROM ".MAIN_DB_PREFIX."c_actioncomm";
		$sql.= " WHERE 1=1";
		if ($active != '') $sql.=" AND active=".$active;
		if (! empty($excludetype)) $sql.=" AND type NOT LIKE '%".$excludetype."%'";
		if ($morefilter) $sql.=" AND ".$morefilter;
		$sql.= " ORDER BY module, position";

		dol_syslog(get_class($this)."::liste_array", LOG_DEBUG);
		$resql=$this->db->query($sql);
		if ($resql)
		{
			$nump = $this->db->num_rows($resql);
			if ($nump)
			{
				$i = 0;
				while ($i < $nump)
				{
					$obj = $this->db->fetch_object($resql);

					$qualified=1;

					// $obj->type can be system, systemauto, module, moduleauto, xxx, xxxauto
					if ($qualified && $onlyautoornot && preg_match('/^system/',$obj->type) && ! preg_match('/^AC_OTH/',$obj->code)) $qualified=0;	// We discard detailed system events. We keep only the 2 generic lines (AC_OTH and AC_OTH_AUTO)

					if ($qualified && $obj->module)
					{
						if ($obj->module == 'invoice' && ! $conf->facture->enabled)	 $qualified=0;
						if ($obj->module == 'order'   && ! $conf->commande->enabled) $qualified=0;
						if ($obj->module == 'propal'  && ! $conf->propal->enabled)	 $qualified=0;
						if ($obj->module == 'invoice_supplier' && ! $conf->fournisseur->enabled)   $qualified=0;
						if ($obj->module == 'order_supplier'   && ! $conf->fournisseur->enabled)   $qualified=0;
						if ($obj->module == 'shipping'  && ! $conf->expedition->enabled)	 $qualified=0;
					}

					if ($qualified)
					{
						$code=$obj->code;
						if ($onlyautoornot && $code == 'AC_OTH') $code='AC_MANUAL';
						if ($onlyautoornot && $code == 'AC_OTH_AUTO') $code='AC_AUTO';
						$transcode=$langs->trans("Action".$code);
						$repid[$obj->id] = ($transcode!="Action".$code?$transcode:$langs->trans($obj->libelle));
						$repcode[$obj->code] = ($transcode!="Action".$code?$transcode:$langs->trans($obj->libelle));
						if ($onlyautoornot && preg_match('/^module/',$obj->type) && $obj->module) $repcode[$obj->code].=' ('.$langs->trans("Module").': '.$obj->module.')';
					}
					$i++;
				}
			}
			if ($idorcode == 'id') $this->liste_array=$repid;
			if ($idorcode == 'code') $this->liste_array=$repcode;
			return $this->liste_array;
		}
		else
		{
			$this->error=$this->db->lasterror();
			return -1;
		}
	}

	function form_select_status_action($formname,$selected,$canedit=1,$htmlname='complete',$showempty=0,$onlyselect=0)
	{
		global $langs,$conf;

		$listofstatus = array(
				'-1' => $langs->trans("ActionNotApplicable"),
				'0' => $langs->trans("ActionRunningNotStarted"),
				'50' => $langs->trans("ActionRunningShort"),
				'100' => $langs->trans("ActionDoneShort")
		);
		// +ActionUncomplete

		if (! empty($conf->use_javascript_ajax))
		{
			print "\n";
			print "<script type=\"text/javascript\">
                var htmlname = '".$htmlname."';

                $(document).ready(function () {
                	select_status();

                    $('#select' + htmlname).change(function() {
                        select_status();
                    });
                    // FIXME use another method for update combobox
                    //$('#val' + htmlname).change(function() {
                        //select_status();
                    //});
                });

                function select_status() {
                    var defaultvalue = $('#select' + htmlname).val();
                    var percentage = $('input[name=percentage]');
                    var selected = '".(isset($selected)?$selected:'')."';
                    var value = (selected>0?selected:(defaultvalue>=0?defaultvalue:''));

                    percentage.val(value);

                    if (defaultvalue == -1) {
						percentage.prop('disabled', true);
                        $('.hideifna').hide();
                    }
                    else if (defaultvalue == 0) {
						percentage.val(0);
						percentage.prop('disabled', true);
                        $('.hideifna').show();
                    }
                    else if (defaultvalue == 100) {
						percentage.val(100);
						percentage.prop('disabled', true);
                        $('.hideifna').show();
                    }
                    else {
                    	if (defaultvalue == 50 && (percentage.val() == 0 || percentage.val() == 100)) { percentage.val(50) };
                    	percentage.removeAttr('disabled');
                        $('.hideifna').show();
                    }
                }
                </script>\n";
		}
		if (! empty($conf->use_javascript_ajax) || $onlyselect)
		{
			//var_dump($selected);
			if ($selected == 'done') $selected='100';
			print '<select '.($canedit?'':'disabled ').'name="'.$htmlname.'" id="select'.$htmlname.'" class="flat">';
			if ($showempty) print '<option value=""'.($selected == ''?' selected':'').'></option>';
			foreach($listofstatus as $key => $val)
			{
				print '<option value="'.$key.'"'.(($selected == $key && strlen($selected) == strlen($key)) || (($selected > 0 && $selected < 100) && $key == '50') ? ' selected' : '').'>'.$val.'</option>';
				if ($key == '50' && $onlyselect == 2)
				{
					print '<option value="todo"'.($selected == 'todo' ? ' selected' : '').'>'.$langs->trans("ActionUncomplete").' ('.$langs->trans("ActionRunningNotStarted")."+".$langs->trans("ActionRunningShort").')</option>';
				}
			}
			print '</select>';
			if ($selected == 0 || $selected == 100) $canedit=0;

			if (empty($onlyselect))
			{
				print ' <input type="text" id="val'.$htmlname.'" name="percentage" class="flat hideifna" value="'.($selected>=0?$selected:'').'" size="2"'.($canedit&&($selected>=0)?'':' disabled').'>';
				print '<span class="hideonsmartphone hideifna">%</span>';
			}
		}
		else
		{
			print ' <input type="text" id="val'.$htmlname.'" name="percentage" class="flat" value="'.($selected>=0?$selected:'').'" size="2"'.($canedit?'':' disabled').'>%';
		}
	}

	public function fetchallrecursivesupplier_invoices($sortorder = '', $sortfield = '', $limit = 0, $offset = 0, $filter = array())
	{
		$sql = "SELECT ";
		$sql .= "f.rowid ";
		$sql .= " FROM " . MAIN_DB_PREFIX . "facture_fourn as f";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "facture_fourn_extrafields as ef ON ef.fk_object=f.rowid";

		$sql .= " WHERE f.entity IN (" . getEntity('projet') . ")";

		if (is_array($filter)) {
			foreach ( $filter as $key => $value ) {
				if ($key == 'f.rowid' || $key == 'ef.recur' || $key == 'ef.origin') {
					$sql .= ' AND ' . $key . ' = ' . $value;
				} elseif ($key == 'f.fk_statut') {
					$sql .= ' AND ' . $key . ' IN (' . implode(',', $value) . ')';
				} elseif ($key == 'f.datef' ) {
					$sql .= ' AND ' . $key . ' BETWEEN '.$value;
				} elseif ($key == 'f.datef<=' ) {
					$sql .= ' AND ' . $key . '\''.$this->db->idate($value).'\'';
				} elseif ($key == 'f.datef>=' ) {
					$sql .= ' AND ' . $key . '\''.$this->db->idate($value).'\'';
				} else {
					$sql .= ' AND ' . $key . ' LIKE \'%' . $this->db->escape($value) . '%\'';
				}
			}
		}
		if (! empty($sortfield)) {
			$sql .= $this->db->order($sortfield, $sortorder);
		}

		if (! empty($limit)) {
			$sql .= ' ' . $this->db->plimit($limit + 1, $offset);
		}

		dol_syslog(get_class($this) . "::" . __METHOD__, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if($resql){
			while($obj=$this->db->fetch_object($resql)){
				$result[] = $obj->rowid;
			}
		}
		return $result;
	}

	public function validateperiodicinvoices()
	{
		global $langs,$conf,$user;

		require_once DOL_DOCUMENT_ROOT . '/fourn/class/fournisseur.facture.class.php';
		$fact = New FactureFournisseur($this->db);

		$result = array();
		$sql = "SELECT ";
		$sql .= "f.rowid ";
		$sql .= " FROM " . MAIN_DB_PREFIX . "facture_fourn as f";
		$sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "facture_fourn_extrafields as ef ON ef.fk_object=f.rowid";
		$sql .= " WHERE f.entity IN (" . getEntity('projet') . ")";
		$sql .= " AND f.fk_statut = 0";
		$sql .= " AND f.datef <= NOW()";
		$sql .= " AND ef.recur = 1";

		$resql = $this->db->query($sql);
		if($resql){
			while($obj=$this->db->fetch_object($resql)){
				$fact->fetch($obj->rowid);
				$fact->update_note('Facture auto validée par batch de nuit le '.dol_print_date(dol_now()),'_private');
				$fact->validate($user);
				$this->output.= 'Facture ' . $fact->ref . ' Validée' . "\n";
			}
		}
		return 0;
	}

}

class Vehicules extends CommonObject
{	
	var $db; // !< To store db handler
	var $error; // !< To return error code (or message)
	var $errors = array (); // !< To return several error codes (or messages)
	var $element = 'vehicules'; // !< Id that identify managed objects
	var $table_element = 'tvi_vehicules';
	
	
	Public $id;
	Public $parc;
	public $type;
	public $immat;
	public $chassis;
	public $marque;
	public $active;
	
	
	function create($user, $notrigger = 0) {
		global $conf, $langs;
		$error = 0;
		
		// Clean parameters
		
		if (isset($this->parc))
			$this->parc = trim($this->parc);
		if (isset($this->type))
			$this->type = trim($this->type);
		if (isset($this->immat))
			$this->immat = trim($this->immat);
		if (isset($this->chassis))
			$this->chassis = trim($this->chassis);
		if (isset($this->marque))
			$this->marque = trim($this->marque);
		if (isset($this->active))
			$this->active = trim($this->actvie);
		
		// Check parameters
		if (empty($this->parc)) {
			$error ++;
			$this->errors[] = 'Saisie du N� de parc obligatoire';
		}
		if (empty($this->type)) {
			$error ++;
			$this->errors[] = 'Saisie du type obligatoire';
		}
		if (empty($this->immat)) {
			$error ++;
			$this->errors[] = "Saisie de l'immatriculation obligatoire";
		}
		if (empty($this->chassis)) {
			$error ++;
			$this->errors[] = "saisie du N� de chassis obligatoire";
		}
		if (empty($this->marque)) {
			$error ++;
			$this->errors[] = "Saisie de la marque obligatoire";
		}
		
		
		if (! $error) {
			// Insert request
			$sql = "INSERT INTO " . MAIN_DB_PREFIX . $this->table_element . "(";
			
			$sql .= "parc, ";
			$sql .= "type, ";
			$sql .= "immat, ";
			$sql .= "chassis, ";
			$sql .= "marque, ";
			$sql .= "active";
			
			$sql .= ") VALUES (";
			
			$sql .= " " . (! isset($this->ref) ? 'NULL' : "'" . $this->db->escape($this->ref) . "'") . ",";
			$sql .= " " . (! isset($this->fk_user_resp) ? 'NULL' : "'" . $this->fk_user_resp . "'") . ",";
			$sql .= " " . (! isset($this->fk_soc) ? 'NULL' : "'" . $this->fk_soc . "'") . ",";
			$sql .= " " . (! isset($this->fk_ctm) ? 'NULL' : "'" . $this->fk_ctm . "'") . ",";
			$sql .= " " . (! isset($this->fk_c_type) ? 'NULL' : "'" . $this->fk_c_type . "'") . ",";
			$sql .= " " . (! isset($this->year) ? 'NULL' : "'" . $this->year . "'") . ",";
			$sql .= " " . (empty($this->description) ? 'NULL' : "'" . $this->db->escape($this->description) . "'") . ",";
			$sql .= " " . $user->id . ",";
			$sql .= " '" . $this->db->idate(dol_now()) . "',";
			$sql .= " " . $user->id . ",";
			$sql .= " '" . $this->db->idate(dol_now()) . "'";
			$sql .= ")";
			
			$this->db->begin();
			
			dol_syslog(get_class($this) . "::create sql=" . $sql, LOG_DEBUG);
			$resql = $this->db->query($sql);
			if (! $resql) {
				$error ++;
				$this->errors[] = "Error " . $this->db->lasterror();
			}
		}
	}
}