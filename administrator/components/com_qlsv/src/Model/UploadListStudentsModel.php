<?php
namespace Hi3PHan\Component\QLSV\Administrator\Model;

use Exception;
use Joomla\CMS\MVC\Model\AdminModel;

defined('_JEXEC') or die;

class   UploadListStudentsModel extends AdminModel
{
    public function getTable($name = 'Students', $prefix = 'Table', $options = [])
    {
        return parent::getTable($name, $prefix, $options);
    }

    public function save($data)
    {
        $table = $this->getTable();

        if (!$table->bind($data)) {
            throw new Exception($table->getError());
        }

        if (!$table->check()) {
            throw new Exception($table->getError());
        }

        if (!$table->store()) {
            throw new Exception($table->getError());
        }

        return true;
    }

    public function getForm($data = [], $loadData = true)
    {
        // TODO: Implement getForm() method.
    }
}
