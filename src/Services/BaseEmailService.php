<?php

namespace LinkGallery\Services;

abstract class BaseEmailService
{
    /**
     * 发送审核通过邮件
     *
     * @param string $to 收件人邮箱
     * @param string $name 收件人姓名
     * @return bool
     */
    abstract public function sendApprovalEmail($to, $name);

    /**
     * 发送审核不通过邮件
     *
     * @param string $to 收件人邮箱
     * @param string $name 收件人姓名
     * @return bool
     */
    abstract public function sendRejectionEmail($to, $name);

    /**
     * 发送邮件的通用方法
     *
     * @param string $to 收件人邮箱
     * @param string $subject 邮件主题
     * @param string $message 邮件内容
     * @return bool
     */
    protected function sendEmail($to, $subject, $message)
    {
        $headers = ['Content-Type: text/plain; charset=UTF-8'];
        return wp_mail($to, $subject, $message, $headers);
    }
}
