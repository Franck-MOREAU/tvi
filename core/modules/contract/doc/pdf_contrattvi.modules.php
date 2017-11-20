<?php
/* Copyright (C) 2003		Rodolphe Quiedeville		<rodolphe@quiedeville.org>
 * Copyright (C) 2004-2010	Laurent Destailleur			<eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012	Regis Houssin				<regis.houssin@capnetworks.com>
 * Copyright (C) 2008		Raphael Bertrand (Resultic)	<raphael.bertrand@resultic.fr>
 * Copyright (C) 2011		Fabrice CHERRIER
 * Copyright (C) 2013       Philippe Grand	            <philippe.grand@atoo-net.com>
 * Copyright (C) 2015       Marcos García               <marcosgdf@gmail.com>
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
 *	\file       htdocs/core/modules/contract/doc/pdf_strato.modules.php
 *	\ingroup    ficheinter
 *	\brief      Strato contracts template class file
 */
require_once DOL_DOCUMENT_ROOT.'/core/modules/contract/modules_contract.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/pdf.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/extrafields.class.php';
dol_include_once('/tvi/class/tvi.class.php');


/**
 *	Class to build contracts documents with model Strato
 */
class pdf_contrattvi extends ModelePDFContract
{
	var $db;
	var $name;
	var $description;
	var $type;

	var $phpmin = array(4,3,0); // Minimum version of PHP required by module
	var $version = 'dolibarr';

	var $page_largeur;
	var $page_hauteur;
	var $format;
	var $marge_gauche;
	var	$marge_droite;
	var	$marge_haute;
	var	$marge_basse;

	/**
	 * Issuer
	 * @var Societe
	 */
	public $emetteur;

	/**
	 * Recipient
	 * @var Societe
	 */
	public $recipient;

	/**
	 *	Constructor
	 *
	 *  @param		DoliDB		$db      Database handler
	 */
	function __construct($db)
	{
		global $conf,$langs,$mysoc;

		$this->db = $db;
		$this->name = 'contrattvi';
		$this->description = $langs->trans("Contrat TVI");

		// Dimension page pour format A4
		$this->type = 'pdf';
		$formatarray=pdf_getFormat();
		$this->page_largeur = $formatarray['width'];
		$this->page_hauteur = $formatarray['height'];
		$this->format = array($this->page_largeur,$this->page_hauteur);
		$this->marge_gauche=isset($conf->global->MAIN_PDF_MARGIN_LEFT)?$conf->global->MAIN_PDF_MARGIN_LEFT:10;
		$this->marge_droite=isset($conf->global->MAIN_PDF_MARGIN_RIGHT)?$conf->global->MAIN_PDF_MARGIN_RIGHT:10;
		$this->marge_haute =isset($conf->global->MAIN_PDF_MARGIN_TOP)?$conf->global->MAIN_PDF_MARGIN_TOP:10;
		$this->marge_basse =isset($conf->global->MAIN_PDF_MARGIN_BOTTOM)?$conf->global->MAIN_PDF_MARGIN_BOTTOM:10;

		$this->option_logo = 1;                    // Affiche logo
		$this->option_tva = 0;                     // Gere option tva FACTURE_TVAOPTION
		$this->option_modereg = 0;                 // Affiche mode reglement
		$this->option_condreg = 0;                 // Affiche conditions reglement
		$this->option_codeproduitservice = 0;      // Affiche code produit-service
		$this->option_multilang = 0;               // Dispo en plusieurs langues
		$this->option_draft_watermark = 1;		   //Support add of a watermark on drafts

		// Get source company
		$this->emetteur=$mysoc;
		if (empty($this->emetteur->country_code)) $this->emetteur->country_code=substr($langs->defaultlang,-2);    // By default, if not defined

		// Define position of columns
		$this->posxdesc=$this->marge_gauche+1;
	}

