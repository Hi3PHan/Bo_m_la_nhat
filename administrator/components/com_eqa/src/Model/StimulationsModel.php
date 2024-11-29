<?php
namespace Kma\Component\Eqa\Administrator\Model;
defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Kma\Component\Eqa\Administrator\Base\EqaListModel;
use Kma\Component\Eqa\Administrator\Helper\DatabaseHelper;

class StimulationsModel extends ListModel {
    public function __construct($config = [], MVCFactoryInterface $factory = null)
    {
        $config['filter_fields']=array('subject_code','subject','type');
        parent::__construct($config, $factory);
    }
    protected function populateState($ordering = 'subject', $direction = 'asc')
    {
        parent::populateState($ordering, $direction);
    }
    public function getListQuery()
    {
        $db = DatabaseHelper::getDatabaseDriver();
        $columns = $db->quoteName(
            array('a.id', 'b.code',      'b.name',  'c.code',      'c.lastname', 'c.firstname', 'a.type', 'a.value', 'a.reason'),
            array('id',   'subject_code','subject', 'learner_code','lastname',   'firstname',   'type',   'value',   'reason')
        );
        $query = $db->getQuery(true)
            ->from('#__eqa_stimulations AS a')
            ->leftJoin('#__eqa_subjects AS b','a.subject_id = b.id')
	        ->leftJoin('#__eqa_learners AS c', 'a.learner_id=c.id')
	        ->select($columns);

        //Filtering
        $search = $this->getState('filter.search');
        if(!empty($search)){
            $like = $db->quote('%'.$search.'%');
            $query->where('(concat_ws(" ",`b`.`lastname`,`b`.`firstname`) LIKE '.$like.' OR `b`.`code` LIKE '.$like .')');
        }

        //Ordering
        $orderingCol = $query->db->escape($this->getState('list.ordering','subject'));
        $orderingDir = $query->db->escape($this->getState('list.direction','asc'));
        $query->order($db->quoteName($orderingCol).' '.$orderingDir);

        return $query;
    }
    public function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('filter.search');
        return parent::getStoreId($id);
    }
}