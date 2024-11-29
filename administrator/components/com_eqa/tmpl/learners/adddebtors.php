<?php

use Joomla\CMS\HTML\HTMLHelper;

defined('_JEXEC') or die;

// Add Bootstrap CSS and JS (if not already included in your Joomla template)
JHtml::_('bootstrap.loadCss');
JHtml::_('bootstrap.framework');
HTMLHelper::_('behavior.formvalidator');

?>
<div class="accordion">
    <div class="accordion-item">
        <h2 class="accordion-header" id="headingOne"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne"> Hướng dẫn sử dụng </button></h2>
        <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
            <div class="accordion-body">
                <ul>
                    <li>Chức năng này cho phép đánh dấu các HVSV đang nợ học phí (hoặc phí khác).
                        Thông tin này có thể được sử dụng để có biện pháp xử lý cần thiết (cấm thi,
                        từ chối phúc khảo...) đối với những trường hợp thí sinh chưa hoàn thành nghĩa vụ
                        tài chính.
                    </li>
                    <li>Có thể thực hiện chức năng này thông qua "Ghi nợ", "Xóa nợ" tại
                        <a href="<?php echo JRoute::_('index.php?option=com_eqa&view=learners') ?>">trang quản lý HVSV</a>
                    </li>
                    <li>
                        CBKT nhập mã HVSV (cách nhau bằng dấu cách, dấu tab, dấu phẩy, dấu chấm phẩy
                        hoặc dấu xuống dòng) vào ô bên dưới.
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<form action="<?php echo JRoute::_('index.php?option=com_eqa');?>" method="post" name="adminForm" id="adminForm" class="form-validate">
    <input type="hidden" name="task">
    <?php
    echo $this->form->renderFieldset('debtors');
    echo JHtml::_('form.token');
    ?>
</form>