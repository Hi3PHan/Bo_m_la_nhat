<?php
namespace Kma\Component\Eqa\Administrator\Helper;
defined('_JEXEC') or die();

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;

abstract class ExamHelper{
    public const TEST_TYPE_UNKNOWN=0;
    public const TEST_TYPE_PAPER = 10;
    public const TEST_TYPE_PROJECT = 11;
    public const TEST_TYPE_THESIS = 12;
    public const TEST_TYPE_PRACTICE=13;
    public const TEST_TYPE_DIALOG = 14;
    public const TEST_TYPE_MACHINE_OBJECTIVE = 20;
    public const TEST_TYPE_MACHINE_HYBRID = 21;
    public const TEST_TYPE_COMBO_OBJECTIVE_PRACTICE = 30;

    public const EXAM_TYPE_OTHER = 0;                   //Thi khác
    public const EXAM_TYPE_SUBJECT_FINAL_TEST = 1;      //Thi kết thúc học phần
    public const EXAM_TYPE_CERTIFICATION = 2;           //Thi sát hạch (đầu vào, đầu ra,...)
    public const EXAM_TYPE_GRADUATION = 3;              //Thi tốt nghiệp

    public const EXAM_STATUS_UNKNOWN = 0;               //Chưa xác định
    public const EXAM_STATUS_QUESTION_BUT_PAM = 10;     //Đã có đề thi, Chưa có điểm quá trình
    public const EXAM_STATUS_PAM_BUT_QUESTION = 11;     //Đã có điểm quá trình, Chưa có đề thi
    public const EXAM_STATUS_QUESTION_AND_PAM = 12;     //Đã có đề thi và điểm quá trình
    public const EXAM_STATUS_READY_TO_EXAM = 20;        //Đã chia phòng thi
	public const EXAM_STATUS_EXAM_CONDUCTED = 21;       //Đã tổ chức thi
	public const EXAM_STATUS_PAPER_INFO_PARTIAL = 22;          //Đã bắt đầu nhập biên bản thi viết
	public const EXAM_STATUS_PAPER_INFO_FULL = 23;             //Đã hoàn thành nhập biên bản thi viết
	public const EXAM_STATUS_MASKING_DONE = 25;                //Đã làm phách, dồn túi
	public const EXAM_STATUS_EXAMINER_ASSIGNED = 26;           //Đã phân công chấm thi viết
    public const EXAM_STATUS_MARKING_STARTED = 31;      //Đã bắt đầu chấm thi
	public const EXAM_STATUS_MARK_PARTIAL = 32;         //Đã có một phần điểm
	public const EXAM_STATUS_MARK_FULL = 33;            //Đã có đủ điểm
    public const EXAM_STATUS_COMPLETED = 100;           //Đã hoàn tất

    public const EXAM_ANOMALY_NONE=0;           //Không có
    public const EXAM_ANOMALY_SUB25=11;        //Trừ 25%
    public const EXAM_ANOMALY_SUB50=12;        //Trừ 50%
    public const EXAM_ANOMALY_BAN=13;           //Đình chỉ thi
    public const EXAM_ANOMALY_ABSENT=20;        //Vắng thi (không lý do)
    public const EXAM_ANOMALY_DELAY=30;         //Hoãn thi (vắng có lý do)
    public const EXAM_ANOMALY_REDO=40;          //Hủy bài thi và làm lại bài thi vào kỳ thi sau

    public const EXAM_PPAA_NONE=0;
    public const EXAM_PPAA_REVIEW=1;
    public const EXAM_PPAA_CORRECTION=2;

	public const CONCLUSION_PASSED = 10;            //Qua môn, hết lượt thi
	public const CONCLUSION_FAILED = 20;            //Không qua môn, thi lại
	public const CONCLUSION_FAILED_EXPIRED = 21;    //Không qua môn, hết lượt thi
	public const CONCLUSION_RESERVED = 30;          //Bảo lưu lượt thi

