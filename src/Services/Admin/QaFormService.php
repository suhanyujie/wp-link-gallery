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
        return $this->model->setReply($id, $reply);
    }


    public function replyForReject($id, $reply)
    {
        return $this->model->reject($id, $reply);
    }
}
