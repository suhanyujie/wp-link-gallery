<?php

namespace LinkGallery\Services\Admin;

use LinkGallery\Services\BaseEmailService;

class QaFormEmailService extends BaseEmailService
{
    /**
     * 发送审核通过邮件
     *
     * @param string $to 收件人邮箱
     * @param string $name 收件人姓名
     * @return bool
     */
    public function sendApprovalEmail($to, $name, $content = '')
    {
        $subject = '問い合わせについて';
        $message = sprintf(
            "%s 様\n\n 問い合わせ ご利用ありがとうございます。\n\n %s",
            $name, $content
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
}