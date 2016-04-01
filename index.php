<?php

require_once 'config.php';
dol_include_once("/budget/class/budget.class.php");

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
	
	$id=GETPOST('id');
	$budget = new TBudget;
	$budget->load($PDOdb, $id);

	switch($_REQUEST['action']) {
		case 'new':
		case 'add':
		case 'create':
		case 'edit':
			if($user->rights->budget->edit < 1) accessforbidden();
			_fiche($PDOdb, $budget, 'edit');
			break;
		
		case 'view':
			_fiche($PDOdb, $budget);
			break;
			
		case 'save':
			if($user->rights->gpec->edit < 1) accessforbidden();
			$budget->set_values($_REQUEST);
			$budget->save($PDOdb);
			setEventMessage('Sauvegardé avec succès');
			header('Location:'.$_SERVER['PHP_SELF'].'?action=view&id='.$budget->getId());
			break;
		default :
			_list($PDOdb);
	}
}

function _list(&$PDOdb)
{
	global $langs;
	
	llxHeader('',$langs->trans('ListBudget'));
	
	$r = new TSSRenderControler($evaluation);
	
	$sql = 'SELECT *';
	$sql.=' FROM '.MAIN_DB_PREFIX.'budget b';
	$sql.=' INNER JOIN '.MAIN_DB_PREFIX.'user u on (u.rowid = e.fk_user)';
	
	$titre = $langs->trans('list').' '.$langs->trans('budgets');
	$THide = array('rowid');
		
	$r->liste($PDOdb, $sql, array(
		'limit'=>array(
			'nbLine'=>$conf->liste_limit
		)
		,'link'=>array(
			'rowid'=>'<a href="'.$linkeval.'" />@rowid@</a>'
			,'label'=>'<a href="'.$link.'" />'.img_picto('', 'object_label.png').' @label@</a>'
			,'budget'=>'<a href="'.$linkeval.'" />'.img_picto('', 'object_generic.png').'Fiche budget</a>'
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
			,'budget'=>$langs->trans('budget')
			,'label'=>$langs->trans('emploi')
		)
		,'search'=>array(
			'rowid'=>true
			,'label'=>true
			,'login'=>true
			,'lastname'=>true
			,'firstname'=>true
			,'status'=>array('recherche'=>$evaluation->TStatusTrad, 'table'=>'e', 'field'=>'status')
		)
		,'eval'=>array(
		)
		,'type'=>array(
		)
		,'orderBy'=>array(
			'rowid'=>'DESC'
		)
	));
		
	llxFooter();
}

function _fiche(&$PDOdb, &$budget, $mode)
{
	global $langs;
	
	llxHeader('',$langs->trans('CreateBudget'));
	
	$doli_form = new Form($db);
	$TBS=new TTemplateTBS();
	
	$TForm=new TFormCore($_SERVER['PHP_SELF'],'form_edit_budget','POST');
	$TForm->Set_typeaff($mode);
	
	echo $TForm->hidden('id', $budget->getId());
	
	if ($mode=='create' || $mode=='view') echo $TForm->hidden('action', 'edit');
	else echo $TForm->hidden('action', 'save');

	print $TBS->render('tpl/'.$tpl_fiche
		,array(
			//'assetField'=>$TFields
		)
		,array(
			'evaluation'=>array(
				'id'=>$budget->getId()
				,'date_eval'=>$TForm->calendrier('', 'date_eval', $evaluation->date_eval)
				,'fk_user'=>($mode != 'edit' ? $user_to_evaluate->getNomUrl(1) : $doli_form->select_dolusers($evaluation->fk_user, 'fk_user', 0,array(),0)) 
				,'fk_emploi'=>$TForm->combo_sexy('', 'fk_emploi', $TEmploi, $evaluation->fk_emploi,1,'','style="min-width:200px;"')
				,'status'=>$evaluation->status
				,'statusTrad'=>$evaluation->getStatus()
			)
			,'title'=>array(
				'date_eval'=>$langs->trans('dateEval')
				,'fk_user'=>$langs->trans('User')
				,'fk_emploi'=>$langs->trans('emploi')
				,'status'=>$langs->trans('status')
			)
			,'buttons'=>array(
				'save'=>($myrights>2)?$TForm->btsubmit($langs->trans('valid'), $langs->trans('valid')):''
				,'edit'=>($myrights>2)?'<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?id='.$evaluation->rowid.'&action=edit">'.$langs->trans('edit').'</a>':''
				,'delete'=>($myrights>2)?'<a class="butActionDelete" href="'.$_SERVER['PHP_SELF'].'?id='.$evaluation->rowid.'&action=delete">'.$langs->trans('delete').'</a>':''
				,'other'=>($myrights>2)?$btother:''
			)
			,'code'=>array(
				'interface'=>dol_buildpath('/gpec/script/interface.php',1)
			)
			,'view'=>array(
				'mode'=>$mode
				,'action'=>$action
			)
		)
	);
	
	echo $TForm->end_form();
		
	llxFooter();
}