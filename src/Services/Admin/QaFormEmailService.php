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
        $message = render_str('admin.qa-forms.email.approval', [
            'name' => $name,
            'content' => $content
        ]);

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
        $message = render_str('admin.qa-forms.email.rejection', [
            'name' => $name
        ]);

        return $this->sendEmail($to, $subject, $message);
    }
}
