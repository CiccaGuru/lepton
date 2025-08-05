<?php

namespace Lepton\Authenticator;

use Lepton\Core\Application;

class UserAuthenticator
{
    private $config;

    public function __construct()
    {
        $this->config = Application::getAuthConfig();

    }

    public function getUserByUsername($username)
    {
        $userModel = $this->config->auth_model;
        $userUsernameField = $this->config->username_field;
        $arguments = [$userUsernameField => $username];
        $user = $userModel::get(...$arguments);
        return $user;
    }

    public function getUserById($id)
    {
        $userModel = $this->config->auth_model;
        $user = $userModel::get($id);
        return $user;
    }


    public function login($username, $password)
    {
        $user = $this->getUserByUsername($username);

        if (!$user) {
            return false;
        }

        $passwordField = $this->config->password_field;
        if (password_verify($password, $user->$passwordField)) {
            $session_hash = bin2hex(random_bytes(32));
            $_SESSION['user_id'] = $user->getPk();
            $_SESSION['session_hash'] = $session_hash;
            if($this->config->login_use_unique_hash) {
                $hashField = $this->config->hash_field;
                $user->$hashField = $session_hash;
                $user->save();
            }
            if(isset($this->config->access_field)){
                $accessField = $this->config->access_field;
                date_default_timezone_set('Etc/UTC');
                $user->$accessField = date('Y-m-d H:i:s', time());
                $user->save();
            }
            return true;
        }

        return false;
    }

    public function isLoggedIn()
    {

        if (!isset($_SESSION['user_id'])) {
            return false;
        }

        $user_id = $_SESSION['user_id'];
        $user = $this->getUserById($user_id);

        //$hashField = $this->config->hash_field;
        if (!$user) {// || $user->$hashField !== $_SESSION['session_hash']) {
            return false;
        }

        if(isset($this->config->active_field)){
            $accessField = $this->config->active_field;
            date_default_timezone_set('Etc/UTC');
            $user->$accessField = date('Y-m-d H:i:s', time());
            $user->save();
        }
        return true;
    }


    public function getLoggedUser()
    {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }

        $user_id = $_SESSION['user_id'];
        $user = $this->getUserById($user_id);
        return $user;
    }



    public function register($username, $password = null, $password_length = 6)
    {
        // Check if username is already taken
        if ($this->getUserByUsername($username)) {
            return false;
        }

        // Hash the password
        if (! $password) {
            $password = $this->randomPassword(length: $password_length);
        }

        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $userModel = $this->config->auth_model;
        $usernameField = $this->config->username_field;
        $passwordField = $this->config->password_field;

        $user = new $userModel();
        $user->$usernameField = $username;
        $user->$passwordField = $password_hash;
        return $user;
    }


    public function passwordReset($id, $length = 6)
    {
        $user = $this->getUserById($id);
        $passwordField = $this->config->password_field;

        $password = $this->randomPassword(length: $length);
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $user->$passwordField = $password_hash;
        $user->save();
        return $password;
    }

    public function logout()
    {
        if ($this->isLoggedIn()) {
            if (isset($_SESSION['user_id'])) {
                if(isset($this->config->hash_field)) {
                    $user = $this->getUserById($_SESSION['user_id']);
                    $hashField = $this->config->hash_field;
                    $user->$hashField = "";
                    $user->save();
                }
            }
            session_unset();
            session_destroy();
        }
    }

    public function getLevel()
    {
        if ($this->isLoggedIn()) {
            $user = $this->getUserById($_SESSION['user_id']);
            $levelField = $this->config->level_field;
            return $user->$levelField;
        }
        return 0;
    }


    public function randomPassword(
        $length,
        $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
    ) {
        $str = '';
        $max = mb_strlen($keyspace, '8bit') - 1;
        if ($max < 1) {
            throw new \Exception('$keyspace must be at least two characters long');
        }
        for ($i = 0; $i < $length; ++$i) {
            $str .= $keyspace[random_int(0, $max)];
        }
        return $str;
    }
}
