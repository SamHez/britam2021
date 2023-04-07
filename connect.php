<?php
/*********************************************************************
* This script has been released with the aim that it will be useful.
* Written by Skycodeit & Clear Basics Programming Blog
* Website: www.Skycodeit.com, www.clearbasics.ug
* Email: info@Skycodeit.com
* All Copy Rights Reserved by Skycode & Clear Basics IT Programming Blog
***********************************************************************/

class Database 
{
	private static $dbName = 'clearbas_britam' ; 
	private static $dbHost = 'localhost' ;
	private static $dbUsername = 'clearbas_root';
	private static $dbUserPassword = 'Skycode@2018';
	
	private static $cont  = null;
	
	public function __construct() {
		exit('Init function is not allowed');
	}
	
	public static function connect()
	{
	   // One connection through whole application
       if ( null == self::$cont )
       {      
        try 
        {
          self::$cont =  new PDO( "mysql:host=".self::$dbHost.";"."dbname=".self::$dbName, self::$dbUsername, self::$dbUserPassword);  
        }
        catch(PDOException $e) 
        {
          die($e->getMessage());  
        }
       } 
       return self::$cont;
	}
	
	public static function disconnect()
	{
		self::$cont = null;
	}
}
 
?>