<?php
namespace Kma\Component\Eqa\Administrator\Model;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Kma\Component\Eqa\Administrator\Base\EqaAdminModel;
use Kma\Component\Eqa\Administrator\Helper\DatabaseHelper;
use Kma\Component\Eqa\Administrator\Helper\ExamHelper;
use Kma\Component\Eqa\Administrator\Helper\GeneralHelper;
use ZipStream\Exception;

defined('_JEXEC') or die();

class ExamseasonModel extends EqaAdminModel{
    public function getSubjectIdsByExamseasonId(int $examseason_id)
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->from('#__eqa_exams')
            ->select('subject_id')
            ->where('examseason_id = '.(int)$examseason_id);
        $db->setQuery($query);
        $ids = $db->loadColumn();
        return $ids;
    }
    public function getSubjectIdsByTerm(int $academicyear_id, int $term)
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->from('#__eqa_classes')
            ->select('subject_id')
            ->where('academicyear_id = '.(int)$academicyear_id . ' AND term = '.$term);
        $db->setQuery($query);
        $ids = $db->loadColumn();
        return array_unique($ids, SORT_NUMERIC);
    }
    public function addExams($examseasonId, $cid){
        $app = Factory::getApplication();
        if(empty($cid)){
            $msg = Text::_('COM_EQA_MSG_NO_ITEM_SPECIFIED');
            $app->enqueueMessage($msg,'warning');
            return;
        }
        $db = DatabaseHelper::getDatabaseDriver();
        $db->transactionStart();
        try{
            foreach ($cid as $subjectId){

                //1. Create an exam and get the exam id
                $db = $this->getDatabase();
                $db->setQuery('SELECT * FROM #__eqa_subjects WHERE id = '.$subjectId);
                $subject = $db->loadObject();
                $exam = [
                    'subject_id' => $subjectId,
                    'examseason_id' => $examseasonId,
                    'name' => $subject->name,
                    'testtype' => $subject->finaltesttype,
                    'duration' => $subject->finaltestduration,
                    'kmonitor' => $subject->kmonitor,
                    'kassess' => $subject->kassess,
                    'usetestbank' => empty($subject->testbankyear)?0:1,
                    'status' => ExamHelper::EXAM_STATUS_NOTHING
                ];
                $table = $this->getTable('exam');
                $table->save($exam);
                $examId = $db->insertid();

                //2. Get all the leaners in all the credit classes of this subject in this academic year and term
                //2.1. Load academic year and term
                $db->setQuery('SELECT * FROM #__eqa_examseasons WHERE id='.(int)$examseasonId);
                $examseason = $db->loadObject();
                //2.2. Get all the credit classes of this subject in this academic year and term
                $db->setQuery('SELECT id FROM #__eqa_classes WHERE academicyear_id='
                    . $examseason->academicyear_id
                    . ' AND term='.$examseason->term
                    . ' AND subject_id='.$subjectId);
                $classIds = $db->loadColumn();
                //2.3. Get all learners (with their class)
                $db->setQuery('SELECT class_id, learner_id FROM #__eqa_class_learner WHERE class_id IN ('
                    .implode(',',array_map('intval',$classIds))
                    .')');
                $learners = $db->loadObjectList();

                //3. Add the leaners to this exam
                $columns = $db->quoteName(['exam_id','class_id', 'learner_id', 'attempt']);
                $attempt = $examseason->attempt;
                $values = array();
                foreach ($learners as $learner){
                    $classId = (int)$learner->class_id;
                    $learnerId = (int)$learner->learner_id;
                    $values[] = implode(',',[$examId,$classId,$learnerId,$attempt]);
                }
                $query = $db->getQuery(true)
                    ->insert('#__eqa_exam_learner')
                    ->columns($columns)
                    ->values($values);
                $db->setQuery($query);
                $db->execute();
            }

            //Update exam count for the examseason
            $db->setQuery('UPDATE #__eqa_examseasons SET nexam=nexam+'.sizeof($cid).' WHERE id='.$examseasonId);
            $db->execute();

            //Commit
            $db->transactionCommit();
            $app->enqueueMessage(sizeof($cid).' exams are added sussessfully', 'success');
        }
        catch (Exception $e){
            $db->transactionRollback();
            $msg = Text::_('COM_EQA_MSG_ERROR_OCCURRED');
            $app->enqueueMessage($msg,'error');
        }
    }

    public function complete($cid)
    {
        $app = Factory::getApplication();
        try {
            $db = $this->getDatabase();
            $set = '(' . implode(',', $cid) . ')';
            $query = $db->getQuery(true)
                ->update('#__eqa_examseasons')
                ->set(array('`completed`=1','`default`=0'))     //Completed cannot be default
                ->where('id IN '. $set);
            $db->setQuery($query);
            if($db->execute())
                $app->enqueueMessage(Text::_('COM_EQA_MSG_TASK_SUCCESS'),'success');
            else
                $app->enqueueMessage(Text::_('COM_EQA_MSG_ERROR_TASK_FAILED'), 'error');
        }
        catch (\Exception $e){
            $app->enqueueMessage($e->getMessage(), 'error');
        }
    }

	public function getExaminees(int $examseasonId)
	{
		$db = DatabaseHelper::getDatabaseDriver();
		$query = $db->getQuery(true)
			->select([
				'DISTINCT(a.learner_id) AS id',
				'd.code AS code',
				'd.lastname AS lastname',
				'd.firstname AS firstname',
				'`e`.`code` AS `group`',
				'f.code AS course'
			])
			->from('#__eqa_exam_learner AS a')
			->leftJoin('#__eqa_exams AS b', 'b.id = a.exam_id')
			->leftJoin('#__eqa_examseasons AS c', 'c.id = b.examseason_id')
			->leftJoin('#__eqa_learners AS d', 'd.id = a.learner_id')
			->leftJoin('#__eqa_groups AS e', 'e.id=d.group_id')
			->leftJoin('#__eqa_courses AS f', 'f.id=e.course_id')
			->where('c.id='.$examseasonId);
		$db->setQuery($query);
		return $db->loadAssocList();
	}
}
