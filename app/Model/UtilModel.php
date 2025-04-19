<?php
namespace App\Model;

class UtilModel
{
    /**
     * Load environment variables from .env file
     */
    public static function carregar_variaveis_de_ambiente(): void
    {
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->safeLoad();
    }
    
    /**
     * Get environment variable value
     * 
     * @param string $name Environment variable name
     * @param mixed $default Default value if environment variable is not set
     * @return mixed Environment variable value or default value
     */
    public static function load_env(string $name, $default = null)
    {
        return $_ENV[$name] ?? $default;
    }
}
