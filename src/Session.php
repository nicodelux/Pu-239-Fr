<?php

namespace DarkAlchemy\Pu239;

class Session
{
    private $config;
    private $cache;

    /**
     * Session constructor.
     *
     * @throws \MatthiasMullie\Scrapbook\Exception\Exception
     * @throws \MatthiasMullie\Scrapbook\Exception\ServerUnhealthy
     */
    public function __construct()
    {
        global $site_config;
        $this->config = $site_config;
        $this->cache  = new Cache();
    }

    /**
     * @return bool
     *
     * @throws Exception
     * @throws \Exception
     * @throws \MatthiasMullie\Scrapbook\Exception\Exception
     * @throws \MatthiasMullie\Scrapbook\Exception\ServerUnhealthy
     */
    public function start()
    {
        if (!session_id()) {
            // Set the session name:
            session_name($this->config['sessionName']);

            $expires = $this->config['cookie_lifetime'] <= 1 ? 900 : $this->config['cookie_lifetime'] * 86400;
            $secure_session = 'https' === get_scheme() ? true : false;
            $domain = $this->config['cookie_domain'] === $this->config['domain'] ? '' : $this->config['cookie_domain'];

            // Set session cookie parameters:
            session_set_cookie_params(
                $expires,
                $this->config['cookie_path'],
                $domain,
                $secure_session,
                true
            );

            // enforce php settings before start session
            ini_set('session.use_strict_mode', 1);
            ini_set('session.use_trans_sid', 0);
            ini_set('default_charset', $this->config['char_set']);
            ini_set('session.lazy_write', 0);
            if (ini_get('session.save_handler') != 'files') {
                ini_set('session.sid_length', 256);
            } else {
                ini_set('session.sid_length', 128);
            }

            // Start the session:
            if (!@session_start()) {
                $this->destroy();

                return false;
            }
        }

        if (!session_id()) {
            $this->destroy();

            return false;
        }

        if (!$this->get('canary')) {
            $this->set('canary', TIME_NOW);
        }

        if (!$this->get('auth')) {
            $this->set('auth', bin2hex(random_bytes(32)));
        }

        if (!$this->get('salt')) {
            $this->set('salt', make_passhash($this->get('auth')));
        }

        if (!$this->get($this->config['session_csrf'])) {
            $this->set($this->config['session_csrf'], bin2hex(random_bytes(32)));
        }

        if ($this->get('canary') <= TIME_NOW - 300) {
            session_regenerate_id(true);
            $this->set('canary', TIME_NOW);
        }

        return true;

    }

    /**
     * @param      $key
     * @param      $value
     * @param null $prefix
     */
    public function set($key, $value, $prefix = null)
    {
        if (null === $prefix) {
            $prefix = $this->config['sessionKeyPrefix'];
        }
        if (in_array($key, $this->config['notifications'])) {
            $current = $this->get($key);
            if ($current) {
                if (!in_array($value, $current)) {
                    $_SESSION[$prefix . $key] = array_merge($current, [$value]);
                }
            } else {
                $_SESSION[$prefix . $key] = [$value];
            }
        } else {
            $this->unset($key);
            $_SESSION[$prefix . $key] = $value;
        }
    }

    /**
     * @param      $key
     * @param null $prefix
     */
    public function get($key, $prefix = null)
    {
        if (empty($key)) {
            return null;
        }

        if (null === $prefix) {
            $prefix = $this->config['sessionKeyPrefix'];
        }

        if (isset($_SESSION[$prefix . $key])) {
            return $_SESSION[$prefix . $key];
        } else {
            return null;
        }
    }

    /**
     * @param      $key
     * @param null $prefix
     */
    public function unset($key, $prefix = null)
    {
        if (null === $prefix) {
            $prefix = $this->config['sessionKeyPrefix'];
        }

        unset($_SESSION[$prefix . $key]);
    }

    /**
     * @param      $token
     * @param null $key
     * @param bool $regen
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function validateToken($token, $key = null, $regen = false)
    {
        if (null === $key) {
            $key = $this->config['session_csrf'];
        }
        if (empty($token)) {
            return false;
        }

        if (hash_equals($this->get($key), $token)) {
            if ($regen) {
                $this->unset($key);
                $this->set($key, bin2hex(random_bytes(32)));
            }

            return true;
        }

        return false;
    }

    /**
     * @throws Exception
     * @throws \Exception
     * @throws \MatthiasMullie\Scrapbook\Exception\Exception
     * @throws \MatthiasMullie\Scrapbook\Exception\ServerUnhealthy
     */
    public function destroy()
    {
        $cookies = new Cookie('remember');
        $cookie  = $cookies->getToken();
        if (!empty($cookie[0])) {
            $this->cache->delete('remember_' . $cookie[0]);
        }

        $this->start();
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                $this->config['cookie_prefix'] . 'remember',
                '',
                TIME_NOW - 86400,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );

            setcookie(
                session_name(),
                '',
                TIME_NOW - 86400,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_unset();
        session_destroy();
    }

    public function close()
    {
        session_write_close();
    }
}
