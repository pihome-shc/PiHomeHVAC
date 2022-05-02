<?php
  session_start();

  // if you have more session-vars that are needed for login, also check
  // if they are set and refresh them as well
  if (isset($_SESSION['persist'])) {
    if ($_SESSION['persist'] == 1) {
        $_COOKIE['PHPSESSID'] = $_COOKIE['PHPSESSID'];
        $_COOKIE['user_login'] =  $_COOKIE['user_login'];
        $_COOKIE['pass_login'] = $_COOKIE['pass_login'];
        $_SESSION['persist'] = $_SESSION['persist'];
        $_SESSION['user_id'] = $_SESSION['user_id'];
        $_SESSION['username'] = $_SESSION['username'];
        $_SESSION['admin'] = $_SESSION['admin'];
    } else {
        session_unset();     // unset $_SESSION variable for the run-time
        session_destroy();   // destroy session data in storage
    }
  }
?>
