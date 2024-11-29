<?php
defined('_JEXEC') or die;
?>

<table class="table table-striped">
    <thead>
    <tr>
        <th>Họ và tên</th>
        <th>Năm sinh</th>
        <th>Điểm trung bình</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($this->items as $i=>$item)
    {?>
<!--        <tr class="row--><?php //echo $i%2;?><!--">-->
<!--            <td>--><?php //echo $item->name;?><!--</td>-->
<!--            <td>--><?php //echo $item->year;?><!--</td>-->
<!--            <td>--><?php //echo $item->avg;?><!--</td>-->
<!--        </tr>-->
    <?php } ?>
    </tbody>
</table>