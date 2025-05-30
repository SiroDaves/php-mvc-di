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

    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function loginView()
    {
        if (isLoggedIn()) return redirectTo('/account');

        View::render('login');
    }

    public function authenticate(LoginRequestDto $loginRequest)
    {
        $v = new Validator(get_object_vars($loginRequest));

        $v->rule('required', ['usernameOrEmail', 'password'])
            ->message('The field is required');

        if ($v->validate()) {
            $user = $this->userRepository->findByUsernameOrEmail($loginRequest->usernameOrEmail, $loginRequest->usernameOrEmail);

            if ($user != null) {
                setUser($user);

                return redirectTo('/account');
            } else {
                addFlashMessage('error', 'User not found');
                setOldFields('login', [
                    'usernameOrEmail' => $loginRequest->usernameOrEmail
                ]);
                View::render('login');
            }
        } else {
            setOldFields('login', [
                'usernameOrEmail' => $loginRequest->usernameOrEmail
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

    public function register(RegisterRequestDto $registerRequest)
    {
        $v = new Validator(get_object_vars($registerRequest));

        $v->rule('required', ['username', 'email', 'password'])
            ->message('The field is required');

        $v->rule('email', ['email'])
            ->message('The field must be an email');

        if ($v->validate()) {
            $existsByUsername = $this->userRepository->existsByUsername($registerRequest->username);
            $existsByEmail = $this->userRepository->existsByEmail($registerRequest->email);

            if ($existsByUsername) {
                addFlashMessage('error', 'Username is already taken');
            }

            if ($existsByEmail) {
                addFlashMessage('error', 'Email is already taken');
            }

            if ($existsByUsername || $existsByEmail) {
                setOldFields('register', [
                    'username' => $registerRequest->username,
                    'email' => $registerRequest->email
                ]);
                View::render('register');
                return;
            }

            $hash = password_hash($registerRequest->password, PASSWORD_BCRYPT);

            $user = new User;
            $user->username = $registerRequest->username;
            $user->email = $registerRequest->email;
            $user->hash = $hash;

            $savedUser = $this->userRepository->save($user);

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
