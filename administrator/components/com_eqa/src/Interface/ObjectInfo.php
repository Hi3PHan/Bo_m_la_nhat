<?php
namespace Kma\Component\Eqa\Administrator\Interface;
defined('_JEXEC') or die();
interface ObjectInfo
{
	public function getHtml(array $options=[]): string;
}