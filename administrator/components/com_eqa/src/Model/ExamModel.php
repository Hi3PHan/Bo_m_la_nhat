<?php
namespace Kma\Component\Eqa\Administrator\Model;
use Collator;
use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseDriver;
use Kma\Component\Eqa\Administrator\Base\EqaAdminModel;
use Kma\Component\Eqa\Administrator\Helper\ConfigHelper;
use Kma\Component\Eqa\Administrator\Helper\DatabaseHelper;
use Kma\Component\Eqa\Administrator\Helper\ExamHelper;
use Kma\Component\Eqa\Administrator\Helper\RoomHelper;
use Kma\Component\Eqa\Administrator\Helper\StimulationHelper;

defined('_JEXEC') or die();

class ExamModel extends EqaAdminModel{
    public function removeExaminees(int $examId, array $learnerIds): bool
    {
	    if (DatabaseHelper::isCompletedExam($examId))
		    throw new Exception('Môn thi hoặc kỳ thi đã kết thúc. Không thể xóa thí sinh');

        $app = Factory::getApplication();
        $db = $this->getDatabase();
        $db->transactionStart();
        try{
            //Remove examinees
            $query = $db->getQuery(true)
                ->delete('#__eqa_exam_learner')
                ->where('exam_id='.$examId.' AND learner_id IN ('.implode(',',$learnerIds).')');
            $db->setQuery($query);
            $db->execute();

            //Commit
            $db->transactionCommit();
        }
        catch (Exception $e){
            $db->transactionRollback();
            $app->enqueueMessage($e->getMessage(),'error');
            return false;
        }
        $msg = Text::sprintf('COM_EQA_N_ITEMS_DELETED',sizeof($learnerIds));
        $app->enqueueMessage($msg,'success');
        return true;
    }

    /**
     * Thêm (thủ công) HVSV của một lớp học phần vào một môn thi (thường chỉ sử dụng
     * trong trường hợp bổ sung thí sinh hoãn thi từ kỳ trước)
     * @param int $examId
     * @param string $classCode
     * @param array $examineeCodes
     * @return bool
     * @since 1.0.3
     */
    public function addExaminees(int $examId, string $classCode, array $examineeCodes, int $attempt): bool
    {
	    if (DatabaseHelper::isCompletedExam($examId))
		    throw new Exception('Môn thi hoặc kỳ thi đã kết thúc. Không thể thêm thí sinh');

        $app = Factory::getApplication();
        $db = $this->getDatabase();

        //Find the class by its code ($classCode)
        $db->setQuery('SELECT * FROM #__eqa_classes WHERE code='.$db->quote($classCode));
        $class = $db->loadObject();
        if(empty($class))
        {
            $msg = Text::_('COM_EQA_MSG_CLASS_CODE_DOES_NOT_EXIST');
            $msg .= ': <b>' . htmlentities($classCode) . '</b>';
            $app->enqueueMessage($msg,'error');
            return false;
        }

        //Check to ensure that the class and the exam belong to the same subject
        $db->setQuery('SELECT * FROM #__eqa_exams WHERE id='.$examId);
        $exam = $db->loadObject();
        if($class->subject_id != $exam->subject_id){
            $msg = Text::sprintf('COM_EQA_MSG_CLASS_S_NOT_MATCH_EXAM_S',
                htmlentities($class->name),
                htmlentities($exam->name));
            $app->enqueueMessage($msg,'error');
            return false;
        }

        //Try to add the learners to the exam
        $db->transactionStart();
        try {
            $learnerIds = DatabaseHelper::getLearnerMap($examineeCodes);
            foreach ($examineeCodes as $examineeCode){
                //Get the 'id'
                $examineeId = $learnerIds[$examineeCode];

                //Get learner info from the class
                $db->setQuery('SELECT * FROM #__eqa_class_learner WHERE class_id='.$class->id.' AND learner_id='.$examineeId);
                $classLearner = $db->loadObject();
                if(empty($classLearner)) {
                    $msg = Text::sprintf('COM_EQA_MSG_LEARNER_S_NOT_IN_CLASS_S',
                        htmlentities($examineeCode),
                        htmlentities($class->name)
                    );
                    throw new Exception($msg);
                }

                //Add learner to the exam
                $query = $db->getQuery(true)
                    ->insert('#__eqa_exam_learner')
                    ->columns('exam_id, class_id, learner_id, attempt')
                    ->values(implode(',',[$examId, $class->id, $examineeId, $attempt]));
                $db->setQuery($query);
                if(!$db->execute()){
                    $msg = Text::_('COM_EQA_MSG_INSERT_INTO_DATABASE_FAILED');
                    throw new Exception($msg);
                }
            }

            //Commit
            $db->transactionCommit();
            $msg = Text::sprintf('COM_EQA_MSG_N_ITEMS_IMPORT_SUCCESS', sizeof($examineeCodes));
            $app->enqueueMessage($msg,'success');
            return true;
        }
        catch (Exception $e){
            $db->transactionRollback();
            $app->enqueueMessage($e->getMessage(),'error');
            return false;
        }
    }

    public function prepareTable($table)
    {
        if(empty($table->questiondeadline))
            $table->questiondeadline=null;
        if(empty($table->questiondate))
            $table->questiondate=null;
        if(empty($table->questionsender_id))
            $table->questionsender_id=null;
        if(empty($table->questionauthor_id))
            $table->questionauthor_id=null;
        if(empty($table->nquestion))
            $table->nquestion=null;
    }

