<?php
 
        function requireLogin(){
            if(!isset($_SESSION["user"])) {

                header("Location: /pocket_money/views/login.php");
                exit();
            }
        }
        function requireAdmin() {

            requireLogin();

            if($_SESSION["user"]["role"] !== "admin") {

                header("Location: /pocket_money/views/dashboard.php");
                exit();
            }
        }
        function requireUser() {

            requireLogin();

            if($_SESSION["user"]["role"] !== "user") {

                header("Location: /pocket_money/views/dashboard.php");
                exit();
            }
        }

?>