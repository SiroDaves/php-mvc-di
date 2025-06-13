<?php

namespace App\Services\impl;

use App\Core\View;
use App\Services\AccountService;

class AccountServiceImpl implements AccountService
{
    public function profileView()
    {
        View::render('account');
    }
}