<?php
/**
 *
 * ref: https://stackoverflow.com/questions/5144893/getting-wordpress-database-name-username-password-with-php
 *
 */

namespace LinkGallery\Models;

class BaseFormModel extends BaseModel
{
    protected $_wpdb;
    protected string $tableName = 'lg_contact_forms';
    protected $dbObj;

    public function __construct()
    {
        global $wpdb;
        $this->_wpdb = $wpdb;
        $this->dbConn = (new parent($wpdb))->dbConn;
        $this->dbObj = $this->table($this->tableName);
    }

    public function getRowById($id)
    {
        $query = $this->table($this->tableName);
        $dataObj = $query->where('id', $id)->first();
        return $dataObj;
    }

    public function setReply($id, string $replyContent)
    {
        $res = 0;
        $query = $this->table($this->tableName);
        $dataObj = $query->where('id', $id)->first();
        $cObj = json_decode($dataObj->content, 256);
        $cObj['reply'] = $replyContent;
        $res = $query->where('id', $id)->update([
            'content' => json_encode($cObj),
            'status' => '1', // 回复，1:回复，2:拒绝
        ]);
        return $res;
    }

    public function reject($id, string $replyContent)
    {
        $query = $this->table($this->tableName);
        $dataObj = $query->where('id', $id)->first();
        $cObj = json_decode($dataObj->content, 256);
        $cObj['reply'] = $replyContent;
        $res = $query->where('id', $id)->update([
            'content' => json_encode($cObj),
            'status' => '2', // 回复，1:回复，2:拒绝
        ]);
        return $res;
    }
}
