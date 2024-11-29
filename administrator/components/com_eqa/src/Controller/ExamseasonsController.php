<?php
namespace Kma\Component\Eqa\Administrator\Controller;
defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Kma\Component\Eqa\Administrator\Base\EqaAdminController;
use Kma\Component\Eqa\Administrator\Helper\GeneralHelper;

class ExamseasonsController extends EqaAdminController
{
    public function complete(){
        // Check for request forgeries
        $this->checkToken();

        //Set redirect in any case
        $url = Route::_('index.php?option=com_eqa&view=examseasons',false);
        $this->setRedirect($url);

        //Check permission
        if(!$this->app->getIdentity()->authorise('core.edit.state'))
        {
            $this->app->enqueueMessage(Text::_('COM_EQA_MSG_UNAUTHORISED'));
            return;
        }

        // Get items to remove from the request.
        $cid = (array) $this->input->get('cid', [], 'int');

        // Remove zero values resulting from input filter
        $cid = array_filter($cid);

        if (empty($cid)) {
            $this->app->enqueueMessage(Text::_('COM_EQA_NO_ITEM_SELECTED'));
            return;
        }

        // Get the model.
        $model = $this->getModel();
        $model->complete($cid);
    }
}
