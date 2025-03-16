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

    // $result = $this->updateStatus($id, $status);
  }


  public function replyForReject($id)
  {
    return $this->model->reject($id, $reply);
  }
}
