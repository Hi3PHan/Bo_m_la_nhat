<?php
namespace Joomla\Component\HelloWorld\Administrator\Table;

use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

defined('_JEXEC') or die;
class CRUDStudentTable extends Table
{
    public function __construct(DatabaseDriver $db)
    {
        parent::__construct('#__students', 'Masv', $db);
    }
    //taop bang
}