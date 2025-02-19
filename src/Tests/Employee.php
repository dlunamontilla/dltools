<?php

namespace DLTools\Test;

use DLTools\Database\Model;

final class Employee extends Model {
    public static ?string $table = "(SELECT * FROM dl_employee) as ciencia";
}
