UPDATE `informations` SET `value` = '2.3.0-b5' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.3.0-b4' LIMIT 1;
