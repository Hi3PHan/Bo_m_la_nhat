<?php
defined("_JEXEC") or die;
?>

<form action="<?php echo JRoute::_('index.php?option=com_qlsv&view=students&Masv='.(int)$this->item->Masv); ?>" id="adminForm" method="POST">
    <?php echo $this->form->renderFieldset('Student'); ?>
    <?php echo $this->form->renderField('Tensv'); ?>
    <?php echo $this->form->renderField('Gioitinh'); ?>
    <?php echo $this->form->renderField('Ngaysinh'); ?>
    <?php echo $this->form->renderField('Que'); ?>
    <?php echo $this->form->renderField('Lop'); ?>
    <?php echo JHtml::_('form.token'); ?>
    <input type="hidden" name="task" />
    <!--    <button type="submit">Submit</button>-->
    <!---->
    <!--    <button action="--><?php //echo JRoute::_('index.php?option=com_helloworld'); ?><!--" >Exit</button>-->
</form>