	/**
     *  Function to build pdf onto disk
     *
     *  @param		CommonObject	$object				Id of object to generate
     *  @param		object			$outputlangs		Lang output object
     *  @param		string			$srctemplatepath	Full path of source filename for generator using a template file
     *  @param		int				$hidedetails		Do not show line details
     *  @param		int				$hidedesc			Do not show desc
     *  @param		int				$hideref			Do not show ref
     *  @return		int									1=OK, 0=KO
	 */
	function write_file($object,$outputlangs,$srctemplatepath='',$hidedetails=0,$hidedesc=0,$hideref=0)
	{
		global $user,$langs,$conf,$hookmanager,$mysoc;

		if (! is_object($outputlangs)) $outputlangs=$langs;
		// For backward compatibility with FPDF, force output charset to ISO, because FPDF expect text to be encoded in ISO
		if (! empty($conf->global->MAIN_USE_FPDF)) $outputlangs->charset_output='ISO-8859-1';

		$outputlangs->load("main");
		$outputlangs->load("dict");
		$outputlangs->load("companies");
		$outputlangs->load("contracts");

		if ($conf->contrat->dir_output)
		{
            $object->fetch_thirdparty();

			// Definition of $dir and $file
			if ($object->specimen)
			{
				$dir = $conf->contrat->dir_output;
				$file = $dir . "/SPECIMEN.pdf";
			}
			else
			{
				$objectref = dol_sanitizeFileName($object->ref);
				$dir = $conf->contrat->dir_output . "/" . $objectref;
				$file = $dir . "/" . $objectref . ".pdf";
			}

			if (! file_exists($dir))
			{
				if (dol_mkdir($dir) < 0)
				{
					$this->error=$outputlangs->trans("ErrorCanNotCreateDir",$dir);
					return 0;
				}
			}

			if (file_exists($dir))
			{
				// Add pdfgeneration hook
				if (! is_object($hookmanager))
				{
					include_once DOL_DOCUMENT_ROOT.'/core/class/hookmanager.class.php';
					$hookmanager=new HookManager($this->db);
				}
				$hookmanager->initHooks(array('pdfgeneration'));
				$parameters=array('file'=>$file,'object'=>$object,'outputlangs'=>$outputlangs);
				global $action;
				$reshook=$hookmanager->executeHooks('beforePDFCreation',$parameters,$object,$action);    // Note that $action and $object may have been modified by some hooks

                $pdf=pdf_getInstance($this->format);
                $default_font_size = pdf_getPDFFontSize($outputlangs)-2;	// Must be after pdf_getInstance
                $heightforinfotot = 50;	// Height reserved to output the info and total part
		        $heightforfreetext= (isset($conf->global->MAIN_PDF_FREETEXT_HEIGHT)?$conf->global->MAIN_PDF_FREETEXT_HEIGHT:5);	// Height reserved to output the free text on last page
	            $heightforfooter = $this->marge_basse + 8;	// Height reserved to output the footer (value include bottom margin)
                $pdf->SetAutoPageBreak(1,0);

                if (class_exists('TCPDF'))
                {
                    $pdf->setPrintHeader(false);
                    $pdf->setPrintFooter(false);
                }
                $pdf->SetFont(pdf_getPDFFont($outputlangs));


				$pdf->Open();
				$pagenb=0;
				$pdf->SetDrawColor(128,128,128);

				$pdf->SetTitle($outputlangs->convToOutputCharset($object->ref));
				$pdf->SetSubject($outputlangs->transnoentities("ContractCard"));
				$pdf->SetCreator("Dolibarr ".DOL_VERSION);
				$pdf->SetAuthor($outputlangs->convToOutputCharset($user->getFullName($outputlangs)));
				$pdf->SetKeyWords($outputlangs->convToOutputCharset($object->ref)." ".$outputlangs->transnoentities("ContractCard")." ".$outputlangs->convToOutputCharset($object->thirdparty->name));
				if (! empty($conf->global->MAIN_DISABLE_PDF_COMPRESSION)) $pdf->SetCompression(false);

				$pdf->SetMargins($this->marge_gauche, $this->marge_haute, $this->marge_droite);   // Left, Top, Right

				// Add Pages from models
				$infile=$conf->tvi->dir_output.'/modelpdf/modelcontract.pdf';
				if (file_exists($infile) && is_readable($infile)) {
					$pagecount = $pdf->setSourceFile($infile);
					for($i = 1; $i <= $pagecount; $i ++) {
						$tplIdx = $pdf->importPage($i);
						if ($tplIdx!==false) {
							$s = $pdf->getTemplatesize($tplIdx);
							$pdf->AddPage($s['h'] > $s['w'] ? 'P' : 'L');
							$pdf->useTemplate($tplIdx);
						} else {
							setEventMessages(null, array($infile.' cannot be added, probably protected PDF'),'warnings');
						}
					}
				}

				$pdf->SetPage(1);

				$extrafields = new ExtraFields($this->db);
				$extralabels = $extrafields->fetch_name_optionals_label($object->table_element, true);

				$object->fetch_optionals($object->id, $extralabels);

				$tvi = new Tvi($this->db);

				//Carac client
				$pdf->SetFont('','', $default_font_size);
				$pdf->SetXY(23, 71.5);
				$adress = $outputlangs->convToOutputCharset($object->thirdparty->name)."\n";
				$adress .= $outputlangs->convToOutputCharset(dol_format_address($object->thirdparty));
				$pdf->MultiCell(80, 4, $adress,0,'L');

				$pdf->SetXY(18, 91.5);
				$str = $outputlangs->convToOutputCharset($object->thirdparty->idprof2)."\n";
				$pdf->MultiCell(80, 0, $str,0,'L');

				$pdf->SetXY(63, 91.5);
				$str = $outputlangs->convToOutputCharset($object->thirdparty->phone)."\n";
				$pdf->MultiCell(80, 0, $str,0,'L');

				//Carac véhicule
				$pdf->SetXY(14, 112);
				$str = $outputlangs->convToOutputCharset($extrafields->showOutputField('modele', $object->array_options['options_type']))."\n";
				$pdf->MultiCell(80, 0, $str,0,'L');

				$pdf->SetXY(68, 112);
				$str = $outputlangs->convToOutputCharset($extrafields->showOutputField('modele', $object->array_options['options_parc']))."\n";
				$pdf->MultiCell(80, 0, $str,0,'L');

				$pdf->SetXY(120, 112);
				$str = $outputlangs->convToOutputCharset($extrafields->showOutputField('marque', $object->array_options['options_marque']))."\n";
				$pdf->MultiCell(80, 0, $str,0,'L');

				$pdf->SetXY(38, 119.5);
				$str = $outputlangs->convToOutputCharset($extrafields->showOutputField('immat', $object->array_options['options_immat']))."\n";
				$pdf->MultiCell(80, 0, $str,0,'L');

				$pdf->SetXY(123, 119.5);
				$str = $outputlangs->convToOutputCharset($extrafields->showOutputField('chassis', $object->array_options['options_chassis']))."\n";
				$pdf->MultiCell(80, 0, $str,0,'L');

				//Date de loc
				$array_filter = array();
				$array_filter['p.rowid'] = $conf->global->TVI_CONTRACT_LOC_PRODUCT;
				$array_filter['d.statut'] = array(
						0,1,4
				);
				$array_filter['t.rowid'] = $object->id;

				$result = $tvi->fetchContractLines('', '', 1, 0, $array_filter);
				if ($result < 0) {
					dol_syslog(var_export($tvi->errors,true),LOG_ERR);
					setEventMessages(null, $tvi->errors, 'errors');
				}
				$date_cnt_start='';
				$date_cnt_end='';
				$loyer=0;
				if (count($tvi->lines_contract)>0) {

					$date_cnt_start = empty($tvi->lines_contract[0]->date_ouverture) ? $tvi->lines_contract[0]->date_ouverture_prevue : $tvi->lines_contract[0]->date_ouverture;
					$date_cnt_end = empty($tvi->lines_contract[0]->date_cloture) ? $tvi->lines_contract[0]->date_fin_validite : $tvi->lines_contract[0]->date_cloture;

					$pdf->SetXY(30, 136.2);
					$str = $outputlangs->convToOutputCharset(dol_print_date($date_cnt_start))."\n";
					$pdf->MultiCell(80, 0, $str,0,'L');

					$pdf->SetXY(30, 144);
					$str = $outputlangs->convToOutputCharset(dol_print_date($date_cnt_end))."\n";
					$pdf->MultiCell(80, 0, $str,0,'L');

					$loyer=$tvi->lines_contract[0]->price_ht;
				}

				// Assurance responsabilité civile
				$array_filter = array();
				$array_filter['p.rowid'] = $conf->global->TVI_CONTRACT_ASS_PRODUCT;
				$array_filter['d.statut'] = array(
						0,1,4
				);
				$array_filter['t.rowid'] = $object->id;

				$result = $tvi->fetchContractLines('', '', 1, 0, $array_filter);
				if ($result < 0) {
					dol_syslog(var_export($tvi->errors,true),LOG_ERR);
					setEventMessages(null, $tvi->errors, 'errors');
				}
				if (count($tvi->lines_contract)>0) {

					$pdf->SetXY(82, 157);
					$str = $outputlangs->convToOutputCharset('OUI')."\n";
					$pdf->MultiCell(80, 0, $str,0,'L');

				} else {
					$pdf->SetXY(82, 157);
					$str = $outputlangs->convToOutputCharset('NON')."\n";
					$pdf->MultiCell(80, 0, $str,0,'L');
				}

				//Assurance Domage
				$array_filter = array();
				$array_filter['p.rowid'] = $conf->gloabl->TVI_CONTRACT_ASSDOM_PRODUCT;
				$array_filter['d.statut'] = array(
						0,1,4
				);
				$array_filter['t.rowid'] = $object->id;

				$result = $tvi->fetchContractLines('', '', 1, 0, $array_filter);
				if ($result < 0) {
					dol_syslog(var_export($tvi->errors,true),LOG_ERR);
					setEventMessages(null, $tvi->errors, 'errors');
				}
				if (count($tvi->lines_contract)>0) {

					$pdf->SetXY(82, 164.5);
					$str = $outputlangs->convToOutputCharset('OUI')."\n";
					$pdf->MultiCell(80, 0, $str,0,'L');

				} else {
					$pdf->SetXY(82, 164.5);
					$str = $outputlangs->convToOutputCharset('NON')."\n";
					$pdf->MultiCell(80, 0, $str,0,'L');
				}

				//Km Sup
				$pdf->SetFont('','', $default_font_size-1);
				$pdf->SetXY(48, 173.8);
				$str = $outputlangs->convToOutputCharset($object->array_options['options_kmsup'])."\n";
				$pdf->MultiCell(80, 0, $str,0,'L');

				//€ Km Sup
				$array_filter = array();
				$array_filter['p.rowid'] = $conf->global->TVI_CONTRACT_KM_PRODUCT;
				$array_filter['d.statut'] = array(
						0,1,4
				);
				$array_filter['t.rowid'] = $object->id;

				$result = $tvi->fetchContractLines('', '', 1, 0, $array_filter);
				if ($result < 0) {
					dol_syslog(var_export($tvi->errors,true),LOG_ERR);
					setEventMessages(null, $tvi->errors, 'errors');
				}
				if (count($tvi->lines_contract)>0) {

					$pdf->SetXY(115, 173.8);
					$str = $outputlangs->convToOutputCharset($tvi->lines_contract[0]->price_ht)."\n";
					$pdf->MultiCell(80, 0, $str,0,'L');
				}

				//1er loyer
				if (!empty($date_cnt_start)) {
					$first_day_of_month=dol_mktime(0, 0, 0, dol_print_date($date_cnt_start, '%m'), 1, dol_print_date($date_cnt_start, '%Y'));
					$next_echeance = dol_time_plus_duree($first_day_of_month, $object->array_options['options_frequency'], $object->array_options['options_unit_frequency']);
					$next_echeance = dol_time_plus_duree($next_echeance, -1, 'd');

					$pdf->SetXY(46, 177.7);
					$str = $outputlangs->convToOutputCharset(dol_print_date($date_cnt_start))."\n";
					$pdf->MultiCell(80, 0, $str,0,'L');

					$pdf->SetXY(73, 177.7);
					$str = $outputlangs->convToOutputCharset(dol_print_date($next_echeance))."\n";
					$pdf->MultiCell(80, 0, $str,0,'L');
				}

				$pdf->SetFont('','', $default_font_size);

				//DU AU
				if (!empty($date_cnt_start)) {
					$pdf->SetXY(7, 190);
					$str = $outputlangs->convToOutputCharset(dol_print_date($date_cnt_start))."\n";
					$pdf->MultiCell(80, 0, $str,0,'L');
				}
				if (!empty($date_cnt_end)) {
					$pdf->SetXY(36, 190);
					$str = $outputlangs->convToOutputCharset(dol_print_date($date_cnt_end))."\n";
					$pdf->MultiCell(80, 0, $str,0,'L');
				}

				//Mensualité
				if (!empty($date_cnt_start) && !empty($date_cnt_end)) {

					$DtSt=new DateTime();
					$DtSt->setTimestamp($date_cnt_start);
					$DtEnd=new DateTime();
					$DtEnd->setTimestamp($date_cnt_end);

					$DateInterval = $DtSt->diff($DtEnd);
					$str=$DateInterval->format('%m');
					if (empty($str)) {
						$str=1;
					}
					$str.= ' Mois';
					$pdf->SetXY(78, 190);
					$str = $outputlangs->convToOutputCharset($str);
					$pdf->MultiCell(80, 0, $str,0,'L');
				}

				//loyer
				if (!empty($loyer)) {
					$pdf->SetXY(116, 190);
					$str = $outputlangs->convToOutputCharset($loyer) . '€ HT ';
					$pdf->MultiCell(80, 0, $str,0,'L');
				}


				//Tableau charges locataires
				//Taxe essieux
				$array_filter = array();
				$array_filter['p.rowid'] = $conf->global->TVI_CONTRACT_TAXES_PRODUCT;
				$array_filter['d.statut'] = array(
						0,1,4
				);
				$array_filter['t.rowid'] = $object->id;

				$result = $tvi->fetchContractLines('', '', 1, 0, $array_filter);
				if ($result < 0) {
					dol_syslog(var_export($tvi->errors,true),LOG_ERR);
					setEventMessages(null, $tvi->errors, 'errors');
				}
				if (count($tvi->lines_contract)>0 && $tvi->lines_contract[0]->price_ht!=0) {
					$pdf->SetXY(103,210);
					$str = $outputlangs->convToOutputCharset($extrafields->showOutputField('unit_frequency', $object->array_options['options_unit_frequency']));
					$pdf->MultiCell(80, 0, $str,0,'L');

					$pdf->SetXY(128,210);
					$str = $outputlangs->convToOutputCharset($tvi->lines_contract[0]->price_ht.' €');
					$pdf->MultiCell(80, 0, $str,0,'L');
				} elseif (count($tvi->lines_contract)>0 && $tvi->lines_contract[0]->price_ht==0) {
					$pdf->SetXY(128,210);
					$str = $outputlangs->convToOutputCharset('"Inclus"');
					$pdf->MultiCell(80, 0, $str,0,'L');
				} else {
					$pdf->SetXY(128,210);
					$str = $outputlangs->convToOutputCharset('"Client"');
					$pdf->MultiCell(80, 0, $str,0,'L');
				}

				//Assurance domage
				$array_filter = array();
				$array_filter['d.fk_product'] = $conf->gloabl->TVI_CONTRACT_ASSDOM_PRODUCT;
				$array_filter['d.statut'] = array(
						0,1,4
				);
				$array_filter['t.rowid'] = $object->id;

				$result = $tvi->fetchContractLines('', '', 1, 0, $array_filter);
				if ($result < 0) {
					dol_syslog(var_export($tvi->errors,true),LOG_ERR);
					setEventMessages(null, $tvi->errors, 'errors');
				}
				if (count($tvi->lines_contract)>0 && $tvi->lines_contract[0]->price_ht!=0) {
					$pdf->SetXY(103,214);
					$str = $outputlangs->convToOutputCharset($extrafields->showOutputField('unit_frequency', $object->array_options['options_unit_frequency']));
					$pdf->MultiCell(80, 0, $str,0,'L');

					$pdf->SetXY(128,214);
					$str = $outputlangs->convToOutputCharset($tvi->lines_contract[0]->price_ht.' €');
					$pdf->MultiCell(80, 0, $str,0,'L');
				} elseif (count($tvi->lines_contract)>0 && $tvi->lines_contract[0]->price_ht==0) {
					$pdf->SetXY(128,214);
					$str = $outputlangs->convToOutputCharset('"Inclus"');
					$pdf->MultiCell(80, 0, $str,0,'L');
				} else {
					$pdf->SetXY(128,214);
					$str = $outputlangs->convToOutputCharset('"Client"');
					$pdf->MultiCell(80, 0, $str,0,'L');
				}


				//Vitesse technique
				$array_filter = array();
				$array_filter['p.rowid'] = $conf->gloabl->TVI_CONTRACT_CT_PRODUCT;;
				$array_filter['d.statut'] = array(
						0,1,4
				);
				$array_filter['t.rowid'] = $object->id;

				$result = $tvi->fetchContractLines('', '', 1, 0, $array_filter);
				if ($result < 0) {
					dol_syslog(var_export($tvi->errors,true),LOG_ERR);
					setEventMessages(null, $tvi->errors, 'errors');
				}
				if (count($tvi->lines_contract)>0 && $tvi->lines_contract[0]->price_ht!=0) {
					$pdf->SetXY(103,218.5);
					$str = $outputlangs->convToOutputCharset($extrafields->showOutputField('unit_frequency', $object->array_options['options_unit_frequency']));
					$pdf->MultiCell(80, 0, $str,0,'L');

					$pdf->SetXY(128,218.5);
					$str = $outputlangs->convToOutputCharset($tvi->lines_contract[0]->price_ht.' €');
					$pdf->MultiCell(80, 0, $str,0,'L');
				} elseif (count($tvi->lines_contract)>0 && $tvi->lines_contract[0]->price_ht==0) {
					$pdf->SetXY(128,218.5);
					$str = $outputlangs->convToOutputCharset('"Inclus"');
					$pdf->MultiCell(80, 0, $str,0,'L');
				} else {
					$pdf->SetXY(128,218.5);
					$str = $outputlangs->convToOutputCharset('"Client"');
					$pdf->MultiCell(80, 0, $str,0,'L');
				}

				//Entretient Pneumatique
				$array_filter = array();
				$array_filter['p.rowid'] = $conf->global->TVI_CONTRACT_ENTREP_PRODUCT;
				$array_filter['d.statut'] = array(
						0,1,4
				);
				$array_filter['t.rowid'] = $object->id;

				$result = $tvi->fetchContractLines('', '', 1, 0, $array_filter);
				if ($result < 0) {
					dol_syslog(var_export($tvi->errors,true),LOG_ERR);
					setEventMessages(null, $tvi->errors, 'errors');
				}
				if (count($tvi->lines_contract)>0 && $tvi->lines_contract[0]->price_ht!=0) {
					$pdf->SetXY(103,222.5);
					$str = $outputlangs->convToOutputCharset($extrafields->showOutputField('unit_frequency', $object->array_options['options_unit_frequency']));
					$pdf->MultiCell(80, 0, $str,0,'L');

					$pdf->SetXY(128,222.5);
					$str = $outputlangs->convToOutputCharset($tvi->lines_contract[0]->price_ht.' €');
					$pdf->MultiCell(80, 0, $str,0,'L');
				} elseif (count($tvi->lines_contract)>0 && $tvi->lines_contract[0]->price_ht==0) {
					$pdf->SetXY(128,222.5);
					$str = $outputlangs->convToOutputCharset('"Inclus"');
					$pdf->MultiCell(80, 0, $str,0,'L');
				} else {
					$pdf->SetXY(128,222.5);
					$str = $outputlangs->convToOutputCharset('"Client"');
					$pdf->MultiCell(80, 0, $str,0,'L');
				}

				//Dépannage - remorquage
				$array_filter = array();
				$array_filter['p.rowid'] = $conf->global->TVI_CONTRCT_DEPREM_PRODUCT;
				$array_filter['d.statut'] = array(
						0,1,4
				);
				$array_filter['t.rowid'] = $object->id;

				$result = $tvi->fetchContractLines('', '', 1, 0, $array_filter);
				if ($result < 0) {
					dol_syslog(var_export($tvi->errors,true),LOG_ERR);
					setEventMessages(null, $tvi->errors, 'errors');
				}
				if (count($tvi->lines_contract)>0 && $tvi->lines_contract[0]->price_ht!=0) {
					$pdf->SetXY(103,226.5);
					$str = $outputlangs->convToOutputCharset($extrafields->showOutputField('unit_frequency', $object->array_options['options_unit_frequency']));
					$pdf->MultiCell(80, 0, $str,0,'L');

					$pdf->SetXY(128,226.5);
					$str = $outputlangs->convToOutputCharset($tvi->lines_contract[0]->price_ht.' €');
					$pdf->MultiCell(80, 0, $str,0,'L');
				} elseif (count($tvi->lines_contract)>0 && $tvi->lines_contract[0]->price_ht==0) {
					$pdf->SetXY(128,226.5);
					$str = $outputlangs->convToOutputCharset('"Inclus"');
					$pdf->MultiCell(80, 0, $str,0,'L');
				} else {
					$pdf->SetXY(128,226.5);
					$str = $outputlangs->convToOutputCharset('"Client"');
					$pdf->MultiCell(80, 0, $str,0,'L');
				}

				$pdf->Close();

				$pdf->Output($file,'F');

				// Add pdfgeneration hook
				if (! is_object($hookmanager))
				{
					include_once DOL_DOCUMENT_ROOT.'/core/class/hookmanager.class.php';
					$hookmanager=new HookManager($this->db);
				}
				$hookmanager->initHooks(array('pdfgeneration'));
				$parameters=array('file'=>$file,'object'=>$object,'outputlangs'=>$outputlangs);
				global $action;
				$reshook=$hookmanager->executeHooks('afterPDFCreation',$parameters,$this,$action);    // Note that $action and $object may have been modified by some hooks

				if (! empty($conf->global->MAIN_UMASK))
				@chmod($file, octdec($conf->global->MAIN_UMASK));

				return 1;
			}
			else
			{
				$this->error=$langs->trans("ErrorCanNotCreateDir",$dir);
				return 0;
			}
		}
		else
		{
			$this->error=$langs->trans("ErrorConstantNotDefined","CONTRACT_OUTPUTDIR");
			return 0;
		}
		$this->error=$langs->trans("ErrorUnknown");
		return 0;   // Erreur par defaut
	}
}

