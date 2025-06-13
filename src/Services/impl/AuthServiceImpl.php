<?php

namespace App\Services\impl;

use App\Core\View;
use App\Dtos\LoginRequestDto;
use App\Dtos\RegisterRequestDto;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use Valitron\Validator;

class AuthServiceImpl implements AuthService
{

    private UserRepository $userRepo;

    public function __construct(UserRepository $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function loginView()
    {
        if (isLoggedIn()) return redirectTo('/account');

        View::render('login');
    }

    public function authenticate(LoginRequestDto $loginReq)
    {
        $v = new Validator(get_object_vars($loginReq));

        $v->rule('required', ['usernameOrEmail', 'password'])
            ->message('The field is required');

        if ($v->validate()) {
            $user = $this->userRepo->findByEmail($loginReq->email);

            if ($user != null) {
                setUser($user);

                return redirectTo('/account');
            } else {
                addFlashMessage('error', 'User not found');
                setOldFields('login', [
                    'email' => $loginReq->email
                ]);
                View::render('login');
            }
        } else {
            setOldFields('login', [
                'email' => $loginReq->email
            ]);
            setValidationErrors('login', $v->errors());
            View::render('login');
        }
    }

    public function registerView()
    {
        if (isLoggedIn()) return redirectTo('/account');
        View::render('register');
    }

    public function register(RegisterRequestDto $registerReq)
    {
        $v = new Validator(get_object_vars($registerReq));

        $v->rule('required', ['username', 'email', 'password'])
            ->message('The field is required');

        $v->rule('email', ['email'])
            ->message('The field must be an email');

        if ($v->validate()) {
            $existsByEmail = $this->userRepo->existsByEmail($registerReq->email);

            if ($existsByEmail) {
                addFlashMessage('error', 'Email is already taken');
            }

            if ($existsByEmail) {
                setOldFields('register', [
                    'username' => $registerReq->username,
                    'email' => $registerReq->email
                ]);
                View::render('register');
                return;
            }

            $hash = password_hash($registerReq->password, PASSWORD_BCRYPT);

            $user = new User;
            $user->username = $registerReq->username;
            $user->email = $registerReq->email;
            $user->hash = $hash;

            $savedUser = $this->userRepo->save($user);

            if ($savedUser != null) {
                setUser($savedUser);
                addFlashMessage('success', 'Registered successfully');

                return redirectTo('/account');
            } else die('Error while saving user');
        } else {
            setValidationErrors('register', $v->errors());
            View::render('register');
        }
    }

    public function logout()
    {
        unsetUser();

        redirectTo('/login');
    }
}