	public function updateExamQuestion(int $examId, int $questionAuthorId, int $questionSenderId, int $questionQuantity, string $questionDate):bool
	{
		//Init
		$db = DatabaseHelper::getDatabaseDriver();
		$app = Factory::getApplication();

		//Lấy thông tin về exam hiện thời
		$query = $db->getQuery(true)
			->from('#__eqa_exams')
			->select('id, name, usetestbank, status')
			->where('id=' . $examId);
		$db->setQuery($query);
		$exam = $db->loadObject();
		if(empty($exam))
		{
			$app->enqueueMessage('Không tìm thấy môn thi','error');
			return false;
		}

		//Nếu dùng ngân hàng thì không thể nhận đề
		if($exam->usetestbank){
			$msg = Text::sprintf('Môn thi <b>%s</b> sử dụng ngân hàng đề nên không thể nhận đề',$exam->name);
			$app->enqueueMessage($msg,'error');
			return false;
		}

		//Tính toán lại trạng thái môn thi
		$status = $exam->status;
		if($status == ExamHelper::EXAM_STATUS_PAM_BUT_QUESTION)
			$status = ExamHelper::EXAM_STATUS_QUESTION_AND_PAM;
		elseif($status < ExamHelper::EXAM_STATUS_QUESTION_AND_PAM)
			$status = ExamHelper::EXAM_STATUS_QUESTION_BUT_PAM;

		//Cập nhật thông tin đề thi và trạng thái môn thi
		$query = $db->getQuery(true)
			->update('#__eqa_exams')
			->set([
				'questionauthor_id=' . $questionAuthorId,
				'questionsender_id=' . $questionSenderId,
				'nquestion=' . $questionQuantity,
				'questiondate=' . $db->quote($questionDate),
				'status=' . $status
			])
			->where('id=' . $examId);
		$db->setQuery($query);
		if(!$db->execute())
		{
			$msg = Text::sprintf('Cập nhật thông tin thất bại cho môn thi <b>%s</b>', $exam->name);
			$app->enqueueMessage($msg,'error');
			return false;
		}

		//Success
		$msg = Text::sprintf('Cập nhật thông tin thành công cho môn thi <b>%s</b>', $exam->name);
		$app->enqueueMessage($msg, 'success');
		return true;
	}
	public function distribute(int $examId, $data):bool
	{
		if (DatabaseHelper::isCompletedExam($examId))
			throw new Exception('Môn thi hoặc kỳ thi đã kết thúc. Không thể chia phòng');

		$app = Factory::getApplication();
		$db = $this->getDatabase();

		//PHẦN A. KIỂM TRA TÍNH HỢP LỆ CỦA DỮ LIỆU
		//Check input validity
		$validInput = !empty($examId) && !empty($data);
		$validInput = $validInput && isset($data['distribute_allowed_only']) && isset($data['create_new_examrooms']) && isset($data['count_distributed']) && isset($data['examinee_code_start']) && isset($data['examsessions']);
		$validInput = $validInput && is_array($data['examsessions']);
		if(!$validInput)
		{
			$app->enqueueMessage(Text::_('COM_EQA_MSG_INVALID_DATA'),'error');
			return false;
		}

		//Check if there are duplicated rooms within an exam session
		//Or examsessions are duplicated
		$examsessionIds = [];
		foreach ($data['examsessions'] as $examsession){
			$examsessionIds[] = $examsession['examsession_id'];
			$roomIds = array();
			foreach ($examsession['rooms'] as $room)
				$roomIds[] = $room['room_id'];
			if(count($roomIds) != count(array_unique($roomIds)))
			{
				$app->enqueueMessage(Text::_('COM_EQA_MSG_DUPLICATED_ROOMS'),'error');
				return false;
			}
		}
		if (count($examsessionIds) != count(array_unique($examsessionIds)))
		{
			$app->enqueueMessage(Text::_('COM_EQA_MSG_DUPLICATED_EXAMSESSIONS'),'error');
			return false;
		}


		//Get the exam infor
		$exam = DatabaseHelper::getExamInfo($examId);
		if(empty($exam)){
			$app->enqueueMessage(Text::_('COM_EQA_MSG_INVALID_DATA'),'error');
			return false;
		}

		$optionDistributeAllowedOnly = $data['distribute_allowed_only'];
		$optionCreateNewExamrooms = $data['create_new_examrooms'];

		//Check for the quantity correspondence
		if($optionDistributeAllowedOnly)
			$numberToDistribute = $exam->countToTake;
		else
			$numberToDistribute = $exam->countTotal;

		if($numberToDistribute != $data['count_distributed'])
		{
			$app->enqueueMessage(Text::_('COM_EQA_MSG_INVALID_DISTRIBUTION'),'error');
			return false;
		}


		/*
		 * PHẦN B. THỰC HIỆN CHIA PHÒNG
		 */

		//0. Reset thông tin chia phòng cho toàn bộ thí sinh của môn thi
		$query = $db->getQuery(true)
			->update('#__eqa_exam_learner')
			->set(array(
				$db->quoteName('code') . ' = NULL',
				$db->quoteName('examroom_id') . ' = NULL'
			))
			->where('exam_id='.$examId);
		$db->setQuery($query);
		$db->execute();

		//1. Load danh sách thí sinh của môn thi
		$examinees = DatabaseHelper::getExamExaminees($examId, $optionDistributeAllowedOnly);
		if(empty($examinees))
		{
			$app->enqueueMessage(Text::_('COM_EQA_MSG_ERROR_OCCURRED'),'error');
			return false;
		}

		//2. Ngẫu nhiên hóa danh sách thí sinh để chia về cá phòng thi
		shuffle($examinees);

		//3. Gán sinh viên vào các phòng thi và đánh số báo danh
		//3.1. Định nghĩa comparator để phục vụ sắp xếp
		$collator = new Collator('vi_VN');
		$comparator = function($a, $b) use ($collator) {
			$r = $collator->compare($a->firstname, $b->firstname);
			if ($r !== 0)
				return $r;
			else
				return $collator->compare($a->lastname, $b->lastname);
		};

		$db->transactionStart();
		$examineeCode = $data['examinee_code_start'];
		$startIndex=0;
		try {
			foreach ($data['examsessions'] as $examsession){
				$examsessionId = $examsession['examsession_id'];
				foreach ($examsession['rooms'] as $room){
					//3.2. Tạo phòng thi (nếu chưa có) và lấy id của phòng thi
					$roomId = $room['room_id'];
					$nExaminee = $room['nexaminee'];

					//a) Kiểm tra xem với ca thi $examsessionId đã có tồn tại phòng thi với $roomId hay chưa
					//Nếu đã tồn tại thì lấy $examroomId
					$query = $db->getQuery(true)
						->select('*')
						->from('#__eqa_examrooms')
						->where('examsession_id='.$examsessionId . ' AND room_id='.$roomId);
					$db->setQuery($query);
					$examroom = $db->loadObject();
					if(!empty($examroom)){
						$examroomId = $examroom->id;            //get exam room's ID

						if($optionCreateNewExamrooms){ //Nếu yêu cầu tạo phòng mới thì báo lỗi
							$msg = Text::sprintf('COM_EQA_MSG_EXAMSESSION_S_ALREADY_USES_ROOM_S',
								DatabaseHelper::getExamsessionName($examsessionId),
								DatabaseHelper::getRoomCode($roomId)
							);
							throw new Exception($msg);
						}
					}

					//b) Nếu chưa có thì tạo phòng thi và xác định id của phòng thi mới ($examroomId)
					//   Đồng thời tăng số lượng phòng thi của ca thi
					else {
						$roomCode = RoomHelper::getRoomCode($roomId);   //Mặc định cho examroom's name
						$values = array(
							$db->quote($roomCode),
							$roomId, $examsessionId);
						$tuple = implode(',', $values);
						$query = $db->getQuery(true)
							->insert('#__eqa_examrooms')
							->columns($db->quoteName(array('name','room_id','examsession_id')))
							->values($tuple);
						$db->setQuery($query);
						if(!$db->execute())                     //Create a new record
							throw new Exception(Text::_('COM_EQA_MSG_DATABASE_ERROR'));
						$examroomId = $db->insertid();          //Lấy $examroomId
					}

					//3.3. Trích lấy phần thí sinh của phòng thi
					$roomExaminees = array_slice($examinees, $startIndex, $nExaminee);
					$startIndex += $nExaminee;

					//3.4. Sắp xếp theo họ và tên
					usort($roomExaminees, $comparator);

					//3.5. Ghi SBD, phòng thi cho thí
					//Tăng tuần tự SBD trong quá trình này
					for($i=0; $i<$nExaminee; $i++)
					{
						$examinee = $roomExaminees[$i];

						//Gán (hoặc gán lại) phòng thi, SBD cho thí sinh
						$query = $db->getQuery(true)
							->update('#__eqa_exam_learner')
							->set(array(
								'code='.$examineeCode,
								'examroom_id='.$examroomId
							))
							->where('exam_id=' . $examId . ' AND learner_id='.$examinee->id);
						$db->setQuery($query);
						if(!$db->execute())
							throw new Exception(Text::_('COM_EQA_MSG_DATABASE_ERROR'));
						$examineeCode++;
					}

					//Cập nhật lại môn thi của phòng thi
					DatabaseHelper::updateExamroomExams($examroomId);

				}   //End of an exam session
			}       //End of al the $data
		}
		catch (Exception $e){
			$db->transactionRollback();
			$app->enqueueMessage($e->getMessage(), 'error');
			return false;
		}

		$db->transactionCommit();
		$app->enqueueMessage(Text::_('COM_EQA_MSG_TASK_SUCCESS'),'success');
		return true;
	}
	public function distribute2(int $examId, $data):bool
	{
		if (DatabaseHelper::isCompletedExam($examId))
			throw new Exception('Môn thi hoặc kỳ thi đã kết thúc. Không thể chia phòng');

 		$app = Factory::getApplication();
		$db = DatabaseHelper::getDatabaseDriver();

		//PHẦN A. KIỂM TRA TÍNH HỢP LỆ CỦA DỮ LIỆU
		//Check input validity
		$validInput = !empty($examId) && !empty($data);
		$validInput = $validInput && isset($data['distribute_allowed_only']) && isset($data['create_new_examrooms'])  && isset($data['examinee_code_start']) && isset($data['examsessions']);
		$validInput = $validInput && is_array($data['examsessions']);
		if(!$validInput)
		{
			$app->enqueueMessage(Text::_('COM_EQA_MSG_INVALID_DATA'),'error');
			return false;
		}

		$optionDistributeAllowedOnly = $data['distribute_allowed_only'];
		$optionCreateNewExamrooms = $data['create_new_examrooms'];


		//Check if examsessions or credit classes are duplicated
		//and if rooms are occupied
		$examsessionIds = [];
		$classIds = [];
		foreach ($data['examsessions'] as $examsession){
			$examsessionIds[] = $examsession['examsession_id'];
			$roomIds = array();
			foreach ($examsession['rooms'] as $room)
			{
				$roomIds[] = $room['room_id'];
				$classIds[] = $room['class_id'];
			}

			//Check if rooms are occupied for this session
			if(!$optionCreateNewExamrooms)
				continue;
			$roomIdSet = '(' . implode(',', $roomIds) . ')';
			$query = $db->getQuery(true)
				->select('count(1)')
				->from('#__eqa_exam_learner AS a')
				->leftJoin('#__eqa_examrooms AS b', 'a.examroom_id=b.id')
				->where('b.room_id IN ' . $roomIdSet);
			$db->setQuery($query);
			if($db->loadResult() > 0){
				$msg = Text::sprintf('COM_EQA_MSG_EXAMSESSION_S_ALREADY_USES_ROOM_S',
					DatabaseHelper::getExamsessionName($examsession['examsession_id']),
					DatabaseHelper::getRoomCode($room['room_id'])
				);
				$app->enqueueMessage($msg,'error');
				return false;
			}
		}
		if (count($examsessionIds) != count(array_unique($examsessionIds)))
		{
			$app->enqueueMessage(Text::_('COM_EQA_MSG_DUPLICATED_EXAMSESSIONS'),'error');
			return false;
		}
		if (count($classIds) != count(array_unique($classIds)))
		{
			$app->enqueueMessage(Text::_('COM_EQA_MSG_DUPLICATED_CLASSES'),'error');
			return false;
		}



		/*
		 * PHẦN B. THỰC HIỆN CHIA PHÒNG
		 */

		//1. Reset thông tin chia phòng cho toàn bộ thí sinh của môn thi
		$query = $db->getQuery(true)
			->update('#__eqa_exam_learner')
			->set(array(
				$db->quoteName('code') . ' = NULL',
				$db->quoteName('examroom_id') . ' = NULL'
			))
			->where('exam_id='.$examId);
		$db->setQuery($query);
		$db->execute();

		//2. Định nghĩa comparator để phục vụ sắp xếp thí sinh
		$collator = new Collator('vi_VN');
		$comparator = function($a, $b) use ($collator) {
			$r = $collator->compare($a->firstname, $b->firstname);
			if ($r !== 0)
				return $r;
			else
				return $collator->compare($a->lastname, $b->lastname);
		};

		//3. Bắt đầu
		$countExaminee = 0;
		$countExamroom = 0;
		$db->transactionStart();
		$examineeCode = $data['examinee_code_start'];
		try {
			foreach ($data['examsessions'] as $examsession){
				$examsessionId = $examsession['examsession_id'];

				//3.1. Do có thể ghép nhiều lớp học phần vào cùng 1 phòng thi
				//nên cần xác định các phòng thi được sử dụng
				//(Trong một ca thi thì phòng thi được xác định bởi phòng vật lý)
				$roomIds = [];
				foreach ($examsession['rooms'] as $room){
					$roomIds[] = $room['room_id'];
				}
				$roomIds = array_unique($roomIds);

				//3.2. Load danh sách thí sinh từng phòng thi
				$examinees = [];
				foreach ($roomIds as $roomId)
					$examinees[$roomId] = [];
				foreach ($examsession['rooms'] as $room)
				{
					$roomId  = $room['room_id'];
					$classId = $room['class_id'];

					//Lấy thí sinh thuộc lớp học phần $classId
					$columns = $db->quoteName(
						array('a.learner_id', 'b.lastname', 'b.firstname'),
						array('id', 'lastname', 'firstname')
					);
					$query = $db->getQuery(true)
						->select($columns)
						->from('#__eqa_exam_learner AS a')
						->leftJoin('#__eqa_learners AS b', 'a.learner_id=b.id')
						->leftJoin('#__eqa_class_learner AS c', 'a.class_id=c.class_id AND a.learner_id=c.learner_id')
						->leftJoin('#__eqa_stimulations AS d', 'a.stimulation_id=d.id')
						->where('a.exam_id=' . $examId . ' AND a.class_id='.$classId);
					if($optionDistributeAllowedOnly)
						$query->where([
							'c.allowed<>0',
							'b.debtor=0',
							'(d.type IS NULL OR d.type=' . StimulationHelper::TYPE_ADD . ')'
						]);

					$db->setQuery($query);
					$classExaminees =  $db->loadObjectList();

					$examinees[$roomId] += $classExaminees;
				}

				//3.3. Ghi thông tin thí sinh từng phòng thi
				foreach ($roomIds as $roomId){
					//3.3.1 Lấy danh sách thí sinh và Sắp xếp theo họ và tên
					$roomExaminees = $examinees[$roomId];
					if(empty($roomExaminees))
						continue;
					usort($roomExaminees, $comparator);
					$countExaminee += count($roomExaminees);
					$countExamroom++;

					//3.3.2. Tạo phòng thi (nếu chưa có) và lấy id của phòng thi
					//a) Kiểm tra xem với ca thi $examsessionId đã có tồn tại phòng thi với $roomId hay chưa
					//Nếu đã tồn tại thì lấy $examroomId
					$query = $db->getQuery(true)
						->select('*')
						->from('#__eqa_examrooms')
						->where('examsession_id='.$examsessionId . ' AND room_id='.$roomId);
					$db->setQuery($query);
					$examroom = $db->loadObject();
					if(!empty($examroom)){
						$examroomId = $examroom->id;            //get exam room's ID
					}

					//b) Nếu chưa có thì tạo phòng thi và xác định id của phòng thi mới ($examroomId)
					//   Đồng thời tăng số lượng phòng thi của ca thi
					else {
						$roomCode = RoomHelper::getRoomCode($roomId);   //Mặc định cho examroom's name
						$values = array(
							$db->quote($roomCode),
							$roomId, $examsessionId);
						$tuple = implode(',', $values);
						$query = $db->getQuery(true)
							->insert('#__eqa_examrooms')
							->columns($db->quoteName(array('name','room_id','examsession_id')))
							->values($tuple);
						$db->setQuery($query);
						if(!$db->execute())                     //Create a new record
							throw new Exception(Text::_('COM_EQA_MSG_DATABASE_ERROR'));
						$examroomId = $db->insertid();          //Lấy $examroomId
					}

					//3.3.3. Ghi SBD, phòng thi cho thí
					//Tăng tuần tự SBD trong quá trình này
					foreach ($roomExaminees as $examinee)
					{
						//Gán phòng thi, SBD cho thí sinh
						$query = $db->getQuery(true)
							->update('#__eqa_exam_learner')
							->set(array(
								'code='.$examineeCode,
								'examroom_id='.$examroomId
							))
							->where('exam_id=' . $examId . ' AND learner_id='.$examinee->id);
						$db->setQuery($query);
						if(!$db->execute())
							throw new Exception(Text::_('COM_EQA_MSG_DATABASE_ERROR'));
						$examineeCode++;
					}

					//Cập nhật lại môn thi của phòng thi
					DatabaseHelper::updateExamroomExams($examroomId);

				}   //End of an exam session
			}       //End of al the $data
		}
		catch (Exception $e){
			$db->transactionRollback();
			$app->enqueueMessage($e->getMessage(), 'error');
			return false;
		}

		$db->transactionCommit();
		$msg = Text::sprintf('COM_EQA_MSG_N_EXAMINEES_DISTRIBUTED_INTO_N_EXAMROOMS',$countExaminee,$countExamroom);
		$app->enqueueMessage($msg,'success');
		return true;
	}

