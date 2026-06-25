<?php

function is_admin() {
    return isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1;
}
