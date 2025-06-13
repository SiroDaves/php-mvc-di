<?php

namespace App\Repositories;

interface UserRepository extends Repository
{
    public function findByEmail($email);

    public function existsByEmail($email);

    public function matchEmailWithPassword($email, $password);
}