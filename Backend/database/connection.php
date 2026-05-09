<?php

function db()
{
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli("127.0.0.1", "root", "", "eco_project");

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
    }

    return $conn;
}