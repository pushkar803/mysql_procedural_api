DELIMITER $$
CREATE PROCEDURE `SearchCompany`(IN `in_CompanyName` VARCHAR(999),IN `in_CompanyNumber` VARCHAR(999),IN `in_City` VARCHAR(999),IN `in_PostalCode` VARCHAR(999),IN `in_Website` VARCHAR(999),IN `in_PhoneNumber` VARCHAR(999),IN `in_Email` VARCHAR(999))
BEGIN
	DECLARE `var_status` 			varchar(200);
	DECLARE `var_userID` 			varchar(200);
	DECLARE `var_procedure_no` 		varchar(200);
	DECLARE `var_procedure_name` 	varchar(200);
    
DROP TEMPORARY TABLE IF EXISTS MainTable;	
CREATE TEMPORARY TABLE MainTable 
(
	`Message` 				Varchar(200)  NOT NULL,
	`Status` 				Varchar(200)  NOT NULL,
	`ProcedureName` 		Varchar(200)  default null,
	`ProcedureID`			Varchar(200)  default null,
     `ID` 					Varchar(400)  default null,
     `CreationDate` 		Varchar(400)  default null
       
    
) ENGINE=MEMORY;
	
    SET `var_procedure_no`   = '101';
	SET `var_procedure_name` = 'SendNewApplication';
    SET `var_status` = 201;
    

#Validation
#--------------------------------------

#Function
#--------------------------------------
IF `var_status` = 201 THEN

INSERT INTO `NewApplications` (`ID`,`CompanyName`, `CompanyNumber`, `City`, `PostalCode`, `Website`, `PhoneNumber`, `Email`, `Status`, `StatusUpdate`, `TimeStamp`)
VALUES (null, `in_CompanyName`, `in_CompanyNumber`, `in_City`, `in_PostalCode`, `in_Website`, `in_PhoneNumber`, `in_Email`, 'new', now(), now());


		INSERT INTO MainTable
		(
			`Message`, `Status`, `ProcedureID`, `ProcedureName`
        ) 
		(
			SELECT
			'Success!', `var_status`, `var_procedure_no`, `var_procedure_name`
		);
        

        UPDATE MainTable SET `ID` 					= 	(SELECT MAX(`ID`) FROM NewApplications);
        UPDATE MainTable SET `CreationDate` 		= 	(SELECT TimeStamp FROM NewApplications where ID like (SELECT MAX(`ID`) FROM NewApplications));
        
END IF;
SELECT * FROM MainTable;
DROP TEMPORARY TABLE MainTable;
END$$
DELIMITER ;
