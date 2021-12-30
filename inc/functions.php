<?php

   function openDb(): object {
        $ini = parse_ini_file("config.ini", true);
    
        $host = $ini['host'];
        $database = $ini['database'];
        $user = $ini['user'];
        $password = $ini['password'];
        $db = new PDO("mysql:host=$host;port=3307;dbname=$database;charset=utf8",$user, $password);
        $db ->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        return $db;
    }

    function returnError(PDOException $pdoex) {
        header('HTTP/1.1 500 Internal Server Error');
        $error = array('error' => $pdoex -> getmessage());
        print json_encode($error);
        exit;
       }

    function checkUser(PDO $dbcon, $username, $passwd){

        $username = filter_var($username, FILTER_SANITIZE_STRING);
        $passwd = filter_var($passwd, FILTER_SANITIZE_STRING);

        try{
            $sql = "SELECT password FROM user WHERE username=?";
            $prepare = $dbcon->prepare($sql);
            $prepare->execute(array($username));

            $rows = $prepare->fetchAll();

            foreach($rows as $row){
                $pw = $row["password"];
                if( password_verify($passwd, $pw)) { 
                    return true;
                }
            }

            return false;

        }catch(PDOException $e) {
            echo '<br>'.$e->getMessage();
        }
    }

    function createUser(PDO $dbcon, $fname, $lname, $username, $passwd) {

        $fname = filter_var($fname, FILTER_SANITIZE_STRING);
        $lname = filter_var($lname, FILTER_SANITIZE_STRING);
        $username = filter_var($username, FILTER_SANITIZE_STRING);
        $passwd = filter_var($passwd, FILTER_SANITIZE_STRING);

        try {
            $hash_pw = password_hash($passwd, PASSWORD_DEFAULT);
            $sql = "INSERT IGNORE INTO user VALUES(?,?,?,?)";
            $prepare = $dbcon->prepare($sql);
            $prepare->execute(array($fname, $lname, $username, $hash_pw));
        } catch(PDOException $e) {
            echo '<br>'.$e->getMessage();
        }
    }

    function createUserInfo(PDO $dbcon, $username, $email, $phone, $address, $zipcode, $city) {

        $username = filter_var($username,FILTER_SANITIZE_STRING);
        $email = filter_var($email,FILTER_SANITIZE_STRING);
        $phone = filter_var($phone,FILTER_SANITIZE_STRING);
        $address = filter_var($address,FILTER_SANITIZE_STRING);
        $zipcode = filter_var($zipcode,FILTER_SANITIZE_STRING);
        $city = filter_var($city,FILTER_SANITIZE_STRING);

            try {
                $sql = "INSERT IGNORE INTO user_info VALUES(?,?,?,?,?,?)";
                $prepare = $dbcon->prepare($sql);
                $prepare->execute(array($username, $email, $phone, $address, $zipcode, $city));
            } catch(PDOException $e) {
                echo '<br>'.$e->getMessage();
            }

    }

    function createDbConnection() {

        try {
            $dbcon = new PDO('mysql:host=localhost;dbname=c0auma00', 'root', '');
            $dbcon->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo '<br>'.$e->getMessage();
        }
        
        return $dbcon;
    }

    function createTable(PDO $con){
        $sql = "CREATE TABLE IF NOT EXISTS user(
            first_name varchar(50) NOT NULL,
            last_name varchar(50) NOT NULL,
            username varchar(50) NOT NULL,
            password varchar(150) NOT NULL,
            PRIMARY KEY (username)
        );";

        /* $sql = "CREATE TABLE IF NOT EXISTS user_info(
            username varchar(50) NOT NULL,
            email varchar(50) PRIMARY KEY,
            phone int NOT NULL,
            address varchar (50) NOT NULL,
            zipcode varchar (5) NOT NULL,
            city varchar(20) NOT NULL,
            FOREIGN KEY (username) 
            REFERENCES user(username)
        );"; */

        try {
            $con->exec($sql);
        } catch (PDOException $e) {
            echo '<br>'.$e->getMessage();
        }

        // testi, että hash toimii
        //createUser($con,'Kalle','Laitela', 'laitelak', 'miia');
        //createUser($con, "Lasse", "Sievinen", "lasu", "hirvipaisti");

        createUserInfo($con, 'lasu', 'lasse@sievinen.com', '040404040', 'Pihlajakatu 2', '00100', 'Helsinki' );

    } 


?>