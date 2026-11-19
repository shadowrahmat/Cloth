@echo off
REM Start DRAWER CLOTHING locally at http://localhost:8001
REM Needs XAMPP MySQL running. Port 8000 is used by the other project, so this uses 8001.
cd /d "%~dp0"
set PHP="D:\skytechglobal au\.tools\php\php.exe"
if not exist %PHP% set PHP=php
if not exist public\build\manifest.json (
  echo Building frontend assets...
  call npm run build
)
start "" http://localhost:8001
%PHP% artisan serve --host=127.0.0.1 --port=8001
