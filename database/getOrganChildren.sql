DROP FUNCTION IF EXISTS `getOrganChildren`;
DELIMITER ;;
CREATE FUNCTION `getOrganChildren`(orgid INT) RETURNS varchar(4000) CHARSET utf8
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
		SELECT GROUP_CONCAT(id) INTO oTempChild FROM organization WHERE type=1 AND FIND_IN_SET(parent_id,oTempChild) > 0;
	END WHILE;
	RETURN oTemp;
END
;;
DELIMITER ;