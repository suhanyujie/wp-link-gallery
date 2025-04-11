<?php

namespace LinkGallery\Services;

class EventFormDataManageService
{
  public static function getColumnMapForExport($flag = '')
  {
    $colName = $flag;
    if ($flag === '') {
      return $colName;
    }
    switch ($flag) {
      case 'your-name':
        $colName = 'お名前';break;
      case 'your-email':
        $colName =  'メールアドレス';break;
      case 'your-message':
        $colName =  'メッセージ';break;
    }
    return $colName;
  }
}
