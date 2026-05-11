<?php
require_once __DIR__ . '/../../Interfaces/Observer/IObserver.php';
require_once __DIR__ . '/../../Interfaces/Observer/ISubject.php';
require_once __DIR__ . '/../../Core/SingletonDatabase.php';
use Interfaces\Observer\IObserver;
use Interfaces\Observer\ISubject;
class NotificationService implements ISubject {
    private array $observers = [];
    private array $eventQueue = [];
    public function registerObserver(IObserver $o): void {
        $this->observers[] = $o;
    }
    public function removeObserver(IObserver $o): void {
        $this->observers = array_filter($this->observers, fn($obs) => $obs !== $o);
    }
    public function notifyObservers(string $event, array $data): void {
        foreach ($this->observers as $observer) {
            $observer->update($event, $data);
        }
    }
    public function publishEvent(string $event, array $data): void {
        $this->eventQueue[] = ['event' => $event, 'data' => $data];
        $this->notifyObservers($event, $data);
    }
    public function queueNotification(int $userId, string $msg, string $type): void {
        $db = SingletonDatabase::getInstance()->getConnection();
        $stmt = $db->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $msg, $type]);
    }
}