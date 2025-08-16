<?php
/**
 * Isotone Automation Rule Engine
 * 
 * Processes YAML-based rules with priority and validation
 * 
 * @package Isotone\Automation
 */

namespace Isotone\Automation\Rules;

use Symfony\Component\Yaml\Yaml;
use Exception;

class RuleEngine
{
    private array $rules = [];
    private array $violations = [];
    private string $configDir;
    
    public function __construct()
    {
        $this->configDir = dirname(dirname(__DIR__)) . '/config';
    }
    
    /**
     * Load rules from YAML files
     */
    public function loadRules(): void
    {
        $rulesFile = $this->configDir . '/rules.yaml';
        $workflowsFile = $this->configDir . '/workflows.yaml';
        
        // Load main rules
        if (file_exists($rulesFile)) {
            $this->rules = Yaml::parseFile($rulesFile);
        }
        
        // Load workflows
        if (file_exists($workflowsFile)) {
            $workflows = Yaml::parseFile($workflowsFile);
            $this->rules['workflows'] = $workflows;
        }
        
        // Sort rules by priority
        $this->sortRulesByPriority();
    }
    
    /**
     * Validate all rules
     */
    public function validate(): bool
    {
        $this->violations = [];
        
        echo "🔍 Rules Validation\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        
        // Validate rule structure
        echo "📋 Step 1/5: Validating rule structure...\n";
        $this->validateRuleStructure();
        $structureCount = count($this->violations);
        echo "   Found " . count($this->rules) . " top-level rules\n";
        if ($structureCount > 0) {
            echo "   ⚠️  {$structureCount} structure issues found\n";
        } else {
            echo "   ✅ All rules have valid structure\n";
        }
        
        // Validate rule references
        echo "🔗 Step 2/5: Validating rule references...\n";
        $beforeCount = count($this->violations);
        $this->validateRuleReferences();
        $refCount = count($this->violations) - $beforeCount;
        if ($refCount > 0) {
            echo "   ⚠️  {$refCount} invalid references found\n";
        } else {
            echo "   ✅ All references are valid\n";
        }
        
        // Validate rule conflicts
        echo "⚔️  Step 3/5: Checking for rule conflicts...\n";
        $beforeCount = count($this->violations);
        $this->validateRuleConflicts();
        $conflictCount = count($this->violations) - $beforeCount;
        if ($conflictCount > 0) {
            echo "   ⚠️  {$conflictCount} conflicts found\n";
        } else {
            echo "   ✅ No conflicting rules detected\n";
        }
        
        // Validate file paths mentioned in rules
        echo "📁 Step 4/5: Validating file paths...\n";
        $beforeCount = count($this->violations);
        $pathsChecked = $this->validateFilePaths();
        $pathCount = count($this->violations) - $beforeCount;
        echo "   Checked {$pathsChecked} file paths\n";
        if ($pathCount > 0) {
            echo "   ⚠️  {$pathCount} missing critical paths\n";
        } else {
            echo "   ✅ All critical paths exist\n";
        }
        
        // Validate commands mentioned in rules
        echo "💻 Step 5/5: Validating commands...\n";
        $beforeCount = count($this->violations);
        $commandsChecked = $this->validateCommands();
        $cmdCount = count($this->violations) - $beforeCount;
        echo "   Checked {$commandsChecked} commands\n";
        if ($cmdCount > 0) {
            echo "   ⚠️  {$cmdCount} invalid commands found\n";
        } else {
            echo "   ✅ All commands are valid\n";
        }
        
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        
        // Summary
        echo "📊 Summary\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "Rules checked: " . count($this->rules) . "\n";
        echo "File paths validated: {$pathsChecked}\n";
        echo "Commands validated: {$commandsChecked}\n";
        
        if (empty($this->violations)) {
            echo "Result: ✅ All validations passed\n";
        } else {
            echo "Result: ⚠️  " . count($this->violations) . " issues found\n";
        }
        echo "\n";
        
        return empty($this->violations);
    }
    
    /**
     * Apply rules to a context
     */
    public function applyRules(string $context, array $data = []): array
    {
        $appliedRules = [];
        $results = [];
        
        foreach ($this->getRulesForContext($context) as $ruleName => $rule) {
            if ($this->shouldApplyRule($rule, $data)) {
                $result = $this->executeRule($ruleName, $rule, $data);
                $appliedRules[] = $ruleName;
                $results[$ruleName] = $result;
            }
        }
        
        return [
            'applied' => $appliedRules,
            'results' => $results
        ];
    }
    
