<?php

namespace LinkGallery\Services\Admin;

use LinkGallery\Models\BaseFormModel;

class QaFormService
{
    private $model;

    public function __construct()
    {
        $this->model = new BaseFormModel();
    }

    public function replyToUserAndSendEmail($id, $reply)
    {
        $dataObj = $this->model->getRowById($id);
        $cObj = json_decode($dataObj->content, 256);
        if (isset($cObj['your-email']) && isset($cObj['your-name'])) {
            $emailSvc = new QaFormEmailService();
            $emailSvc->sendApprovalEmail($cObj['your-email'], $cObj['your-name'], $reply);
        }
        return $this->model->setReply($id, $reply);
    }


    public function replyForReject($id, $reply)
    {
        return $this->model->reject($id, $reply);
    }
}
