$path = 'c:\xampp\htdocs\projet2222\views\frontoffice\profile.php'
$content = [System.IO.File]::ReadAllText($path)
$content = $content.Replace('placeholder="Minimum 6 caracteres"', 'placeholder="Minimum 8 caracteres"')
$content = $content.Replace('minlength="6"', 'minlength="8"')
$content = $content.Replace('.{6,}', '.{8,}')
$content = $content.Replace('6 caracteres minimum, une lettre majuscule et un caractere special.', '8 caracteres minimum, une lettre majuscule et un caractere special.')
[System.IO.File]::WriteAllText($path, $content, [System.Text.Encoding]::UTF8)
Write-Host "Done!"
