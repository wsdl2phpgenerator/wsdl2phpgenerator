@echo off
REM wsdl2phpgenerator
REM

set PHPBIN="@php_bin@"
%PHPBIN% "wsdl2php.php" %*
