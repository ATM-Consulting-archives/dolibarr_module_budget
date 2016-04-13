<table class="border" width="100%">
	
	<tr>
		<td> [langs.trans(Label);strconv=no] </td>
		<td>[budget.label;strconv=no]</td>
	</tr>
	<tr>
		<td> [langs.trans(Project);strconv=no] </td>
		<td>[budget.fk_project;strconv=no]</td>
	</tr>
	<tr>
		<td> [langs.trans(Status);strconv=no] </td>
		<td>[budget.statut;strconv=no]</td>
	</tr>
	<tr>
		<td> [langs.trans(DateStart);strconv=no] </td>
		<td>[budget.date_debut;strconv=no]</td>
	</tr>
	<tr>
		<td> [langs.trans(DateEnd);strconv=no] </td>
		<td>[budget.date_fin;strconv=no]</td>
	</tr>
	
	<tr>
		<td colspan="2">
		
		<table border="0" class="border" width="100%">
			<tr>
				<td>[line.code_compta;strconv=no;block=tr]</td>
				<td>[line.label;strconv=no]</td>
				<td>[line.amount;strconv=no]</td>
			</tr>
			
		</table>
			
		</td>
	</tr>
	
</table>

<div class="tabsAction">
	<div class="inline-block divButAction">
	[buttons.val;block=div;strconv=no]
	</div>
</div>

<h2>Historique des budget du projet</h2>
<table border="0" class="border" width="100%">
			<tr budgets.style>
				<td>[budgets.getNomUrl;strconv=no;block=tr]</td>
				<td>[budgets.get_date(date_debut);strconv=no;]</td>
				<td>[budgets.get_date(date_fin);strconv=no;]</td>
				<td align="right">[budgets.amount;frm=0 000,00]</td>
				<td>[budgets.libStatut;strconv=no]</td>
			</tr>
			
</table>
