<?php

namespace DLTools\Test;

use DLTools\Database\Model;

final class Employee extends Model {
    protected static string $timezone = '-05:00';
    // protected static ?string $table = "SELECT * FROM dl_employee";
}