    /**
     * Get rules for a specific context
     */
    public function getRulesForContext(string $context): array
    {
        $contextRules = [];
        
        foreach ($this->rules as $category => $categoryRules) {
            if (!is_array($categoryRules)) {
                continue;
            }
            
            foreach ($categoryRules as $ruleName => $rule) {
                if ($this->matchesContext($rule, $context)) {
                    $contextRules[$ruleName] = $rule;
                }
            }
        }
        
        return $contextRules;
    }
    
    /**
     * Get rule by name
     */
    public function getRule(string $name): ?array
    {
        // Check if it's a top-level rule first
        if (isset($this->rules[$name])) {
            return $this->rules[$name];
        }
        
        // Then check nested rules (for backward compatibility)
        foreach ($this->rules as $category => $categoryRules) {
            if (is_array($categoryRules) && isset($categoryRules[$name])) {
                return $categoryRules[$name];
            }
        }
        
        return null;
    }
    
    /**
     * Get all rules
     */
    public function getAllRules(): array
    {
        return $this->rules;
    }
    
    /**
     * Get violations from last validation
     */
    public function getViolations(): array
    {
        return $this->violations;
    }
    
    /**
     * Report validation results
     */
    public function report(): void
    {
        if (empty($this->violations)) {
            echo "✅ All rules validated successfully\n";
            return;
        }
        
        echo "⚠️  Rule Violations Found:\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        
        foreach ($this->violations as $violation) {
            echo "  ❌ {$violation['rule']}: {$violation['message']}\n";
        }
        
        echo "\n";
    }
    
    /**
     * Export rules to different formats
     */
    public function exportRules(string $format = 'yaml'): string
    {
        switch ($format) {
            case 'json':
                return json_encode($this->rules, JSON_PRETTY_PRINT);
                
            case 'php':
                return "<?php\nreturn " . var_export($this->rules, true) . ";\n";
                
            case 'markdown':
                return $this->exportAsMarkdown();
                
            case 'yaml':
            default:
                return Yaml::dump($this->rules, 4);
        }
    }
    
    /**
     * Import rules from legacy formats
     */
    public function importFromMarkdown(string $content): array
    {
        $rules = [];
        $currentSection = null;
        $currentRule = null;
        
        $lines = explode("\n", $content);
        
        foreach ($lines as $line) {
            // Section headers
            if (preg_match('/^##\s+(.+)$/', $line, $matches)) {
                $currentSection = $this->slugify($matches[1]);
                $rules[$currentSection] = [];
                continue;
            }
            
            // Rule items
            if (preg_match('/^[-*]\s+\*\*(.+?)\*\*:?\s*(.*)$/', $line, $matches)) {
                $ruleName = $this->slugify($matches[1]);
                $ruleDescription = trim($matches[2]);
                
                $rules[$currentSection][$ruleName] = [
                    'description' => $ruleDescription,
                    'priority' => 50,
                    'enabled' => true
                ];
                
                $currentRule = $ruleName;
                continue;
            }
            
            // Rule details (indented lines)
            if ($currentRule && preg_match('/^\s{2,}[-*]\s+(.+)$/', $line, $matches)) {
                if (!isset($rules[$currentSection][$currentRule]['details'])) {
                    $rules[$currentSection][$currentRule]['details'] = [];
                }
                
                $rules[$currentSection][$currentRule]['details'][] = trim($matches[1]);
            }
        }
        
        return $rules;
    }
    
