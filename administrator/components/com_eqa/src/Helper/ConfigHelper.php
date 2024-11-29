<?php
namespace Kma\Component\Eqa\Administrator\Helper;
defined('_JEXEC') or die();

use DateTime;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;
use Matrix\Exception;
use phpseclib3\Crypt\DH\Parameters;

abstract class ConfigHelper{
	private static bool $uninitialized = true;
	private static Registry $params;
	private static function init():void
	{
		self::$params = ComponentHelper::getParams('com_eqa');
		self::$uninitialized = false;
	}

	public static function getParentOrganization(): string
	{
		if(self::$uninitialized)
			self::init();
		return self::$params->get('params.parent_organization', 'Ban Cơ yếu Chính phủ');
	}
	public static function getOrganization(): string
	{
		if (self::$uninitialized)
			self::init();
		return self::$params->get('params.organization', 'Học viện Kỹ thuật mật mã');
	}
	public static function getExaminationUnit(): string
	{
		if(self::$uninitialized)
			self::init();
		return self::$params->get('params.examination_unit', 'Phòng KT&ĐBCLĐT');
	}
	public static function getMaxExamAttempts():int
	{
		if(self::$uninitialized)
			self::init();
		return self::$params->get('params.max_exam_attempts', 2);
	}
	public static function getProgressMarkPrecision(): int
	{
		if(self::$uninitialized)
			self::init();
		return self::$params->get('params.precision_progress');
	}
	public static function getExamMarkPrecision(): int
	{
		if(self::$uninitialized)
			self::init();
		return self::$params->get('params.precision_exam', 1);
	}
	public static function getModuleMarkPrecision(): int
	{
		if(self::$uninitialized)
			self::init();
		return self::$params->get('params.precision_module', 1);
	}
	public static function getSecondAttemptLimit(): int
	{
		if(self::$uninitialized)
			self::init();
		return self::$params->get('params.second_attempt_limit', ExamHelper::SECOND_ATTEMPT_LIMIT_EXAM);
	}
	public static function getExamineeCodeStart(): int
	{
		if(self::$uninitialized)
			self::init();
		return self::$params->get('params.examinee_code_start', 100);
	}
	public static function getExamineeMaskStart(): int
	{
		if(self::$uninitialized)
			self::init();
		return self::$params->get('params.examinee_mask_start', 100);
	}
	public static function getExamineeMaskInterval(): int
	{
		if(self::$uninitialized)
			self::init();
		return self::$params->get('params.examinee_mask_interval', 5);
	}
}


