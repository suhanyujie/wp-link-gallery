<?php
/**
 *
 * ref: https://stackoverflow.com/questions/5144893/getting-wordpress-database-name-username-password-with-php
 *
 */
namespace LinkGallery\Models;

use Illuminate\Database\Capsule\Manager as Capsule;

class BaseFormModel extends BaseModel
{
  protected $baseModel;
  protected string $tabeName;

  public function __construct()
  {
    global $wpdb;
    $parentObj = new parent($wpdb);
    $parentObj->initEloquentOrm();
    $this->tabeName = 'lg_contact_forms';
  }

  public function setReply($id , string $replyContent)
  {
    $query = Capsule::table($this->tabeName);
    $dataObj = $query->where('id', $id)->first();
    $cObj['reply'] = $replyContent;
    $res = $query->where('id', $id)->update([
      'content' => json_encode($cObj),
      'status'=>'1', // 回复，1:回复，2:拒绝
    ]);
    return $res;
  }

  public function reject($id)
  {
    $query = Capsule::table($this->tabeName);
    $dataObj = $query->where('id', $id)->first();
    $cObj = json_decode($dataObj->content, 256);
    $res = $query->where('id', $id)->update([
      'status'=>'2', // 回复，1:回复，2:拒绝
    ]);
    return $res;
  }
}
