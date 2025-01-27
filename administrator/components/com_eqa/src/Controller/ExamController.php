<?php
namespace Kma\Component\Eqa\Administrator\Controller;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use JRoute;
use Kma\Component\Eqa\Administrator\Base\EqaFormController;
use Kma\Component\Eqa\Administrator\Helper\DatabaseHelper;
use Kma\Component\Eqa\Administrator\Helper\DatetimeHelper;
use Kma\Component\Eqa\Administrator\Helper\ExamHelper;
use Kma\Component\Eqa\Administrator\Helper\IOHelper;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

defined('_JEXEC') or die();
require_once JPATH_ROOT.'/vendor/autoload.php';

class ExamController extends  EqaFormController {
	public function cancel($key = null)
	{
		$this->checkToken();
		parent::cancel($key);

		//Rerite the redirect URL for 'cancel' in the 'addexaminees' layout
		$examId = $this->input->post->getInt('exam_id');
		if(!empty($examId))
			$this->setRedirect(Route::_('index.php?option=com_eqa&view=examexaminees&exam_id='.$examId,false));
	}
    public function removeExaminees(){
        //Get exam id
        $examId = $this->input->getInt('exam_id');

        //Set redirect in any case
        $this->setRedirect(JRoute::_('index.php?option=com_eqa&view=examexaminees&exam_id='.$examId,false));

        // Check for request forgeries
        if(!$this->checkToken('post',false))
            return;

        //Check permissions
        if(!$this->app->getIdentity()->authorise('core.delete',$this->option)){
            $this->app->enqueueMessage(Text::_('COM_EQA_MSG_UNAUTHORISED'),'error');
            return;
        }

        // Get items to remove from the request.
        $learnerIds = (array) $this->input->get('cid', [], 'int');

        // Remove zero values resulting from input filter
        $learnerIds = array_filter($learnerIds);

        if (empty($learnerIds)) {
            $this->app->enqueueMessage('COM_EQA_NO_ITEM_SELECTED','warning');
        } else {
            // Get the model.
            $model = $this->getModel();

            // Remove the items.
            $model->removeExaminees($examId, $learnerIds);
        }

    }
    public function addExaminees()
    {
        //Get the id of the exam to add examinees
        $examId = $this->app->input->getInt('exam_id');

        // Access check
        if (!$this->app->getIdentity()->authorise('core.create',$this->option)) {
            // Set the internal error and also the redirect error.
            $this->setMessage(Text::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_CREATE'), 'error');
            $this->setRedirect(
                Route::_(
                    'index.php?option=com_eqa&view=examexaminees&exam_id='.$examId,
                    false
                )
            );
            return false;
        }

        //Xác định pha của nhiệm vụ
        $phase = $this->app->input->getAlnum('phase','');
        if($phase !== 'getdata')
        {
            // Redirect to the 'add examinees' screen.
            $this->setRedirect(
                Route::_(
                    'index.php?option=com_eqa&view=exam&layout=addexaminees&exam_id='.$examId,
                    false
                )
            );
        }
        else
        {
            //Pha này thì cần check token
            $this->checkToken();

            //1. Chuẩn bị dữ liệu
            //1.1 Mã lớp học phần
            $classCode = trim($this->input->getString('classcode'));

            //1.2. Mã thí sinh
            $inputExamineeCodes = $this->input->getString('learnercodelist');
            $normalizedExamineeCodes = preg_replace('/[\s,;]+/', ' ', $inputExamineeCodes);
            $normalizedExamineeCodes = trim($normalizedExamineeCodes);
            $examineeCodes = explode(' ', $normalizedExamineeCodes);

            //1.3. Lần thi
            $attempt = $this->input->getInt('attempt');

            //2. Gọi model để thêm thí sinh
            $model = $this->getModel();
            $model->addExaminees($examId, $classCode, $examineeCodes, $attempt);

            //Add xong thì redirect về trang xem danh sách lớp học phần
            $this->setRedirect(
                Route::_(
                    'index.php?option=com_eqa&view=examexaminees&exam_id='.$examId,
                    false
                )
            );
        }

        return true;

    }
	public function saveQuestion()
	{
		//Check token
		$this->checkToken();

		//Check permission
		if(!$this->app->getIdentity()->authorise('core.edit',$this->option))
		{
			$msg = Text::_('COM_EQA_MSG_UNAUTHORISED');
			$this->setMessage($msg, 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_eqa', false));
			return;
		}

		//Get data
		$input = $this->input->post;
		$examId = $input->getInt('exam_id');
		$questionAuthorId = $input->getInt('questionauthor_id');
		$questionSenderId = $input->getInt('questionsender_id');
		$questionQuantity = $input->getInt('nquestion');
		$questionDate = $input->getString('questiondate');
		if(empty($examId) || empty($questionAuthorId) || empty($questionSenderId) || empty($questionQuantity) || !DatetimeHelper::isValidDate($questionDate))
		{
			$msg = Text::_('COM_EQA_MSG_INVALID_DATA');
			$this->setMessage($msg, 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_eqa&view=exam&layout=question', false));
			return;
		}

		//Cập nhật thông tin về đề thi, đồng thời cập nhật trạng thái môn thi
		$model = $this->getModel();
		$model->updateExamQuestion($examId, $questionAuthorId, $questionSenderId, $questionQuantity, $questionDate);

		//Redirect
		$this->setRedirect(JRoute::_('index.php?option=com_eqa'));
	}
	public function distribute()
	{
		//Get the id of the exam to add examinees
		$examId = $this->app->input->getInt('exam_id');

		// Access check
		if (!$this->app->getIdentity()->authorise('core.create',$this->option)) {
			// Set the internal error and also the redirect error.
			$this->setMessage(Text::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_CREATE'), 'error');
			$this->setRedirect(
				Route::_(
					'index.php?option=com_eqa&view=examexaminees&exam_id='.$examId,
					false
				)
			);
			return false;
		}

		//Xác định pha của nhiệm vụ
		$phase = $this->app->input->getAlnum('phase','');
		if($phase !== 'getdata')
		{
			// Redirect to the 'distribute' screen.
			$this->setRedirect(
				Route::_(
					'index.php?option=com_eqa&view=exam&layout=distribute&exam_id='.$examId,
					false
				)
			);
		}
		else
		{
			//Pha này thì cần check token
			$this->checkToken();

			//1. Chuẩn bị dữ liệu

			//2. Gọi model để thêm thí sinh
			$model = $this->getModel();
			$data = $this->input->get('jform',null,'array');
			$model->distribute($examId, $data);

			//Add xong thì redirect về trang xem danh sách lớp học phần
			$this->setRedirect(
				Route::_(
					'index.php?option=com_eqa&view=examrooms',
					false
				)
			);
		}

		return true;

	}
	public function distribute2()
	{
		//Check token
		$this->checkToken();

		//Get the id of the exam to add examinees
		$examId = $this->app->input->getInt('exam_id');

		// Access check
		if (!$this->app->getIdentity()->authorise('core.create',$this->option)) {
			// Set the internal error and also the redirect error.
			$this->setMessage(Text::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_CREATE'), 'error');
			$this->setRedirect(
				Route::_(
					'index.php?option=com_eqa&view=examexaminees&exam_id='.$examId,
					false
				)
			);
			return false;
		}

		//1. Chuẩn bị dữ liệu
		$data = $this->input->get('jform',null,'array');

		//2. Gọi model để thêm thí sinh
		$model = $this->getModel();
		$model->distribute2($examId, $data);

		//Add xong thì redirect về trang xem danh sách lớp học phần
		$this->setRedirect(
			Route::_(
				'index.php?option=com_eqa&view=examrooms',
				false
			)
		);

		return true;

	}
	public function export()
	{
		$app = $this->app;
		$this->checkToken();
		if(!$app->getIdentity()->authorise('core.manage',$this->option))
		{
			echo Text::_('COM_EQA_MSG_UNAUTHORISED');
			exit();
		}

		//Prepare data
		$examId = $this->input->getInt('exam_id');
		if(empty($examId))
		{
			$this->setMessage(Text::_('COM_EQA_MSG_ERORR_OCCURRED'));
			return;
		}
		$exam = DatabaseHelper::getExamInfo($examId);
		$examinees = DatabaseHelper::getExamExaminees($examId, false);

		// Prepare the spreadsheet
		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getSheet(0);
		$sheetName = $exam->name;
		if(strlen($sheetName)>20)
			$sheetName = substr($sheetName,0,20);
		$sheetName .= ' (' . $exam->id . ')';
		$sheet->setTitle($sheetName);
		IOHelper::writeExamExaminees($sheet, $exam, $examinees);

		// Export the spreadsheet to a temporary file
		$tempFile = tempnam(sys_get_temp_dir(), $exam->name) . '.xlsx';
		$writer = new Xlsx($spreadsheet);
		$writer->save($tempFile);

		// Force download of the Excel file
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="Danh sách thí sinh của môn thi.xlsx"');
		header('Cache-Control: max-age=0');
		readfile($tempFile);

		// Clean up temporary file
		unlink($tempFile);
		exit();
	}
	public function exportitest()
	{
		$app = $this->app;
		$this->checkToken();
		if(!$app->getIdentity()->authorise('core.manage',$this->option))
		{
			echo Text::_('COM_EQA_MSG_UNAUTHORISED');
			exit();
		}

		//Prepare data
		$examId = $this->input->getInt('exam_id');
		if(empty($examId))
		{
			$this->setMessage(Text::_('COM_EQA_MSG_ERORR_OCCURRED'), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_eqa&view=examexaminees&exam_id='.$examId, false));
			return;
		}
		$exam = DatabaseHelper::getExamInfo($examId);

		//Nếu không phải thi trắc nghiệm thì bỏ
		if($exam->testtype != ExamHelper::TEST_TYPE_MACHINE_OBJECTIVE && $exam->testtype!=ExamHelper::TEST_TYPE_MACHINE_HYBRID)
		{
			$this->setMessage(Text::_('COM_EQA_MSG_NOT_MACHINE_TEST'), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_eqa&view=examexaminees&exam_id='.$examId, false));
			return;
		}


		//Get info about all the examinees of the exam, ordering by exam time
		$db = DatabaseHelper::getDatabaseDriver();
		$columms = $db->quoteName(
			array('d.start', 'b.name', 'c.code',       'a.code'),
			array('start',   'room',   'learner_code', 'code')
		);
		$query = $db->getQuery(true)
			->from('#__eqa_exam_learner AS a')
			->leftJoin('#__eqa_examrooms AS b', 'a.examroom_id=b.id')
			->leftJoin('#__eqa_learners AS c', 'a.learner_id=c.id')
			->leftJoin('#__eqa_examsessions AS d', 'b.examsession_id=d.id')
			->select($columms)
			->where('a.examroom_id>0 AND a.exam_id='.$examId)
			->order(array(
				$db->quoteName('start') . ' ASC',
				'code ASC'
			));
		$db->setQuery($query);
		$items = $db->loadObjectList();

		// Prepare the spreadsheet
		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getSheet(0);

		//Write the header row
		$sheet->setCellValue('A1', 'Đợt thi');
		$sheet->setCellValue('B1', 'Ngày thi');
		$sheet->setCellValue('C1', 'Ca/Tiết');
		$sheet->setCellValue('D1', 'Phòng thi');
		$sheet->setCellValue('E1', 'Mã TS/TĐN');
		$sheet->setCellValue('F1', 'SBD');
		$sheet->setCellValue('G1', 'Ghi chú 1');
		$sheet->setCellValue('H1', 'Ghi chú 2');
		$sheet->getStyle('A1:H1')->getFont()->setBold(true);

		/*
		 * Ghi thông tin thí sinh. Ca thi sớm nhất được đánh số là 1.
		 * Khi có sự thay đổi 'start' thì tăng ca thêm 1
		 * Riêng "Đợt thi" thì luôn đặt là 1
		 */
		$lastStart='';
		$row=2;
		$session = 0;
		foreach ($items as $item)
		{
			//Xác định ca thi
			if($item->start !== $lastStart)
			{
				$session++;
				$lastStart = $item->start;
			}

			//Ghi các cột
			$sheet->setCellValue('A'.$row, 1);
			$sheet->setCellValue('B'.$row, DatetimeHelper::getFullDate($item->start));
			$sheet->setCellValue('C'.$row, $session);
			$sheet->setCellValue('D'.$row, $item->room);
			$sheet->setCellValue('E'.$row, $item->learner_code);
			$sheet->setCellValue('F'.$row, $item->code);

			//Next
			$row++;
		}


		// Export the spreadsheet to a temporary file
		$tempFile = tempnam(sys_get_temp_dir(), $exam->name) . '.xlsx';
		$writer = new Xlsx($spreadsheet);
		$writer->save($tempFile);

		// Force download of the Excel file
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="Ca iTest - ' . $exam->name . '.xlsx"');
		header('Cache-Control: max-age=0');
		readfile($tempFile);

		// Clean up temporary file
		unlink($tempFile);
		exit();
	}

	/**
	 * Cập nhật thông tin về các trường hợp HVSV được khuyến khích
	 *
	 * @since version 1.0.0
	 */
	public function stimulate()
	{
		//Get exam id
		$examId = $this->input->getInt('exam_id');
		if(empty($examId))
		{
			$this->setMessage(Text::_('COM_EQA_MSG_INVALID_DATA'),'error');
			return;
		}

		//Set redirect in any case
		$this->setRedirect(JRoute::_('index.php?option=com_eqa&view=examexaminees&exam_id='.$examId,false));

		// Check for request forgeries
		if(!$this->checkToken('post',false))
			return;

		//Check permissions
		if(!$this->app->getIdentity()->authorise('core.edit',$this->option)){
			$this->app->enqueueMessage(Text::_('COM_EQA_MSG_UNAUTHORISED'),'error');
			return;
		}

		//Process
		$model = $this->getModel();
		$model->updateStimulations($examId);
	}
	public function importitest()
	{
		//Check token
		$this->checkToken();

		//Check permission
		if(!$this->app->getIdentity()->authorise('core.edit',$this->option))
		{
			$msg = Text::_('COM_EQA_MSG_UNAUTHORISED');
			$this->setMessage($msg, 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_eqa',false));
			return;
		}

		//Get data
		$examId = $this->input->post->getInt('exam_id');
		$multiple = $this->input->post->getInt('multiple');
		$file = $this->input->files->get('file');
		if(empty($examId) || empty($file['tmp_name']))
		{
			$this->setMessage("Dữ liệu form không hợp lệ", 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_eqa',false));
			return;
		}

		//Đọc file
		$spreadsheet = IOHelper::loadSpreadsheet($file['tmp_name']);

		//Tìm sheet để đọc
		if($multiple)
			$sheetName = 'Tất cả';
		else
			$sheetName = 'Bảng điểm';
		$sheet = $spreadsheet->getSheetByName($sheetName);
		if(empty($sheet))
		{
			$msg = Text::sprintf("File không hợp lệ. Không tìm thấy sheet <b>%s</b>", $sheetName);
			$this->setMessage($msg, 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_eqa',false));
			return;
		}

		//Đọc dữ liệu từ file excel.
		//Nạp dữ liệu vào mảng nên index các cột, dòng được tính 0-based
		$data = $sheet->toArray();
		$examinees = [];
		$colCode = 1;
		$colLearnerCode = 2;
		$colMark=8;
		$colwDescription=10;
		for($row=1; true; $row++)
		{
			if(empty($data[$row][$colCode]))
				break;
			$dataRow = $data[$row];
			$examinee = new \stdClass();
			$examinee->code = (int)$dataRow[$colCode];
			$examinee->learnerCode = $dataRow[$colLearnerCode];
			$mark = $dataRow[$colMark];
			if(!is_numeric($mark))
				$examinee->mark = 0;
			else
				$examinee->mark = (float)$mark;
			$examinee->description = $dataRow[$colwDescription];
			$examinees[] = $examinee;
		}

		/**
		 * KIỂM TRA TÍNH CHÍNH XÁC CỦA DỮ LIỆU
		 * Đề phòng trường hợp cán bộ chọn nhầm môn thi khiến việc nhập điểm làm sai lệch số liệu,
		 * ở đây sẽ kiểm tra sự phù hợp giữa Số báo danh với Mã HVSV của 10 thí sinh đầu tiên
		 */
		$len = min([10, sizeof($examinees)]);
		$inputCodes = [];
		for ($i=0; $i<$len; $i++)
		{
			$key = $examinees[$i]->learnerCode;
			$value = $examinees[$i]->code;
			$inputCodes[$key] = $value;
		}
		$ok = DatabaseHelper::checkExamCorrectness($examId, $inputCodes);
		if(!$ok)
		{
			$msg = Text::sprintf("Số báo danh không trùng khớp với mã HVSV. Hãy kiểm tra lại, đảm bảo chọn đúng môn thi");
			$this->setMessage($msg, 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_eqa',false));
			return;
		}

		//Nhập dữ liệu
		$model = $this->getModel();
		if(!$model->importitest($examId, $examinees))
		{
			$this->setRedirect(JRoute::_('index.php?option=com_eqa',false));
			return;
		}

		//Cập nhật trạng thái môn thi
		$exam = DatabaseHelper::getExamInfo($examId);
		$msg = Text::sprintf("Môn thi <b>%s</b>: %d/%d đã có kết quả",
			$exam->name,
			$exam->countConcluded,
			$exam->countToTake + $exam->countExempted
		);
		$this->app->enqueueMessage($msg);
		$this->setRedirect(JRoute::_('index.php?option=com_eqa&view=examexaminees&exam_id='.$examId, false));
	}
}