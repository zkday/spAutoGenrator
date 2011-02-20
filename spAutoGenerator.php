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
 * @name spAutoGenerator 
 * @author zkday
 * @copyright: trongduc266@congdongcviet.com | trongduc266@gmail.com
 * @license: GPL.
 * @version 0.1.1
 * @date 24/11/2009
 * @access public
 *
 * L?p mô sinh ra store proceduce c? b?n v?i database là mysql
 * ?ng v?i m?i table nó s? sinh ra 5 store t??ng ?ng
 *  
 * - Get All Fields and Records From Table.
 * - Get All Fields From Table By Primary Key.
 * - Insert Into Table.
 * - Update Table By Primary Key.
 * - Delete from Table By Primary Key.
 * 
 */
class spAutoGenerator {
	/**
	 * Database Host or IP
	 *
	 * @var string
	 * @access private
	 */
	private $db_host 		= null;
	/**
	 * Database Username
	 *
	 * @var string
	 * @access private
	 */
	private $db_username 	= null;
	/**
	 * Database Password
	 *
	 * @var string
	 * @access private
	 */
	private $db_password 	= null;
	/**
	 * Database Name
	 *
	 * @var string 
	 * @access private
	 */
	private $db_name 		= null;
	/**
	 * Hold the DB Link
	 *
	 * @var string
	 * @access private
	 */
	private $db_link		= null;
	/**
	 * Enter description here...
	 *
	 * @var string
	 * @access private
	 */
	private $path	 		= null;
	/**
	 * Initialize needed variables
	 *
	 * @param string $host
	 * @param string $username
	 * @param string $password
	 * @param string $db
	 * @return  boolean
	 * @access public
	 */
	public function __construct($db_host, $db_username, $db_password, $db_name, $path){
		$this->db_host 		= $db_host;
		$this->db_username 	= $db_username;
		$this->db_password 	= $db_password;
		$this->db_name 		= $db_name;
		$this->path 		= $path;

		//check if the variables are not empty
		if (is_null($this->db_host)) {
			exit('Please provide a DB Host.');
		}

		if (is_null($this->db_username)) {
			exit('Please provide a DB Username.');
		}

		if (is_null($this->db_password)) {
			exit('Please provide a DB Password.');
		}

		if (is_null($this->db_name)) {
			exit('Please provide a DB Name.');
		}

		if (is_null($this->path)) {
			exit('Please provide a Physical Path.');
		}

		//check if path is a valid directory
		if (!is_dir($this->path)) {
			exit('Please provide a valid path for an existing and writable directory.');
		}

		//check if directory is a writable
		if (!is_writable($this->path)) {
			exit('Please provide a valid path for a writable directory.');
		}

		//connet to the DB
		if (($this->db_link = mysql_connect($db_host, $db_username, $db_password)) === false) {
			exit('Please provide a valid DB-Connection.');
		}

		//select the DB
		if (($dbSelected = mysql_select_db($db_name)) === false) {
			exit('Please provide a valid DB-Name.');
		}

		return true;
}	
	/**
	 * Enter description here...
	 *
	 * @return unknown
	 */
	public function __generateSP() {
	$storedProcedures = '';

	///echo $this->_getMaxLengthOfCharacter('ten','category_cha'); exit();
	
		//get DB tables
		$query = mysql_query("SHOW TABLES ", $this->db_link);
		
		// get all table name in database
		while ($row = mysql_fetch_array($query)) {
			$dbTables[] = $row[0];			
		}		
//inialize counter to 0
		$j = 0;		// bien dung danh so thu tu cua table
		foreach ($dbTables as $dbtable) {		
			/// lay tat ca cac thong tin can thiet
			$strSelectInfoTable= "SELECT information_schema.COLUMNS.COLUMN_NAME, information_schema.COLUMNS.DATA_TYPE,
										information_schema.COLUMNS.CHARACTER_MAXIMUM_LENGTH, information_schema.COLUMNS.COLUMN_TYPE,
										information_schema.COLUMNS.COLUMN_KEY
								FROM information_schema.COLUMNS
								WHERE table_schema='$this->db_name' and table_name='$dbtable'";
			
			
			$result = mysql_query($strSelectInfoTable);			
			//$infoTable = mysql_fetch_row($result);			
			if(count($result)<=0){ // neu khong co dong nao --- table khong co cot nao --> nghi choi
				continue;
			}	
			$i = 0;		// bi?n dùng ?? ?ánh s? th? t? c?a columns
			while ( $row = mysql_fetch_array($result) ) {
// bang thu may? dongthumay? chucnang?
			
			    $dbFields[$j][$i]['tblname'] 			= 	$dbtable;
				$dbFields[$j][$i]['name'] 			= 	$row['COLUMN_NAME'];
				$dbFields[$j][$i]['type'] 			= 	$row['DATA_TYPE'];
				$dbFields[$j][$i]['maxlength'] 		= 	strcasecmp($row['CHARACTER_MAXIMUM_LENGTH'],"")==0?'':
														'('. $row['CHARACTER_MAXIMUM_LENGTH'] .')';
				$dbFields[$j][$i]['columns_type']	=	$row['COLUMN_TYPE'];
				$dbFields[$j][$i]['key']	=	(strcasecmp($row['COLUMN_KEY'],"PRI")==0? 1 : 0); // neu la pri thi la khoa chinh
				//echo $dbFields[$j][$i]['tblname']."--->" .$dbFields[$j][$i]['name']."--->".$dbFields[$j][$i]['type']."--->".$dbFields[$j][$i]['maxlength']."<br />";
				$i++;
			}
			$j++;
		}
		mysql_free_result($result);
		mysql_close();

		$countTables = count($dbFields);
		
		for ($i = 0; $i < $countTables; $i++){
			//stored procedures body text
			$getTables = '';
			$getTableDetails = '';
			$insertTable = '';
			$updateTable = '';
			$deleteTable = '';

			//stored procedures parameters
			$updateTableParam 	= '';
			$insertTableParam 	= '';
			$whereCondParam 	= '';
			$whereCondition 	= '';

			//stored procedures parameters list inside the sp
			$updateTableList 	= '';
			$insertTableList 	= '';
			$getTableDetailsList= '';
			$getTableList 		= '';
			$insertValuesList 	= '';

			/**
			 * 
			 *can thong tin:
			 *	1 - ten colum --- 1
			 *	2 - co la khoa chinh hay khong? --- 8
			 *	3 - kieu du lieu
			 *	4 - kich thuoc cua kieu du lieu neu la text
			*/ 
			$countFields = count($dbFields[$i]);			
			$tableName 			= $dbTables[$i];
			
			for ($l=0; $l<$countFields; $l++){
				if ($dbFields[$i][$l]['key']){ // ki?m tra columns th? $l c?a b?ng th? $i có ph?i là khóa chính ko?

					$tablePrimaryKey 	= $dbFields[$i][$l]['name'];						   
					
					$whereCondParam 	.= 'IN p' . ucfirst($tablePrimaryKey) . ' ' . $dbFields[$i][$l]['type'] . ' ' . $dbFields[$i][$l]['maxlength'] . ', '; // them dau , cho truong hop co nhieu id
										
					$whereCondition 	.= '`'.$tablePrimaryKey . '` = ' . 'p' . ucfirst($tablePrimaryKey). ' and '; // them vao toa tu and cho truong hop nhieu dieu kien
					$getTableDetailsList.= '`'.$tablePrimaryKey . '`, ';

					//parameters
					$updateTableParam 	.= " IN p" . ucfirst($tablePrimaryKey). ' '.$dbFields[$i][$l]['type'] . $dbFields[$i][$l]['maxlength'] . ',' ;
				} 
				
				else {
						//parameters
						$updateTableParam 	.= " IN p" . ucfirst($dbFields[$i][$l]['name']) . ' ' . $dbFields[$i][$l]['type'] . ' ' . $dbFields[$i][$l]['maxlength'] . ',';
						$insertTableParam 	.= " IN p" . ucfirst($dbFields[$i][$l]['name']) . ' ' . $dbFields[$i][$l]['type'] . ' ' . $dbFields[$i][$l]['maxlength'] . ',';
					
					//echo $whereCondParam;exit();
					//fields list in the query
					$updateTableList 	.= '`'.$dbFields[$i][$l]['name'] . '` = p' . ucfirst($dbFields[$i][$l]['name']) . ', ';
					$insertTableList 	.= '`'.$dbFields[$i][$l]['name'] . '`, ';
					$getTableDetailsList.= '`'.$dbFields[$i][$l]['name'] . '`, ';
					$getTableList 		.= '`'.$dbFields[$i][$l]['name'] . '`, ';
					$insertValuesList 	.= 'p' . ucfirst($dbFields[$i][$l]['name']) . ', ';
				}
			}			
			$whereCondition 	= substr($whereCondition,0,-5); // xoa ky tu toan tu and thua cuoi chuoi di.
			$whereCondParam		= substr($whereCondParam, 0, -2); // xoa ky tu dau , cuoi di
							
			$updateTableParam 	= substr($updateTableParam, 0, -1);
			$insertTableParam 	= substr($insertTableParam, 0, -1);
			$updateTableList 	= substr($updateTableList, 0, -2);
			$insertTableList 	= substr($insertTableList, 0, -2);
			$getTableDetailsList= substr($getTableDetailsList, 0, -2);
			$getTableList 		= substr($getTableList, 0, -2);
			$insertValuesList	= substr($insertValuesList, 0, -2);
			$__now = date('Y-m-d H:i:s');
					

				$deleteTable ="
DROP PROCEDURE IF EXISTS SP_Delete".ucfirst($tableName)."ByID;
DELIMITER;
CREATE PROCEDURE SP_Delete".ucfirst($tableName)."ByID(".$whereCondParam.")
\tBEGIN\n
\t\tDELETE FROM 	`".$tableName."`
\t\tWHERE 	`".$tablePrimaryKey."` = p".ucfirst($tablePrimaryKey).";
\tEND;
DELIMITER; ";

$insertTable = "
DROP PROCEDURE IF EXISTS SP_InsertInto".ucfirst($tableName).";
DELIMITER; 
CREATE PROCEDURE SP_InsertInto".ucfirst($tableName)."(".$insertTableParam.")
\tBEGIN
\t\tINSERT INTO `".$tableName."` (".$insertTableList.") 
\t\tVALUES (".$insertValuesList.");
\tEND; 
DELIMITER; ";
			
			
		$getTables = "
DROP PROCEDURE IF EXISTS SP_GetAll".ucfirst($tableName).";
DELIMITER; 
CREATE PROCEDURE SP_GetAll".ucfirst($tableName)."() 
\tBEGIN
\t\tSELECT 	".$getTableList."
\t\tFROM 	`".$tableName."`;
\tEND; 
DELIMITER; ";			
			
		$getTableDetails = "
DROP PROCEDURE IF EXISTS SP_Get".ucfirst($tableName)."DetailsByID;
DELIMITER; 
CREATE PROCEDURE SP_Get".ucfirst($tableName)."DetailsByID(".$whereCondParam." )
BEGIN
\t\tSELECT 	".$getTableDetailsList."
\t\tFROM 	`".$tableName."` 
\t\tWHERE 	".$whereCondition.";
\tEND; 
DELIMITER; ";

		$updateTable = "
DROP PROCEDURE IF EXISTS SP_Update".ucfirst($tableName)."ByID;				
DELIMITER; 
CREATE PROCEDURE SP_Update".ucfirst($tableName)."ByID( ".$updateTableParam." )
\tBEGIN 
\t\tUPDATE 	`".$tableName."` 
\t\tSET 	".$updateTableList."
\t\tWHERE 	".$whereCondition.";
\tEND; 
DELIMITER; ";
		/// moi them vao day.
		// neu truong hop ma ko co cac truong hop set thi bo qua cau lenh update nay		
		$updateTableList = trim($updateTableList,' ');		
		if(!strcasecmp("",$updateTableList))
		{
			$updateTable = " ";
		}				
// mo file
//exit();
			$ourFileName = $this->path."\\".$this->db_name."_StoreProceduce.sql";
			//echo $ourFileName; exit();
		
			// string will write file
			$buffwrite = $updateTable;
			$ourFileHandle = fopen($ourFileName, 'a') or die("can't open file");
			//fwrite($ourFileHandle,$commandDoc);
			fwrite($ourFileHandle,$updateTable);			
			fwrite($ourFileHandle,$insertTable);
			fwrite($ourFileHandle,$getTableDetails);
			fwrite($ourFileHandle,$deleteTable);

			fclose($ourFileHandle);
		}

		return true;
	}
}
?>