<?php
namespace Kma\Component\Eqa\Administrator\Controller;
defined('_JEXEC') or die();
require JPATH_ROOT.'/vendor/autoload.php';

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Language\Text;
use JRoute;
use Kma\Component\Eqa\Administrator\Helper\DatabaseHelper;
use Kma\Component\Eqa\Administrator\Helper\EmployeeHelper;
use Kma\Component\Eqa\Administrator\Base\EqaAdminController;
use Kma\Component\Eqa\Administrator\Helper\ExamHelper;
use Kma\Component\Eqa\Administrator\Helper\GeneralHelper;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xls;

class ClassesController extends EqaAdminController {
    public function import(): void
    {
        $fileFormField = 'file_classes';
        // Check for request forgeries.
        $this->checkToken();

        //Set redirect to list view in any case
        $this->setRedirect(
            JRoute::_(
                'index.php?option=' . $this->option . '&view=' . $this->view_list
                . $this->getRedirectToListAppend(),
                false
            )
        );

        //Access Check
        $app = Factory::getApplication();
        if(!$app->getIdentity()->authorise('core.create','com_eqa'))
        {
            $this->setMessage(Text::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'), 'error');
            return;
        }

        //Check input files
        $files = $this->input->files->get($fileFormField);
        if(empty($files[0]['tmp_name'])){
            $this->setMessage(Text::_('COM_EQA_MSG_ERROR_NO_FILE_UPLOADED'), 'error');
            return;
        }

        //Preparing some utilities for import operation
        $db = DatabaseHelper::getDatabaseDriver();
        $model = $this->getModel();
        $subjectMap = DatabaseHelper::getSubjectMap();
        $learnerMap = DatabaseHelper::getLearnerMap();

        //Initialize table objects
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $now = date('Y-m-d H:i:s');
        $autoYearAndTerm = $this->input->getInt('auto_year_and_term');
        if(!$autoYearAndTerm){
            $academicyear_id = $this->input->getInt('academicyear_id');
            $term = $this->input->getInt('term');
        }
        $class = new Creditclass();
        $class->created_by = GeneralHelper::getCurrentUserId();
        $class->created_at = $now;
        $class_learner = new ClassLearner();
        $parser = new ClassnameParser();

        //Process files
        foreach ($files as $file)
        {
            try {
                // Check if the file is Excel 97 (.xls)
                $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
                if ($fileExtension === 'xls') {
                    $reader = new Xls();
                } else {
                    // Assume it's an Excel 2007 or later (.xlsx)
                    $reader = IOFactory::createReader('Xlsx');
                }
                $spreadsheet = $reader->load($file['tmp_name']);
            }
            catch (Exception $e){
                $msg = '<b>' . htmlentities($file['name']) . '</b> : ' . $e->getMessage();
                $app->enqueueMessage($msg, 'error');
                continue;
            }


            $sheetNumber = $spreadsheet->getSheetCount();

            //Xử lý từng Worksheet
            for($sh=0; $sh<$sheetNumber; $sh++) {
                $worksheet = $spreadsheet->getSheet($sh);
                $data = $worksheet->toArray('');

                //Lấy tên lớp học phần tại ô C7 và kiểm tra tính hợp lệ
                $class->name = trim($data[6][2]);
                if(!$parser->parse($class->name))
                {
                    $msg = Text::_('COM_EQA_MSG_INVALID_CLASS_NAME');
                    $msg .= ' : ' . $file['name'].' --> '.$worksheet->getTitle().' : '.$class->name;
                    $app->enqueueMessage(htmlentities($msg),'error');
                    continue;
                }

                //Nếu là lớp con thì bỏ qua
                if(!$parser->isPrimaryClass)
                {
                    continue;
                }

                //Xác định năm học và học kỳ từ tên lớp học phần (nếu cần)
                if($autoYearAndTerm){
                    $academicyear_id = DatabaseHelper::getAcademicyearId($parser->year);
                    if(empty($academicyear_id))
                    {
                        $msg = Text::sprintf('COM_EQA_MSG_ACADEMICYEAR_DOES_NOT_EXIST_FOR_CLASS_S',htmlspecialchars($class->name));
                        $app->enqueueMessage(htmlentities($msg),'error');
                        continue;
                    }
                    $term = $parser->term;
                }
                $class->academicyear_id = $academicyear_id;
                $class->term = $term;

                //Lấy mã môn học tại ô M6 và xác định id, hình thức thi của môn học
                $subjectCode = trim($data[5][12]);
                if(!isset($subjectMap[$subjectCode])){
                    $msg = Text::_('COM_EQA_MSG_SUBJECT_CODE_DOES_NOT_EXIST');
                    $msg .= ' : ' . $file['name'].' --> '.$worksheet->getTitle().' : '.$subjectCode;
                    $app->enqueueMessage(htmlentities($msg),'error');
                    continue;
                }
                $class->subject_id = $subjectMap[$subjectCode];

                //Lấy họ và tên giảng viên tại ô D8 và xác định Id của giảng viên
                $lecturerFullname = trim($data[7][3]);
                $class->lecturer_id = EmployeeHelper::getId($lecturerFullname);

                //Tính toán mã lớp học phần theo công thức
                //[Mã môn học]-[Học kỳ]-[Năm]([coursegroup]-[phân ngành][số hiệu lớp])
                $class->code  = $subjectCode . '-' . $parser->getClassCodeTail();

                //Xác định nhóm khóa đào tạo
                $class->coursegroup = $parser->coursegroup;

                //Khởi tạo sĩ số lớp
                $class->size = 0;

                //Thực hiện import dữ liệu
                //Áp dụng transaction theo từng lớp học (từng sheet)
                $db->transactionStart();
                try{
                    //1. Tạo lớp học phần để lấy id của nó
                    $table = $model->getTable();
                    $class->id=null;
                    if(!$table->save($class))       //Tạo lớp học phần thất bại
                    {
                        $msg = Text::_('COM_EQA_MSG_INSERT_CLASS_FAILED');
                        $msg .= ' : ' . $file['name'] . ' : ' . $worksheet->getTitle() . ' : ' .$class->name;
                        $app->enqueueMessage(htmlentities($msg),'error');
                    }
                    else                            //Tạo lớp học phần thành công
                    {
                        //Lưu lại id của lớp học phần vừa mới được tạo
                        $class->id = $db->insertid();

                        //2. Import thông tin các HVSV
                        //   Sinh viên đầu tiên ở dòng số 14 (0-based), cụ thể là ô B15
                        $countAbsence = 0;
                        $countFail = 0;
                        $countSuccess = 0;
                        $listAbsence='';
                        $listFail = '';
                        $class_learner->class_id = $class->id;
                        for($i=14;;$i++)  //Duyệt danh sách HVSV
                        {
                            $learnerCode = trim($data[$i][1]);
                            if(empty($learnerCode))                 //Kết thúc danh sách HVSV
                                break;

                            if(!isset($learnerMap[$learnerCode])){  //HVSV chưa có trong CSDL
                                $countAbsence++;
                                $listAbsence .= $learnerCode . ', ';
                            }
                            else{
                                $class_learner->learner_id = $learnerMap[$learnerCode];
                                if(!$db->insertObject('#__eqa_class_learner',$class_learner)){  //Thêm HVSV thất bại
                                    $countFail++;
                                    $listFail .= $learnerCode . ', ';
                                }
                                else{
                                    $countSuccess++;
                                }
                            }
                        }

                        //3. Duyệt xong. Cập nhật lại sĩ số và xuất các thông báo
                        $class->size = $countSuccess;
                        $table->save($class);
                        $msg = $class->name . ': ';
                        if($countAbsence==0 && $countFail==0) {
                            $format = Text::_('COM_EQA_MSG_CLASS_IMPORT_N_LEARNERS_SUCCESS');
                            $msg .= sprintf($format, $countSuccess);
                            $app->enqueueMessage($msg,'success');
                        }
                        else{
                            if($countAbsence>0){
                                $format = Text::_('COM_EQA_MSG_CLASS_IMPORT_N_LEARNERS_ABSENT');
                                $msg .= sprintf($format, $countAbsence);
                                $msg .= ' (' . substr($listAbsence,0,strlen($listAbsence)-2) . '); ';
                            }
                            if($countFail>0){
                                $format = Text::_('COM_EQA_MSG_CLASS_IMPORT_N_LEARNERS_FAILED');
                                $msg .= sprintf($format, $countFail);
                                $msg .= ' (' . substr($listFail,0,strlen($listFail)-2) . ')';
                            }
                            $app->enqueueMessage($msg,'error');
                        }
                    }
                    //4. Commit
                    $db->transactionCommit();
                }
                catch (Exception $e){
                    $db->transactionRollback();
                    throw $e;
                }
            }
        }
    }
    public function importPam(): void
    {
        $fileFormField = 'files';

        // Check for request forgeries.
        $this->checkToken();


        //Set redirect to list view in any case
        $this->setRedirect(
            JRoute::_(
                'index.php?option=' . $this->option . '&view=' . $this->view_list
                . $this->getRedirectToListAppend(),
                false
            )
        );

        //Access Check
        $app = Factory::getApplication();
        if(!$app->getIdentity()->authorise('core.edit','com_eqa'))
        {
            $this->setMessage(Text::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'), 'error');
            return;
        }

        //Get and Check form input
        $optionIgnoreBlankClasses = $this->input->getInt('ignore_blank_classes');
        $optionCompletePamCalculation = $this->input->getInt('complete_pam_calculation');
        $optionPamDateToday = $this->input->getInt('pam_date_today');
        $files = $this->input->files->get('files');
        if(empty($files)){
            $this->setMessage(Text::_('COM_EQA_MSG_ERROR_NO_FILE_UPLOADED'), 'error');
            return;
        }

        //Preparing some utilities for import operation
        $db = DatabaseHelper::getDatabaseDriver();
        $filter = InputFilter::getInstance();
        $learnerMap = DatabaseHelper::getLearnerMap(); //[code]=>id

        //Initialize table objects
        $parser = new ClassnameParser();

        //Process files
        foreach ($files as $file)
        {
            try {
                // Check if the file is Excel 97 (.xls)
                $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
                if ($fileExtension === 'xls') {
                    $reader = new Xls();
                } else {
                    // Assume it's an Excel 2007 or later (.xlsx)
                    $reader = IOFactory::createReader('Xlsx');
                }
                $spreadsheet = $reader->load($file['tmp_name']);
            }
            catch (Exception $e){
                $msg = '<b>' . htmlentities($file['name']) . '</b> : ' . $e->getMessage();
                $app->enqueueMessage($msg, 'error');
                continue;
            }


            $sheetNumber = $spreadsheet->getSheetCount();

            //Xử lý từng Worksheet
            for($sh=0; $sh<$sheetNumber; $sh++) {
                $worksheet = $spreadsheet->getSheet($sh);
                $data = $worksheet->toArray('');

                //Lấy tên lớp học phần tại ô C7 và kiểm tra tính hợp lệ
                $className = trim($data[6][2]);
                if(!$parser->parse($className))
                {
                    $msg = Text::_('COM_EQA_MSG_INVALID_CLASS_NAME');
                    $msg .= ' : ' . $file['name'].' --> '.$worksheet->getTitle().' : '.$className;
                    $app->enqueueMessage(htmlentities($msg),'error');
                    continue;
                }

                //Nếu là lớp con thì bỏ qua
                if(!$parser->isPrimaryClass)
                {
                    continue;
                }

                //Tính toán mã lớp học phần theo công thức
                //[Mã môn học]-[Học kỳ]-[Năm]([coursegroup]-[phân ngành][số hiệu lớp])
                //Sau đó xác định id của lớp học phần
                $subjectCode = trim($data[5][12]);
                $classCode  = $subjectCode . '-' . $parser->getClassCodeTail();
                $classId = DatabaseHelper::getClassId($classCode);
                if(empty($classId)){
                    $msg = Text::sprintf('COM_EQA_MSG_CLASS_CODE_S_DOES_NOT_EXIST', $classCode);
                    $app->enqueueMessage($msg, 'error');
                    continue;
                }

                //Kiểm tra xem có phải lớp trắng hay không
                //Bằng cách kiểm tra TP1 của SV đầu tiên trong danh sách tại ô I15
                $pam1 = $data[14][8];
                if(trim($pam1)=='')
                {
                    if($optionIgnoreBlankClasses)
                        continue;
                    else{
                        $msg = Text::sprintf('COM_EQA_MSG_INVALID_PAM_AT_CLASS_S', $classCode);
                        $app->enqueueMessage($msg,'error');
                        return;
                    }
                }

                //Thực hiện import dữ liệu
                //Áp dụng transaction theo từng lớp học (từng sheet)
                $db->transactionStart();
                $classLearners = DatabaseHelper::getClassLearners($classId);
                $countIgnored=0;
                $countSet=0;
                $countTotal=0;
                try{
                    for($i=14;;$i++)  //Duyệt danh sách HVSV
                    {
                        $learnerCode = trim($data[$i][1]);
                        if(empty($learnerCode))                 //Kết thúc danh sách HVSV
                            break;

                        $countTotal++;
                        if(!isset($learnerMap[$learnerCode])){
                            $msg = Text::sprintf('COM_EQA_MSG_LEARNER_CODE_S_DOES_NOT_EXIST',$learnerCode);
                            throw new Exception($msg);
                        }
                        $learnerId = $learnerMap[$learnerCode];

                        //Kiểm tra xem HVSV có tên trong lớp học phần hay không
                        if(!isset($classLearners[$learnerId])){
                            $msg = Text::sprintf('COM_EQA_MSG_LEARNER_S_NOT_IN_CLASS_S',$learnerCode,$classCode);
                            throw new Exception($msg);
                        }

                        //Kiểm tra xem HVSV hiện thời đã có điểm quá trình hay chưa
                        //nếu đã có điểm thì bỏ qua (KHÔNG UPDATE điểm)
                        $learner = $classLearners[$learnerId];
                        if(!is_null($learner['pam1']) || !is_null($learner['pam2']) || !is_null($learner['pam']))
                        {
                            $countIgnored++;
                            continue;
                        }

                        //Lấy điểm quá trình từ trong danh sách để ghi vào DB
                        //Một số GV nhập điểm với dấu phẩy thay vì dấu chấm ==> Cần xem xét để xử lý
	                    $description ='';
                        $pam1 = $data[$i][8];
                        if(is_string($pam1)){
                            $pam1 = str_replace(',','.',trim($pam1));
                        }
                        if(!is_numeric($pam1)){
	                        if($pam1==='N25' || $pam1==='N100' || $pam1==='TKD' || $pam1==='TKĐ'){
		                        $description = $pam1;
		                        $pam1=0;
	                        }
							else{
								$msg = Text::sprintf('COM_EQA_MSG_INVALID_PAM_AT_CLASS_S', $classCode.':'.$learnerCode);
								throw new Exception($msg);
							}
						}
						else
                            $pam1 = (float)$pam1;

                        $pam2 = $data[$i][9];
                        if(is_string($pam2)){
                            $pam2 = str_replace(',','.',trim($pam2));
                        }
                        if(!is_numeric($pam2)){
	                        if($pam2==='N25' || $pam2==='N100' || $pam2==='TKD' || $pam2==='TKĐ'){
		                        $description = $pam2;
		                        $pam2=0;
	                        }
	                        else{
		                        $msg = Text::sprintf('COM_EQA_MSG_INVALID_PAM_AT_CLASS_S', $classCode.':'.$learnerCode);
		                        throw new Exception($msg);
	                        }
                        }
						else
                            $pam2 = (float)$pam2;

                        $pam = $data[$i][10];
                        if(is_string($pam)){
                            $pam = str_replace(',','.',trim($pam));
                            if($pam==''){
                                if($optionCompletePamCalculation)
                                    $pam = ExamHelper::getPamForDefaultFormular($pam1, $pam2);
                                else{
                                    $msg = Text::sprintf('COM_EQA_MSG_INVALID_PAM_AT_CLASS_S', $classCode.':'.$learnerCode);
                                    throw new Exception($msg);
                                }
                            }
                            elseif(!is_numeric($pam)){
	                            if($pam==='N25' || $pam==='N100' || $pam==='TKD' || $pam==='TKĐ'){
		                            $description = $pam;
		                            $pam=0;
	                            }
	                            else{
		                            $msg = Text::sprintf('COM_EQA_MSG_INVALID_PAM_AT_CLASS_S', $classCode.':'.$learnerCode);
		                            throw new Exception($msg);
	                            }
                            }
                            else
                                $pam = (float)$pam;
                        }
                        $allowed = ExamHelper::isAllowedToFinalExam($pam1,$pam2,$pam);
	                    $description2 = $data[$i][12]; //Column M
	                    $description2 = $filter->clean($description2);
	                    $description2 = trim($description2);
						if(!empty($description) && !empty($description2))
							$description .= '; ' . $description2;
						else
							$description .= $description2;

                        //Ghi vào CSDL
                        $columnValues = array(
                            $db->quoteName('pam1') . '=' . $pam1,
                            $db->quoteName('pam2') . '=' . $pam2,
                            $db->quoteName('pam') . '=' . $pam,
                            $db->quoteName('allowed') . '=' . $allowed
                        );
                        if(!empty($description))
                            $columnValues[] = $db->quoteName('description') . '=' . $db->quote($description);
                        if(!$allowed)
                            $columnValues[] = $db->quoteName('expired') . '=1';

                        $query = $db->getQuery(true)
                            ->update('#__eqa_class_learner')
                            ->set($columnValues)
                            ->where(array(
                                $db->quoteName('class_id') . '=' . $classId,
                                $db->quoteName('learner_id') . '=' . $learnerId
                            ));
                        $db->setQuery($query);
                        if(!$db->execute())
                            throw new Exception(Text::_('COM_EQA_MSG_DATABASE_ERROR'));
                        $countSet++;
                    }

                    //Cập nhật số lượng HVSV có ĐQT
                    $npam = DatabaseHelper::updateClassNPam($classId);

                    //Cập nhật ngày bàn giao điểm quấ trình của lớp học
                    if($optionPamDateToday && $npam == sizeof($classLearners)){
                        DatabaseHelper::setClassPamDate($classId);
                    }

                    //4. Commit
                    $db->transactionCommit();
                    $msg = Text::sprintf('COM_EQA_MSG_IMPORT_PAM_CLASS_S_SIZE_N_TOTAL_N_SET_N_IGNORED_N',
                        $classCode, sizeof($classLearners), $countTotal, $countSet, $countIgnored);
                    $app->enqueueMessage($msg);
                }
                catch (Exception $e){
                    $db->transactionRollback();
                    $app->enqueueMessage($e->getMessage(),'error');
                    return;
                }
            }
        }
    }
}

class Creditclass{
    public int|null $id;
    public string $coursegroup;
    public string $code;
    public string $name;
    public int $size;
    public int $subject_id;
    public int|null $lecturer_id;
    public int $academicyear_id;
    public int $term;
    public string $created_by;
    public string $created_at;
}

class ClassLearner{
    public int $class_id;
    public int $learner_id;
}

class ClassnameParser{
    public string $subject;
    public string $term;
    public string $year;
    public string $subProgram;
    public string $coursegroup;
    public string $order;
    public bool $isPrimaryClass;

    /*
     * Có 3 dạng PATTERN cơ bản
     * 1) Lớp chính
     * 2) Lớp con
     * 3) Lớp thuần túy thực hành
     * Mỗi PATTERN gồm 6 groups
     * - Group1: Tên môn học
     * - Group2: Học kỳ
     * - Group3: Năm (năm đầu của năm học)
     * - Group4: Nhóm khóa học
     * - Group5(*): Phân ngành
     * - Group6: Số hiệu lớp
     * Ví dụ:
     * An toàn hệ thống nhúng-2-23 (DT4-HTN-01)
     * Giáo dục thể chất 4-2-23- bóng bàn (C7D6-.01)
     */

    protected const PATTERN1 = '/^([\s\S]+)-([1-3])-([0-9]{2})-?[\p{L}\s]*\(([A-Z0-9]+)-?([\p{L}\s]*)-?([0-9]{2})\)$/u';
    protected const PATTERN2 = '/^([\s\S]+)-([1-3])-([0-9]{2})-?[\p{L}\s]*\(([A-Z0-9]+)-?([\p{L}\s]*)-?([0-9]{2})\.[0-9]{1,2}\)$/u';
    protected const PATTERN3 = '/^([\s\S]+)-([1-3])-([0-9]{2})-?[\p{L}\s]*\(([A-Z0-9]+)-?([\p{L}\s]*)\.([0-9]{1,2})\)$/u';

    /*
     * Ngoài ra có PATTERN thứ 4 dành riêng cho môn Giáo dục thể chất 5
     * Ví dụ:   Giáo dục thể chất 5-1-24 (C7D601-bóng bàn)
     * -Groups 1-4: như trên
     * -Group5: số hiệu lớp
     * -Group6: phân môn
     */
    protected const PATTERN4 = '/^([\s\S]+)-([1-3])-([0-9]{2})\s*\(([A-Z0-9]+)([0-9]{2})-([\p{L}\s]*)\)$/u';

    public function parse(string $name):bool
    {
        //init
        $this->isPrimaryClass = true;

        //Try PATTERN1 (Lớp Lý thuyết)
        $matched = preg_match(self::PATTERN1, $name, $matches);

        //Try PATTERN2 (Lớp con thực hành)
        if(!$matched) {
            $matched = preg_match(self::PATTERN2, $name, $matches);
            if($matched)
                $this->isPrimaryClass = false;
        }

        //Try PATTERN3 (Lớp thuần túy thực hành)
        if(!$matched){
            $matched = preg_match(self::PATTERN3, $name, $matches);
        }

        if($matched)
        {
            $this->subject = $matches[1];
            $this->term = $matches[2];
            $this->year = $matches[3];
            $this->coursegroup = $matches[4];
            $this->order = $matches[6];

            $s = trim($matches[5]);
            $this->subProgram = match ($s){
                'An toàn', 'AT hệ thống TT' => 'AT',
                'Công nghệ' => 'CN',
                'kỹ nghệ', 'Kỹ nghệ ATM' => 'KN',
                'HTN' => 'HTN',
                default => ''
            };
            return true;
        }


        //Kiểm tra pattern4
        $matched = preg_match(self::PATTERN4, $name, $matches);
        if($matched)
        {
            $this->subject = $matches[1];
            $this->term = $matches[2];
            $this->year = $matches[3];
            $this->coursegroup = $matches[4];
            $this->order = $matches[5];
            $this->subProgram='';
            return true;
        }
        return false;
    }
    public function getClassCodeTail(){
        $tail = $this->term . '-' . $this->year;
        $tail .= '(' . $this->coursegroup . '-' . $this->subProgram . $this->order . ')';
        return $tail;
    }

}
