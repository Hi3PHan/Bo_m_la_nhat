<?php
namespace Kma\Component\Eqa\Administrator\Controller;
defined('_JEXEC') or die();
require_once JPATH_ROOT.'/vendor/autoload.php';

use Joomla\CMS\Language\Text;
use Kma\Component\Eqa\Administrator\Base\EqaAdminController;
use Kma\Component\Eqa\Administrator\Helper\DatabaseHelper;
use Kma\Component\Eqa\Administrator\Helper\IOHelper;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExamsController extends EqaAdminController {
	public function export()
	{
		$app = $this->app;
		$this->checkToken();
		if(!$app->getIdentity()->authorise('core.manage',$this->option))
		{
			echo Text::_('COM_EQA_MSG_UNAUTHORISED');
			exit();
		}

		$examIds = (array) $this->input->get('cid', [], 'int');

		// Prepare the spreadsheet
		$spreadsheet = new Spreadsheet();
		$spreadsheet->removeSheetByIndex(0);

		foreach ($examIds as $index => $examId) {
			$exam = DatabaseHelper::getExamInfo($examId);
			$examinees = DatabaseHelper::getExamExaminees($examId, false);

			// Create a worksheet for each exam
			$sheet = $spreadsheet->createSheet($index);
			/*-----------------
			 * Dường như việc đặt tên unicode cho sheet khiến phát
			 * sinh lỗi mở file trong một số trường hợp!!!!
			$sheetName = $exam->name;
			if(strlen($sheetName)>15)
				$sheetName = substr($sheetName,0,15);
			$sheetName .= ' (' . $exam->id . ')';
			\------------*/
			$sheetName = $examId;
			$sheet->setTitle($sheetName);
			IOHelper::writeExamExaminees($sheet, $exam, $examinees);
		}

		// Force download of the Excel file
		$fileName = "Danh sách thí sinh môn thi.xlsx";
		IOHelper::sendHttpXlsx($spreadsheet, $fileName);
		exit();
	}
}
