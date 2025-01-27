<?php

namespace Hi3PHan\Component\QLSV\Administrator\Model;
defined('_JEXEC') or die();


use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;

class ResultsModel extends ListModel
{
    protected $table_name = '#__qlsv_ketqua';

    public function __construct($config = [], MVCFactoryInterface $factory = null)
    {
//        $config['filter_fields']=array('code','unit_code','firstname','published', 'ordering');
        parent::__construct($config, $factory);
    }

    protected function populateState($ordering = 'id', $direction = 'asc')
    {
        parent::populateState($ordering, $direction); // TODO: Change the autogenerated stub
    }
    public function getTable($name = 'Ketqua', $prefix = '', $options = [])
    {
        return parent::getTable($name, $prefix, $options);
    }

    protected function getListQuery()
    {
        // Lấy đối tượng cơ sở dữ liệu để thực hiện các truy vấn SQL
        $db = $this->getDatabase();
        $query = $db->getQuery(true);
        // Khởi tạo đối tượng truy vấn SQL mới


        // Chọn tất cả các cột từ bảng sinh viên
        $query->select('*')
            // Xác định bảng cần truy vấn (bảng sinh viên)
            ->from($this->table_name)
            ->order($this->getState('list.ordering', 'id') . ' ' . $this->getState('list.direction', 'ASC'))
//                    ->setLimit( $this->getState('list.start'),$this->getState('list.limit'));
        ;
        // Thiết lập truy vấn SQL cho đối tượng cơ sở dữ liệu
//        $db->setQuery($query, $this->getState('list.start'), $this->getState('list.limit'));

//        $db->setQuery($query);

//        $query->from($db->quoteName('#__students') . ' AS a');
        // Thiết lập bảng cần truy vấn là `#__mywalks` với bí danh `a`.

        // Thực hiện truy vấn và trả về danh sách sinh viên dưới dạng danh sách đối tượng
        return $query;
    }

    protected function getStoreId($id = '')
    {
        return parent::getStoreId($id); // TODO: Change the autogenerated stub
    }


}