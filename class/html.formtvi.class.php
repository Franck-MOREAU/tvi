<?php
/* TVI
 * Copyright (C) 2016	Florian HENRY 		<florian.henry@atm-consulting.fr>
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
require_once DOL_DOCUMENT_ROOT . '/core/class/html.form.class.php';

/**
 * \file volvo/class/html.formvolvo.class.php
 * \brief Class for HML form
 */
class FormTvi extends Form
{
	public $resPrint = '';

	/**
	 * Display select
	 *
	 * @param string $htmlname select field
	 * @param int $selectid à preselectionner
	 * @param string $outputformat output format (html,stringarray)
	 * @return string select field depending $outputformat
	 */
	function select_dict($htmlname = '', $selectid, $tablename='', $fieldslabel=array(), $outputformat = 'multiselect',$empty = null,  $fieldid='rowid') {
		global $conf, $user, $langs;

		$out = '';

		$sql = 'SELECT '.$fieldid.', '.implode(' , ',$fieldslabel).' ';
		$sql .= 'FROM ' . MAIN_DB_PREFIX . $tablename. ' as dict';

		if ($outputformat == 'multiselect') {
			$arrayselect_multi = array();
		}

		dol_syslog(get_class($this) . "::" . __METHOD__, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql) {

			if ($outputformat == 'html') {
				$out .= '<select id="' . $htmlname . '" class="flat" name="' . $htmlname . '">';
			}

			if (! empty($empty)) {
				if ($outputformat == 'html') {
					$out .= '<option value=""></option>';
				} elseif ($outputformat == 'stringarray') {
					$out .= '0:,';
				}
			}

			while ( $obj = $this->db->fetch_object($resql) ) {

				$label_array=array();
				foreach($fieldslabel as $fieldlabel) {
					$label_array[]=$obj->$fieldlabel;
				}

				if ($outputformat == 'multiselect') {
					$arrayselect_multi[$obj->$fieldid] = implode('-',$label_array);
				}

				if ($outputformat == 'html') {

					if (($selectid > 0 || $selectid != '') && $selectid == $obj->$fieldid) {
						$out .= '<option value="' . $obj->$fieldid . '" selected="selected">' . implode('-',$label_array) . '</option>';
					} else {
						$out .= '<option value="' . $obj->$fieldid . '">' . implode('-',$label_array) . '</option>';
					}
				} elseif ($outputformat == 'stringarray') {
					$out .= $obj->rowid . ':' . str_replace(',', ' ', implode('-',$label_array)) . ',';
				}
			}

			if ($outputformat == 'html') {
				$out .= '</select>';
			}
		} else {
			setEventMessage(get_class($this) . "::" . __METHOD__ . " Error=" . $this->db->lasterror(), 'errors');
		}

		if ($outputformat == 'multiselect') {
			return $this->multiselectarray($htmlname, $arrayselect_multi, $selectid, 1, 0, '', 0, '161');
		}

		return $out;
	}
}