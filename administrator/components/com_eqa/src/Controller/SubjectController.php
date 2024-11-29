<?php
namespace Kma\Component\Eqa\Administrator\Controller;
defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Kma\Component\Eqa\Administrator\Base\EqaFormController;
use Kma\Component\Eqa\Administrator\Helper\DatabaseHelper;
use Kma\Component\Eqa\Administrator\Helper\GeneralHelper;
use Kma\Component\Eqa\Administrator\Helper\StimulationHelper;
use Kma\Component\Eqa\Administrator\Model\StimulationsModel;

class SubjectController extends  EqaFormController {
	public function stimulate()
	{
		//Redirect in any case
		$this->setRedirect(\JRoute::_('index.php?option=com_eqa&view=stimulations',false));

		//Check token
		if(!$this->checkToken('post', false))
			return;

		//Check privilege
		if(!$this->app->getIdentity()->authorise('core.create',$this->option))
		{
			$this->setMessage(Text::_('COM_EQA_MSG_UNAUTHORISED'),'error');
			return;
		}

		//Get data
		$subjectId = $this->input->getInt('subject_id');
		$stimulType = $this->input->getInt('type');
		$stimulValue = $this->input->getFloat('value');
		$stimulReason = $this->input->getString('reason');
		$learnerCodes = $this->input->getString('learnercodelist');
		$learnerCodes = preg_replace('/[\s,;]+/', ' ', $learnerCodes);
		$learnerCodes = trim($learnerCodes);
		$learnerCodes = explode(' ',  $learnerCodes);
		$learnerIds = DatabaseHelper::getLearnerIds($learnerCodes);
		if(empty($subjectId) || empty($learnerIds) || empty($stimulValue) || empty($stimulReason) || empty($learnerIds))
		{
			$this->setMessage(Text::_('COM_EQA_MSG_INVALID_DATA'),'error');
			return;
		}
		$timeStamp = date('Y-m-d H:i:s');
		$username = GeneralHelper::getCurrentUserId();

		//Process
		$model = $this->createModel('stimulation');
		$model->stimulate($subjectId, $learnerIds, $stimulType, $stimulValue, $stimulReason, $timeStamp, $username);
	}
	public function clearStimulations()
	{
		//Redirect in any case
		$this->setRedirect(\JRoute::_('index.php?option=com_eqa&view=stimulations',false));

		//Check token
		if(!$this->checkToken('post', false))
			return;

		//Check privilege
		if(!$this->app->getIdentity()->authorise('core.delete',$this->option))
		{
			$this->setMessage(Text::_('COM_EQA_MSG_UNAUTHORISED'),'error');
			return;
		}

		//Get data
		$stimulIds = (array) $this->input->get('cid', [], 'int');

		if (empty($stimulIds))
		{
			$this->app->enqueueMessage('COM_EQA_NO_ITEM_SELECTED', 'warning');
			return;
		}

		// Process
		$model = $this->createModel('stimulation');
		$model->clear($stimulIds);
	}
}
