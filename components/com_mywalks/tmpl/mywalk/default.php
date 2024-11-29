<?php
/**
 * @package     Mywalks.Site
 * @subpackage  com_mywalks
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

?>
<div style="background-color: blue" class="page-header"  class="red">
	<h1 style="color: red"><?php echo $this->item->title; ?></h1>
</div>

<p  style="color: green"><?php echo $this->item->description; ?>!</p>

<h2><?php echo Text::_('COM_MYWALKS_WALK_REPORTS'); ?></h2>

<div class="table-responsive">
  <table class="table table-striped">
  <thead>
    <tr>
 		<th scope="col"><?php echo Text::_('COM_MYWALKS_WALK_DATE'); ?></th>
		<th scope="col"><?php echo Text::_('COM_MYWALKS_WALK_WEATHER'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($this->reports as $id => $report) : ?>
	<tr>
		<td><?php echo $report->date; ?></td>
		<td><?php echo $report->weather; ?></td>
	</tr>
	<?php endforeach; ?><?php //endif; ?>
	</tbody>
  </table>
</div>

<a href="<?php echo Route::_('index.php?option=com_mywalks'); ?>"><?php echo "Click this to back"; ?></a>
<hr>

<h2> Hello broo</h2>