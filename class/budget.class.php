<?php

/**
 * Class TBudget
 */
dol_include_once('/budget/class/insurance.class.php');

class TBudget extends TObjetStd {
	public $date_debut;
	public $date_fin;
	public $fk_project;
	public $statut;
	public $TStatut;
	public $user_valid;
	public $user_reject;
	public $label;
	public $inactif;
	public $amount;
	public $amount_ca;
	public $amount_depense;
	public $amount_insurance;
	public $amount_production;
	public $amount_encours_n;
	public $amount_encours_n1;
	public $encours_taux;
	public $encours_n1;
	public $marge_globale;
	public $TResultat;
	public $TBudgetLine;
	public $TInsurance;
	public $PDOdb;
	
	function __construct() {
		global $langs;
		
        parent::set_table(MAIN_DB_PREFIX.'sig_budget');
		parent::add_champs('date_debut,date_fin',array('type'=>'date', 'index'=>true));
		parent::add_champs('fk_project',array('type'=>'integer', 'index'=>true));
		parent::add_champs('code_analytique',array('type'=>'varchar', 'index'=>true));
		parent::add_champs('statut,user_valid,user_reject',array('type'=>'integer'));
		parent::add_champs('amount,encours_n1',array('type'=>'float'));
		parent::_init_vars('label');
        parent::start();

		$this->setChild('TBudgetLine','fk_budget');

		$this->TStatut = array(
			0=>$langs->trans('Draft')
			,1=>$langs->trans('Validé')
			/*,2=>'En attente de validation'*/
			,3=>$langs->trans('Refusé')
			,4=>$langs->trans('Revu')
		);
		
		$this->amount_ca 			= 0;
		$this->amount_production 	= 0;
		$this->amount_depense 		= 0;
		$this->amount_encours_n 	= 0;
		$this->amount_encours_n1 	= 0;
		$this->encours_taux 		= 0;
		$this->mage_globale 		= 0;
		
		$this->TResultat 			= array();
		$this->TBudgetLine 			= array();
		$this->TInsurancePrice		= array();

	}
	
	function save(&$PDOdb) {
		
		$this->amount = 0;
		
		foreach($this->TBudgetLine as &$l) {
			
			$this->amount += $l->amount;
		}
		
		parent::save($PDOdb);
	}
	
	function load(&$PDOdb, $rowid) {
		parent::load($PDOdb, $rowid);
		$this->PDOdb = $PDOdb;

		$this->load_insurance();
		$this->load_amount();
	}
	
	function load_insurance() {
		$TInsurance			= TInsurance::getInsurance($this->PDOdb, $this->date_debut, $this->date_fin, 1);
		$this->TInsurance	= $TInsurance;
		$TPercentage		= array();
		
		$year 	= date('Y',$this->date_debut);
		$month 	= (int) date('m',$this->date_debut);
		
		foreach ($TInsurance as $insurance){
			if (!empty($insurance['category'])){
				foreach ($insurance['category'] as $category){
					foreach ($category['@bymonth'][$year][$month]['subcategory'] as $subcateg){
						$result = 0;
						$percentage = $subcateg['percentage'];
						foreach($this->TBudgetLine as $line) {
							if($line->code_compta === $subcateg['code_compta']) {
								$result = $line->amount * ($percentage/100);
								break;
							}
						}
						$this->TInsurancePrice[$percentage] += $result;
						$this->amount_insurance += $result;
					}
				}
			}
		}
	}
	
	function load_amount() {
		// Répartition des recettes / dépenses
		foreach($this->TBudgetLine as &$l) {
			$classe_compta = (int) substr($l->code_compta,0,1);
			if($l->code_compta === '00') $classe_compta = 7;
			else if($l->code_compta=== '000') $classe_compta = 6;
			
			if ($classe_compta == 6) {
				$this->amount_depense += $l->amount;
			}else if($classe_compta == 7) {
				$this->amount_ca += $l->amount;
			}
			$this->amount += $l->amount;
		}
		// Ajout total assurance
		$this->amount_depense += $this->amount_insurance;
		// Ajout encours
		$this->amount_ca += $this->encours_n1;
		
		if($this->amount_ca != 0) {
			// Calcul taux encours
			$t_production = $this->amount_ca + $this->amount_encours_n + $this->amount_encours_n1;
			$t_marge = $t_production - $this->amount_depense;
			
			$this->encours_taux = $this->amount_depense / $t_production;
			$this->marge_globale = $this->amount_ca - $this->amount_depense;
		}
	}
	
