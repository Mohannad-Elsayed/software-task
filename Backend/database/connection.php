<?php

function db()
{
    $conn = new mysqli("localhost", "root", "", "eco_project");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}