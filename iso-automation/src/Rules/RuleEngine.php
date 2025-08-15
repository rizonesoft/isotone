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
        
        // Validate rule structure
        $this->validateRuleStructure();
        
        // Validate rule references
        $this->validateRuleReferences();
        
        // Validate rule conflicts
        $this->validateRuleConflicts();
        
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
        foreach ($this->rules as $category => $categoryRules) {
            if (isset($categoryRules[$name])) {
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
        foreach ($this->rules as $category => $categoryRules) {
            if (!is_array($categoryRules)) {
                continue;
            }
            
            foreach ($categoryRules as $ruleName => $rule) {
                // Check required fields
                if (!isset($rule['priority'])) {
                    $this->violations[] = [
                        'rule' => "$category.$ruleName",
                        'message' => 'Missing priority field'
                    ];
                }
                
                // Validate priority range
                if (isset($rule['priority']) && ($rule['priority'] < 0 || $rule['priority'] > 100)) {
                    $this->violations[] = [
                        'rule' => "$category.$ruleName",
                        'message' => 'Priority must be between 0 and 100'
                    ];
                }
                
                // Validate applies_to patterns
                if (isset($rule['applies_to']) && !is_array($rule['applies_to'])) {
                    $this->violations[] = [
                        'rule' => "$category.$ruleName",
                        'message' => 'applies_to must be an array'
                    ];
                }
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