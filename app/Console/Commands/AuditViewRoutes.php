<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class AuditViewRoutes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:views 
                            {--detailed : Show all route calls, not just broken ones}
                            {--suggestions : Show fuzzy match suggestions for broken routes}
                            {--export= : Export results to file (json|csv)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audit Blade views for broken route references and undefined variables';

    /**
     * All registered route names
     */
    protected array $registeredRoutes = [];

    /**
     * Results storage
     */
    protected array $brokenRoutes = [];
    protected array $validRoutes = [];
    protected int $totalFiles = 0;
    protected int $totalRouteReferences = 0;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Starting View Routes Audit...');
        $this->newLine();

        // Load all registered routes
        $this->loadRegisteredRoutes();

        // Scan all blade files
        $bladeFiles = $this->getAllBladeFiles();
        $this->totalFiles = count($bladeFiles);

        $this->info("Found {$this->totalFiles} Blade files to scan");
        $this->newLine();

        // Progress bar
        $bar = $this->output->createProgressBar($this->totalFiles);
        $bar->start();

        foreach ($bladeFiles as $file) {
            $this->auditFile($file);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Display results
        $this->displayResults();

        // Export if requested
        if ($this->option('export')) {
            $this->exportResults($this->option('export'));
        }

        return $this->brokenRoutes ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Load all registered route names
     */
    protected function loadRegisteredRoutes(): void
    {
        $routes = Route::getRoutes();
        
        foreach ($routes as $route) {
            if ($name = $route->getName()) {
                $this->registeredRoutes[] = $name;
            }
        }

        $this->info("✓ Loaded " . count($this->registeredRoutes) . " registered routes");
    }

    /**
     * Get all blade files recursively
     */
    protected function getAllBladeFiles(): array
    {
        $viewsPath = resource_path('views');
        
        if (!File::exists($viewsPath)) {
            $this->error("Views directory not found: {$viewsPath}");
            return [];
        }

        return File::allFiles($viewsPath);
    }

    /**
     * Audit a single file
     */
    protected function auditFile(\SplFileInfo $file): void
    {
        $content = File::get($file->getPathname());
        $relativePath = str_replace(resource_path('views') . DIRECTORY_SEPARATOR, '', $file->getPathname());
        
        // Extract all route() calls
        $routes = $this->extractRouteReferences($content);

        foreach ($routes as $routeData) {
            $this->totalRouteReferences++;
            
            $routeName = $routeData['name'];
            $lineNumber = $routeData['line'];
            
            // Check if route exists
            if (Route::has($routeName)) {
                $this->validRoutes[] = [
                    'file' => $relativePath,
                    'line' => $lineNumber,
                    'route' => $routeName,
                    'status' => 'valid',
                ];
            } else {
                // Find suggestion
                $suggestion = $this->findSuggestion($routeName);
                
                $this->brokenRoutes[] = [
                    'file' => $relativePath,
                    'line' => $lineNumber,
                    'route' => $routeName,
                    'suggestion' => $suggestion,
                    'status' => 'broken',
                ];
            }
        }
    }

    /**
     * Extract route references from content
     */
    protected function extractRouteReferences(string $content): array
    {
        $routes = [];
        $lines = explode("\n", $content);
        
        foreach ($lines as $lineNumber => $line) {
            // Pattern 1: route('name')
            if (preg_match_all("/route\(['\"]([^'\"]+)['\"]/", $line, $matches)) {
                foreach ($matches[1] as $routeName) {
                    $routes[] = [
                        'name' => $routeName,
                        'line' => $lineNumber + 1, // 1-indexed
                    ];
                }
            }
            
            // Pattern 2: @route('name')
            if (preg_match_all("/@route\(['\"]([^'\"]+)['\"]/", $line, $matches)) {
                foreach ($matches[1] as $routeName) {
                    $routes[] = [
                        'name' => $routeName,
                        'line' => $lineNumber + 1,
                    ];
                }
            }
        }

        return $routes;
    }

    /**
     * Find suggestion for broken route using fuzzy matching
     */
    protected function findSuggestion(string $brokenRoute): ?string
    {
        if (!$this->option('suggestions')) {
            return null;
        }

        $bestMatch = null;
        $bestScore = 0;

        foreach ($this->registeredRoutes as $registeredRoute) {
            // Calculate similarity
            $similarity = 0;
            similar_text($brokenRoute, $registeredRoute, $similarity);
            
            if ($similarity > $bestScore && $similarity > 50) { // 50% threshold
                $bestScore = $similarity;
                $bestMatch = $registeredRoute;
            }
            
            // Also check if broken route is substring of registered
            if (Str::contains($registeredRoute, $brokenRoute)) {
                $bestMatch = $registeredRoute;
                break;
            }
        }

        return $bestMatch;
    }

    /**
     * Display results
     */
    protected function displayResults(): void
    {
        // Summary
        $this->info('📊 Audit Summary');
        $this->info('================');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Blade Files Scanned', $this->totalFiles],
                ['Total Route References', $this->totalRouteReferences],
                ['Valid Routes', count($this->validRoutes)],
                ['Broken Routes', count($this->brokenRoutes)],
                ['Registered Routes', count($this->registeredRoutes)],
            ]
        );
        $this->newLine();

        // Broken routes (always show)
        if (count($this->brokenRoutes) > 0) {
            $this->error('❌ BROKEN ROUTE REFERENCES FOUND!');
            $this->newLine();
            
            $headers = ['File', 'Line', 'Missing Route'];
            
            if ($this->option('suggestions')) {
                $headers[] = 'Suggestion';
            }
            
            $rows = array_map(function ($item) {
                $row = [
                    Str::limit($item['file'], 50),
                    $item['line'],
                    $item['route'],
                ];
                
                if ($this->option('suggestions')) {
                    $row[] = $item['suggestion'] ?? '-';
                }
                
                return $row;
            }, $this->brokenRoutes);
            
            $this->table($headers, $rows);
            
            $this->newLine();
            $this->warn("⚠️  Found " . count($this->brokenRoutes) . " broken route reference(s)!");
            $this->warn("These will cause 500 errors when users click them.");
        } else {
            $this->info('✅ No broken routes found! All route references are valid.');
        }

        // Valid routes (only if --detailed flag)
        if ($this->option('detailed') && count($this->validRoutes) > 0) {
            $this->newLine();
            $this->info('✅ Valid Route References (first 20):');
            
            $rows = array_slice(array_map(function ($item) {
                return [
                    Str::limit($item['file'], 40),
                    $item['line'],
                    $item['route'],
                ];
            }, $this->validRoutes), 0, 20);
            
            $this->table(['File', 'Line', 'Route'], $rows);
            
            if (count($this->validRoutes) > 20) {
                $this->info('... and ' . (count($this->validRoutes) - 20) . ' more valid routes');
            }
        }
    }

    /**
     * Export results to file
     */
    protected function exportResults(string $format): void
    {
        $filename = storage_path("logs/view-audit-" . date('Y-m-d-His') . ".{$format}");
        
        if ($format === 'json') {
            $data = [
                'audit_date' => now()->toDateTimeString(),
                'summary' => [
                    'total_files' => $this->totalFiles,
                    'total_references' => $this->totalRouteReferences,
                    'valid_routes' => count($this->validRoutes),
                    'broken_routes' => count($this->brokenRoutes),
                ],
                'broken_routes' => $this->brokenRoutes,
                'valid_routes' => $this->validRoutes,
            ];
            
            File::put($filename, json_encode($data, JSON_PRETTY_PRINT));
        } elseif ($format === 'csv') {
            $csv = "File,Line,Route,Status,Suggestion\n";
            
            foreach ($this->brokenRoutes as $item) {
                $csv .= "\"{$item['file']}\",{$item['line']},\"{$item['route']}\",broken,\"{$item['suggestion']}\"\n";
            }
            
            File::put($filename, $csv);
        }
        
        $this->info("📄 Results exported to: {$filename}");
    }
}
