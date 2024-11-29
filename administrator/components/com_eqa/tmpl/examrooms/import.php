<?php
defined('_JEXEC') or die();

use Joomla\CMS\HTML\HTMLHelper;
$urlSamplePaper = JUri::root() . 'media/com_eqa/xlsx/sample_exam_report_paper.xlsx';
$urlSampleNonpaper = JUri::root() . 'media/com_eqa/xlsx/sample_exam_report_nonpaper.xlsx';
$urlEditWarning = JRoute::_('index.php?option=com_eqa&view=examrooms',false);
$formAction = JRoute::_('index.php?option=com_eqa');
HTMLHelper::_('behavior.formvalidator');
?>
<div>
	<b>Lưu ý:</b>
	<ol>
		<li>Đây là chức năng nhập biên bản coi thi, coi thi - chấm thi. Với <b>bài thi viết</b>,
			chức năng này chỉ ghi nhận số tờ giấy thi của các thí sinh để phục vụ cho việc làm phách.
			Đối với <b>bài thi khác</b>, chức năng này sẽ ghi nhận điểm thi, đồng thời tính toán điểm học phần
			(bao gồm việc trừ điểm nếu có xử lý kỷ luật; bao gồm việc giới hạn điểm thi lần 2 ở mức 6,9),
			cũng như đánh giá Đạt/Không đạt, xác định thí sinh đã hết lượt thi hay chưa.</li>
		<li>Để đảm bảo tính	chính xác của dữ liệu, cán bộ khảo thí cần 	<a href="<?php echo $urlEditWarning;?>">NHẬP
			THÔNG TIN BẤT THƯỜNG</a> (vắng thi,	hoãn thi, kỷ luật...) của thí sinh
			<span style="font-weight: bold; color: red">TRƯỚC KHI</span> thực hiện chức năng này. Đối với <b>bài thi viết</b>, cần nhập đủ
			mọi loại bất thường. Đối với <b>bài thi khác</b>, thường thì điểm mà CBChT ghi vào biên bản là điểm cuối cùng,
			sau khi đã xử lý trừ điểm (nếu có); do vậy chỉ nhập thông tin bất thường cho các trường hợp <b>đình chỉ thi</b>
			và <b>bảo lưu lượt thi</b> (hoãn thi, ốm đau bất thường, phải dừng thi do nguyên nhân khách quan...).
			<br/><b>Ghi chú</b>: chỉ cần nhập thông tin ở cột "Bất thường", nội dung cột "Ghi chú" sẽ được nhập từ
			file biên bản phòng thi, nếu có.
		</li>
		<li>Mẫu biên bản <b>Coi thi viết</b>: <a href="<?php echo $urlSamplePaper;?>">download</a></li>
		<li>Mẫu biên bản <b>Coi thi - Chấm thi</b> thực hành, vấn đáp, đồ án:
			<a href="<?php echo $urlSampleNonpaper;?>">download</a></li>
		<li><span style="font-weight: bold; color: red">Đặc biệt lưu ý</span>: không được làm sai lệch số hiệu <b>mã phòng thi</b>
			trong biên bản (mã phòng thi là một số nguyên được gán tự động khi xuất thông tin phòng thi).
			Sai lệch mã phòng thi sẽ dẫn đến sai lệch số liệu nhập vào hệ thống.</li>
	</ol>
</div>
<form action="<?php echo $formAction;?>" method="POST" enctype="multipart/form-data" name="adminForm" id="adminForm" class="form-validate" >
	<input type="hidden" name="task" value=""/>
	<?php
	echo $this->form->renderFieldset('upload');
	echo JHtml::_('form.token');
	?>
</form>
<?php
