<?php 
/**
 *
 * spAutoGenerator 
 *
 * LICENSE
 *
 * This source file is subject to the new GPL license that is bundled
 * with this package in the file LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to trongduc266@congdongcviet.com or trongduc266@gmail.com so we can send you a copy immediately.
 *
 *
 * @name index.php
 * @author zkday
 * @copyright: trongduc266@congdongcviet.com | trongduc266@gmail.com
 * @license: GPL.
 * @version 0.1.1
 * @date 24/11/2009
 * @access public
 *
 */

if(isset($_REQUEST['btnSubmit'])){
	$servername = "localhost";
	$username = "root";
	$pass = "";
	$path = "C:\\wamp\\www";
	$database = 'supermarketdb';
	

	if(isset($_REQUEST['servername'])){
		$servername= $_REQUEST['servername'];
	}
	if(isset($_REQUEST['username'])){
		$username = $_REQUEST['username'];
	}
	if(isset($_REQUEST['pass'])){
		$pass = $_REQUEST['pass'];
	}
	if(isset($_REQUEST['path'])){
		$path = $_REQUEST['path'];
	}
	if(isset($_REQUEST['database'])){
		$database = $_REQUEST['database'];
	}

	require('spAutoGenerator.php');
	//supermarketdb
	$generateSP = new spAutoGenerator($servername, $username, $pass, $database, $path);
	if ($generateSP->__generateSP()) {
		print 'All stored procedures has been created succesfully.';
	} else {
		print '<br />Operation Not Completed.';
	}
	unset($generateSP);
}
?>

<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title>Store procedures Auto Generator</title>
</head>

<body>
<h2>Chương trình phát sinh code store proceduce tự động với mysql</h2>
<table>
	<form method="post">
		<tr>
			<td>Tên Server: </td>
			<td>
				<input type="text" name="servername">
			</td>
		</tr>
		<tr>
			<td>tên database: </td>
			<td>
				<input type="text" name="database">
			</td>
		</tr>
		
		<tr>
			<td>UserName đăng nhập DB: </td>
			<td>
				<input type="text" name="username">
			</td>
		</tr>
		<tr>
			<td>Pass Đăng nhập DB: </td>
			<td>
				<input type="password" name='pass'>
			</td>
		</tr>
		<tr>
			<td>Đường dẫn lưu file: </td>
			<td><input type="text" name='path'></input></td>
		</tr>		
		<tr>
			<td colspan="2">
				<input type="submit" name='btnSubmit' value="Tạo tự động">
			</td>
		</tr>
	</form>
</table>
</body>
</html>