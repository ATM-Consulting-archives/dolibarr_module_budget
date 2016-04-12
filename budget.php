<?php

require 'config.php';

dol_include_once("/budget/class/budget.class.php");
dol_include_once('/sig/class/categorie_comptable.class.php');

if(empty($conf->sig->enabled)) exit('SIGrequire');

// Contrôle d'accès
if (!($user->admin || $user->rights->budget->read)) {
    accessforbidden();
}

$langs->load('budget@budget');

$PDOdb=new TPDOdb;

// Get parameters
_action($PDOdb);

function _action(&$PDOdb) {
	global $user, $conf;
	
	$budget = new TBudget;
	$action = GETPOST('action');
	
	switch($action) {
		case 'new':
		
			_fiche($PDOdb, $budget, 'edit');
			break;
		
		case 'edit':
			$id=(int)GETPOST('id');
			$budget->load($PDOdb, $id);
			
			_fiche($PDOdb, $budget, 'edit');
			break;
		
		case 'view':
			$id=(int)GETPOST('id');
			$budget->load($PDOdb, $id);
			
			_fiche($PDOdb, $budget);
			break;
			
		case 'save':
			$id=(int)GETPOST('id');
			$budget->load($PDOdb, $id);
			
			$budget->set_values($_REQUEST);
			
			foreach ($_REQUEST['TBudgetLine'] as $code_compta => $data) {
				$budget->setAmountForCode($code_compta, $data['amount']);
			}
			
			$budget->save($PDOdb);
			
			setEventMessage('Sauvegardé avec succès');
			
			_fiche($PDOdb, $budget);
			
			break;
		default :
			_list($PDOdb);
	}
}

function _list(&$PDOdb)
{
	global $langs;
	
	llxHeader('',$langs->trans('ListBudget'));
	dol_fiche_head();
	
	$r = new TListviewTBS('listB');
	
	$sql = 'SELECT rowid,label,date_debut,date_fin,fk_project,statut';
	$sql.=' FROM '.MAIN_DB_PREFIX.'sig_budget b';
	
	$titre = $langs->trans('list').' '.$langs->trans('budgets');
	$THide = array('rowid');
		
	echo $r->render($PDOdb, $sql, array(
		'limit'=>array(
			'nbLine'=>$conf->liste_limit
		)
		,'link'=>array(
			'label'=>'<a href="?action=view&id=@rowid@" />'.img_picto('', 'object_label.png').' @label@</a>'
			
		)
		,'translate'=>array()
		,'hide'=>$THide
		,'liste'=>array(
			'titre'=> $titre
			,'image'=>img_picto('','title.png', '', 0)
			,'picto_precedent'=>img_picto('','back.png', '', 0)
			,'picto_suivant'=>img_picto('','next.png', '', 0)
			,'messageNothing'=>"Il n'y a aucun ".$langs->trans('budget')." à afficher"
			,'picto_search'=>img_picto('','search.png', '', 0)
		)
		,'title'=>array(
			'rowid'=>'ID'
			,'date_debut'=>$langs->trans('DateStart')
			,'date_fin'=>$langs->trans('DateEnd')
			,'label'=>$langs->trans('Label')
		)
		
		,'eval'=>array(
			'fk_project'=>'_get_project_link(@val@)'
		)
		,'type'=>array(
			'date_debut'=>'date'
			,'date_fin'=>'date'
		)
		,'orderBy'=>array(
			'date_debut'=>'DESC'
		)
	));
		
	dol_fiche_end();
	llxFooter();
}

function _get_project_link($fk_project) {
	global $db,$conf,$langs,$user;
	
	dol_include_once('/projet/class/project.class.php');
	
	$projet=new Project($db);
	if($projet->fetch($fk_project)>0) {
		return $projet->getNomUrl(1);
	}
	else{
		return 'N/A';
	}
	
	
}

function _get_lines(&$PDOdb,&$TForm,&$budget) {
	
	$TCode = TCategComptable::getAllCodeComptable();
	
	$Tab=array();
	
	foreach($TCode as $code_compta=>$label) {
			$Tab[]=array(
				'code_compta'=>$code_compta
				,'label'=>$label
				,'amount'=>$TForm->texte('', 'TBudgetLine['.$code_compta.'][amount]', $budget->getAmountForCode($code_compta) , 10,30)
			);
		
		
	}
	
	return $Tab;
}

function _fiche(&$PDOdb, &$budget, $mode='view')
{
	global $langs, $conf,$db;
	
	llxHeader('',$langs->trans('Budget'));
	
	$doli_form = new Form($db);
	$TBS=new TTemplateTBS();
	
	dol_fiche_head();
	
	$TForm=new TFormCore('auto','form_edit_budget','POST');
	$TForm->Set_typeaff($mode);
	
	echo $TForm->hidden('id', $budget->getId());
	
	echo $TForm->hidden('action', 'save');

	$TLine=array();

	if($mode == 'view') {
		$TButton=array(
			'<a class="butAction" href="?action=edit&id='.$budget->getId().'">'.$langs->trans('Modify').'</a>'
		);
	}
	else{
		$TButton=array(
			$TForm->btsubmit($langs->trans('Valid'), 'bt_submit')
		);
	}

	$TLine = _get_lines($PDOdb,$TForm, $budget);

	
	echo $TBS->render('tpl/budget.fiche.tpl.php',
		array(
			'line'=>$TLine
			,'buttons'=>$TButton
		)
		,array(
			'budget'=>array(
				'label'=>$TForm->texte('','label',$budget->label, 80,255)
				,'date_debut'=>$TForm->calendrier('','date_debut',$budget->date_debut)
				,'date_fin'=>$TForm->calendrier('','date_fin',$budget->date_fin)	
			)
			,'langs'=>$langs
		)
	);
	
	echo $TForm->end_form();
	
	dol_fiche_end();
		
	llxFooter();
}