	/***
	 * @param   DatabaseDriver  $db         Để duy trì transaction
	 * @param                   $examId
	 *
	 * @return array [$success, $countTotal, $countUpdated]
	 *               - $success: có lỗi hay không
	 *               - $countTotal: tổng số trường hợp có quyết định miễn thi
	 *               - $countUpdadted: số trường hợp thực sự được miễn thi (được thi mới được miễn thi)
	 * @since 1.1.0
	 */
	private function updateStimulExemptions(DatabaseDriver $db, $subjectId, $examId, $learnerIds): array
	{
		//1. Get stimulations
		$columns = $db->quoteName(array('id', 'learner_id','value'));
		$learnerIdSet = '(' . implode(',', $learnerIds) . ')';
		$query = $db->getQuery(true)
			->from('#__eqa_stimulations')
			->select($columns)
			->where(array(
				$db->quoteName('subject_id') . '=' . $subjectId,
				$db->quoteName('type') . '=' . StimulationHelper::TYPE_EXEMPT,
				$db->quoteName('learner_id') . ' IN ' . $learnerIdSet
			));
		$db->setQuery($query);
		$stimulations = $db->loadObjectList();
		$countTotal = sizeof($stimulations);

		//2. Update stimulations
		$countUpdated=0;
		foreach ($stimulations as $learnerStimul)
		{
			$learnerId = $learnerStimul->learner_id;

			//Get learner progress
			$db->setQuery('SELECT class_id FROM #__eqa_exam_learner WHERE exam_id='.$examId . ' AND learner_id='.$learnerId);
			$classId = $db->loadResult();
			$db->setQuery('SELECT pam, allowed FROM #__eqa_class_learner WHERE class_id='.$classId . ' AND learner_id='.$learnerId);
			$learnerProgress = $db->loadObject();

			//Nếu không được thi hoặc đang nợ học phí thì không áp dụng miễn thi
			if(!$learnerProgress->allowed || DatabaseHelper::isDebtor($learnerId))
				continue;

			//Tính toán điểm học phần
			$moduleMark = ExamHelper::calculateModuleMark($subjectId, $learnerProgress->pam, $learnerStimul->value, 1);
			$moduleGrade = ExamHelper::calculateModuleGrade($moduleMark);
			$conclusion = ExamHelper::CONCLUSION_PASSED;

			//Cập nhật dữ liệu
			//a) Đánh dấu thí sinh hết lượt thi ở lớp học phần
			$query = $db->getQuery(true)
				->update('#__eqa_class_learner')
				->set('expired=1')
				->where('class_id='.$classId . ' AND learner_id='.$learnerId);
			$db->setQuery($query);
			if(!$db->execute())
				return [false, $countTotal, 0];

			//b) Ghi nhận kết quả sau khi miễn thi
			$query = $db->getQuery(true)
				->update('#__eqa_exam_learner')
				->set([
					$db->quoteName('stimulation_id') . '=' . $learnerStimul->id,
					$db->quoteName('mark_orig') . '=' . $learnerStimul->value,
					$db->quoteName('ppaa') . '=' . ExamHelper::EXAM_PPAA_NONE,
					$db->quoteName('mark_ppaa') . '=NULL',
					$db->quoteName('mark_final') . '=' . $learnerStimul->value,
					$db->quoteName('module_mark') . '=' . $moduleMark,
					$db->quoteName('module_grade') . '=' . $db->quote($moduleGrade),
					$db->quoteName('conclusion') . '=' . $conclusion
				])
				->where('exam_id='.$examId . ' AND learner_id='.$learnerId);
			$db->setQuery($query);
			if(!$db->execute())
				return [false, $countTotal, 0];

			//Count
			$countUpdated++;
		}
		return [true, $countTotal, $countUpdated];
	}
	private function updateStimulAdditions(DatabaseDriver $db, $subjectId, $examId, $learnerIds): array
	{
		//1. Get stimulations
		$columns = $db->quoteName(array('id', 'learner_id','value'));
		$learnerIdSet = '(' . implode(',', $learnerIds) . ')';
		$query = $db->getQuery(true)
			->from('#__eqa_stimulations')
			->select($columns)
			->where(array(
				$db->quoteName('subject_id') . '=' . $subjectId,
				$db->quoteName('type') . '=' . StimulationHelper::TYPE_ADD,
				$db->quoteName('learner_id') . ' IN ' . $learnerIdSet
			));
		$db->setQuery($query);
		$stimulations = $db->loadObjectList();
		$countTotal = sizeof($stimulations);

		//2. Update stimulations
		$countUpdated=0;
		foreach ($stimulations as $learnerStimul)
		{
			$learnerId = $learnerStimul->learner_id;

			//Get learner progress
			$db->setQuery('SELECT class_id FROM #__eqa_exam_learner WHERE exam_id='.$examId . ' AND learner_id='.$learnerId);
			$classId = $db->loadResult();
			$db->setQuery('SELECT pam1, pam2, pam, allowed FROM #__eqa_class_learner WHERE class_id='.$classId . ' AND learner_id='.$learnerId);
			$learnerProgress = $db->loadObject();

			//Nếu không được thi hoặc đang nợ học phí thì không áp dụng miễn thi
			if(!$learnerProgress->allowed || DatabaseHelper::isDebtor($learnerId))
				continue;

			//Cập nhật dữ liệu
			$query = $db->getQuery(true)
				->update('#__eqa_exam_learner')
				->set('stimulation_id=' . $learnerStimul->id)
				->where('exam_id='.$examId . ' AND learner_id='.$learnerId);
			$db->setQuery($query);
			if(!$db->execute())
				return [false, 0, 0];

			//Count
			$countUpdated++;
		}
		return [true, $countTotal, $countUpdated];
	}
	private function updateStimulTransfer(DatabaseDriver $db, $subjectId, $examId, $learnerIds): array
	{
		//1. Get stimulations
		$columns = $db->quoteName(array('id', 'learner_id','value'));
		$learnerIdSet = '(' . implode(',', $learnerIds) . ')';
		$query = $db->getQuery(true)
			->from('#__eqa_stimulations')
			->select($columns)
			->where(array(
				$db->quoteName('subject_id') . '=' . $subjectId,
				$db->quoteName('type') . '=' . StimulationHelper::TYPE_TRANS,
				$db->quoteName('learner_id') . ' IN ' . $learnerIdSet
			));
		$db->setQuery($query);
		$stimulations = $db->loadObjectList();
		$countTotal = sizeof($stimulations);

		//3. Update stimulations
		$countUpdated=0;
		foreach ($stimulations as $learnerStimul)
		{
			$learnerId = $learnerStimul->learner_id;

			//Nếu đang nợ học phí thì không được khuyến khích
			if(DatabaseHelper::isDebtor($learnerId))
				continue;

			//a) Ghi thông tin vào lớp học phần
			$db->setQuery('SELECT class_id FROM #__eqa_exam_learner WHERE exam_id='.$examId . ' AND learner_id='.$learnerId);
			$classId = $db->loadResult();
			$query = $db->getQuery(true)
				->update('#__eqa_class_learner')
				->set([
					'pam1=' . $learnerStimul->value,
					'pam2=' . $learnerStimul->value,
					'pam=' . $learnerStimul->value,
					'expired=1',
					'description=' . $db->quote(StimulationHelper::getStimulationType(StimulationHelper::TYPE_TRANS))
				])
				->where('class_id='.$classId . ' AND learner_id='.$learnerId);
			$db->setQuery($query);
			if(!$db->execute())
				return [false, $countTotal, 0];


			//b) Ghi nhận kết quả sau khi miễn thi
			$moduleMark = $learnerStimul->value;
			$moduleGrade = ExamHelper::calculateModuleGrade($moduleMark);
			$conclusion = ExamHelper::CONCLUSION_PASSED;
			$query = $db->getQuery(true)
				->update('#__eqa_exam_learner')
				->set([
					$db->quoteName('stimulation_id') . '=' . $learnerStimul->id,
					$db->quoteName('mark_orig') . '=' . $learnerStimul->value,
					$db->quoteName('ppaa') . '=' . ExamHelper::EXAM_PPAA_NONE,
					$db->quoteName('mark_ppaa') . '=NULL',
					$db->quoteName('mark_final') . '=' . $learnerStimul->value,
					$db->quoteName('module_mark') . '=' . $moduleMark,
					$db->quoteName('module_grade') . '=' . $db->quote($moduleGrade),
					$db->quoteName('conclusion') . '=' . $conclusion
				])
				->where('exam_id='.$examId . ' AND learner_id='.$learnerId);
			$db->setQuery($query);
			if(!$db->execute())
				return [false, $countTotal, 0];

			//Count
			$countUpdated++;
		}
		return [true, $countTotal, $countUpdated];
	}
	public function updateStimulations($examId): bool
	{
		if (DatabaseHelper::isCompletedExam($examId))
			throw new Exception('Môn thi hoặc kỳ thi đã kết thúc. Không thể cập nhật thông tin khuyến khích');


		//1. Init
		$app = Factory::getApplication();
		$db = DatabaseHelper::getDatabaseDriver();

		//2. Clean all current 'stimulations' of this exam
		//2.1. Xóa điểm KTHP của các trường hợp được miễn thi
		//     Cần xử lý riêng nhóm này vì điểm ưu tiên đã được đưa vào bảng #__eqa_exam_learner
		//a) Xác định các trường hợp như thế
		$stimulationTypes = [
			StimulationHelper::TYPE_EXEMPT,
			StimulationHelper::TYPE_TRANS
		];
		$stimulationTypeSet = '(' . implode(',', $stimulationTypes) . ')';
		$query = $db->getQuery(true)
			->from('#__eqa_exam_learner AS a')
			->leftJoin('#__eqa_stimulations AS b', 'a.stimulation_id=b.id')
			->select ('a.learner_id')
			->where(array(
				'exam_id = ' . $examId,
				$db->quoteName('b.type') . ' IN ' . $stimulationTypeSet,
			));
		$db->setQuery($query);
		$learnerIds = $db->loadColumn();

		//b) Xóa thông tin điểm khuyến khích
		if(!empty($learnerIds))
		{
			$learnerIdSet = '(' . implode(',', $learnerIds) . ')';
			$query  = $db->getQuery(true)
				->update('#__eqa_exam_learner')
				->set(array(
					'stimulation_id=NULL',
					'mark_orig=NULL',
					'mark_ppaa=NULL',
					'mark_final=NULL',
					'module_mark=NULL',
					'module_grade=NULL',
					'conclusion=NULL'
				))
				->where(array(
					'exam_id = ' . $examId,
					'learner_id IN ' . $learnerIdSet
				));
			$db->setQuery($query);
			if(!$db->execute()){
				$app->enqueueMessage(Text::_('COM_EQA_MSG_DATABASE_ERROR'),'error');
				return false;
			}
		}

		//2.2. Xóa các trường hợp khuyến khích còn lại
		$db->setQuery('UPDATE #__eqa_exam_learner SET stimulation_id=NULL WHERE stimulation_id IS NOT NULL AND exam_id=' . $examId);
		if(!$db->execute()){
			$app->enqueueMessage(Text::_('COM_EQA_MSG_DATABASE_ERROR'),'error');
			return false;
		}

		//3. Cập nhật khuyến khích
		//3.1. Determine the subject of this exam
		$db->setQuery('SELECT subject_id FROM #__eqa_exams WHERE id=' . $examId);
		$subjectId = $db->loadResult();
		//3.2. Lấy danh sách thí sinh của môn thi (xác định phạm vi tìm kiếm trong #__eqa_stimulations)
		$db->setQuery('SELECT learner_id FROM #__eqa_exam_learner WHERE exam_id='.$examId);
		$learnerIds = $db->loadColumn();
		$db->transactionStart();
		$countTotal=0;
		$countUpdated=0;
		try{
			[$success, $total, $updated] = $this->updateStimulExemptions($db, $subjectId, $examId, $learnerIds);
			if(!$success)
				throw new Exception(Text::_('COM_EQA_MSG_DATABASE_ERROR'));
			$countTotal += $total;
			$countUpdated += $updated;

			[$success, $total, $updated] = $this->updateStimulAdditions($db, $subjectId, $examId, $learnerIds);
			if(!$success)
				throw new Exception(Text::_('COM_EQA_MSG_DATABASE_ERROR'));
			$countTotal += $total;
			$countUpdated += $updated;

			[$success, $total, $updated] = $this->updateStimulTransfer($db, $subjectId, $examId, $learnerIds);
			if(!$success)
				throw new Exception(Text::_('COM_EQA_MSG_DATABASE_ERROR'));
			$countTotal += $total;
			$countUpdated += $updated;
		}
		catch (Exception $e)
		{
			$db->transactionRollback();
			$app->enqueueMessage($e->getMessage(),'error');
			return false;
		}
		$db->transactionCommit();
		$msg = Text::sprintf('COM_EQA_MSG_N_LEARNERS_GOT_STIMULATION_N_APPLIED', $countTotal, $countUpdated);
		$app->enqueueMessage($msg, 'success');
		return true;
	}

