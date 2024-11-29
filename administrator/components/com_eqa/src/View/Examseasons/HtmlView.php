<?php
namespace Kma\Component\Eqa\Administrator\View\Examseasons;    //The namespace must end with the VIEW NAME.
defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Kma\Component\Eqa\Administrator\Base\EqaItemsHtmlView;
use Kma\Component\Eqa\Administrator\Base\EqaListLayoutItemFieldOption;
use Kma\Component\Eqa\Administrator\Base\EqaListLayoutItemFields;
use Kma\Component\Eqa\Administrator\Helper\ToolbarHelper;
use Kma\Component\Eqa\Administrator\Helper\ExamHelper;

class HtmlView extends EqaItemsHtmlView
{

    protected function configureItemFields():void{
        $option = new EqaListLayoutItemFields();

        $option->sequence = EqaListLayoutItemFields::defaultFieldSequence();
        $option->check = EqaListLayoutItemFields::defaultFieldCheck();

        $option->customFieldset1 = array();
        $option->customFieldset1[] = new EqaListLayoutItemFieldOption('academicyear', 'COM_EQA_ACADEMICYEAR', true, false,'text-center');
        $option->customFieldset1[] = new EqaListLayoutItemFieldOption('term', 'COM_EQA_TERM', true, false,'text-center');
        $option->customFieldset1[] = new EqaListLayoutItemFieldOption('type', 'COM_EQA_GENERAL_EXAMSEASON_TYPE', true, false);
        $option->customFieldset1[] = new EqaListLayoutItemFieldOption('attempt', 'COM_EQA_EXAM_ATTEMPT', true, false, 'text-center');
        $option->customFieldset1[] = new EqaListLayoutItemFieldOption('name', 'COM_EQA_EXAMSEASON_NAME', false, true);
        $field = new EqaListLayoutItemFieldOption('nexam', 'COM_EQA_EXAM',true,false,'text-center');
        $field->urlFormatString = 'index.php?option=com_eqa&view=exams&filter[examseason_id]=%d';
        $option->customFieldset1[] = $field;
        $field = new EqaListLayoutItemFieldOption('nexamsession', 'COM_EQA_EXAMSESSION',true,false,'text-center');
        $field->urlFormatString = 'index.php?option=com_eqa&view=examsessions&filter[examseason_id]=%d';
        $option->customFieldset1[] = $field;
        $option->default = EqaListLayoutItemFields::defaultFieldDefault();
        $option->customFieldset2[] = new EqaListLayoutItemFieldOption('completed','COM_EQA_COMPLETED',true,false,'text-center');

        //Set the option
        $this->itemFields = $option;
    }
    protected function prepareDataForLayoutDefault(): void
    {
        parent::prepareDataForLayoutDefault();

        //Disable 'publish' and 'unpublish' toolbar buttons
        $this->toolbarOption->taskPublish=false;
        $this->toolbarOption->taskUnpublish=false;

        //Preprocessing
        if(!empty($this->layoutData->items)) {
            foreach ($this->layoutData->items as $item) {
                $item->type = ExamHelper::ExamType($item->type);
                if($item->completed){
                    $item->completed = Text::_('JYES');
                    $item->optionRowCssClass='table-active';
                    $item->optionIgnoreEditUrl=true;
                    $item->optionIgnoreToggleDefaultButton=true;
                }
                else
                    $item->completed=Text::_('JNO');
            }
        }


    }
    protected function addToolbarForLayoutDefault(): void
    {
        parent::addToolbarForLayoutDefault();
        ToolbarHelper::appendConfirmButton('core.edit.state','COM_EQA_MSG_CONFIRM_COMPLETE_EXAMSEASON','lock','COM_EQA_BUTTON_COMPLETE','examseasons.complete',true,'btn btn-danger');
		ToolbarHelper::appenddButton('core.manage', 'download','Thí sinh','examseason.exportExaminees',true);
    }

}

