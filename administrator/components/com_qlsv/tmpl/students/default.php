<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

?>


<form action="<?php echo Route::_('index.php?option=com_qlsv'); ?>"
      name="adminForm" id="adminForm" method="POST">

    <?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

    <?php if (empty($this->items)) : ?>
        <div class="alert alert-info">
            <span class="fa fa-info-circle" aria-hidden="true"></span>
            <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
        </div>

        <div class="w-10">
            <?php echo $this->pagination->getLimitBox(); ?>
        </div>
    <?php else : ?>


    <h2>Danh sách sinh viên</h2>
    <table class="table">
        <thead>
        <tr>
            <th class="w-5 text-center" ><?php echo HTMLHelper::_('grid.checkall'); ?></th>
            <th>ID</th>
            <th>Tên</th>
            <th>Giới tính</th>
            <th>Ngày sinh</th>
            <th>Quê Quán</th>
            <th>Lớp</th>

        </tr>
        </thead>
        <tbody>
        <?php if (!empty($this->items) && is_array($this->items)): ?>
            <?php foreach ($this->items as $i=>$student): ?>
                <tr>
                    <td><?php echo HTMLHelper::_('grid.id', $i, $student->Masv); ?></td>
                    <td><?php echo $student->Masv; ?></td>
                    <td><?php echo $student->Tensv; ?></td>
                    <td><?php echo $student->Gioitinh; ?></td>
                    <td><?php echo $student->Ngaysinh; ?></td>
                    <td><?php echo $student->Que; ?></td>
                    <td><?php echo $student->Lop; ?></td>

                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="5">Không có sinh viên nào.</td>
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
    <?php endif; ?>


    <div>
        <?php echo $this->pagination->getResultsCounter();?>
        <span class="icon-screen"></span>
        <?php echo $this->pagination->getPagesCounter(); ?>
        <?php echo $this->pagination->getListFooter(); ?>

    </div>
</form>
