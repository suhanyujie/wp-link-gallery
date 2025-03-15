<?php

namespace LinkGallery\Services;

class VolunteerEmailService extends BaseEmailService
{
    /**
     * 发送审核通过邮件
     *
     * @param string $to 收件人邮箱
     * @param string $name 收件人姓名
     * @return bool
     */
    public function sendApprovalEmail($to, $name)
    {
        $subject = 'ボランティア申請が承認されました';
        $message = sprintf(
            "%s 様\n\nボランティア申請が承認されました。\n\nご協力ありがとうございます。",
            $name
        );

        return $this->sendEmail($to, $subject, $message);
    }

    /**
     * 发送审核不通过邮件
     *
     * @param string $to 收件人邮箱
     * @param string $name 收件人姓名
     * @return bool
     */
    public function sendRejectionEmail($to, $name)
    {
        $subject = 'ボランティア申請が却下されました';
        $message = sprintf(
            "%s 様\n\n申し訳ございませんが、ボランティア申請が却下されました。\n\nご協力ありがとうございます。",
            $name
        );

        return $this->sendEmail($to, $subject, $message);
    }
}
