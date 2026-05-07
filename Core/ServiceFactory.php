<?php

require_once __DIR__ . '/DependencyInjectionContainer.php';
require_once __DIR__ . '/../Interfaces/UserRepositoryInterface.php';
require_once __DIR__ . '/../Interfaces/AdminPatientManagerInterface.php';
require_once __DIR__ . '/../Interfaces/AdminTherapistManagerInterface.php';
require_once __DIR__ . '/../Interfaces/AdminTherapistLicenseManagerInterface.php';
require_once __DIR__ . '/../Interfaces/AdminTherapistPerformanceInterface.php';
require_once __DIR__ . '/../Interfaces/AdminAuditManagerInterface.php';
require_once __DIR__ . '/../Interfaces/AdminRBACManagerInterface.php';
require_once __DIR__ . '/../Interfaces/PatientRepositoryInterface.php';
require_once __DIR__ . '/../Interfaces/PatientWellnessInterface.php';
require_once __DIR__ . '/../Interfaces/PatientJournalInterface.php';
require_once __DIR__ . '/../Interfaces/PatientIntakeInterface.php';
require_once __DIR__ . '/../Interfaces/TherapistRepositoryInterface.php';
require_once __DIR__ . '/../Interfaces/TherapistPatientInsightInterface.php';

/**
 * Service Factory for creating service instances using dependency injection
 * Provides a clean API for accessing services without direct instantiation
 */
class ServiceFactory {
    
    private static $container = null;
    
    /**
     * Get the dependency injection container
     * @return DependencyInjectionContainer
     */
    private static function getContainer(): DependencyInjectionContainer {
        if (self::$container === null) {
            self::$container = DependencyInjectionContainer::getInstance();
            self::$container->initializeDefaults();
        }
        return self::$container;
    }
    
    /**
     * Get user repository service
     * @return UserRepositoryInterface
     */
    public static function getUserRepository(): UserRepositoryInterface {
        return self::getContainer()->resolve(UserRepositoryInterface::class);
    }
    
    /**
     * Get admin patient manager service
     * @return AdminPatientManagerInterface
     */
    public static function getAdminPatientManager(): AdminPatientManagerInterface {
        return self::getContainer()->resolve(AdminPatientManagerInterface::class);
    }
    
    /**
     * Get admin therapist manager service
     * @return AdminTherapistManagerInterface
     */
    public static function getAdminTherapistManager(): AdminTherapistManagerInterface {
        return self::getContainer()->resolve(AdminTherapistManagerInterface::class);
    }
    
    /**
     * Get admin therapist license manager service
     * @return AdminTherapistLicenseManagerInterface
     */
    public static function getAdminTherapistLicenseManager(): AdminTherapistLicenseManagerInterface {
        return self::getContainer()->resolve(AdminTherapistLicenseManagerInterface::class);
    }
    
    /**
     * Get admin therapist performance service
     * @return AdminTherapistPerformanceInterface
     */
    public static function getAdminTherapistPerformance(): AdminTherapistPerformanceInterface {
        return self::getContainer()->resolve(AdminTherapistPerformanceInterface::class);
    }
    
    /**
     * Get admin audit manager service
     * @return AdminAuditManagerInterface
     */
    public static function getAdminAuditManager(): AdminAuditManagerInterface {
        return self::getContainer()->resolve(AdminAuditManagerInterface::class);
    }
    
    /**
     * Get admin RBAC manager service
     * @return AdminRBACManagerInterface
     */
    public static function getAdminRBACManager(): AdminRBACManagerInterface {
        return self::getContainer()->resolve(AdminRBACManagerInterface::class);
    }
    
    /**
     * Get patient repository service
     * @return PatientRepositoryInterface
     */
    public static function getPatientRepository(): PatientRepositoryInterface {
        return self::getContainer()->resolve(PatientRepositoryInterface::class);
    }
    
    /**
     * Get patient wellness service
     * @return PatientWellnessInterface
     */
    public static function getPatientWellnessService(): PatientWellnessInterface {
        return self::getContainer()->resolve(PatientWellnessInterface::class);
    }
    
    /**
     * Get patient journal service
     * @return PatientJournalInterface
     */
    public static function getPatientJournalService(): PatientJournalInterface {
        return self::getContainer()->resolve(PatientJournalInterface::class);
    }
    
    /**
     * Get patient intake service
     * @return PatientIntakeInterface
     */
    public static function getPatientIntakeService(): PatientIntakeInterface {
        return self::getContainer()->resolve(PatientIntakeInterface::class);
    }
    
    /**
     * Get therapist repository service
     * @return TherapistRepositoryInterface
     */
    public static function getTherapistRepository(): TherapistRepositoryInterface {
        return self::getContainer()->resolve(TherapistRepositoryInterface::class);
    }
    
    /**
     * Get therapist patient insight service
     * @return TherapistPatientInsightInterface
     */
    public static function getTherapistPatientInsightService(): TherapistPatientInsightInterface {
        return self::getContainer()->resolve(TherapistPatientInsightInterface::class);
    }
    
    /**
     * Reset the container (useful for testing)
     */
    public static function reset(): void {
        if (self::$container !== null) {
            self::$container->clear();
            self::$container = null;
        }
    }
}
