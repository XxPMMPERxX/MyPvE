$OLD_COMPOSE="docker-compose"
$NEW_COMPOSE="docker compose"

Get-ChildItem (Get-Location) |
    Where-Object {-not $_.PsIsContainer -and $_.Name -eq ".env"} |
    Get-Content |
    ForEach-Object {
        if ($_ -match "^#.*" -or $_ -match "^(?!.*=).*$") {
            return
        }
        $key, $value = $_.split('=', 2);
        Set-Variable -Name $key -Value $value
    }
Invoke-Expression "${NEW_COMPOSE} version" | Out-Null
$RESULT_NEW_COMPOSE=$?

Invoke-Expression "${OLD_COMPOSE} version" | Out-Null
$RESULT_OLD_COMPOSE=$?

if ($RESULT_NEW_COMPOSE -eq $true) {
    $COMPOSE=$NEW_COMPOSE
} elseif ($RESULT_OLD_COMPOSE -eq $true) {
    $COMPOSE=$OLD_COMPOSE
} else {
    Write-Error 'docker-compose or docker compose not installed'
    exit 1
}

if ($null -eq $CONTAINER_NAME) {
    $CONTAINER_NAME="pmmp"
}

Invoke-Expression "${COMPOSE} run --rm ${CONTAINER_NAME} /usr/bin/start-pocketmine"
