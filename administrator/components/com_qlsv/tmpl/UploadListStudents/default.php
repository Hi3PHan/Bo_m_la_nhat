<?php
defined('_JEXEC') or die;
?>
<h2>Upload File Excel Sinh Viên</h2>
<form action="<?php echo JRoute::_('index.php?option=com_qlsv&task=UploadListStudents.processUpload'); ?>"  method="POST" name="adminForm" id="adminForm" enctype="multipart/form-data">
    <div>
        <label for="file">Chọn file Excel:</label>
        <input type="file" name="file" id="file" accept=".xlsx,.xls" required />
    </div>
    <br>
    <button type="submit" class="btn btn-primary">Upload</button>
    <?php echo JHtml::_('form.token');?>
</form>
