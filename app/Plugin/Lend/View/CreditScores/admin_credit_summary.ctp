<?php /* SVN: $Id: index.ctp 2879 2010-08-27 11:08:48Z sakthivel_135at10 $ */ ?>
<table class="table table-striped table-bordered table-condensed table-hover">
  <tr>
    <th class="text-left"><?php echo __l('Credit Scores');?></th>
    <th colspan="5" class="text-center"><?php echo __l('Latest Target Interest Rate');?></th>
  </tr>
  <?php
    if (!empty($credit_scores)):
      $i = 0;
      foreach ($credit_scores as $key => $credit_score):
    ?>
  <tr>
	<td class="text-left"><?php echo $this->Html->cText($credit_score['CreditScore']['name']);?></td>
	<?php foreach($credit_score['Lend'] as $index => $value){ ?>
		<td class="text-center"><?php echo $this->Html->cFloat($value['target_interest_rate']). '%';?></td>
	<?php }
	if(count($credit_score['Lend']) < 5){
		for($j=count($credit_score['Lend']); $j<5; $j++ ){ ?>
			<td class="text-center"> - </td>
<?php	}
	}
	?>
  </tr>
  <?php
      endforeach;
  ?>
  <?php
    endif;
  ?>
</table>