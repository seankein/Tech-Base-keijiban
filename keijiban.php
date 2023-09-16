<!DOCTYPE html>

<html lang="ja">

<head>

    <meta charset="UTF-8">
    <title>mission_5-1</title>
    <link rel=“stylesheet” type=“text/css” href=“http://mplus-fonts.sourceforge.jp/webfonts/general-j/mplus_webfonts.css”>    
    <style>
        .box2{
            padding: 0.5em 1em;
            margin: 2em 0;
            color: #474747;
            background: whitesmoke;/*背景色*/
            border-left: double 7px #4ec4d3;/*左線*/
            border-right: double 7px #4ec4d3;/*右線*/
        }
        .box2 p {
            margin: 0; 
            padding: 0;
        }
        .box1{
            padding: 0.2em 0.5em;
            margin: 2em 0;
            background: #d6ebff;
            box-shadow: 0px 0px 0px 10px #d6ebff;
            border: dashed 2px white;
        }
        .box1 p {
            margin: 0; 
            padding: 0;
        }
        .box3 {
            padding: 0.2em 0.5em;
            margin: 2em 0;
            color: #565656;
            background: #ffeaea;
            box-shadow: 0px 0px 0px 10px #ffeaea;
            border: dashed 2px #ffc3c3;
            border-radius: 8px;
        }
        .box3 p {
            margin: 0; 
            padding: 0;
        }
        P { text-align: center }
    </style>
</head>

<body>
    <div class="box1">
    <p>ケイジバンへようこそ！</p>
    </div>
    <?php
        $syslog = "ようこそ";
        $dsn = 'データベース名';
        $user = 'ユーザー名';
        $password = 'パスワード';
        $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
    //投稿だった場合
        if(isset($_POST["submit"])){
            $date = date("Y/m/d H:i:s");
    //テキストはいっていますか？
            if( isset($_POST["comment"]) && isset($_POST["name"]) && isset($_POST["password"])){
            $comment = $_POST["comment"];
            $name = $_POST["name"];
            $password = $_POST["password"];
            }
    //データベースは存在しますか？
            $sql = "SHOW TABLES LIKE 'keiji'";
            if(empty($pdo->query($sql)->fetchAll())){
                $sql = "CREATE TABLE IF NOT EXISTS keiji"
                ." ("
                . "id INT AUTO_INCREMENT PRIMARY KEY,"
                . "name char(32),"
                . "comment TEXT,"
                . "date TEXT,"
                . "password TEXT"
                .");";
                $stmt = $pdo->query($sql);
            }
    //テキストが空じゃない場合書き込みますよ？
            if(!empty($comment) || !empty($name)){
                $sql = "INSERT INTO keiji (name, comment, date, password) VALUES (:name, :comment, :date, :password)";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
                $stmt->bindParam(':date', $date, PDO::PARAM_STR);
                $stmt->bindParam(':password', $password, PDO::PARAM_STR);
                $stmt->execute();
                $syslog = "投稿完了";
            }
    //削除だった場合
        }elseif(isset($_POST["delete"])){
    //入力がある場合
            if(isset($_POST["erasenum"])){
                $erasenum = $_POST["erasenum"];
                $inputpassword = $_POST["inputpassword"];
    //パスワードがあっているか確認
                $sql = "SELECT * FROM keiji WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id', $erasenum, PDO::PARAM_INT);
                $stmt->execute();
                $fetch = $stmt->fetch(PDO::FETCH_ASSOC);
                if($fetch['password'] != $inputpassword){
                    $syslog = "wrong password";
                    $erasenum = NULL;
                }
    //パスワードがあっていたら削除
                if(isset($erasenum)){
                    $sql = "DELETE FROM keiji WHERE id = :id";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':id', $erasenum, PDO::PARAM_INT);
                    $stmt->execute();
                    $syslog = "削除完了";
                }
            }
    //編集だった場合
        }elseif(isset($_POST["edit"])){
    //入力がある場合
            if(isset($_POST["editnum"])){
                $editnum = $_POST["editnum"];
                $inputpassword = $_POST["inputpassword"];
    //パスワードがあっているか確認
                $sql = "SELECT * FROM keiji WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id', $editnum, PDO::PARAM_INT);
                $stmt->execute();
                $fetch = $stmt->fetch(PDO::FETCH_ASSOC);
                $editname = $fetch['name'];
                $editcomment = $fetch['comment'];
                if($fetch['password'] != $inputpassword){
                    $editname = NULL;
                    $editcomment = NULL;
                    $syslog = "wrong password";
                }
            }
    //編集完了した場合
        }elseif(isset($_POST["editmode"])){
            $editnum = $_POST["editnum"];
            $editname = $_POST["editname"];
            $editcomment = $_POST["editcomment"];
            $inputpassword = $_POST["inputpassword"];
            $editdate = date("Y/m/d H:i:s");
            $sql = "UPDATE keiji SET name = :name, comment = :comment, date = :date, password = :password WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':name', $editname, PDO::PARAM_STR);
            $stmt->bindParam(':comment', $editcomment, PDO::PARAM_STR);
            $stmt->bindParam(':date', $editdate, PDO::PARAM_STR);
            $stmt->bindParam(':password', $inputpassword, PDO::PARAM_STR);
            $stmt->bindParam(':id', $editnum, PDO::PARAM_INT);
            $stmt->execute();
            $syslog = "編集完了";
            $editname = NULL;
            $editcomment = NULL;
        }
    ?>
    <div class="box2">
    <?php
    //モード切替
    if(isset($editname) && isset($editcomment)){
        $syslog = "編集モード";
        echo "<form action='' method='post'>";
        echo "<input type='text' name='editname' value='".$editname."'> ";
        echo "<input type='text' name='editcomment' value='".$editcomment."'> ";
        echo "<input type='hidden' name='editnum' value='".$editnum."'> ";
        echo "<input type='text' name='inputpassword' value='password'> ";
        echo "<input type='submit' name='editmode' value='編集完了'>";
        echo "</form>";
    }else{
        echo "<form action='' method='post'>";
        echo "<input type='text' name='name' value='名前'> ";
        echo "<input type='text' name='comment' value='コメント'> ";
        echo "<input type='text' name='password' value='password'> ";
        echo "<input type='submit' name='submit' value='投稿'> ";
        echo "</form>";
    }
    ?>
    <form action="" method="post">
        <input type="text" name="erasenum" value="投稿番号" >
        <input type='text' name='inputpassword' value='password'>
        <input type="submit" name="delete" value="削除">
    </form>
    <form action="" method="post">
        <input type="text" name="editnum" value="投稿番号" >
        <input type='text' name='inputpassword' value='password'>
        <input type="submit" name="edit" value="編集">
    </form>
    </div>
    <div class="box3">
    <?php
        //システムログ表示
        echo "システムログ:$syslog<br>";
        // ヘッダー行を表示
        echo "+---------+------------+----------------------+----------------------+<br>";
        echo "|  Post   |    Name    |       Comment        |        Date          |<br>";
        echo "+---------+------------+----------------------+----------------------+<br>";
        $sql = 'SELECT * FROM keiji';
        $stmt = $pdo->query($sql);
        $results = $stmt->fetchAll();
        foreach ($results as $row){
            //$rowの中にはテーブルのカラム名が入る
            printf("| %-7s | %-10s | %-20s | %s |<br>", $row['id'], $row['name'], $row['comment'], $row['date']);
        echo "<hr>";
        }
        // フッター行を表示
        echo "+---------+------------+----------------------+----------------------+<br>";
    
    ?>
    </div>
</body>
</html>