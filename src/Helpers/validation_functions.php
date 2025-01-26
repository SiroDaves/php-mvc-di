<?php

function setValidationErrors($form, $errors)
{
    if (!isset($_SESSION['validation_errors'])) $_SESSION['validation_errors'] = []; 

    $_SESSION['validation_errors'][$form] = $errors;
}

function getFieldErrors($form, $field)
{
    if (isset($_SESSION['validation_errors']) && isset($_SESSION['validation_errors'][$form])
        && isset($_SESSION['validation_errors'][$form][$field]))
    {
        return $_SESSION['validation_errors'][$form][$field];
    }
}

function isFieldInvalid($form, $field): bool
{
    if (!isset($_SESSION['validation_errors'])) return false;

    if (!isset($_SESSION['validation_errors'][$form])) return false;

    return isset($_SESSION['validation_errors'][$form][$field]);
}

function clearValidationErrors()
{
    if (isset($_SESSION['validation_errors'])) unset($_SESSION['validation_errors']);
}