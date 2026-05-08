<?php
require_once __DIR__ . '/../Core/DependencyInjectionContainer.php';
require_once __DIR__ . '/../Models/Moderator.php';
require_once __DIR__ . '/../Models/Services/NotificationService.php';

class ModeratorDashboardController {
    public function handleRequest() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $currentModId = $_SESSION['user_id'] ?? null;

        if (!$currentModId || $_SESSION['role'] !== 'Moderator') {
            header('Location: ../Views/Auth/login.php');
            exit();
        }
        $container = DependencyInjectionContainer::getInstance();
        $notifier = $container->resolve(NotificationService::class);
        $moderator = new Moderator($currentModId, 'Forum');
        $notifier->registerObserver($moderator->getObserver());
        include __DIR__ . '/../Views/Moderator/dashboard.php';
    }
}
?>