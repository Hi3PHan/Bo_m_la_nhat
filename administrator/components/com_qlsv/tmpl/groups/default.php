<?php
defined('_JEXEC') or die();
$items = $this->items;
?>
<form action="" method="post" name="adminForm" id="adminForm">
	<input type="hidden" name="task">
	<table class="table table-hover">
		<thead>
		<th>TT</th>
		<th>Ký hiệu</th>
		<th>Ghi chú</th>
		</thead>
		<tbody>
		<?php
		if(!empty($items)){
			$seq = 0;
			foreach ($items as $item){
				$seq++;
				echo '<tr>';
				echo '<td>', $seq, '</td>';
				echo '<td>', $item->code, '</td>';
				echo '<td>', $item->description, '</td>';
				echo '</tr>';
			}
		}
		?>
		</tbody>
	</table>
</form>
