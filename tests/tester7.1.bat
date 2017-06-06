#@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../vendor/nette/tester/src/tester
"C:\Program Files (x86)\PHP\v7.0\php.exe" "%BIN_TARGET%" %*