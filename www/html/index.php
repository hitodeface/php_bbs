<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>掲示板</title>
</head>

<body>
	<div class="header">
		<h2>掲示板</h2>
	</div>

	<div class="main">

		<?php

			// 最初に定義しておく変数
			$deleteNum = ''; // 削除する投稿番号
			$editNum = ''; // 編集する投稿番号
			$preName = ''; // 編集前の名前
			$preComment = ''; // 編集前のコメント
			$prePassword = ''; // 編集前のパスワード

			
			// DB接続設定
			$dsn = 'xxxxxxxx'; 
			$username = 'xxxxxxxx'; 
			$password = 'xxxxxxxx';

			try {
				
				$pdo = new PDO(
					$dsn,
					$username,
					$password,
					array(
						PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,  // 例外を投げる設定
						PDO::ATTR_EMULATE_PREPARES => false, // 静的プレースフォルダを使用する設定
					)
				);

				$sql = "CREATE TABLE IF NOT EXISTS postlist" // テーブルの作成
				// カラムの設定(投稿番号、名前、コメント、日付、パスワード)
				." ("       
				. "id INT AUTO_INCREMENT PRIMARY KEY,"
				. "name char(32),"
				. "comment TEXT,"
				. "datetime datetime,"
				. "password char(32)"
				.");";
				$stmt = $pdo->query($sql);
				
				// 投稿	
				if(!empty($_POST['name']) && !empty($_POST['comment'])){
					// editPostNumが空欄の場合は新規投稿として処理する
					if(empty($_POST['editPostNum'])){
					$sql = $pdo -> prepare("INSERT INTO postlist (name, comment, datetime, password) VALUES (:name, :comment, :datetime, :password)");
					$sql -> bindParam(':name', $name, PDO::PARAM_STR);
					$sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
					$sql -> bindParam(':datetime', $datetime, PDO::PARAM_STR);
					$sql -> bindParam(':password', $password, PDO::PARAM_STR);
					$name = $_POST['name'];
					$comment = $_POST['comment'];
					$datetime = date("Y/m/d H:i:s");
					$password = $_POST['pass'];
					$sql -> execute();
				
					// editPostNumに投稿番号が入力されている場合は編集として処理
					}elseif(!empty($_POST['editPostNum'])){
					$id = $_POST['editPostNum'];
					$name = $_POST['name'];
					$comment = $_POST['comment'];
					$datetime = date("Y/m/d H:i:s");
					$password = $_POST['pass'];
					$sql = 'UPDATE postlist SET name=:name,comment=:comment,datetime=:datetime,password=:password WHERE id=:id';
					$stmt = $pdo->prepare($sql);
					$stmt->bindParam(':name', $name, PDO::PARAM_STR);
					$stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
					$stmt->bindParam(':datetime', $datetime, PDO::PARAM_STR);
					$stmt->bindParam(':password', $password, PDO::PARAM_STR);
					$stmt->bindParam(':id', $id, PDO::PARAM_INT);
					$stmt->execute();
					}


				// 投稿削除
				}elseif(!empty($_POST['deleteNum'])){
					$id = $_POST['deleteNum'];
					$sql = 'SELECT * FROM postlist WHERE id=:id ';
					$stmt = $pdo->prepare($sql);                  
					$stmt->bindParam(':id', $id, PDO::PARAM_INT); 
					$stmt->execute();                             
					$results = $stmt->fetchAll(); 
						foreach ($results as $row){
							if($row['password'] == $_POST['deletePass']){
								$sql = 'delete from postlist where id=:id';
								$stmt = $pdo->prepare($sql);
								$stmt->bindParam(':id', $id, PDO::PARAM_INT);
								$stmt->execute();			
							}
						}

				// 投稿編集
				}elseif(!empty($_POST['editNum'])){
					$id = $_POST['editNum'];
					$sql = 'SELECT * FROM postlist WHERE id=:id ';
					$stmt = $pdo->prepare($sql);                  
					$stmt->bindParam(':id', $id, PDO::PARAM_INT); 
					$stmt->execute();                             
					$results = $stmt->fetchAll(); 
						foreach ($results as $row){
							if($row['password'] == $_POST['editPass']){
								$editNum = $_POST['editNum'];
								$preName = $row['name'];
								$preComment = $row['comment'];
								$prePassword = $row['password'];
			
							}
						}
				}

				// 投稿表示
				$sql = 'SELECT * FROM postlist';
				$stmt = $pdo->query($sql);
				$results = $stmt->fetchAll();
				foreach ($results as $row){
					//$rowの中にはテーブルのカラム名が入る
					echo $row['id'].'. '.$row['name'].' '.$row['datetime'].'<br>';
					echo $row['comment'].'<br>';

				echo "<hr>";
				}
			
			} catch (PDOException $e) {
			
				$error = $e->getMessage(); // 例外処理
			
			}

		?>

	</div>

	<div class="new_post">
		<p>投稿(パスワードは投稿を削除・編集するときに必要になります)</p>
		<form action="" method="post">
			<!-- 編集の時は$preName, $preComment, $editNum, $prePasswordが入力された状態 -->
			<input type="text" name="name" placeholder="名前" value="<?php echo $preName; ?>" required>
			<input type="password" name="pass" placeholder="パスワード" value="<?php echo $prePassword; ?>"　required> 
			<br>
			<input type="text" name="comment" placeholder="コメント" value="<?php echo $preComment; ?>" size="50" required>
			<input type="hidden" name="editPostNum" value="<?php echo $editNum; ?>">
			<input type="submit" name="submit">

		</form>
	</div>

	<div class="delete_post">
		<p>投稿削除(パスワード必須)</p>
		<form action="" method="post">
			<input type="number" name="deleteNum" placeholder="削除対象番号" required>
			<input type="password" name="deletePass" placeholder="パスワード" required>
			<input type="submit" name="submit2" value="削除">

		</form>
	</div>

	<div class="edit_post">
		<p>投稿編集(パスワード必須)</p>
		<form action="" method="post">
			<input type="number" name="editNum" placeholder="編集対象番号" required>
			<input type="password" name="editPass" placeholder="パスワード" required>
			<input type="submit" name="submit3" value="編集">
		</form>
	</div>

</body>
</html>