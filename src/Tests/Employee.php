<?php

namespace DLTools\Test;

use DLTools\Database\Model;

final class Employee extends Model {
    protected static ?string $table = "dl_employee";
}