	/**
	 * @throws Exception
	 */
	public function setExamStatus(int $examId, int $status): bool
	{
		if (DatabaseHelper::isCompletedExam($examId))
			throw new Exception('Môn thi hoặc kỳ thi đã kết thúc. Không thể cập nhật trạng thái');

		$db = DatabaseHelper::getDatabaseDriver();
		$query = $db->getQuery(true)
			->update('#__eqa_exams')
			->set($db->quoteName('status') . '=' . $status)
			->where('id=' . $examId);
		$db->setQuery($query);
		return $db->execute();
	}
	public function importitest(int $examId, array $examinees): bool
	{
		//Init
		$app = Factory::getApplication();
		$db = DatabaseHelper::getDatabaseDriver();

		$db->transactionStart();
		try
		{
			foreach ($examinees as $examinee){
				$learnerCode = $examinee->learnerCode;
				$mark = $examinee->mark;
				$description = $examinee->description;
				/**
				 * Việc import gồm một số bước
				 *  - Ghi điểm $mark vào bảng #__eqa_exam_learner (cột 'mark_orig')
				 *    đồng thời tính toán các giá trị 'mark_final', 'module_grade'
				 *  - Cập nhật số lượt thi, điều kiện tiếp tục thi vào bảng #__eqa_class_learner
				 */
				//a) Tìm id, pam, anomaly của thí sinh
				$columns = $db->quoteName(
					array('a.learner_id', 'c.subject_id', 'a.class_id', 'b.pam', 'a.attempt', 'a.anomaly', 'b.ntaken'),
					array('id',           'subject_id',   'class_id',   'pam',   'attempt',   'anomaly',   'ntaken')
				);
				$query = $db->getQuery(true)
					->select($columns)
					->from('#__eqa_exam_learner AS a')
					->leftJoin('#__eqa_class_learner AS b', 'a.class_id=b.class_id AND a.learner_id=b.learner_id')
					->leftJoin('#__eqa_exams AS c', 'a.exam_id=c.id')
					->where('a.exam_id=' . $examId . ' AND a.code=' . $examinee->code);
				$db->setQuery($query);
				$obj = $db->loadObject();
				if(empty($obj))
				{
					$msg = Text::sprintf('Không tìm thấy thông tin thí sinh <b>%s</b> trong CSDL môn thi', $learnerCode);
					throw new Exception($msg);
				}

				//Trích xuất, bổ sung thông tin thí sinh
				$learnerId = $obj->id;
				$pam = $obj->pam;
				$anomaly = $obj->anomaly;
				$attempt = $obj->attempt;
				$subjectId = $obj->subject_id;
				$classId = $obj->class_id;
				$ntaken = $obj->ntaken;

				//b) Tính toán và cập nhật điểm
				$finalMark = ExamHelper::calculateFinalMark($mark, ExamHelper::EXAM_ANOMALY_NONE, $attempt);
				$moduleMark = ExamHelper::calculateModuleMark($subjectId, $pam, $finalMark, $attempt);
				$moduleGrade = ExamHelper::calculateModuleGrade($moduleMark);
				$conclusion = ExamHelper::conclude($moduleMark, $finalMark);
				if(empty($description))
					$description = 'NULL';
				else
					$description = $db->quote($description);
				$query = $db->getQuery(true)
					->update('#__eqa_exam_learner')
					->set([
						'mark_orig = ' . $mark,
						'mark_final = ' . $finalMark,
						'module_mark = ' . $moduleMark,
						'module_grade = ' . $db->quote($moduleGrade),
						'conclusion = ' . $conclusion,
						'description = ' . $description
					])
					->where('exam_id=' . $examId . ' AND learner_id=' . $learnerId);
				$db->setQuery($query);
				if(!$db->execute())
				{
					$msg = Text::sprintf('Lỗi cập nhật điểm học phần cho thí sinh <b>%s</b>', $learnerCode);
					throw new Exception($msg);
				}

				//c) Cập nhật số lượt thi, điều kiện tiếp tục dự thi
				if(!in_array($anomaly, [ExamHelper::EXAM_ANOMALY_DELAY, ExamHelper::EXAM_ANOMALY_REDO]))
					$ntaken = $attempt;
				$maxAttempts = ConfigHelper::getMaxExamAttempts();
				$expired = 0;
				if($conclusion == ExamHelper::CONCLUSION_PASSED || $anomaly == ExamHelper::EXAM_ANOMALY_BAN || ($ntaken >= $maxAttempts))
					$expired=1;
				$query = $db->getQuery(true)
					->update('#__eqa_class_learner')
					->set([
						'ntaken = ' . $ntaken,
						'expired = ' . $expired
					])
					->where([
						'class_id = ' . $classId,
						'learner_id = ' . $learnerId
					]);
				$db->setQuery($query);
				if(!$db->execute())
				{
					$msg = Text::sprintf('Lỗi cập nhật thông tin điểm học phần cho <b>%s</b>', $learnerCode);
					throw new Exception($msg);
				}
			}
			//Commit on success
			$db->transactionCommit();
		}
		catch (Exception $e)
		{
			$db->transactionRollback();
			$msg = $e->getMessage();
			$app->enqueueMessage($msg, 'error');
			return false;
		}

		$msg = Text::sprintf('Nhập điểm thành công %d thí sinh', sizeof($examinees));
		$app->enqueueMessage($msg, 'success');
		return true;
	}
    public function delete(&$pks)
    {
        $app = Factory::getApplication();
        $db = $this->getDatabase();
        $db->transactionStart();
        try {
            //First, decrease the exam count of examseasons
            foreach ($pks as $examId){
                $db->setQuery('SELECT `examseason_id` FROM `#__eqa_exams` WHERE `id`='.(int)$examId);
                $examseasonId= $db->loadResult();
                $query = $db->getQuery(true)
                    ->update('#__eqa_examseasons')
                    ->set('nexam=nexam-1')
                    ->where('id='.(int)$examseasonId);
                $db->setQuery($query);
                if(!$db->execute())
                    throw new Exception(Text::_('COM_EQA_MSG_DATABASE_ERROR'));
            }

            //And then, call parent do delete exams
            if(!parent::delete($pks))
                throw new Exception(Text::_('COM_EQA_MSG_DATABASE_ERROR'));
        }
        catch (Exception $e){
            $db->transactionRollback();
            $app->enqueueMessage($e->getMessage(), 'error');
            return false;
        }
        $db->transactionCommit();
        return true;
    }
}
