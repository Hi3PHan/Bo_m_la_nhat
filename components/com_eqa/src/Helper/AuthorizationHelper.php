<?php
namespace Kma\Component\Eqa\Site\Helper;
use Joomla\CMS\User\User;

defined('_JEXEC') or die();
abstract class AuthorizationHelper{
    /**
     * Trích xuất giá trị "username" từ đối tượng $identity.
     * Sở dĩ phải có hàm này là vì đặc điểm của plugin SSO
     * Với tùy chọn "Tự động tạo tài khoản mới", cái thì lấy đúng
     * mã sinh viên, cái thì lấy họ và tên để làm username.
     * Trong mọi trường hợp, địa chỉ email sẽ phải chính xác, cho
     * phép trích được username.
     * Tuy nhiên, trong giai đoạn phát triển (development), có thể
     * không có địa chỉ email --> phải căn cứ vào username.
     *
     * @since   1.0
     * @param   User    $identity   Người dùng đã đăng nhập
     * @return  string  Username của người dùng đã đăng nhập.
     **/
    public static function getStudentUsername($identity){
        $patternStudentUsername = "/[ACDTN]{2,3}[0-9]{5,6}/i";
        $patternStudentEmail = "/[ACDTN]{2,3}[0-9]{5,6}@actvn\.edu\.vn/i";
        if(preg_match($patternStudentUsername, $identity->username))
            return $identity->username;
        else if(preg_match($patternStudentEmail, $identity->email))
            return substr($identity->email, 0, strlen($identity->email)-strlen("@actvn.edu.vn"));
        else
            return null;
    }
}
