DROP FUNCTION IF EXISTS getTaskTypeChildrens;
CREATE FUNCTION getTaskTypeChildrens(orgid INT)
RETURNS VARCHAR(4000)
BEGIN
	DECLARE oTemp VARCHAR(4000);
	DECLARE oTempChild VARCHAR(4000);
	 
	SET oTemp = '';
	SET oTempChild = CAST(orgid AS CHAR);

	WHILE oTempChild IS NOT NULL
		DO
		IF oTemp = '' THEN
			SET oTemp = CONCAT(oTemp,oTempChild);
		ELSE
			SET oTemp = CONCAT(oTemp,',',oTempChild);
		END IF;
		SELECT GROUP_CONCAT(id) INTO oTempChild FROM task_type WHERE FIND_IN_SET(parent_id,oTempChild) > 0;
	END WHILE;
	RETURN oTemp;
END