	public const SECOND_ATTEMPT_LIMIT_NONE = 0;
	public const SECOND_ATTEMPT_LIMIT_EXAM = 1;
	public const SECOND_ATTEMPT_LIMIT_MODULE=2;
    /**
     * Hàm này dịch từ MÃ HÌNH THỨC THI thành HÌNH THỨC THI
     * @param int $testTypeCode   Hằng số quy ước cho mã hình thức thi
     * @return string|null  Tên loại kỳ thi (dịch từ tập tin language) tương ứng với hằng số
     * @since 1.0
     */
    static public function getTestType(int $testTypeCode): string|null
    {
        return match ($testTypeCode) {
            self::TEST_TYPE_UNKNOWN => "Chưa xác định",
            self::TEST_TYPE_PAPER => "Tự luận",
            self::TEST_TYPE_PROJECT => "Đồ án",
            self::TEST_TYPE_THESIS => "Tiểu luận",
            self::TEST_TYPE_PRACTICE => "Thực hành",
            self::TEST_TYPE_DIALOG => "Vấn đáp",
            self::TEST_TYPE_MACHINE_OBJECTIVE => "Trắc nghiệm (máy)",
            self::TEST_TYPE_MACHINE_HYBRID => "Hỗn hợp (máy)",
            self::TEST_TYPE_COMBO_OBJECTIVE_PRACTICE => "Trắc nghiệm + Thực hành",
            default => null,
        };
    }

    /**
     * Hàm này dịch từ MÃ HÌNH THỨC THI thành TÊN VIẾT TẮT CỦA HÌNH THỨC THI
     * @param int $testTypeCode   Hằng số quy ước cho mã hình thức thi
     * @return string|null  Tên loại kỳ thi (dịch từ tập tin language) tương ứng với hằng số
     * @since 1.0
     */
    static public function getTestTypeAbbr(int $testTypeCode): string|null
    {
        return match ($testTypeCode) {
            self::TEST_TYPE_UNKNOWN => "NA",
            self::TEST_TYPE_PAPER => "TL",
            self::TEST_TYPE_PROJECT => "ĐA",
            self::TEST_TYPE_THESIS => "TiL",
            self::TEST_TYPE_PRACTICE => "TH",
            self::TEST_TYPE_DIALOG => "VĐ",
            self::TEST_TYPE_MACHINE_OBJECTIVE => "TN",
            self::TEST_TYPE_MACHINE_HYBRID => "TN+",
            self::TEST_TYPE_COMBO_OBJECTIVE_PRACTICE => "TN+TH",
            default => null,
        };
    }

    /**
     * Hàm này trả về mảng HÌNH THỨC THI trong đó $key là mã hình thức thi,
     * còn $value là tên hình thức thi được dịch từ tập tin ngôn ngữ.
     * @return array    Mỗi phần tử $key=>$value ứng với $key là mã hình thức thi, $value là tên hình thức thi
     * @since 1.0
     */
    static public function getTestTypes(): array
    {
        $testtypes = array();
        $testtypes[self::TEST_TYPE_UNKNOWN] = self::getTestType(self::TEST_TYPE_UNKNOWN);
        $testtypes[self::TEST_TYPE_PAPER] = self::getTestType(self::TEST_TYPE_PAPER);
        $testtypes[self::TEST_TYPE_PROJECT] = self::getTestType(self::TEST_TYPE_PROJECT);
        $testtypes[self::TEST_TYPE_THESIS] = self::getTestType(self::TEST_TYPE_THESIS);
        $testtypes[self::TEST_TYPE_PRACTICE] = self::getTestType(self::TEST_TYPE_PRACTICE);
        $testtypes[self::TEST_TYPE_DIALOG] = self::getTestType(self::TEST_TYPE_DIALOG);
        $testtypes[self::TEST_TYPE_MACHINE_OBJECTIVE] = self::getTestType(self::TEST_TYPE_MACHINE_OBJECTIVE);
        $testtypes[self::TEST_TYPE_MACHINE_HYBRID] = self::getTestType(self::TEST_TYPE_MACHINE_HYBRID);
        $testtypes[self::TEST_TYPE_COMBO_OBJECTIVE_PRACTICE] = self::getTestType(self::TEST_TYPE_COMBO_OBJECTIVE_PRACTICE);
        return $testtypes;
    }

