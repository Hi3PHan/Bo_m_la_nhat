<?php

namespace Hi3PHan\Component\QLSV\Administrator\Controller;

defined('_JEXEC') or die;
require_once JPATH_ROOT.'/vendor/autoload.php';

use Exception;
use JControllerLegacy;
use JFactory;
use Joomla\CMS\Router\Route;
use PhpOffice\PhpSpreadsheet\IOFactory;


class UploadListStudentsController extends JControllerLegacy
{
//    public function processExcel()
//    {
//        $app = JFactory::getApplication();
//        $file = $app->input->files->get('file', null);
//
//        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
//            $app->enqueueMessage('File upload thất bại.', 'error');
//            $this->setRedirect('index.php?option=com_qlsv&view=upload');
//            return;
//        }
//
//// Đường dẫn tạm để lưu file
//        $filePath = JPATH_ROOT . '/tmp/' . $file['name'];
//        move_uploaded_file($file['tmp_name'], $filePath);
//
//        try {
//// Đọc file Excel
//            $spreadsheet = IOFactory::load($filePath);
//            $sheetData = $spreadsheet->getActiveSheet()->toArray();
//
//// Bỏ hàng tiêu đề
//            unset($sheetData[0]);
//
//// Gọi model để lưu vào cơ sở dữ liệu
//            $model = $this->getModel('Student');
//            foreach ($sheetData as $row) {
//                $data = [
//                    'masv' => $row[0],
//                    'tensv' => $row[1],
//                    'gioitinh' => $row[2],
//                    'quequan' => $row[3],
//                    'ngaysinh' => $row[4],
//                    'lop' => $row[5]
//                ];
//                $model->save($data);
//            }
//
//            $app->enqueueMessage('Dữ liệu đã được lưu thành công!');
//        } catch (Exception $e) {
//            $app->enqueueMessage('Có lỗi xảy ra: ' . $e->getMessage(), 'error');
//        }
//
//// Xóa file sau khi xử lý
//        unlink($filePath);
//
//        $this->setRedirect('index.php?option=com_qlsv&view=upload');
//    }

    public function upload()
    {

        // Hiển thị view upload
        $this->setRedirect(Route::_('index.php?option=com_qlsv&view=UploadListStudents', false));
    }


        public function processUpload()
    {
        JFactory::getApplication()->enqueueMessage('processUpload method called', 'message');

        $app = JFactory::getApplication();
        $file = $app->input->files->get('file', null);

        // Kiểm tra tính hợp lệ của file
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            $app->enqueueMessage('File upload thất bại.', 'error');
            $this->setRedirect('index.php?option=com_qlsv&view=UploadListStudents');
            return;
        }

        // Đường dẫn tạm thời để lưu file
        $filePath = JPATH_ROOT . '/tmp/' . $file['name'];
        move_uploaded_file($file['tmp_name'], $filePath);

        try {
            // Đọc file Excel
            $spreadsheet = IOFactory::load($filePath);
            $sheetData = $spreadsheet->getActiveSheet()->toArray();

            // Bỏ qua dòng tiêu đề (giả sử dòng đầu là tiêu đề)
            unset($sheetData[0]);

            // Lưu dữ liệu vào CSDL
            $model = $this->getModel('UploadListStudents');
            foreach ($sheetData as $row) {
                $data = [
//                    'Masv' => $row[0],
                    'Tensv' => $row[1],
                    'Gioitinh' => $row[2],
                    'Ngaysinh' => $row[3],
                    'Que' => $row[4],
                    'Lop' => $row[5]
                ];
                $model->save($data);
            }

            $app->enqueueMessage('Dữ liệu đã được lưu thành công!');
        } catch (Exception $e) {
            $app->enqueueMessage('Có lỗi xảy ra: ' . $e->getMessage(), 'error');
        }
        // Xóa file tạm sau khi xử lý
        unlink($filePath);

        // Chuyển hướng về trang danh sách sinh viên
        $this->setRedirect('index.php?option=com_qlsv&view=Students');
    }
}
