<?php

namespace Hi3PHan\Component\QLSV\Administrator\Table;
defined('_JEXEC') or die();


use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;



class StudentsTable extends Table
{
    public function __construct( DatabaseDriver $db)
    {
        parent::__construct('#__qlsv_sinhvien', 'Masv', $db);
    }
}