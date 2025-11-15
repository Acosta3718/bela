<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Validator;

class AuthController extends Controller
{
    public function login()
    {
        return $this->view('auth/login');
    }

    public function authenticate()
    {
        $data = Request::all();
        $errors = Validator::validate($data, [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if ($errors) {
            return $this->view('auth/login', compact('errors'));
        }

        if (!Auth::attempt($data['email'], $data['password'])) {
            $errors['general'][] = 'Credenciales inválidas';
            return $this->view('auth/login', compact('errors'));
        }

        return $this->redirect('/');
    }

    public function logout()
    {
        Auth::logout();
        return $this->redirect('/login');
    }
}