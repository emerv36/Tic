$ErrorActionPreference = 'Stop'

$contractRoot = Join-Path $PSScriptRoot '..\Contracts\personal\v1'
$cases = @(
    @{ Example = 'persona-create.valid.json'; Schema = 'persona-command.schema.json'; Expected = $true },
    @{ Example = 'persona-academic-field.invalid.json'; Schema = 'persona-command.schema.json'; Expected = $false },
    @{ Example = 'persona-phone.invalid.json'; Schema = 'persona-command.schema.json'; Expected = $false },
    @{ Example = 'vinculo-admin-create.valid.json'; Schema = 'vinculo-command.schema.json'; Expected = $true },
    @{ Example = 'vinculo-admin-update.valid.json'; Schema = 'vinculo-command.schema.json'; Expected = $true },
    @{ Example = 'vinculo-docente-catalog.invalid.json'; Schema = 'vinculo-command.schema.json'; Expected = $false },
    @{ Example = 'foto.valid.json'; Schema = 'foto-command.schema.json'; Expected = $true },
    @{ Example = 'foto-mime.invalid.json'; Schema = 'foto-command.schema.json'; Expected = $false },
    @{ Example = 'carnet-assign.valid.json'; Schema = 'carnet-command.schema.json'; Expected = $true },
    @{ Example = 'carnet-replacement-missing-reason.invalid.json'; Schema = 'carnet-command.schema.json'; Expected = $false },
    @{ Example = 'estado-baja.valid.json'; Schema = 'estado-institucional-command.schema.json'; Expected = $true },
    @{ Example = 'estado-reactivacion-persona.valid.json'; Schema = 'estado-institucional-command.schema.json'; Expected = $true },
    @{ Example = 'ack-processed.valid.json'; Schema = 'integration-ack.schema.json'; Expected = $true },
    @{ Example = 'ack-rejected.valid.json'; Schema = 'integration-ack.schema.json'; Expected = $true },
    @{ Example = 'ack-vinculo-created.valid.json'; Schema = 'integration-ack.schema.json'; Expected = $true },
    @{ Example = 'persona-query.valid.json'; Schema = 'persona-query-response.schema.json'; Expected = $true },
    @{ Example = 'persona-query-docente-catalog.invalid.json'; Schema = 'persona-query-response.schema.json'; Expected = $false },
    @{ Example = 'persona-query-admin-null.invalid.json'; Schema = 'persona-query-response.schema.json'; Expected = $false },
    @{ Example = 'ack-processed-null-version.invalid.json'; Schema = 'integration-ack.schema.json'; Expected = $false },
    @{ Example = 'problem-precorrelation.valid.json'; Schema = 'integration-problem.schema.json'; Expected = $true },
    @{ Example = 'event-carnet.valid.json'; Schema = 'integration-event.schema.json'; Expected = $true },
    @{ Example = 'event-institutional.valid.json'; Schema = 'integration-event.schema.json'; Expected = $true },
    @{ Example = 'event-academic-field.invalid.json'; Schema = 'integration-event.schema.json'; Expected = $false },
    @{ Example = 'persona-role.invalid.json'; Schema = 'persona-command.schema.json'; Expected = $false },
    @{ Example = 'event-mismatch.invalid.json'; Schema = 'integration-event.schema.json'; Expected = $false }
)

foreach ($case in $cases) {
    $examplePath = Join-Path $contractRoot ('examples\' + $case.Example)
    $schemaPath = Join-Path $contractRoot $case.Schema
    $validationErrors = @()
    $actual = Test-Json -LiteralPath $examplePath -SchemaFile $schemaPath -ErrorAction SilentlyContinue -ErrorVariable validationErrors
    if ($actual -ne $case.Expected) {
        throw "Resultado inesperado para $($case.Example): esperado=$($case.Expected), recibido=$actual"
    }
}

Write-Output 'validate_contract_examples: OK'