    /**
     * Hàm này dịch từ MÃ LOẠI KỲ THI thành LOẠI KỲ THI
     * @param int $typeCode   Hằng số quy ước cho mã loại kỳ thi
     * @return string|null  Tên loại kỳ thi (dịch từ tập tin language) tương ứng với hằng số
     * @since 1.0
     */
    static public function ExamType(int $typeCode): string|null
    {
        return match ($typeCode) {
            self::EXAM_TYPE_OTHER => "Khác",
            self::EXAM_TYPE_SUBJECT_FINAL_TEST => "KTHP",
            self::EXAM_TYPE_CERTIFICATION => "Sát hạch",
            self::EXAM_TYPE_GRADUATION => "Tốt nghiệp",
            default => null,
        };
    }

    /**
     * Hàm này trả về mảng thông tin loại kỳ thi trong đó $key là mã loại kỳ thi,
     * còn $value là tên loại kỳ thi được dịch từ tập tin ngôn ngữ.
     * @return array    Mỗi phần tử $key=>$value ứng với $key là mã loại kỳ thi, $value là tên loại kỳ thi
     * @since 1.0
     */
    static public function ExamTypes(): array
    {
        $types = array();
        $types[self::EXAM_TYPE_SUBJECT_FINAL_TEST] = self::ExamType(self::EXAM_TYPE_SUBJECT_FINAL_TEST);
        $types[self::EXAM_TYPE_CERTIFICATION] = self::ExamType(self::EXAM_TYPE_CERTIFICATION);
        $types[self::EXAM_TYPE_GRADUATION] = self::ExamType(self::EXAM_TYPE_GRADUATION);
        $types[self::EXAM_TYPE_OTHER] = self::ExamType(self::EXAM_TYPE_OTHER);
        return $types;
    }

    /**
     * Hàm này dịch từ MÃ TRẠNG THÁI MÔN THI thành TRẠNG THÁI MÔN THI
     * @param int $statusCode   Hằng số quy ước cho mã trạng thái
     * @return string|null  Tên trạng thái tương ứng với hằng số
     * @since 1.0
     */
    static public function ExamStatus(int $statusCode): string|null
    {
        return match ($statusCode) {
            self::EXAM_STATUS_UNKNOWN => 'Chưa biết',
            self::EXAM_STATUS_QUESTION_BUT_PAM => 'Đã có đề thi, chưa có điểm quá trình',
            self::EXAM_STATUS_PAM_BUT_QUESTION => 'Đã có điểm quá trình, chưa có đề thi',
            self::EXAM_STATUS_QUESTION_AND_PAM => 'Đã có đề thi và điểm quá trình',
            self::EXAM_STATUS_READY_TO_EXAM => 'Đã sẵn sàng để thi',
	        self::EXAM_STATUS_EXAM_CONDUCTED => 'Đã thi xong',
	        self::EXAM_STATUS_PAPER_INFO_PARTIAL => 'Đã bắt đầu nhập thông tin bài thi viết',
	        self::EXAM_STATUS_PAPER_INFO_FULL => 'Đã nhập xong thông tin bài thi viết',
	        self::EXAM_STATUS_MASKING_DONE => 'Đã đánh phách, dồn túi',
	        self::EXAM_STATUS_EXAMINER_ASSIGNED => 'Đã phân công chấm thi viết',
            self::EXAM_STATUS_MARKING_STARTED => 'Đã giao bài thi cho CBChT',
	        self::EXAM_STATUS_MARK_PARTIAL => 'Đã có một phần điểm thi',
	        self::EXAM_STATUS_MARK_FULL => 'Đã có đủ điểm thi',
            self::EXAM_STATUS_COMPLETED => 'Đã hoàn tất',
            default => null,
        };
    }

