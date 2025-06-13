<?php

function route(string $routeParam): string
{
    return '/' . ltrim($routeParam, '/');
}

function redirectTo(string $route)
{
    header("Location: " . route($route));
}

function isLoggedIn(): bool
{
    return isset($_SESSION['user']);
}