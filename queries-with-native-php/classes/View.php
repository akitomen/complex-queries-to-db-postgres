<?php


class View
{
    public static function render(string $view, ...$params)
    {
        foreach ($params as $param) {
            foreach ($param as $key => $value) {
                ${$key} = $value;
            }
        }
        require __DIR__ . "/../views/$view.php";
    }
}
