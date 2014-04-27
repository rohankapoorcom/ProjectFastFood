<?php
include_once 'db-connect.php';
 
function sec_session_start() {
    $session_name = 'fff_session_id';   // Set a custom session name
    $secure = SECURE;
    // This stops JavaScript being able to access the session id.
    $httponly = true;
    // Forces sessions to only use cookies.
    if (ini_set('session.use_only_cookies', 1) === FALSE) {
        header("Location: ../error.php?err=Could not initiate a safe session (ini_set)");
        exit();
    }
    // Gets current cookies params.
    $cookieParams = session_get_cookie_params();
    session_set_cookie_params($cookieParams["lifetime"],
        $cookieParams["path"], 
        $cookieParams["domain"], 
        $secure,
        $httponly);
    // Sets the session name to the one set above.
    session_name($session_name);
    session_start();            // Start the PHP session 
    session_regenerate_id();    // regenerated the session, delete the old one. 
}

function login($email, $password, $mysql_con) {
    $_SESSION['junk'] = "hello";
    // Using prepared statements means that SQL injection is not possible. 
    if ($stmt = $mysql_con->prepare("SELECT id, email, salt, password
        FROM users
       WHERE email = ?
        LIMIT 1")) {
        $stmt->bind_param('s', $email);  // Bind "$email" to parameter.
        $stmt->execute();    // Execute the prepared query.
        $stmt->store_result();
 
        // get variables from result.
        $stmt->bind_result($user_id, $username, $salt, $db_password);
        $stmt->fetch();

        // hash the password with the unique salt.
        $password = hash('sha512', $password . $salt);
        if ($stmt->num_rows == 1) {
            
            // Check if the password in the database matches
            // the password the user submitted.
            if ($db_password == $password) {
                // Password is correct!
                // Get the user-agent string of the user.
                $user_browser = $_SERVER['HTTP_USER_AGENT'];
                // XSS protection as we might print this value
                $user_id = preg_replace("/[^0-9]+/", "", $user_id);
                $_SESSION['user_id'] = $user_id;
                // XSS protection as we might print this value
                $username = preg_replace("/[^a-zA-Z0-9_\-]+/", 
                                                            "", 
                                                            $username);
                $_SESSION['username'] = $username;
                $_SESSION['login_string'] = hash('sha512', 
                          $password . $user_browser);
                // Login successful.
                return true;
            }

            else {
                // Password is not correct
                // We record this attempt in the database
                $_SESSION['junk'] = "hello";
                return false;
            }
        } 
        else {
            $_SESSION['junk'] = "hello";
            // No user exists.
            return false;
        }
    }
}

function login_check($mysqli) {
    // Check if all session variables are set 
    if (isset($_SESSION['user_id'], 
                        $_SESSION['username'], 
                        $_SESSION['login_string'])) {
 
        $user_id = $_SESSION['user_id'];
        $login_string = $_SESSION['login_string'];
        $username = $_SESSION['username'];
 
        // Get the user-agent string of the user.
        $user_browser = $_SERVER['HTTP_USER_AGENT'];
 
        if ($stmt = $mysqli->prepare("SELECT password 
                                      FROM users 
                                      WHERE id = ? LIMIT 1")) {
            // Bind "$user_id" to parameter. 
            $stmt->bind_param('i', $user_id);
            $stmt->execute();   // Execute the prepared query.
            $stmt->store_result();
 
            if ($stmt->num_rows == 1) {
                // If the user exists get variables from result.
                $stmt->bind_result($password);
                $stmt->fetch();
                $login_check = hash('sha512', $password . $user_browser);
 
                if ($login_check == $login_string) {
                    // Logged In!!!! 
                    return true;
                } else {
                    // Not logged in 
                    return false;
                }
            } else {
                // Not logged in 
                return false;
            }
        } else {
            // Not logged in 
            return false;
        }
    } else {
        // Not logged in 
        return false;
    }
}

function esc_url($url) {
 
    if ('' == $url) {
        return $url;
    }
 
    $url = preg_replace('|[^a-z0-9-~+_.?#=!&;,/:%@$\|*\'()\\x80-\\xff]|i', '', $url);
 
    $strip = array('%0d', '%0a', '%0D', '%0A');
    $url = (string) $url;
 
    $count = 1;
    while ($count) {
        $url = str_replace($strip, '', $url, $count);
    }
 
    $url = str_replace(';//', '://', $url);
 
    $url = htmlentities($url);
 
    $url = str_replace('&amp;', '&#038;', $url);
    $url = str_replace("'", '&#039;', $url);
 
    if ($url[0] !== '/') {
        // We're only interested in relative links from $_SERVER['PHP_SELF']
        return '';
    } else {
        return $url;
    }
}

function parse_grubhub($inputString) {
    $len = strlen($inputString);
    $valid = true;  # Start off optimistic
   
    $arrivePos = strpos($inputString, "arrive around", 0);
    $dashPos = 0;
    $periodPos = 0;
    $contactPos = 0;
    $brkPos = 0;
    $spacePos = 0;
    $dollarPos = 0;
    $totalPos = 0;
    $deliverPos = 0;
    $commaPos = 0;
    $arriveLow = "";
    $arriveHigh = "";
    $restaurant = "";
    $total = "";
    $addressRaw = "";
    $addressStreet = "";
    $zip = "";
   
    $valid = ($arrivePos !== false && $len > $arrivePos+36);
    if($valid) {
            $dashPos = strpos($inputString, '-', $arrivePos+14);
            $valid = ($arrivePos !== false);
    }
    if($valid) {
            $arriveLow = substr($inputString, $arrivePos+14, $dashPos-$arrivePos-15);
            $periodPos = strpos($inputString, '.', $dashPos);
            $valid = ($periodPos !== false);
    }
    if($valid) {
            $arriveHigh = substr($inputString, $dashPos+2, $periodPos-$dashPos-2);
           
            //print("ArriveLow = \"".$arriveLow."\"<br />");
            //print("ArriveHigh = \"".$arriveHigh."\"<br />");
           
            $contactPos = strpos($inputString, "contact you.", $periodPos);
            $valid = ($contactPos !== false);
    }
    if($valid) {
            $brkPos = strpos($inputString, "Order", $contactPos+16);
            $valid = ($brkPos !== false);
    }
    if($valid) {
            $restaurant = trim(substr($inputString, $contactPos+16, $brkPos-$contactPos-18));
           
            //print("Restaurant = \"".$restaurant."\"<br />");
            
            $totalPos = strPos($inputString, "TOTAL", $brkPos);
            $valid = ($totalPos !== false);
    }
    if($valid) {
        $totalPos = strPos($inputString, "$", $totalPos);
        $valid = ($totalPos !== false);
    }
    if($valid) {
            $brkPos = strpos($inputString, ".", $totalPos+2);
            $valid = ($brkPos !== false);
    }
    if($valid) {

            $brkPos += 3;
            $total = trim(substr($inputString, $totalPos+2, $brkPos-$totalPos-2));
           
            //print("Total = \"".$total."\"<br /><br />");
           
            $deliverPos = strpos($inputString, "Deliver to", $brkPos);
            $valid = ($deliverPos !== false);
    }
    if($valid) {
            $brkPos = strpos($inputString, "\n", $deliverPos+12);
            $commaPos = strpos($inputString, ", ", $deliverPos);
            $valid = ($commaPos !== false && $brkPos !== false);
    }
    if($valid) {
            $deliverPos = $brkPos+1;
            $addressRaw = trim(substr($inputString, $deliverPos, $commaPos+4-$deliverPos));
           
            $urbana = strpos($addressRaw, "Urbana, IL");
            $champaign = strpos($addressRaw, "Champaign, IL");
            if($urbana !== false) {
                    $zip = "61801";
                    $addressStreet = trim(str_replace("\n", " ",substr($addressRaw, 0, $urbana)));
            } else if($champaign !== false) {
                    $zip = "61820";
                    $addressStreet = trim(str_replace("\n", " ",substr($addressRaw, 0, $champaign)));
            } else {
                    $spacePos = strrpos($addressRaw, "\n", ($commaPos-$deliverPos)-strlen($addressRaw));
                    if($spacePos !== false) {
                            $addressStreet = trim(str_replace("\n", " ", substr($addressRaw, 0, $spacePos)));
                    }
            }
           
            //print("Address = \"".$addressStreet."\"<br />");
            //print("Zip = \"".$zip."\"<br /><br />");
    }
    if($valid) {
            //print("Parse Complete!");
            $obj = (object) array("low" => $arriveLow, "high" => $arriveHigh, "restaurant" => $restaurant, "price" => $total, "address" => $addressStreet, "zip" => $zip);
            return $obj;
    } else {
            //print("Parse Fail!");
            return false;
    }
}