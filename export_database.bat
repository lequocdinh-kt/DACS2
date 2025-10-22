@echo off
echo ========================================
echo  EXPORT DATABASE FOR DEPLOYMENT
echo ========================================
echo.

REM Set MySQL credentials
set MYSQL_USER=root
set MYSQL_PASS=
set DB_NAME=dacs2
set OUTPUT_FILE=dacs2_export_%date:~-4,4%%date:~-7,2%%date:~-10,2%.sql

echo Exporting database: %DB_NAME%
echo Output file: %OUTPUT_FILE%
echo.

REM Path to mysqldump (adjust if needed)
set MYSQLDUMP="C:\xampp\mysql\bin\mysqldump.exe"

REM Check if mysqldump exists
if not exist %MYSQLDUMP% (
    echo ERROR: mysqldump.exe not found!
    echo Please check the path: %MYSQLDUMP%
    pause
    exit /b 1
)

REM Export database
%MYSQLDUMP% -u %MYSQL_USER% --password=%MYSQL_PASS% %DB_NAME% > %OUTPUT_FILE%

if %errorlevel% == 0 (
    echo.
    echo ========================================
    echo  SUCCESS! Database exported
    echo ========================================
    echo File saved as: %OUTPUT_FILE%
    echo.
    echo Next steps:
    echo 1. Upload this SQL file to your hosting
    echo 2. Import via phpMyAdmin
    echo 3. Update config.php with hosting database info
    echo.
) else (
    echo.
    echo ========================================
    echo  ERROR! Export failed
    echo ========================================
    echo Please check your MySQL credentials
    echo.
)

pause