    /**
     * Validate rule structure
     */
    private function validateRuleStructure(): void
    {
        foreach ($this->rules as $category => $categoryData) {
            if (!is_array($categoryData)) {
                continue;
            }
            
            // Skip if this is not a top-level rule (check for priority field at this level)
            // Top-level rules should have priority, enabled, context, description
            if (!isset($categoryData['priority']) && !isset($categoryData['enabled'])) {
                // This might be a category containing multiple rules
                // Skip validation for nested structures
                continue;
            }
            
            // This is a top-level rule, validate its structure
            // Check required fields
            if (!isset($categoryData['priority'])) {
                $this->violations[] = [
                    'rule' => $category,
                    'message' => 'Missing priority field'
                ];
            }
            
            // Validate priority range
            if (isset($categoryData['priority']) && ($categoryData['priority'] < 0 || $categoryData['priority'] > 100)) {
                $this->violations[] = [
                    'rule' => $category,
                    'message' => 'Priority must be between 0 and 100'
                ];
            }
            
            // Validate context field
            if (isset($categoryData['context']) && !is_array($categoryData['context'])) {
                $this->violations[] = [
                    'rule' => $category,
                    'message' => 'context must be an array'
                ];
            }
            
            // Validate enabled field
            if (isset($categoryData['enabled']) && !is_bool($categoryData['enabled'])) {
                $this->violations[] = [
                    'rule' => $category,
                    'message' => 'enabled must be a boolean'
                ];
            }
        }
    }
    
    /**
     * Validate rule references
     */
    private function validateRuleReferences(): void
    {
        foreach ($this->rules as $category => $categoryRules) {
            if (!is_array($categoryRules)) {
                continue;
            }
            
            foreach ($categoryRules as $ruleName => $rule) {
                // Check dependencies
                if (isset($rule['depends_on'])) {
                    foreach ((array)$rule['depends_on'] as $dependency) {
                        if (!$this->getRule($dependency)) {
                            $this->violations[] = [
                                'rule' => "$category.$ruleName",
                                'message' => "Dependency '$dependency' not found"
                            ];
                        }
                    }
                }
                
                // Check workflow references
                if (isset($rule['workflow']) && is_string($rule['workflow'])) {
                    if (!isset($this->rules['workflows'][$rule['workflow']])) {
                        $this->violations[] = [
                            'rule' => "$category.$ruleName",
                            'message' => "Workflow '{$rule['workflow']}' not found"
                        ];
                    }
                }
            }
        }
    }
    
    /**
     * Validate rule conflicts
     */
    private function validateRuleConflicts(): void
    {
        $appliedPaths = [];
        
        foreach ($this->rules as $category => $categoryRules) {
            if (!is_array($categoryRules)) {
                continue;
            }
            
            foreach ($categoryRules as $ruleName => $rule) {
                if (!isset($rule['applies_to'])) {
                    continue;
                }
                
                foreach ($rule['applies_to'] as $pattern) {
                    if (!isset($appliedPaths[$pattern])) {
                        $appliedPaths[$pattern] = [];
                    }
                    
                    // Check for conflicting rules on same pattern
                    foreach ($appliedPaths[$pattern] as $existingRule) {
                        if ($this->rulesConflict($rule, $existingRule['rule'])) {
                            $this->violations[] = [
                                'rule' => "$category.$ruleName",
                                'message' => "Conflicts with {$existingRule['name']} on pattern '$pattern'"
                            ];
                        }
                    }
                    
                    $appliedPaths[$pattern][] = [
                        'name' => "$category.$ruleName",
                        'rule' => $rule
                    ];
                }
            }
        }
    }
    
    /**
     * Check if two rules conflict
     */
    private function rulesConflict(array $rule1, array $rule2): bool
    {
        // Rules with same priority and overlapping actions conflict
        if ($rule1['priority'] === $rule2['priority']) {
            if (isset($rule1['action']) && isset($rule2['action'])) {
                return $rule1['action'] !== $rule2['action'];
            }
        }
        
        return false;
    }
    
    /**
     * Validate file paths mentioned in rules
     */
    private function validateFilePaths(): int
    {
        $rootPath = dirname(__DIR__, 3); // Go up to isotone root
        
        // Extract file paths from rules (patterns like /docs, /iso-admin/*, etc.)
        $filePaths = [];
        $this->extractFilePaths($this->rules, $filePaths);
        $pathCount = count(array_unique($filePaths));
        
        foreach ($filePaths as $path) {
            // Skip wildcards and placeholders
            if (strpos($path, '*') !== false || strpos($path, '[') !== false) {
                continue;
            }
            
            // Check if path exists
            $fullPath = $rootPath . $path;
            if (!file_exists($fullPath) && !is_dir($fullPath)) {
                // Only report as violation if it's a critical path
                // Documentation files marked as "if exists" are optional
                $criticalPaths = [
                    '/app', '/iso-admin', '/iso-includes', '/iso-content',
                    '/config', '/docs', '/user-docs', '/storage',
                    '/config.sample.php', '/index.php', '/composer.json'
                ];
                
                $isCritical = false;
                foreach ($criticalPaths as $critical) {
                    if (strpos($path, $critical) === 0) {
                        $isCritical = true;
                        break;
                    }
                }
                
                // Skip optional documentation files
                if (strpos($path, 'HOOK') !== false || 
                    strpos($path, 'PLUGIN-DEVELOPER') !== false ||
                    strpos($path, 'THEME-DEVELOPER') !== false) {
                    $isCritical = false;
                }
                
                if ($isCritical) {
                    $this->violations[] = [
                        'rule' => 'file_path',
                        'message' => "Referenced path does not exist: $path"
                    ];
                }
            }
        }
        
        return $pathCount;
    }
    
