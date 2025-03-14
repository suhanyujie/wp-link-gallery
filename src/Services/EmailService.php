<?php

namespace LinkGallery\Services;

class EmailService
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
        $subject = '学習者申請が承認されました';
        $message = sprintf(
            "%s 様\n\n学習者申請が承認されました。\n\nご利用ありがとうございます。",
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
        $subject = '学習者申請が却下されました';
        $message = sprintf(
            "%s 様\n\n申し訳ございませんが、学習者申請が却下されました。\n\nご利用ありがとうございます。",
            $name
        );

        return $this->sendEmail($to, $subject, $message);
    }

    /**
     * 发送邮件的通用方法
     *
     * @param string $to 收件人邮箱
     * @param string $subject 邮件主题
     * @param string $message 邮件内容
     * @return bool
     */
    private function sendEmail($to, $subject, $message)
    {
        $headers = ['Content-Type: text/plain; charset=UTF-8'];
        return wp_mail($to, $subject, $message, $headers);
    }
}
