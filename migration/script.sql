CREATE PROCEDURE explode( pDelim VARCHAR(32), pStr VARCHAR(1000))
BEGIN
  DROP TABLE IF EXISTS temp_explode;
  CREATE TEMPORARY TABLE temp_explode (id INT AUTO_INCREMENT PRIMARY KEY NOT NULL, word VARCHAR(40));
  SET @sql := CONCAT('INSERT INTO temp_explode (word) VALUES (', REPLACE(QUOTE(pStr), pDelim, '\'), (\''), ')');
  PREPARE myStmt FROM @sql;
  EXECUTE myStmt;
END;
DROP PROCEDURE IF EXISTS explode;
--SET @str  = "The quick brown fox jumped over the lazy dog";
--SET @delim = " ";
--CALL explode(@delim,@str);
--SELECT id,word FROM temp_explode;