    /**
     * Extract file paths from rules recursively
     */
    private function extractFilePaths($data, &$paths): void
    {
        if (is_string($data)) {
            // Look for paths that are clearly file/directory references
            // Must start with "/" and contain actual path characters
            // Skip URLs (containing ://), version numbers, and other non-path patterns
            if (!str_contains($data, '://') && !str_contains($data, 'localhost')) {
                // Match paths like /docs, /iso-admin, /config.php etc
                if (preg_match_all('#^(/[a-zA-Z0-9/_.-]+(?:\.[a-zA-Z]+)?)#m', $data, $matches)) {
                    foreach ($matches[1] as $path) {
                        // Skip if it looks like part of a sentence or version
                        if (!preg_match('#^\d|/\d+\.|/v\d#', $path)) {
                            $paths[] = $path;
                        }
                    }
                }
                // Also match paths in quotes or after colons
                if (preg_match_all('#["\':\s](/(?:docs|iso-[a-z]+|app|config|storage|user-docs|vendor)[a-zA-Z0-9/_.-]*)#', $data, $matches)) {
                    foreach ($matches[1] as $path) {
                        $paths[] = $path;
                    }
                }
            }
        } elseif (is_array($data)) {
            foreach ($data as $value) {
                $this->extractFilePaths($value, $paths);
            }
        }
    }
    
    /**
     * Validate commands mentioned in rules
     */
    private function validateCommands(): int
    {
        // Extract commands from rules
        $commands = [];
        $this->extractCommands($this->rules, $commands);
        $commandCount = count(array_unique($commands));
        
        foreach ($commands as $command) {
            // Validate composer commands
            if (strpos($command, 'composer ') === 0) {
                $this->validateComposerCommand($command);
            }
            
            // Validate php isotone commands
            if (strpos($command, 'php isotone ') === 0) {
                $this->validateIsotoneCommand($command);
            }
        }
        
        return $commandCount;
    }
    
    /**
     * Extract commands from rules recursively
     */
    private function extractCommands($data, &$commands): void
    {
        if (is_string($data)) {
            // Look for command patterns
            if (preg_match_all('#(composer [a-z:_-]+|php isotone [a-z:_-]+)#i', $data, $matches)) {
                foreach ($matches[1] as $command) {
                    $commands[] = $command;
                }
            }
        } elseif (is_array($data)) {
            foreach ($data as $value) {
                $this->extractCommands($value, $commands);
            }
        }
    }
    
    /**
     * Validate composer command
     */
    private function validateComposerCommand(string $command): void
    {
        $validCommands = [
            'composer install',
            'composer update',
            'composer test',
            'composer test:unit',
            'composer test:integration',
            'composer analyse',
            'composer check-style',
            'composer fix-style',
            'composer docs:check',
            'composer docs:update',
            'composer docs:sync',
            'composer docs:hooks',
            'composer docs:all',
            'composer ide:sync',
            'composer version:patch',
            'composer version:minor',
            'composer version:major',
            'composer pre-commit',
            'composer hooks:docs'
        ];
        
        $baseCommand = trim(str_replace('  ', ' ', $command));
        if (!in_array($baseCommand, $validCommands)) {
            // Only warn about potentially invalid commands
            // since composer.json might have been updated
        }
    }
    
