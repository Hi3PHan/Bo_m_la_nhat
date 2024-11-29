<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_CRUDs
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;


echo "hello";
?>

<div class="page-header">
    <h1>
        <?php echo $this->item->title; ?>
    </h1>
</div>

<p>
    <?php echo $this->item->description; ?>
</p>

<h2>
    <?php echo "List Student!!!" ?>
</h2>

<div class="table-responsive">
    <table class="table table-striped">

        <thead>
        <tr>
            <th>
            <th scope="col"> <?php echo "ListStudent" ?></th>
            <th scope="col"> <?php echo "ListSubject" ?></th>
            </th>
        </tr>
        </thead>

        <tbody>

        <?php foreach ($this->reports as $id => $report): ?>
            <tr>
                <td><?php echo $report->students; ?></td>
                <td><?php echo $report->subject; ?></td>
            </tr>
        <?php endforeach; ?><?php //endif; ?>

        //xem lại vòng foreach
        </tbody>
    </table>
</div>

<a href="<?php echo Route::_('index.php?option=com_CRUDs'); ?>">
    <?php echo "Back"; ?>
</a>