    /**
     * Hàm này trả về mảng TRẠNG THÁI MÔN THI trong đó $key là mã trạng thái,
     * còn $value là tên trạng thái được dịch từ tập tin ngôn ngữ.
     * @return array    Mỗi phần tử $key=>$value ứng với $key là mã trạng thái, $value là tên trạng thái
     * @since 1.0
     */
    static public function ExamStatuses(): array
    {
        $statuses = array();
        $statuses[self::EXAM_STATUS_UNKNOWN]            = self::ExamStatus(self::EXAM_STATUS_UNKNOWN);
        $statuses[self::EXAM_STATUS_QUESTION_BUT_PAM]   = self::ExamStatus(self::EXAM_STATUS_QUESTION_BUT_PAM);
        $statuses[self::EXAM_STATUS_PAM_BUT_QUESTION]   = self::ExamStatus(self::EXAM_STATUS_PAM_BUT_QUESTION);
        $statuses[self::EXAM_STATUS_QUESTION_AND_PAM]   = self::ExamStatus(self::EXAM_STATUS_QUESTION_AND_PAM);
        $statuses[self::EXAM_STATUS_READY_TO_EXAM]      = self::ExamStatus(self::EXAM_STATUS_READY_TO_EXAM);
	    $statuses[self::EXAM_STATUS_EXAM_CONDUCTED]     = self::ExamStatus(self::EXAM_STATUS_EXAM_CONDUCTED);
	    $statuses[self::EXAM_STATUS_PAPER_INFO_PARTIAL] = self::ExamStatus(self::EXAM_STATUS_PAPER_INFO_PARTIAL);
	    $statuses[self::EXAM_STATUS_PAPER_INFO_FULL]    = self::ExamStatus(self::EXAM_STATUS_PAPER_INFO_FULL);
	    $statuses[self::EXAM_STATUS_MASKING_DONE]       = self::ExamStatus(self::EXAM_STATUS_MASKING_DONE);
	    $statuses[self::EXAM_STATUS_EXAMINER_ASSIGNED]  = self::ExamStatus(self::EXAM_STATUS_EXAMINER_ASSIGNED);
        $statuses[self::EXAM_STATUS_MARKING_STARTED]    = self::ExamStatus(self::EXAM_STATUS_MARKING_STARTED);
	    $statuses[self::EXAM_STATUS_MARK_PARTIAL]       = self::ExamStatus(self::EXAM_STATUS_MARK_PARTIAL);
	    $statuses[self::EXAM_STATUS_MARK_FULL]          = self::ExamStatus(self::EXAM_STATUS_MARK_FULL);
        $statuses[self::EXAM_STATUS_COMPLETED]          = self::ExamStatus(self::EXAM_STATUS_COMPLETED);
        return $statuses;
    }

    static public function getAnomaly(int $anomalyCode){
        return match ($anomalyCode)
        {
            self::EXAM_ANOMALY_NONE => "Không",
            self::EXAM_ANOMALY_SUB25 => "Kỷ luật, trừ 25%",
            self::EXAM_ANOMALY_SUB50 => "Kỷ luật, trừ 50%",
            self::EXAM_ANOMALY_BAN => "Đình chỉ thi",
            self::EXAM_ANOMALY_ABSENT => "Vắng thi (không lý do)",
            self::EXAM_ANOMALY_DELAY => "Hoãn thi (có lý do)",
            self::EXAM_ANOMALY_REDO => "Dừng thi, bảo lưu lượt thi"
        };
    }
    static public function getAnomalies(){
        $anomalies = array();
        $anomalies[self::EXAM_ANOMALY_NONE] = self::getAnomaly(self::EXAM_ANOMALY_NONE);
        $anomalies[self::EXAM_ANOMALY_SUB25] = self::getAnomaly(self::EXAM_ANOMALY_SUB25);
        $anomalies[self::EXAM_ANOMALY_SUB50] = self::getAnomaly(self::EXAM_ANOMALY_SUB50);
        $anomalies[self::EXAM_ANOMALY_BAN] = self::getAnomaly(self::EXAM_ANOMALY_BAN);
        $anomalies[self::EXAM_ANOMALY_ABSENT] = self::getAnomaly(self::EXAM_ANOMALY_ABSENT);
        $anomalies[self::EXAM_ANOMALY_DELAY] = self::getAnomaly(self::EXAM_ANOMALY_DELAY);
        $anomalies[self::EXAM_ANOMALY_REDO] = self::getAnomaly(self::EXAM_ANOMALY_REDO);
        return $anomalies;
    }

	static public function getPostPrimaryAssessmentAction(int $pppaCode){
        return match ($pppaCode)
        {
            self::EXAM_PPAA_NONE => "Không",
            self::EXAM_PPAA_REVIEW => "Phúc khảo",
            self::EXAM_PPAA_CORRECTION => "Sửa sai điểm",
            default => false
        };
    }

    static public function getPostPrimaryAssessmentActions(){
        $ppaa = array();
        $ppaa[self::EXAM_PPAA_NONE] = self::getPostPrimaryAssessmentAction(self::EXAM_PPAA_NONE);
        $ppaa[self::EXAM_PPAA_REVIEW] = self::getPostPrimaryAssessmentAction(self::EXAM_PPAA_REVIEW);
        $ppaa[self::EXAM_PPAA_CORRECTION] = self::getPostPrimaryAssessmentAction(self::EXAM_PPAA_CORRECTION);
        return $ppaa;
    }

