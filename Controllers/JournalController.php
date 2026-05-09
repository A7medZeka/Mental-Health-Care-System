<?php
// Controllers/JournalController.php
require_once __DIR__ . '/../Models/Repositories/JournalRepository.php';

/**
 * JournalController — UC 22: Manage Journal Entry Privacy.
 */
class JournalController {

    private JournalRepository $repo;

    public function __construct(JournalRepository $repo) {
        $this->repo = $repo;
    }

    /**
     * +saveEntry(patientId, content, privacyLevel) : void
     */
    public function saveEntry(int $patientId, string $title, string $content, string $privacyLevel = 'Private'): array {
        if (empty($title) || empty($content)) {
            return ['success' => false, 'message' => 'Title and content are required.'];
        }

        $valid = ['Private', 'Shared'];
        if (!in_array($privacyLevel, $valid, true)) {
            $privacyLevel = 'Private';
        }

        $ok = $this->repo->storeEntry([
            'patient_id'    => $patientId,
            'title'         => $title,
            'content'       => $content,
            'privacy_level' => $privacyLevel,
        ]);

        return ['success' => $ok, 'message' => $ok ? 'Journal entry saved.' : 'Save failed.'];
    }

    /**
     * +updatePrivacy(entryId, level) : void
     */
    public function updatePrivacy(int $entryId, int $patientId, string $level): array {
        $valid = ['Private', 'Shared'];
        if (!in_array($level, $valid, true)) {
            return ['success' => false, 'message' => 'Invalid privacy level.'];
        }

        $ok = $this->repo->updatePrivacyFlag($entryId, $level);
        return ['success' => $ok, 'message' => $ok ? 'Privacy updated.' : 'Update failed.'];
    }

    /**
     * +getSharedEntries(patientId) : array
     */
    public function getSharedEntries(int $patientId): array {
        return $this->repo->getSharedEntries($patientId);
    }

    public function getEntries(int $patientId, int $limit = 10): array {
        return $this->repo->fetchEntries($patientId, $limit);
    }
}
