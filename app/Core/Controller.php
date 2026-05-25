<?php
declare(strict_types=1);

namespace App\Core;

final class ViewNotFoundException extends \RuntimeException {}

abstract class Controller
{
    protected function view(string $view, array $data = [], string $layout = 'site'): void
    {
        $viewFile = __DIR__ . '/../Views/' . $view . '.php';
        if (!is_file($viewFile)) {
            throw new ViewNotFoundException('Представление не найдено: ' . $view);
        }

        extract($data, EXTR_SKIP);
        $layoutFile = __DIR__ . '/../Views/layouts/' . $layout . '.php';
        if (!is_file($layoutFile)) {
            require $viewFile;
            return;
        }
        require $layoutFile;
    }

    protected function notFound(string $message = 'Страница не найдена'): void
    {
        http_response_code(404);
        $this->view('site/404', ['title' => '404', 'message' => $message]);
    }
}
