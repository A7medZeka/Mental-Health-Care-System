<?php
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/Observers/ModeratorObserver.php';

class Moderator extends User {

    private string $assigned_section;
    private ModeratorObserver $observer;
    public function __construct(int $moderator_id, string $assigned_section = 'Forum') {

        parent::__construct();

        $this->assigned_section = $assigned_section;
        $this->observer = new ModeratorObserver($moderator_id);
    }

    public function getAssignedSection(): string {
        return $this->assigned_section;
    }
    public function getObserver(): ModeratorObserver {
        return $this->observer;
    }
}