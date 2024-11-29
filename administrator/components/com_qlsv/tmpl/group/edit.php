<?php
defined('_JEXEC') or die();
$action = JRoute::_('index.php?option=com_tta');
?>
<form action="<?php echo $action;?>" method="post" id="adminForm" name="adminForm">
	<input type="hidden" name="task">
	<?php
	echo JHtml::_('form.token');
	if(!empty($this->form))
		echo $this->form->renderFieldset('group');
	?>
</form>
