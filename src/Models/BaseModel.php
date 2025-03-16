<?php
/**
 *
 * ref: https://stackoverflow.com/questions/5144893/getting-wordpress-database-name-username-password-with-php
 *
 */
namespace LinkGallery\Models;

use Illuminate\Database\Capsule\Manager as Capsule;

class BaseModel
{
    public $_oDb;

    public function __construct($oDb)
    {
        $this->_oDb = $oDb;
    }

    public function __get($sField)
    {
        if($sField != '_oDb')
            return $this->_oDb->$sField;
    }

    public function __set($sField, $mValue)
    {
        if($sField != '_oDb')
            $this->_oDb->$sField = $mValue;
    }

    public function __call($sMethod, array $aArgs)
    {
        return call_user_func_array(array($this->_oDb, $sMethod), $aArgs);
    }

    public function getDbName() { return $this->_oDb->dbname;     }
    public function getDbUser() { return $this->_oDb->dbuser; }
    public function getDbPass() { return $this->_oDb->dbpassword; }
    public function getDbHost() { return $this->_oDb->dbhost;     }

    public function initEloquentOrm()
    {
        global $wpdb;

        try {
            $capsule = new Capsule();

            $options = [
                'driver' => 'mysql',
                'host' => $this->getDbHost(),
                'port' => $this->_oDb->dbport,
                'database' => $this->getDbName(),
                'username' => $this->getDbUser(),
                'password' => $this->getDbPass(),
                'charset' => $wpdb->charset,
                'collation' => $wpdb->collate,
                'prefix' => $wpdb->prefix,
                'options' => [
                    \PDO::ATTR_TIMEOUT => 5,
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_PERSISTENT => false,
                    \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                ],
                'other' => [
                    'server'=>$this->_oDb->dbhost ,
                    'server-host'=> DB_HOST ,
                ],
            ];
            error_log(print_r($options, true));
            $capsule->addConnection($options);

            $capsule->setAsGlobal();
            $capsule->bootEloquent();
        } catch (\Exception $e) {
            error_log('Database connection error: ' . $e->getMessage());
            throw new \Exception('数据库连接失败，请检查配置信息或联系管理员。');
        }
    }
}
