<?php

namespace Gigafeed\AdvancedLaravelMigrations\Classes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Migrations\Migration as BaseMigration;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

/**
 * Class Migration
 * @package App\Support\Classes
 */
class Migration extends BaseMigration
{
    /**
     * @var Model|null The model
     */
    protected $model = null;
    /**
     * @var string The table name
     */
    protected $tableName;
    /**
     * @var string The directory in which to store database data
     */
    protected $backupDir;
    /**
     * @var boolean If the app is running in production
     */
    protected $isRunningInProduction = true;

    /**
     * Migration constructor.
     *
     * @param Model|null $model The model to use for the migration
     * @param string|null $tableName The name of the table
     */
    public function __construct($model = null, $tableName = null)
    {
        /**
         * Get the configuration for the DB backup dir. Then set it.
         */
        $backupDir = config('app.backup_dir', base_path('/storage/framework/maint_mode_db_backup'));
        if (!file_exists($backupDir)) {
            mkdir($backupDir);
        }
        $this->backupDir = $backupDir;
        /**
         * Verify that the given model is not null and extends Model
         */
        if (!is_null($model)) {
            if (!($model == Model::class || is_subclass_of($model, Model::class))) {
                throw new InvalidArgumentException('You can only pass a model or pivot into the migration constructor.');
            }
            $this->model = $model;
        }
        /**
         * Check if the given table name is or is not null.
         *
         * If the table name <b>IS</b> null then cast it to string and set the variable
         *
         * If the table name <b>IS NOT</b> null then call the the getTableName function
         */
        if (!is_null($tableName)) {
            $this->tableName = (string)$tableName;
        } else {
            if (!method_exists($this->model, "getTableName")) {
                throw new InvalidArgumentException('The function `getTableName()` in ' . $this->model . ' does not exist');
            }
            $this->tableName = $this->model::getTableName();
        }
        /**
         * Determine if the app is running in production
         */
        if (config('app.env', 'production') !== 'production') {
            $this->isRunningInProduction = false;
        }
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ($this->isRunningInProduction) {
            $savedDbFile = $this->backupDir . '/table_' . $this->tableName . '.crypt.saved_db';
        } else {
            $savedDbFile = $this->backupDir . '/table_' . $this->tableName . '.saved_db';
        }
        if (file_exists($savedDbFile)) {
            $this->model::unguard();
            $jsonData = file_get_contents($savedDbFile);
            $this->model::insert(json_decode($this->decryptSavedData($jsonData), true));
            unlink($savedDbFile);
        }
    }

    /**
     * Decrypt the database data
     *
     * @param $encrypted string The encrypted data to be decrypted
     * @return mixed The decrypted data
     */
    protected final function decryptSavedData($encrypted)
    {
        if ($this->isRunningInProduction) {
            return decrypt($encrypted, true);
        }
        return $encrypted;
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if ($this->isRunningInProduction) {
            $savedDbFile = $this->backupDir . '/table_' . $this->tableName . '.crypt.saved_db';
        } else {
            $savedDbFile = $this->backupDir . '/table_' . $this->tableName . '.saved_db';
        }
        if (file_exists($savedDbFile)) {
            unlink($savedDbFile);
        }
        /*if (!is_null($this->model)) {
            $columns = Schema::getColumnListing($this->tableName); // users table
            $this->model::makeVisible($columns);
            $tableData = $this->model::all();
        } else {*/
        $tableData = \DB::table($this->tableName)->lockForUpdate()->get();
        //}
        file_put_contents($savedDbFile, $this->encryptSavedData(json_encode($tableData)));
        Schema::dropIfExists($this->tableName);
    }

    /**
     * Encrypt the database data
     *
     * @param $data string The data to be encrypted
     * @return string The encrypted data
     */
    protected final function encryptSavedData($data): string
    {
        if ($this->isRunningInProduction) {
            return encrypt($data, true);
        }
        return $data;
    }
}