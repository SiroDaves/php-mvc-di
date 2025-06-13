<?php

namespace App\Core;

class View
{
    public static function render(string $view, array $data = [])
    {
        extract($data);

        $viewPath = str_replace('.', '/', $view);

        $file = dirname(__DIR__) . "/Views/{$viewPath}.php";

        if (file_exists($file)) {
            require $file;
        } else {
            http_response_code(404);
            echo "View '{$view}' not found.";
        }
    }
}