	static public function getSecondAttemptLimit($code){
		return match ($code)
		{
			self::SECOND_ATTEMPT_LIMIT_NONE => Text::_('Không giới hạn'),
			self::SECOND_ATTEMPT_LIMIT_EXAM => Text::_('Giới hạn điểm thi KTHP bằng 6.9'),
			self::SECOND_ATTEMPT_LIMIT_MODULE => Text::_('Giới hạn điểm học phần bằng 6.9'),
			default => false
		};
	}

	static public function getSecondAttemptLimits(){
		$limits = array();
		$limits[self::SECOND_ATTEMPT_LIMIT_NONE] = self::getSecondAttemptLimit(self::SECOND_ATTEMPT_LIMIT_NONE);
		$limits[self::SECOND_ATTEMPT_LIMIT_EXAM] = self::getSecondAttemptLimit(self::SECOND_ATTEMPT_LIMIT_EXAM);
		$limits[self::SECOND_ATTEMPT_LIMIT_MODULE] = self::getSecondAttemptLimit(self::SECOND_ATTEMPT_LIMIT_MODULE);
		return $limits;
	}
    static public function getExamName(int $examId){
        static $exams;

        //Load exams if not exist
        if(empty($exams)){
            $db = DatabaseHelper::getDatabaseDriver();
            $query = $db->getQuery(true)
                ->from('#__eqa_exams')
                ->select('id, name');
            $db->setQuery($query);
            $exams = $db->loadAssocList('id','name');
        }

        //Return
        if(empty($exams) || !array_key_exists($examId, $exams))
            return null;
        return $exams[$examId];
    }

    static public function getPamForDefaultFormular(float $pam1, float $pam2)
    {
        $pam = 0.7*$pam1 + 0.3*$pam2;
        return $pam;
    }
    static public function isAllowedToFinalExam(float $pam1, float $pam2, float $pam){
        if($pam1<4.0 || $pam2<4.0)
            return 0;
        return  1;
    }
	static public function calculateFinalMark(float $originalMark, int $anomaly, int $attempt)
	{
		$precision = ConfigHelper::getExamMarkPrecision();
		$finalMark = match ($anomaly)
		{
			self::EXAM_ANOMALY_NONE => $originalMark,
			self::EXAM_ANOMALY_SUB25 => round(0.75 * $originalMark, $precision),
			self::EXAM_ANOMALY_SUB50 => round(0.5 * $originalMark, $precision),
			default => 0,
		};
		if($attempt>1 && ConfigHelper::getSecondAttemptLimit()==self::SECOND_ATTEMPT_LIMIT_EXAM)
			$finalMark = max([$finalMark, 6.9]);
		return $finalMark;
	}
	static public function calculateModuleMark(int $subjectId, float $pam, float $examMark, int $attempt)
	{
		$precision = ConfigHelper::getModuleMarkPrecision();
		$limit = ConfigHelper::getSecondAttemptLimit();
		$moduleMark = 0.3*$pam + 0.7*$examMark;
		$moduleMark = round($moduleMark, $precision);
		if($attempt>1 && $limit==self::SECOND_ATTEMPT_LIMIT_MODULE)
			$moduleMark = max([$moduleMark, 6.9]);
		return $moduleMark;
	}
	static public function calculateModuleGrade(float $moduleMark)
	{
		if($moduleMark < 4.0)
			return 'F';
		if($moduleMark <= 4.7)
			return 'D';
		if($moduleMark <= 5.4)
			return 'D+';
		if($moduleMark <= 6.2)
			return 'C';
		if($moduleMark <= 6.9)
			return 'C+';
		if($moduleMark <= 7.7)
			return 'B';
		if($moduleMark <= 8.4)
			return 'B+';
		if($moduleMark <= 8.9)
			return 'A';
		return 'A+';
	}
	static public function conclude($moduleMark, $finalExamMark)
	{
		if ($finalExamMark < 4.0 || $moduleMark < 4.0)
			return self::CONCLUSION_FAILED;
		return self::CONCLUSION_PASSED;
	}
}

