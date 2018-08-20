<?php
/**
 * Created by PhpStorm.
 * User: Hollyphat
 * Date: 6/20/2018
 * Time: 8:08 AM
 */

header("Access-Control-Allow-Origin: *");
//define('ENV','local');

require_once "admin/core/db.php";

/*function user_details($id,$v){
    global $db;
    $sql = $db->prepare("SELECT * FROM users WHERE id = :id");
    $sql->execute(array(
        'id' => $id
    ));

    $rs = $sql->fetch(PDO::FETCH_ASSOC);

    $sql->closeCursor();

    return $rs[$v];
}
*/
if(isset($_POST['reg-ok'])){    
    $names = $_POST['name'];
    $password = sha1($_POST['password']);
    $gender = $_POST['gender'];
    $email = $_POST['email'];
    
    $phone = $_POST['phone'];    
    $error = array();

    
    
    $sql_check = $db->prepare("SELECT NULL FROM users WHERE email = :email");
    $sql_check->execute(array('email' => $email));
    $counts = $sql_check->rowCount();
    $sql_check->closeCursor();

    if($counts > 0) {
        $email_err = "<span style='color: #f00;'>Email address already exist!</span>";
        $error[] = $email_err;
    }

    $sql_check = $db->prepare("SELECT NULL FROM users WHERE phone = :phone");
    $sql_check->execute(array('phone' => $phone));
    $counts = $sql_check->rowCount();
    $sql_check->closeCursor();

    if($counts > 0) {
        $phone_err = "<span style='color: #f00;'>Phone number already exist!</span>";
        $error[] = $phone_err;

        //$out = array('ok' => 0, 'msg' => $phone_err);
        //echo json_encode($out);
    }

    if(strlen($_POST['password']) < 7){
        $pass_err = "<span style='color: #f00;'>Password is invalid, it must be at least 7 characters!</span>";
        $error[] = $pass_err;

        //$out = array('ok' => 0, 'msg' => $pass_err);
        //echo json_encode($out);
    }

    $err_count = count($error);

    if($err_count == 0){
        //save
        $save = $db->query("INSERT INTO users(names,email,password,phone,gender) 
            VALUES('$names','$email','$password','$phone','$gender')");
        $save->closeCursor();

        $out = array('ok' => 1, 'msg' => "Your account was created successfully, you can now login");
        echo json_encode($out);
    }
    else{
            $m = "<p>Some error occured</p>";

            foreach ($error as $err){
                $m .= $err."<br>";
            }
            $out = array('ok' => 0, 'msg' => $m);

            echo json_encode($out);
            exit();

    }
    exit();
}

if(isset($_POST['pass-ok'])){

    $sql = $db->prepare("SELECT names,email FROM users WHERE phone = :u AND email = :e");
    $sql->execute(array(
        'u' => $_POST['phone'],
        'e' => $_POST['email']
    ));

    if($sql->rowCount() == 0){
        $out = array('ok' => 0, 'msg' => "Invalid details");
    }else{
        //create new pass
        $new_pass = rand(0,9)."".rand(0,9)."".rand(0,9)."".rand(0,9)."".rand(0,9)."".rand(0,9);
        $n = $db->prepare("UPDATE users SET password = :p WHERE email = :u");
        $n->execute(array(
            'p' => sha1($new_pass),
            'u' => $_POST['email']
        ));
        $n->closeCursor();
        $r = $sql->fetch(PDO::FETCH_ASSOC);
        $names = $r['names'];
        $email = $r['email'];
        $subject = "Password reset";
        $message = "Hi $names, you recently requests to change your Mobile Ontology Account Password";
        $message .= "\Your new password is \"$new_pass\" without quote!\n";
        $message .= "Login with your email and password to change the new password!.";

        @mail($email, $subject, $message);
        $out = array('ok' => 1, 'msg' => "New password has been sent to your email address!");
    }

    echo json_encode($out);
    exit();
}

if(isset($_POST['login-ok'])){
    $email = addslashes($_POST['email']);
    $password = sha1($_POST['password']);

    $sql = $db->query("SELECT id,names,email,phone,gender FROM users WHERE email = '$email' AND password = '$password'");
    $rows = $sql->rowCount();

    if($rows == 0){
        $msg = array('ok' => 0, 'msg' => "Invalid login details!");
    }else{
        $rs = $sql->fetch(PDO::FETCH_ASSOC);
        
        $me = $rs['id'];
        //herb lists
        $herbs = $db->query("SELECT id,title FROM herbs");
        $herbs_list = array();
        while($herbs_rs = $herbs->fetch(PDO::FETCH_ASSOC)){
            $herbs_list[] = $herbs_rs;
        }
        
        //saved herbs
        
        $saved_herbs = $db->query("SELECT saved_herbs.herb_id, herbs.id, herbs.title FROM saved_herbs INNER JOIN herbs ON saved_herbs.herb_id = herbs.id WHERE saved_herbs.user_id = '$me'");
        $saved_herbs_lists = array();

        while($saved_herbs_rs = $saved_herbs->fetch(PDO::FETCH_ASSOC)){            
            $saved_herbs_lists[] = $saved_herbs_rs;
        }
        $msg = array('ok' => 1, 'datas' => $rs, 'herbs' => $herbs_list, 'saved_herbs' => $saved_herbs_lists);
    }
    $sql->closeCursor();

    echo json_encode($msg);
    exit();
}



if(isset($_POST['save'])){
    $user = $me = $_POST['user_id'];
    $q = $_POST['herb_id'];

    //GET USER FRIEND LISTS
    $friends = $db->query("SELECT NULL FROM saved_herbs WHERE user_id = '$user' AND herb_id = '$q'");

    if($friends->rowCount() == 0){
        $total = 1;
        $in = $db->query("INSERT INTO saved_herbs(user_id,herb_id) VALUES('$user', '$q')");
        $in->closeCursor();
    }else{
        $total = 0;
    }

    $out = array();
    $out['total'] = $total;

    $saved_herbs = $db->query("SELECT saved_herbs.herb_id, herbs.id, herbs.title FROM saved_herbs INNER JOIN herbs ON saved_herbs.herb_id = herbs.id WHERE saved_herbs.user_id = '$me'");
    $saved_herbs_lists = array();

    while($saved_herbs_rs = $saved_herbs->fetch(PDO::FETCH_ASSOC)){            
        $saved_herbs_lists[] = $saved_herbs_rs;
    }

    $out['saved_herbs'] = $saved_herbs_lists;
    echo json_encode($out);
    exit();
    //my_freinds
}

if(isset($_POST['removed'])){
    $user = $me = $_POST['user_id'];
    $q = $_POST['herb_id'];

    //GET USER FRIEND LISTS
    $friends = $db->query("SELECT NULL FROM saved_herbs WHERE user_id = '$user' AND herb_id = '$q'");

    if($friends->rowCount() == 1){
        $total = 1;
        $in = $db->query("DELETE FROM saved_herbs WHERE user_id = '$user' AND herb_id = '$q'");
        $in->closeCursor();
    }else{
        $total = 0;
    }


    $out = array();
    $out['total'] = $total;

    $saved_herbs = $db->query("SELECT saved_herbs.herb_id, herbs.id, herbs.title FROM saved_herbs INNER JOIN herbs ON saved_herbs.herb_id = herbs.id WHERE saved_herbs.user_id = '$me'");
    $saved_herbs_lists = array();

    while($saved_herbs_rs = $saved_herbs->fetch(PDO::FETCH_ASSOC)){            
        $saved_herbs_lists[] = $saved_herbs_rs;
    }

    $out['saved_herbs'] = $saved_herbs_lists;
    
    echo json_encode($out);
    exit();
    //my_freinds
}




if(isset($_POST['update-profile'])){
    $user_id = $_POST['user_id'];
    $arr = array();
    $arr['names'] = $_POST['names'];
    $arr['gender'] = $_POST['gender'];
    $arr['phone'] = $_POST['phone'];
    $arr['id'] = $_POST['user_id'];

    $up = $db->prepare("UPDATE USERS SET names = :names, gender = :gender, phone = :phone WHERE id = :id");
    $up->execute($arr);
    $up->closeCursor();

    if(isset($_POST['password']) && ($_POST['password'] != "")){
        $up2 = $db->prepare("UPDATE users SET password = :p WHERE id = :id");
        $up2->execute(array(
            'p' => sha1($_POST['password']),
            'id' => $_POST['user_id']
        ));
        $up->closeCursor();
    }

    echo "ok";
}


if(isset($_GET['load_herb'])){
    $id = $_GET['id'];
    $user_id = $_GET['user_id'];
    $sql = $db->query("SELECT * FROM herbs WHERE id = '$id'");
    $rs = $sql->fetch(PDO::FETCH_ASSOC);
    $out = array();

    $saved = $db->query("SELECT NULL FROM saved_herbs WHERE herb_id = '$id' AND user_id ='$user_id'");
    $rows = $saved->rowCount();

    $out['herbs'] = $rs;
    $out['t'] = $rows;
    echo json_encode($out);
    //echo json_encode($rs);
    exit();
}


if(isset($_GET['load_search'])){
    $q = addslashes($_GET['q']);

    $qq = addslashes($_GET['q']);
    $qq2 = explode("+", $qq);
    $k = "";
    foreach ($qq2 as $kk) {
        # code...
        $k .="|".$kk;
    }
    $k = substr($k, 1);

    //var_dump($k);
    //exit;

    //$sql = "SELECT * FROM ads WHERE (title REGEXP('$k') or ads_desc REGEXP('$k')) AND status = '1'";


    $sql = "SELECT id,title FROM herbs WHERE title REGEXP('$k') OR info REGEXP('$k')";
        
    $loads = $db->query($sql);

    $out = array();    
    $out['total'] = $loads->rowCount();
    //if()
    $herbs = array();
    while($loads_rs = $loads->fetch(PDO::FETCH_ASSOC)) {
        $herbs[] = $loads_rs;
    }
    $loads->closeCursor();

    $out['herbs'] = $herbs;

    echo json_encode($out);
    exit();
}