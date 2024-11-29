<?php
defined("_JEXEC") or die;
?>

<form action="<?php echo JRoute::_('index.php?option=com_qlsv&view=subjects&Mamh='.(int)$this->item->Mamh); ?>" id="adminForm" method="POST">
    <?php echo $this->form->renderFieldset('Subject'); ?>
    <?php echo $this->form->renderField('Mamh'); ?>
    <?php echo $this->form->renderField('Tenmh'); ?>
    <?php echo $this->form->renderField('DVHT'); ?>
    <?php echo JHtml::_('form.token'); ?>
    <input type="hidden" name="task" />
    <!--    <button type="submit">Submit</button>-->
    <!---->
    <!--    <button action="--><?php //echo JRoute::_('index.php?option=com_helloworld'); ?><!--" >Exit</button>-->
</form>
