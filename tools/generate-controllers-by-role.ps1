$ErrorActionPreference = 'Stop'

$repoRoot = Split-Path -Parent $PSScriptRoot
$routeFile = Join-Path $repoRoot 'routes\web.php'
$outFile = Join-Path $repoRoot 'controllers_by_role.txt'

if (!(Test-Path $routeFile)) {
  throw "Route file not found: $routeFile"
}

$lines = Get-Content -Encoding UTF8 $routeFile

$roles = @('admin', 'pic', 'pts')
$controllersByRole = @{}
foreach ($r in $roles) {
  $controllersByRole[$r] = New-Object 'System.Collections.Generic.HashSet[string]'
}

# Match: Route::<verb>( [arrayOfMethods,] 'path', [Some\Controller::class, 'method'] )
$rxController = [regex]::new('\[(?<ctrl>[^\]]+?)::class\s*,')

$braceDepth = 0
$roleStack = New-Object System.Collections.Generic.List[object]

function Get-CurrentRole {
  param([System.Collections.Generic.List[object]]$stack)
  if ($stack.Count -eq 0) { return $null }
  return $stack[$stack.Count - 1].Role
}

function Count-Char {
  param([string]$s, [char]$ch)
  $count = 0
  foreach ($c in $s.ToCharArray()) { if ($c -eq $ch) { $count++ } }
  return $count
}

$inRoute = $false
$routeRole = $null
$routeBuf = New-Object System.Text.StringBuilder

foreach ($lineRaw in $lines) {
  $line = $lineRaw

  # Detect role-group starts (based on middleware definition)
  $startRole = $null
  if ($line -match "Route::middleware\(\s*\[[^\]]*role:admin") { $startRole = 'admin' }
  elseif ($line -match "Route::middleware\(\s*\[[^\]]*role:pic") { $startRole = 'pic' }
  elseif ($line -match "Route::middleware\(\s*\[[^\]]*auth:pts") { $startRole = 'pts' }

  # Update brace depth for this line
  $opens = (Count-Char -s $line -ch '{')
  $closes = (Count-Char -s $line -ch '}')
  $braceDepth += ($opens - $closes)

  # Push role after entering the group scope
  if ($null -ne $startRole) {
    $roleStack.Add([pscustomobject]@{ Role = $startRole; Depth = $braceDepth })
  }

  # Pop roles whose scope ended
  while ($roleStack.Count -gt 0 -and $braceDepth -lt $roleStack[$roleStack.Count - 1].Depth) {
    $roleStack.RemoveAt($roleStack.Count - 1)
  }

  # Accumulate route statements across multiple lines until ');'
  if (-not $inRoute) {
    if ($line -match '^\s*Route::(get|post|put|delete|match)\b') {
      $inRoute = $true
      $routeRole = Get-CurrentRole -stack $roleStack
      [void]$routeBuf.Clear()
      [void]$routeBuf.AppendLine($line)
      if ($line -match '\);\s*$') {
        $inRoute = $false
      }
    }
  } else {
    [void]$routeBuf.AppendLine($line)
    if ($line -match '\);\s*$') {
      $inRoute = $false
    }
  }

  if (-not $inRoute -and $routeBuf.Length -gt 0) {
    $stmt = $routeBuf.ToString()
    [void]$routeBuf.Clear()

    if ($null -ne $routeRole -and $controllersByRole.ContainsKey($routeRole)) {
      $mCtrl = $rxController.Match($stmt)
      if ($mCtrl.Success) {
        $ctrlRaw = $mCtrl.Groups['ctrl'].Value.Trim()
        $ctrlRaw = $ctrlRaw -replace '^\\+', ''
        $ctrlRaw = $ctrlRaw -replace '^App\\Http\\Controllers\\', ''
        $ctrlRaw = $ctrlRaw -replace '^App\\Http\\Controllers\\', ''
        $ctrlRaw = $ctrlRaw -replace '^\s+|\s+$', ''
        $ctrlName = ($ctrlRaw -split '\\')[-1]
        [void]$controllersByRole[$routeRole].Add($ctrlName)
      }
    }
    $routeRole = $null
  }
}

# Build global list (controllers used by >=2 roles)
$allControllers = New-Object 'System.Collections.Generic.HashSet[string]'
foreach ($r in $roles) {
  foreach ($c in $controllersByRole[$r]) { [void]$allControllers.Add($c) }
}

$global = @()
foreach ($c in ($allControllers | Sort-Object)) {
  $usedBy = @()
  foreach ($r in $roles) {
    if ($controllersByRole[$r].Contains($c)) { $usedBy += $r }
  }
  if ($usedBy.Count -ge 2) {
    $global += [pscustomobject]@{ Controller = $c; Roles = ($usedBy -join ', ') }
  }
}

$sb = New-Object System.Text.StringBuilder
[void]$sb.AppendLine("Generated: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')")
[void]$sb.AppendLine("Source: routes/web.php")
[void]$sb.AppendLine('')

[void]$sb.AppendLine('GLOBAL (controller dipakai >= 2 role)')
if ($global.Count -eq 0) {
  [void]$sb.AppendLine('- (tidak ada)')
} else {
  foreach ($g in $global | Sort-Object Controller) {
    [void]$sb.AppendLine("- $($g.Controller)  [roles: $($g.Roles)]")
  }
}

foreach ($r in $roles) {
  [void]$sb.AppendLine('')
  [void]$sb.AppendLine($r.ToUpper())
  $list = $controllersByRole[$r] | Sort-Object
  if ($list.Count -eq 0) {
    [void]$sb.AppendLine('- (tidak ada)')
  } else {
    foreach ($c in $list) {
      [void]$sb.AppendLine("- $c")
    }
  }
}

Set-Content -Path $outFile -Value $sb.ToString() -Encoding UTF8
Write-Host "Wrote $outFile"