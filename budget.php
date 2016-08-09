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
	global $user, $conf,$langs;
	
	$budget = new TBudget;
	$action = GETPOST('action');
	
	switch($action) {
		
		case 'valid':
			$id=(int)GETPOST('id');
			$budget->load($PDOdb, $id);
			$budget->statut = 1;
			$budget->user_valid = $user->id;
			
			$budget->save($PDOdb);
			
			setEventMessage('Budget validé');
			_fiche($PDOdb, $budget);
			break;
		case 'revu':
			$id=(int)GETPOST('id');
			$budget->load($PDOdb, $id);
			$budget->statut = 4;
			$budget->user_valid = $user->id;
			
			$budget->save($PDOdb);
			
			setEventMessage('Budget '.$langs->trans('revu'));
			_fiche($PDOdb, $budget);
			break;
		case 'revoir':
			$id=(int)GETPOST('id');
			$budget->load($PDOdb, $id);
			$budget->statut = 4;
			$budget->user_valid = $user->id;
			
			$newbudget = $budget->revoir($PDOdb);
			$budget->save($PDOdb);
			
			setEventMessage('Budget '.$langs->trans('revu'));
			header('Location:?action=view&id='.$newbudget->rowid);
			break;
		case 'reject':
			$id=(int)GETPOST('id');
			$budget->load($PDOdb, $id);
			$budget->statut = 3;
			$budget->user_reject = $user->id;
			$budget->save($PDOdb);
			setEventMessage('Budget refusé');
			_fiche($PDOdb, $budget);
			break;
		case 'delete':
			$id=(int)GETPOST('id');
			$budget->load($PDOdb, $id);
			$budget->delete($PDOdb);
			setEventMessage('Budget supprimé');
			header('Location:?action=list');
			break;
		case 'reopen':
			$id=(int)GETPOST('id');
			$budget->load($PDOdb, $id);
			$budget->statut = 0;
			$budget->save($PDOdb);
			
			_fiche($PDOdb, $budget);
			break;
		case 'new':
			$budget->date_debut = strtotime(date('Y-01-01'));
			$budget->date_fin = strtotime(date('Y-12-31'));
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
				$budget->setAmountForCode(''.$code_compta, $data['amount']);
			}
			$budget->save($PDOdb);
			setEventMessage('Sauvegardé avec succès');
			header('Location:?action=view&id='.$budget->getId());
			
			break;
		default :
			_list($PDOdb);
	}
}

