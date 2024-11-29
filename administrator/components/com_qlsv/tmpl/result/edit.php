<?php
defined("_JEXEC") or die;
?>

<form action="<?php echo JRoute::_('index.php?option=com_qlsv&view=results&id='.(int)$this->item->id); ?>" id="adminForm" method="POST">
    <?php echo $this->form->renderFieldset('Result'); ?>
    <?php echo $this->form->renderField('Masv'); ?>
    <?php echo $this->form->renderField('Mamh'); ?>
    <?php echo $this->form->renderField('Diem'); ?>
    <?php echo JHtml::_('form.token'); ?>
    <input type="hidden" name="task" />
    <!--    <button type="submit">Submit</button>-->
    <!---->
    <!--    <button action="--><?php //echo JRoute::_('index.php?option=com_helloworld'); ?><!--" >Exit</button>-->
</form>
