<?php

/**
 * Simple Dependency Injection Container
 * Implements Dependency Inversion Principle by managing object creation and dependencies
 */
class DependencyInjectionContainer {
    
    private static $instance = null;
    private $bindings = [];
    private $instances = [];
    
    private function __construct() {}
    
    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Bind an interface to a concrete implementation
     * @param string $interface
     * @param string $concrete
     */
    public function bind(string $interface, string $concrete): void {
        $this->bindings[$interface] = $concrete;
    }
    
    /**
     * Resolve an interface to a concrete instance
     * @param string $interface
     * @return mixed
     */
    public function resolve(string $interface) {
        // Check if we already have an instance
        if (isset($this->instances[$interface])) {
            return $this->instances[$interface];
        }
        
        // Check if we have a binding
        if (isset($this->bindings[$interface])) {
            $concrete = $this->bindings[$interface];
            
            // Create new instance
            $instance = new $concrete();
            
            // Store singleton instances
            $this->instances[$interface] = $instance;
            
            return $instance;
        }
        
        // If no binding, try to instantiate the interface directly
        if (class_exists($interface)) {
            $instance = new $interface();
            $this->instances[$interface] = $instance;
            return $instance;
        }
        
        throw new Exception("No binding found for {$interface} and class does not exist.");
    }
    
    /**
     * Initialize default bindings for the application
     */
    public function initializeDefaults(): void {
        // User repository bindings
        $this->bind(UserRepositoryInterface::class, User::class);
        
        // Admin management bindings
        $this->bind(AdminPatientManagerInterface::class, AdminPatientManager::class);
        $this->bind(AdminTherapistManagerInterface::class, AdminTherapistManager::class);
        $this->bind(AdminTherapistLicenseManagerInterface::class, AdminTherapistLicenseManager::class);
        $this->bind(AdminTherapistPerformanceInterface::class, AdminTherapistPerformance::class);
        $this->bind(AdminAuditManagerInterface::class, AdminAuditManager::class);
        $this->bind(AdminRBACManagerInterface::class, AdminRBACManager::class);
        
        // Patient service bindings
        $this->bind(PatientRepositoryInterface::class, Patient::class);
        $this->bind(PatientWellnessInterface::class, Patient::class);
        $this->bind(PatientJournalInterface::class, Patient::class);
        $this->bind(PatientIntakeInterface::class, Patient::class);
        
        // Therapist service bindings
        $this->bind(TherapistRepositoryInterface::class, Therapist::class);
        $this->bind(TherapistPatientInsightInterface::class, Therapist::class);
    }
    
    /**
     * Clear all bindings and instances (useful for testing)
     */
    public function clear(): void {
        $this->bindings = [];
        $this->instances = [];
    }
}