	function fetch_resultat() {
		$TAllCateg = TCategComptable::getStructureCodeComptable();
		
		$year = date('Y',$this->date_debut);
		$month = (int) date('m',$this->date_debut);
		
		$this->TResultat['libelle'] = $this->label;
		$this->TResultat['date'] = date('d/m/Y',$this->date_debut);
		$this->TResultat['year'] = date('Y',$this->date_debut);
		$this->TResultat['month'] = (int) date('m',$this->date_debut);
		$this->TResultat['tx_encours'] = $this->encours_taux;
		$this->TResultat['statut'] = $this->statut;
		
		foreach($TAllCateg as $label=>$TCateg) {
			$this->TResultat['category'][_get_key($label)]['libelle'] = $label;
			$this->TResultat['category'][_get_key($label)]['code_budget'] = $TCateg['code'];
			$this->TResultat['category'][_get_key($label)]['@bymonth'][$year][$month]['price'] = $this->getAmountForCode($code_compta);
			if(!empty($TCateg['subcategory'])) {
				foreach($TCateg['subcategory'] as $TSubCateg)
				{
					$code_compta = $TSubCateg['code_compta'];
					$price = $this->getAmountForCode($code_compta);
					$this->TResultat['category'][_get_key($label)]['@bymonth'][$year][$month]['subcategory'][_get_key($TSubCateg['libelle'])]['libelle'] = $TSubCateg['label'];
					$this->TResultat['category'][_get_key($label)]['@bymonth'][$year][$month]['subcategory'][_get_key($TSubCateg['libelle'])]['code_compta'] = $code_compta;
					$this->TResultat['category'][_get_key($label)]['@bymonth'][$year][$month]['subcategory'][_get_key($TSubCateg['libelle'])]['price'] = $price;
					$this->TResultat['category'][_get_key($label)]['@bymonth'][$year][$month]['price'] += $price;
				}
			}
		}
	}
	
	function libStatut() {
		
		return $this->TStatut[$this->statut];
		
	}
	
	function getNomUrl($picto=1) {
		$url = '<a href="'.dol_buildpath('/budget/budget.php?action=view&id='.$this->getId(),1).'" />'.($picto ? img_picto('', 'object_label.png').' ' : '').$this->label.'</a>';
		
		return $url;
	}
	
	function getAmountForCode($code_compta) {
		
		foreach($this->TBudgetLine as &$l) {
			if($l->code_compta == $code_compta && strlen($l->code_compta) == strlen($code_compta)) {
				return $l->amount;
			}
		}
		
	}

	function setAmountForCode($code_compta,$amount) {
		foreach($this->TBudgetLine as $k=> &$l) {
			if($l->code_compta == $code_compta && strlen($l->code_compta) == strlen($code_compta)) {
				$l->amount = $amount;
				return $k;
			}
		}
		
		$PDOdb=new stdClass;
		$k = $this->addChild($PDOdb, 'TBudgetLine');
		
		$this->TBudgetLine[$k]->code_compta = $code_compta;
		$this->TBudgetLine[$k]->amount = $amount;
		
		return $k;
	}

	function revoir(&$PDOdb) {
		$budget = new TBudget;
		$budget = clone $this;
		$budget->statut = 0;
		$budget->label .= ' (copie)';
		$budget->rowid = null;
		$budget->date_cre = time();
		foreach($budget->TBudgetLine as &$l) {
			$ligne = new TBudgetLine;
			$ligne = clone $l;
			$ligne->rowid = null;
			$ligne->fk_budget=null;
			$l = $ligne;
		}
		$budget->save($PDOdb);
		return $budget;
	}
	
	static function getTypeBudget($statut) {
		$TypeStatus = array(
			1=>'valide',
			//2=>'valid',
			3=>'refuse',
			4=>'revu'
		);
		return $TypeStatus[$statut];
	}
	
	static function getBudget(&$PDOdb, $code_analytique= null,$fk_project=null, $statut = 1, $datetime_debut=null, $datetime_fin=null) {
		$sql = "SELECT rowid";
		$sql.=" FROM ".MAIN_DB_PREFIX."sig_budget";
		if(!empty($code_analytique)) {
			// Cas code analytique
			if(!is_array($code_analytique)) {
				$sql.=" WHERE code_analytique='".$code_analytique."'";
			} else {
				$isfirst=true;
				$sql.=" WHERE 1 AND(";
				foreach($code_analytique as $one_groupe) {
					if(!$isfirst) {
						$sql.=" OR ";
					}
					$sql.="code_analytique LIKE '".$one_groupe."%'";
					$isfirst=false;
				}
				$sql.=")";
			}
		} else if(!empty($fk_project)) {
			// Cas avec projet
			if(!is_array($fk_project))
				$sql.=" WHERE fk_project=".$fk_project;
			else
				$sql.=" WHERE fk_project IN (".implode(',', $fk_project).")";
		} else {
			// Cas sans projet avec dates
			if(!empty($datetime_debut)) {
				$sql.=" WHERE date_debut>='".date('Ymd',$datetime_debut).'\'';
				if(!empty($datetime_fin))
					$sql.=" AND date_debut<='".date('Ymd',$datetime_fin).'\'';
				// Cas budget global
				$sql.=" AND fk_project = 0";
			}
		}
		
		$sql.=" AND statut IN (".$statut.") ORDER BY date_debut ";
		$Tab = $PDOdb->ExecuteAsArray($sql);
		
		$TBudget = array();
		foreach($Tab as $row) {
			$budget=new TBudget;
			$budget->load($PDOdb, $row->rowid);
			$budget->fetch_resultat();
			$TBudget[] = $budget->TResultat;
		}
		return $TBudget;
	}
	
}

/**
 * Class TBudgetLine
 */
class TBudgetLine extends TObjetStd {
	
	function __construct() {
		global $langs;
		
        parent::set_table(MAIN_DB_PREFIX.'sig_budget_ligne');
		parent::add_champs('code_compta');
		parent::add_champs('amount',array('type'=>'float'));
		parent::add_champs('fk_budget',array('type'=>'integer', 'index'=>true));
		
		
		parent::_init_vars();
        parent::start();

	}
}