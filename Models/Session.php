<?php
// Models/Session.php
require_once __DIR__ . '/../Interfaces/IStateMachine.php';
require_once __DIR__ . '/Therapist.php';
require_once __DIR__ . '/ClinicalNote.php'; // ربط كلاس الملحوظات

class Session implements IStateMachine {
    private int $session_id;
    private int $appointment_id;
    private string $meeting_link;
    private $actual_start_time;
    private $actual_end_time;
    private string $session_state;

    // 1. علاقة الـ Association القديمة (Session contains Therapist)
    private ?Therapist $therapist;

    // ==========================================================
    // 2. تحقيق الـ Composition (المعين الأسود) Label: "has"
    // التعددية هي 0..* يعني الجلسة الواحدة ليها قائمة ملحوظات
    // ==========================================================
    private array $clinicalNotes = [];

    public function __construct(array $data = [], ?Therapist $therapist = null) {
        $this->session_id        = (int) ($data['session_id'] ?? 0);
        $this->appointment_id    = (int) ($data['appointment_id'] ?? 0);
        $this->meeting_link      = $data['meeting_link'] ?? '';
        $this->actual_start_time = $data['actual_start_time'] ?? null;
        $this->actual_end_time   = $data['actual_end_time'] ?? null;
        $this->session_state     = $data['session_state'] ?? 'Scheduled';
        $this->therapist         = $therapist;
    }

    /**
     * دالة إضافة ملحوظة (تحقق الـ Composition)
     * الجلسة بتستقبل الملحوظة وتخزنها في قائمتها الخاصة
     */
    public function addClinicalNote(ClinicalNote $note): void {
        // التأكد إن الملحوظة تابعة لنفس الجلسة برمجياً
        if ($note->getSessionId() === $this->session_id) {
            $this->clinicalNotes[] = $note;
        }
    }

    /**
     * جلب كل الملحوظات (0..*)
     */
    public function getClinicalNotes(): array {
        return $this->clinicalNotes;
    }

    // Getters وحل مشاكل الـ IDE
    public function getSessionId(): int { return $this->session_id; }
    public function getTherapist(): ?Therapist { return $this->therapist; }
    public function getState(): string { return $this->session_state; }

    public function transition(string $newState): bool {
        $this->session_state = $newState;
        return true;
    }
}