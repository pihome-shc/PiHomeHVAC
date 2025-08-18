DROP PROCEDURE IF EXISTS `RenameColumnIfExists`;

-- Step 1: Setting the Delimiter
DELIMITER //

-- Step 2: Creating the Procedure
CREATE PROCEDURE RenameColumnIfExists()
BEGIN
    -- Step 3: Declaring Variables
    DECLARE column_exists INT;

    -- Step 4: Checking if the Column Exists
    SELECT COUNT(*) INTO column_exists
    FROM information_schema.columns
    WHERE table_name = 'user' 
    AND column_name = 'admin_account';

    -- Step 5: Conditional Logic
    IF column_exists > 0 THEN
        ALTER TABLE user RENAME COLUMN admin_account TO access_level;
    END IF;
END //

-- Step 6: Restoring the Delimiter
DELIMITER ;

-- Step 7: Calling the Procedure
CALL RenameColumnIfExists();

UPDATE user SET access_level = IF(access_level=1, 0, 1);