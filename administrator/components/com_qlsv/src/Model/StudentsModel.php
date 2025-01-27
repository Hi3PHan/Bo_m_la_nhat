<?php

namespace Hi3PHan\Component\QLSV\Administrator\Model;
defined('_JEXEC') or die();


use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;

class StudentsModel extends ListModel
{
    protected $table_name = '#__qlsv_Sinhvien';

    public function __construct($config = [], MVCFactoryInterface $factory = null)
    {
    $config['filter_fields']=array('Masv','Tensv','Gioitinh' , 'Ngaysinh' , 'Que','Lop');
        parent::__construct($config, $factory);
    }

    protected function populateState($ordering = 'Masv', $direction = 'ASC')
    {
//        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
//        $this->setState('filter.search', $search);
//

        $app = \JFactory::getApplication();

        // Lấy giá trị fullordering từ request
        $fullOrdering = $app->input->get('filter_fullordering', '', 'STRING');
        var_dump($fullOrdering);
        if (!empty($fullOrdering)) {
            // Tách cột và hướng sắp xếp
            list($ordering, $direction) = explode(' ', $fullOrdering);
        }

        // Thiết lập trạng thái sắp xếp
        $this->setState('list.ordering', $ordering);
        $this->setState('list.direction', $direction);

        // List state information.
        parent::populateState($ordering, $direction);

    }

    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true);

        $query->select('*')
                ->from($db->quoteName('#__qlsv_sinhvien'));

        $search = $this->getState('filter.search');
        if(!empty($search) ){
            $query->where('Tensv LIKE '. $db->quote('%'. $search.'%'));
        }
        $gender = $this->getState('filter.GioiTinh');
        if(!empty($gender)){
            $query->where('Gioitinh LIKE '. $db->quote('%'. $gender.'%'));
        }
        $class = $this->getState('filter.Class');
        if(!empty($class)){
            $query->where('Lop LIKE '. $db->quote('%'. $class.'%'));
        }


        $orderingCol = $db->escape($this->state->get('list.ordering'));
        $orderingDir  = $db->escape($this->state->get('list.direction'));
        $query->order($db->quoteName($orderingCol).' '.$orderingDir);
        //Trong mọi trường hợp, sắp theo tên nữa cho đẹp

//        $query->order('Tensv '.$orderingDir);
        echo $query->dump();
        return $query;
    }

//    protected function getListQuery()
//    {
//        // Create a new query object.
//        $db    = $this->getDatabase();
//        $query = $db->getQuery(true);
//
//        // Select the required fields from the table.
//        $query->select(
//            $this->getState(
//                'list.select',
//                '*, (SELECT count('
//                . $db->quoteName('date')
//                . ') FROM ' . $db->quoteName('#__qlsv_sinhvien')
//                . ' WHERE walk_id = a.id) AS nvisits'
//            )
//        );
//        $query->from($db->quoteName('#__mywalks') . ' AS a');
//
//        // Filter by published state
//        $published = (string) $this->getState('filter.published');
//
//        if (is_numeric($published))
//        {
//            $query->where($db->quoteName('a.state') . ' = :published')
//                ->bind(':published', $published, ParameterType::INTEGER);
//        }
//        elseif ($published === '')
//        {
//            $query->whereIn($db->quoteName('a.state'), array(0, 1));
//        }
//
//        // Filter by search in title.
//        $search = $this->getState('filter.search');
//
//        if (!empty($search))
//        {
//            $search = '%' . trim($search) . '%';
//            $query->where($db->quoteName('a.title') . ' LIKE :search')
//                ->bind(':search', $search, ParameterType::STRING);
//        }
//
//        // Add the list ordering clause.
//        $orderCol  = $this->state->get('list.ordering', 'a.id');
//        $orderDirn = $this->state->get('list.direction', 'ASC');
//
//        if ($orderCol === 'title') {
//            $ordering = [
//                $db->quoteName('a.title') . ' ' . $db->escape($orderDirn),
//            ];
//        } else {
//            $ordering = $db->escape($orderCol) . ' ' . $db->escape($orderDirn);
//        }
//
//        $query->order($ordering);
//
//        return $query;
//    }

    protected function getStoreId($id = '')
    {
        return parent::getStoreId($id); // TODO: Change the autogenerated stub
    }


}