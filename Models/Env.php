<?php

Class Environment {
    /**
     * @var Environment
     */
    protected static $_env_instance = null;

    /**
     * @var EnvironmentData
     */
    protected $_env;

    public static function load_env() {
        if (self::$_env_instance === null) {
            self::$_env_instance = new Environment();
        }
        return self::$_env_instance;
    }

    /**
     * @return EnvironmentData
     */
    private function __construct() {
        try {
            $this->_env = parse_ini_file('.env');
        }
        catch (Exception $e) {
            echo $e;
        }
    }

    // Get environment variable
    public function get_env($variable) {
        return $this->_env[$variable] ?? null;
    }

    public function __destruct()
    {
        $this->_env = null;
        self::$_env_instance = null;
    }
}
?>