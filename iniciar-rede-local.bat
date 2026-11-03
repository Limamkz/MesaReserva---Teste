@echo off
setlocal EnableExtensions
title MesaReserva - Acesso pela rede local
color 0A
echo ==========================================
echo       MESARESERVA - REDE LOCAL
echo ==========================================
echo.
echo Este computador sera o servidor do site.
echo Todos os computadores precisam estar na mesma rede Wi-Fi/rede local.
echo.
net session >nul 2>&1
if %errorlevel%==0 (
    echo Criando regras do Firewall do Windows para Apache na rede privada...
    netsh advfirewall firewall delete rule name="MesaReserva Apache HTTP" >nul 2>&1
    netsh advfirewall firewall add rule name="MesaReserva Apache HTTP" dir=in action=allow protocol=TCP localport=80 profile=private >nul 2>&1
    netsh advfirewall firewall delete rule name="MesaReserva Apache 8080" >nul 2>&1
    netsh advfirewall firewall add rule name="MesaReserva Apache 8080" dir=in action=allow protocol=TCP localport=8080 profile=private >nul 2>&1
    netsh advfirewall firewall delete rule name="MesaReserva Apache 443" >nul 2>&1
    netsh advfirewall firewall add rule name="MesaReserva Apache HTTPS" dir=in action=allow protocol=TCP localport=443 profile=private >nul 2>&1
    if exist "C:\xampp\apache\bin\httpd.exe" (
        netsh advfirewall firewall delete rule name="MesaReserva Apache programa" >nul 2>&1
        netsh advfirewall firewall add rule name="MesaReserva Apache programa" dir=in action=allow program="C:\xampp\apache\bin\httpd.exe" enable=yes profile=private >nul 2>&1
    )
    echo Regras adicionadas. Se o Apache usa outra porta, libere essa porta tambem.
) else (
    echo ATENCAO: execute este arquivo como ADMINISTRADOR para configurar o Firewall.
)
echo.
echo Confira se Apache e MySQL estao iniciados no XAMPP.
echo Enderecos IPv4 encontrados neste computador:
ipconfig | findstr /i /c:"IPv4 Address" /c:"Endereco IPv4"
echo.
echo No proprio computador: http://localhost/MesaReserva/
echo Nos outros PCs, use: http://IP-DO-COMPUTADOR/MesaReserva/
echo Troque IP-DO-COMPUTADOR pelo IPv4 acima.
echo.
echo IMPORTANTE:
echo - Se o Apache usa porta 8080, use http://IP-DO-COMPUTADOR:8080/MesaReserva/
echo - Defina sua rede Wi-Fi como Privada no Windows.
echo - Nao use a rede Wi-Fi de convidados; ela pode bloquear outros dispositivos.
echo - Este acesso funciona na mesma rede local, nao pela Internet.
echo - Nunca desative o Firewall inteiro nem encaminhe portas para a Internet.
echo.
pause
start "" "http://localhost/MesaReserva/"
endlocal
