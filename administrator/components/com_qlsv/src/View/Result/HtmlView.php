<?php
namespace Hi3Phan\Component\QLSV\Administrator\View\Result;

use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

defined('_JEXEC') or die();
class HtmlView extends BaseHtmlView
{
    protected $form;
    protected $item;


    public function display($tpl = null)
    {
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');
        $this->addToolbar();


//        Đoạn mã này thường được sử dụng trong các view hoặc controller của Joomla
//        để kiểm tra xem có lỗi gì xảy ra trong quá trình lấy dữ liệu từ model hay không.
//        Nếu có lỗi, thay vì tiếp tục hiển thị trang với dữ liệu không đầy đủ hoặc không chính xác,
//        đoạn mã này sẽ dừng thực hiện và hiển thị thông báo lỗi.
        if (count($errors = $this->get('Errors')))
        {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        return parent::display($tpl);

    }

    protected function addToolBar()
    {
        if(empty($this->item->Masv)){
            // Hiển thị tiêu đề và thêm nút "Lưu" và "Hủy"
            ToolbarHelper::title('Nhập kết quả');
            ToolbarHelper::save('Result.save', 'Thêm');
            ToolbarHelper::cancel('Result.cancel', 'Hủy');
        }
        else{
            // Hiển thị tiêu đề và thêm nút "Lưu" và "Hủy"
            ToolbarHelper::title('Sửa kết quả');
            ToolbarHelper::save('Result.save', 'Lưu');
            ToolbarHelper::cancel('Result.cancel', 'Hủy');
        }
    }
}