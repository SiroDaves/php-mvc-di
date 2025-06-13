<?php

namespace App\Models;

use DateTime;

class User {
    public int $id;
    public string $email;
    public DateTime $createdAt;
    public DateTime $updatedAt;
}