<?php

/**
 * Class TBudget
 */
class TBudget extends TObjetStd {
	public $date_debut;
	public $date_fin;
	public $fk_project;
	public $statut;
	public $TStatut;
	public $user_valid;
	public $user_reject;
	public $label;
	public $amount;
	public $amount_ca;
	public $amount_depense;
	public $amount_production;
	public $amount_encours_n;
	public $amount_encours_n1;
	public $encours_taux;
	public $marge_globale;
	public $TResultat;
	public $TBudgetLine;
	
	function __construct() {
		global $langs;
		
        parent::set_table(MAIN_DB_PREFIX.'sig_budget');
		parent::add_champs('date_debut, date_fin',array('type'=>'date', 'index'=>true));
		parent::add_champs('fk_project',array('type'=>'integer', 'index'=>true));
		parent::add_champs('statut,user_valid,user_reject',array('type'=>'integer'));
		parent::add_champs('amount',array('type'=>'float'));
		parent::_init_vars('label');
        parent::start();

		$this->setChild('TBudgetLine','fk_budget');

		$this->TStatut = array(
			0=>'Brouillon'
			,1=>'Validé'
			/*,2=>'En attente de validation'*/
			,3=>'Refusé'
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
		
		foreach($this->TBudgetLine as &$l) {
			$classe_compta = (int) substr($l->code_compta,0,1);
			if ($classe_compta == 6) {
				$this->amount_depense += $l->amount;
			}else if($classe_compta == 7) {
				$this->amount_ca += $l->amount;
			}
			$this->amount += $l->amount;
		}
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
			if($l->code_compta == $code_compta) {
				return $l->amount;
			}
		}
		
	}

	function setAmountForCode($code_compta,$amount) {
		foreach($this->TBudgetLine as $k=> &$l) {
			if($l->code_compta == $code_compta) {
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
	
	static function getBudget(&$PDOdb, $fk_project, $statut = 1) {
		$sql = "SELECT rowid";
		$sql.=" FROM ".MAIN_DB_PREFIX."sig_budget";
		if(!is_array($fk_project))
			$sql.=" WHERE fk_project=".$fk_project;
		else
			$sql.=" WHERE fk_project IN (".implode(',', $fk_project).")";
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