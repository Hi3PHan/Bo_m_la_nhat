<?php
namespace Kma\Component\Eqa\Administrator\View\Examexaminees; //The namespace must end with the VIEW NAME.
defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use JRoute;
use Kma\Component\Eqa\Administrator\Base\EqaItemsHtmlView;
use Kma\Component\Eqa\Administrator\Base\EqaListLayoutItemFieldOption;
use Kma\Component\Eqa\Administrator\Base\EqaListLayoutItemFields;
use Kma\Component\Eqa\Administrator\Helper\DatabaseHelper;
use Kma\Component\Eqa\Administrator\Helper\ExamHelper;
use Kma\Component\Eqa\Administrator\Helper\StimulationHelper;
use Kma\Component\Eqa\Administrator\Helper\ToolbarHelper;

class HtmlView extends EqaItemsHtmlView {
    protected $exam;
    protected function configureItemFields():void{
        $this->itemFields = new EqaListLayoutItemFields();
        $fields = $this->itemFields;      //Just shorten the name
        $fields->sequence = EqaListLayoutItemFields::defaultFieldSequence();
        $fields->check = EqaListLayoutItemFields::defaultFieldCheck();

	    $fields->customFieldset1[] = new EqaListLayoutItemFieldOption('code','COM_EQA_EXAMINEE_CODE_ABBR', true, false, 'text-center');
	    $fields->customFieldset1[] = new EqaListLayoutItemFieldOption('learner_code','COM_EQA_LEARNER_CODE', true, false, 'text-center');
        $fields->customFieldset1[] = EqaListLayoutItemFields::defaultFieldLastname();
        $fields->customFieldset1[] = EqaListLayoutItemFields::defaultFieldFirstname();
        $f = new EqaListLayoutItemFieldOption('attempt', 'COM_EQA_EXAM_ATTEMPT_ABBR', true, false, 'text-center');
        $f->titleDesc=Text::_('COM_EQA_EXAM_ATTEMPT');
        $fields->customFieldset1[] = $f;
        $f = new EqaListLayoutItemFieldOption('pam1', 'COM_EQA_PAM1_ABBR', false, false, 'text-center');
        $f->titleDesc=Text::_('COM_EQA_PAM1');
        $fields->customFieldset1[] = $f;
        $f = new EqaListLayoutItemFieldOption('pam2', 'COM_EQA_PAM2_ABBR', false, false, 'text-center');
        $f->titleDesc=Text::_('COM_EQA_PAM2');
        $fields->customFieldset1[] = $f;
        $f = new EqaListLayoutItemFieldOption('pam', 'COM_EQA_PAM_ABBR', false, false, 'text-center');
        $f->titleDesc=Text::_('COM_EQA_PAM');
        $fields->customFieldset1[] = $f;
        $f = new EqaListLayoutItemFieldOption('allowed', 'COM_EQA_ALLOWED_TO_TAKE_EXAM_ABBR', true, false, 'text-center');
        $f->titleDesc=Text::_('COM_EQA_ALLOWED_TO_TAKE_EXAM');
        $fields->customFieldset1[] = $f;
		$fields->customFieldset1[] = new EqaListLayoutItemFieldOption('debtor','COM_EQA_DEBT',true,false,'text-center');
		$fields->customFieldset1[] = new EqaListLayoutItemFieldOption('stimulation', 'COM_EQA_STIMULATION_SHORT', true, false, 'text-center');
        $f = new EqaListLayoutItemFieldOption('mark_final', 'COM_EQA_MARK_FINALEXAM_ABBR', false, false, 'text-center');
        $f->titleDesc=Text::_('COM_EQA_MARK_FINALEXAM');
        $fields->customFieldset1[] = $f;
        $f = new EqaListLayoutItemFieldOption('module_mark', 'COM_EQA_MODULE_MARK_ABBR', false, false, 'text-center');
        $f->titleDesc=Text::_('COM_EQA_MODULE_MARK');
        $fields->customFieldset1[] = $f;
        $f = new EqaListLayoutItemFieldOption('module_grade', 'COM_EQA_MODULE_GRADE_ABBR', false, false, 'text-center');
        $f->titleDesc=Text::_('COM_EQA_MODULE_GRADE');
        $fields->customFieldset1[] = $f;
    }
    protected function prepareDataForLayoutDefault(): void
    {
        //Prepare the model before calling parent
        $examId = Factory::getApplication()->input->get('exam_id');
        $model = $this->getModel();
        $model->setState('filter.exam_id',$examId);
        parent::prepareDataForLayoutDefault();

        //Tham số dưới đây sẽ khiến DisplayController luôn redirect tới view và layout mong muốn
        //giúp cố định 'exam_id'
        $this->layoutData->formActionParams = [
            'view'=>'examexaminees',
            'layout'=>'default',
            'exam_id'=>$examId
        ];

        //Class Item
        $this->exam = DatabaseHelper::getExamInfo($examId);

        //Layout data preprocessing
        if(!empty($this->layoutData->items))
        {
            foreach ($this->layoutData->items as $item)
            {
                if($item->allowed)
                    $item->allowed = Text::_('JYES');
                else {
                    $item->allowed = Text::_('JNO');
                    $item->optionRowCssClass='table-danger';
                }

	            if($item->debtor){
		            $item->debtor = Text::_('JYES');
		            $item->optionRowCssClass='table-danger';
	            }
	            else {
		            $item->debtor = Text::_('JNO');
	            }

				if($item->stimulation !== null)
					$item->stimulation = StimulationHelper::getStimulationType($item->stimulation);
            }
        }

    }
    protected function addToolbarForLayoutDefault(): void
    {
        $option = $this->toolbarOption;
        ToolbarHelper::title($option->title);
        $url = JRoute::_('index.php?option=com_eqa&view=exams',false);
        ToolbarHelper::appendLink(null,$url,'COM_EQA_EXAM', 'arrow-up-2');
        ToolbarHelper::appenddButton('core.create','plus-2','COM_EQA_BUTTON_ADD_EXAMINEES','exam.addExaminees',false,'btn btn-success');
        ToolbarHelper::appendDelete('exam.removeExaminees');
		ToolbarHelper::appenddButton('core.edit', 'loop','COM_EQA_STIMULATION_SHORT','exam.stimulate',false, 'btn btn-success');
	    ToolbarHelper::appenddButton('core.create','calendar','COM_EQA_DISTRIBUTE_AMONG_ROOMS','exam.distribute',false);
		$urlDistribute2 = JRoute::_('index.php?option=com_eqa&view=exam&layout=distribute2&exam_id='.$this->exam->id, false);
	    ToolbarHelper::appendLink('core.create', $urlDistribute2,'COM_EQA_DISTRIBUTE_AMONG_ROOMS_2', 'calendar');
	    ToolbarHelper::appenddButton(null,'download','COM_EQA_EXPORT','exam.export');
	    ToolbarHelper::appenddButton(null,'download','COM_EQA_EXPORT_ITEST','exam.exportitest');
    }
}