function _list(&$PDOdb)
{
	global $langs,$conf;
	
	llxHeader('',$langs->trans('ListBudget'));
	dol_fiche_head();
	
	$r = new TListviewTBS('listB');
	
	!empty($conf->global->SIG_USE_CODE_ANALYTIQUE)?$use_analytique=1:$use_analytique=0;
	
	$THide = array('rowid');
	
	$sql = 'SELECT rowid,label,date_debut,fk_project';
	if($use_analytique)
	{
		$sql .=',code_analytique';
		$THide[]='fk_project';
	}
	$sql .= ',statut';
	$sql.=' FROM '.MAIN_DB_PREFIX.'sig_budget b';
	
	$titre = $langs->trans('list').' '.$langs->trans('budgets');
	
	$budget = new TBudget;
	
	echo $r->render($PDOdb, $sql, array(
		'limit'=>array(
			'nbLine'=>$conf->liste_limit
		)
		,'link'=>array(
			'label'=>'<a href="?action=view&id=@rowid@" />'.img_picto('', 'object_label.png').' @label@</a>'
			
		)
		,'translate'=>array(
			'statut'=>$budget->TStatut
		)
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
			,'label'=>$langs->trans('Label')
		)
		
		,'eval'=>array(
			'fk_project'=>'_get_project_link(@val@)'
		)
		,'type'=>array(
			'date_debut'=>'date'
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
		return 'Global';
	}
}

function _get_lines(&$PDOdb,&$TForm,&$budget) {
	global $langs;
	$TBigCateg = TCategComptable::getStructureCodeComptable();
	
	$Tab=array();
	
	$TColor=array(
		'c4daeb','b7d3e7','ddeaf4','f7fafc','fff'
	);
	$TClass=array(
		'level0','level1','level2','level3','level4'
	);
	foreach($TBigCateg as $label=>$TCateg) {
		$Tab[]=array(
			'code_compta'=>$label
			,'label'=>$TCateg['libelle']
			,'amount'=>''
			,'color'=>'#c4daeb'
			,'class'=>'level0'
		);
		if(!empty($TCateg['subcategory']))
		{
			foreach($TCateg['subcategory'] as $TSubCateg) {
				$code_compta = $TSubCateg['code_compta'];
				$Tab[]=array(
					'code_compta'=>$code_compta
					,'label'=>$TSubCateg['label']
					,'amount'=>$TForm->texte('', 'TBudgetLine['.$code_compta.'][amount]', $budget->getAmountForCode($code_compta) , 10,30)
					,'color'=>(!empty($TColor[strlen($code_compta)]) ? '#'.$TColor[strlen($code_compta)] : '#fff')
					,'class'=>(!empty($TClass[strlen($code_compta)]) ? $TClass[strlen($code_compta)] : 'level4')
				);
			}
		}
	}
	
	return $Tab;
}

function _fiche(&$PDOdb, &$budget, $mode='view')
{
	global $langs, $conf,$db;
	dol_include_once('/sig/lib/sig.lib.php');
	
	!empty($conf->global->SIG_USE_CODE_ANALYTIQUE)?$use_analytique=1:$use_analytique=0;
	
	llxHeader('',$langs->trans('Budget'));
	
	$doli_form = new Form($db);
	$TBS=new TTemplateTBS();
	
	dol_fiche_head();
	
	
	dol_include_once('/core/class/html.formprojet.class.php');
	$formProject = new FormProjets($db);
	$form = new Form($db);
	
	$TForm=new TFormCore('auto','form_edit_budget','POST');
	$TForm->Set_typeaff($mode);
	echo $TForm->hidden('id', $budget->getId());
	
	echo $TForm->hidden('action', 'save');

	$TLine=$TButton=array();

	if($mode == 'view') {
		$TButton[] = '<a class="butAction" href="?action=list">'.$langs->trans('Liste').'</a>';
		if($budget->statut != 4)$TButton[] = '<a class="butAction" href="?action=revu&id='.$budget->getId().'">'.$langs->trans('Disable').'</a>';
	
		if($budget->statut == 0)$TButton[] = '<a class="butAction" href="?action=valid&id='.$budget->getId().'">'.$langs->trans('Valid').'</a>';
		if($budget->statut == 0)$TButton[] = '<a class="butActionDelete" href="?action=reject&id='.$budget->getId().'">'.$langs->trans('Refuser').'</a>';
		
		$TButton[] = '<a class="butActionDelete" onclick="return confirm(\'Êtes vous certain ?\')" href="?action=delete&id='.$budget->getId().'">'.$langs->trans('Delete').'</a>';
		
		if($budget->statut == 1)$TButton[] = '<a class="butAction" href="?action=revoir&id='.$budget->getId().'">'.$langs->trans('Revoir').'</a>';
		
		if($budget->statut > 0)$TButton[] = '<a class="butAction" href="?action=reopen&id='.$budget->getId().'">'.$langs->trans('Reopen').'</a>';
		else $TButton[]='<a class="butAction" href="?action=edit&id='.$budget->getId().'">'.$langs->trans('Modify').'</a>';
		
		if($use_analytique)
			$select_codes_analytiques = $budget->code_analytique;
		else
			$select_project = _get_project_link($budget->fk_project);
	}
	else{
		$TButton[]='<a class="butActionDelete" href="?action=view&id='.$budget->getId().'">'.$langs->trans('Cancel').'</a>';
		
		$TButton[]=$TForm->btsubmit($langs->trans('Valid'), 'bt_submit');
		
		if($use_analytique) {
			$select_codes_analytiques = select_codes_analytiques($PDOdb,$budget->code_analytique, 'code_analytique');
		} else {
			ob_start();
			$formProject->select_projects(-1,$budget->fk_project, 'fk_project');
			$select_project =ob_get_clean();
		}
	}

	$TLine = _get_lines($PDOdb,$TForm, $budget);

	$TBudget = TBudget::getBudget($PDOdb, $budget->fk_project,false, '0,1,3');
	echo $TBS->render('tpl/budget.fiche.tpl.php',
		array(
			'line'=>$TLine
			,'buttons'=>$TButton
			,'budgets'=>$TBudget
		)
		,array(
			'budget'=>array(
				'label'=>$TForm->texte('','label',$budget->label, 80,255)
				,'date_debut'=>$TForm->calendrier('','date_debut',$budget->date_debut)
				,'date_fin'=>$TForm->calendrier('','date_fin',$budget->date_fin)
				,'encours_n1'=>$TForm->texte('','encours_n1',$budget->encours_n1, 12,255)
				,'statut'=>$budget->TStatut[$budget->statut]
				,'idstatut'=>$budget->statut
				,'fk_project'=>$select_project
				,'code_analytique'=>$select_codes_analytiques
				,'amount_ca'=>price($budget->amount_ca, 0, '',1, -1, 2)
				,'amount_production'=>price($budget->amount_production, 0, '',1, -1, 2)
				,'amount_insurance'=>price($budget->amount_insurance, 0, '',1, -1, 2)
				,'encours_taux'=>round($budget->encours_taux,4)*100
				,'amount_encours_n1'=>price($budget->encours_n1, 0, '',1, -1, 2)
				,'amount_depense'=>price($budget->amount_depense, 0, '',1, -1,2)
				,'total_marge'=>price($budget->marge_globale, 0, '',1, -1, 2)
				,'use_analytique'=>$use_analytique
			)
			,'langs'=>$langs
			,'mode'=>$mode
		)
	);
	
	echo $TForm->end_form();
	
	dol_fiche_end();
		
	llxFooter();
}