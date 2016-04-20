<table class="border" width="100%">
	
	<tr>
		<td> [langs.trans(Label);strconv=no] </td>
		<td>[insurance.label;strconv=no]</td>
	</tr>
	<tr>
		<td> [langs.trans(Project);strconv=no] </td>
		<td>[insurance.fk_project;strconv=no]</td>
	</tr>
	<tr>
		<td> [langs.trans(Status);strconv=no] </td>
		<td>[insurance.statut;strconv=no]</td>
	</tr>
	<tr>
		<td> [langs.trans(DateStart);strconv=no] </td>
		<td>[insurance.date_debut;strconv=no]</td>
	</tr>
	<tr>
		<td> [langs.trans(DateEnd);strconv=no] </td>
		<td>[insurance.date_fin;strconv=no]</td>
	</tr>
	
	<tr>
		<td colspan="2">
		
		<table border="0" class="border" width="100%">
			<tr style="background-color: [line.color];">
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

<h2>Historique des budgets du projet</h2>
<table border="0" class="border" width="100%">
			<tr budgets.style>
				<td>[insurances.getNomUrl;strconv=no;block=tr]</td>
				<td>[insurances.get_date(date_debut);strconv=no;]</td>
				<td>[insurances.get_date(date_fin);strconv=no;]</td>
				<td align="right">[insurances.amount;frm=0 000,00]</td>
				<td>[insurances.libStatut;strconv=no]</td>
			</tr>
			
</table>
