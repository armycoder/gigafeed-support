# Advanced Laravel Migrations
Simple package for saving database data when reversing migrations
<br>
### NOTE:
I am releasing this package as v1.0 AS IS (This was an internal package for internal uses). I will start working on a v2.0 soon. I would suggest waiting for the v2.0
<br><br>
Please read the code before installing. The classes are super simple. I will add more functionality and make this library less coupled in v2.0
### Installation
```bash
composer require gigafeed/advanced-laravel-migrations
```

### Example Usage
Just extend the `Gigafeed\AdvancedDatabaseMigrations\Classes\Migration` class

```php
<?php

use App\PublicContact;

/** 
 *This class extends the laravel migration class. 
 *It just adds a little extra helper functions 
 */
use Gigafeed\AdvancedLaravelMigrations\Classes\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreatePublicContactsTable
 */
class CreatePublicContactsTable extends Migration
{
    public function __construct()
    {
        parent::__construct(PublicContact::class);
    }
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->string('id', 30)->primary();
            $table->string('first_name', 35);
            $table->string('last_name', 35);
            $table->string('email', 75)->index();
            $table->string('message', 255);
            $table->timestamp('read_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        parent::up();
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        parent::down();
    }
}
```