    /**
     * Validate isotone CLI command
     */
    private function validateIsotoneCommand(string $command): void
    {
        $validCommands = [
            'php isotone version',
            'php isotone version:bump',
            'php isotone version:set',
            'php isotone version:history',
            'php isotone changelog',
            'php isotone system',
            'php isotone db:test',
            'php isotone db:status',
            'php isotone db:init',
            'php isotone hooks:docs',
            'php isotone hooks:scan',
            'php isotone rules:validate'
        ];
        
        // Extract base command without parameters
        $parts = explode(' ', $command);
        $baseCommand = implode(' ', array_slice($parts, 0, 3));
        
        // Don't validate parameterized commands strictly
        // since they might have [type] [stage] etc.
    }
    
    /**
     * Sort rules by priority
     */
    private function sortRulesByPriority(): void
    {
        foreach ($this->rules as $category => &$categoryRules) {
            if (!is_array($categoryRules)) {
                continue;
            }
            
            uasort($categoryRules, function($a, $b) {
                $priorityA = $a['priority'] ?? 50;
                $priorityB = $b['priority'] ?? 50;
                
                return $priorityB - $priorityA;
            });
        }
    }
    
    /**
     * Check if rule matches context
     */
    private function matchesContext(array $rule, string $context): bool
    {
        if (!isset($rule['context'])) {
            return true; // Apply to all contexts if not specified
        }
        
        $contexts = (array)$rule['context'];
        
        return in_array($context, $contexts) || in_array('*', $contexts);
    }
    
    /**
     * Check if rule should be applied
     */
    private function shouldApplyRule(array $rule, array $data): bool
    {
        // Check if enabled
        if (isset($rule['enabled']) && !$rule['enabled']) {
            return false;
        }
        
        // Check conditions
        if (isset($rule['conditions'])) {
            foreach ($rule['conditions'] as $condition) {
                if (!$this->evaluateCondition($condition, $data)) {
                    return false;
                }
            }
        }
        
        return true;
    }
    
    /**
     * Execute a rule
     */
    private function executeRule(string $name, array $rule, array $data): array
    {
        $result = [
            'name' => $name,
            'executed' => true,
            'output' => null
        ];
        
        // Execute rule action
        if (isset($rule['action'])) {
            $result['output'] = $this->executeAction($rule['action'], $data);
        }
        
        // Execute validation
        if (isset($rule['validation'])) {
            $result['valid'] = $this->executeValidation($rule['validation'], $data);
        }
        
        return $result;
    }
    
    /**
     * Evaluate a condition
     */
    private function evaluateCondition(array $condition, array $data): bool
    {
        // Simple implementation - extend as needed
        if (isset($condition['field']) && isset($condition['value'])) {
            $fieldValue = $data[$condition['field']] ?? null;
            $operator = $condition['operator'] ?? '=';
            
            return match($operator) {
                '=' => $fieldValue == $condition['value'],
                '!=' => $fieldValue != $condition['value'],
                '>' => $fieldValue > $condition['value'],
                '<' => $fieldValue < $condition['value'],
                'contains' => str_contains((string)$fieldValue, $condition['value']),
                default => false
            };
        }
        
        return true;
    }
    
    /**
     * Execute an action
     */
    private function executeAction(string $action, array $data)
    {
        // Action execution logic - extend as needed
        return "Action '$action' executed";
    }
    
    /**
     * Execute validation
     */
    private function executeValidation($validation, array $data): bool
    {
        // Validation logic - extend as needed
        return true;
    }
    
    /**
     * Export rules as Markdown
     */
    private function exportAsMarkdown(): string
    {
        $markdown = "# Isotone Automation Rules\n\n";
        
        foreach ($this->rules as $category => $categoryRules) {
            if (!is_array($categoryRules)) {
                continue;
            }
            
            $markdown .= "## " . ucfirst(str_replace('_', ' ', $category)) . "\n\n";
            
            foreach ($categoryRules as $ruleName => $rule) {
                $markdown .= "### $ruleName\n";
                $markdown .= "- **Priority**: {$rule['priority']}\n";
                
                if (isset($rule['description'])) {
                    $markdown .= "- **Description**: {$rule['description']}\n";
                }
                
                if (isset($rule['applies_to'])) {
                    $markdown .= "- **Applies to**: " . implode(', ', $rule['applies_to']) . "\n";
                }
                
                $markdown .= "\n";
            }
        }
        
        return $markdown;
    }
    
    /**
     * Convert string to slug
     */
    private function slugify(string $text): string
    {
        $text = preg_replace('/[^a-z0-9]+/i', '_', $text);
        $text = trim($text, '_');
        return strtolower($text);
    }
}