<form action="
<?php use Joomla\CMS\HTML\HTMLHelper;

echo JRoute::_('index.php?option=com_qlsv'); ?>"
      name="adminForm" id="adminForm" method="POST">
    <div class="w-10">
        <?php echo $this->pagination->getLimitBox(); ?>
    </div>

    <?php defined('_JEXEC') or die; ?>

    <h2>Danh sách sinh viên</h2>
    <table class="table">
        <thead>
        <tr>
            <th class="w-5 text-center" ><?php echo HTMLHelper::_('grid.checkall'); ?></th>
            <th>Mã môn học</th>
            <th>Tên môn học</th>
            <th>Tín chỉ</th>
        </tr>
        </thead>
        <tbody>
        <?php if (!empty($this->items) && is_array($this->items)): ?>
            <?php foreach ($this->items as $i=>$subject): ?>
                <tr>
                    <td><?php echo HTMLHelper::_('grid.id', $i, $subject->Mamh); ?></td>
                    <td><?php echo $subject->Mamh; ?></td>
                    <td><?php echo $subject->Tenmh; ?></td>
                    <td><?php echo $subject->DVHT; ?></td>

                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="5">Không có mon học nào.</td>
            </tr>
        <?php endif; ?>
        </tbody>


        <input type="hidden" name="task" value="">
        <!--    Trường input ẩn này được sử dụng để lưu giữ giá trị task (nhiệm vụ) cần thực hiện.-->
        <input type="hidden" name="boxchecked" value="0">
        <!--    Trường boxchecked lưu giữ số lượng các bản ghi đã được chọn (bằng checkbox).-->
        <!--    Ban đầu, giá trị được đặt là 0 và sẽ được thay đổi khi người dùng chọn các bản ghi.-->
        <?php echo HTMLHelper::_('form.token'); ?>
    </table>

    <div>


        <?php echo $this->pagination->getResultsCounter();?>
        <span class="icon-screen"></span>
        <?php echo $this->pagination->getPagesCounter(); ?>
        <?php echo $this->pagination->getListFooter(); ?>
    </div>




</form>