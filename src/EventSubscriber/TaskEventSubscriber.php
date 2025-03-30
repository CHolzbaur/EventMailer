<?php

namespace KimaiPlugin\EventMailerBundle\EventSubscriber;

use App\Event\TaskUpdateEvent;
use App\Entity\Task;
use App\Configuration\SystemConfiguration;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class TaskEventSubscriber implements EventSubscriberInterface
{
    private MailerInterface \$mailer;
    private Connection \$conn;
    private SystemConfiguration \$config;

    public function __construct(MailerInterface \$mailer, Connection \$conn, SystemConfiguration \$config)
    {
        \$this->mailer = \$mailer;
        \$this->conn = \$conn;
        \$this->config = \$config;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            TaskUpdateEvent::class => ['onTaskUpdate']
        ];
    }

    public function onTaskUpdate(TaskUpdateEvent \$event)
    {
        // Nur ausführen, wenn die Plugin-Einstellung zum Mailversand aktiviert ist
        if (!\$this->config->find('plugin_event_mailer.send_task_assignment_mail')) {
            return;
        }

        /** @var Task \$task */
        \$task = \$event->getTask();
        \$originalTask = \$event->getOriginalTask();

        \$user = \$task->getUser();
        \$oldUser = \$originalTask->getUser();

        // Nur reagieren, wenn ein Task neu zugewiesen wird (vorher kein User, jetzt ein User)
        if (\$user === null || (\$oldUser !== null && \$oldUser->getId() === \$user->getId())) {
            return;
        }

        \$userId = \$user->getId();
        \$activity = \$task->getActivity();
        \$activityId = \$activity->getId();

        // Prüfe Flag mail_for_user: Existiert die Regel? Wenn ja, muss der User diese Option aktiviert haben.
        if (\$this->hasRule('mail_for_user') && !\$this->userWantsMail(\$userId)) {
            return;
        }
        // Prüfe Flag mail_for_activity: Existiert die Regel? Wenn ja, darf die Activity nur, wenn der Flag gesetzt ist.
        if (\$this->hasRule('mail_for_activity') && !\$this->activityWantsMail(\$activityId)) {
            return;
        }

        // Basis-URL aus den Plugin-Einstellungen (konfigurierbar in der Oberfläche)
        \$baseUrl = rtrim(\$this->config->find('plugin_event_mailer.task_mailer_base_url'), '/');
        \$taskUrl = \$baseUrl . "/de/tasks/" . \$task->getId() . "/details";

        \$email = (new Email())
            ->to(\$user->getEmail())
            ->subject('Dir wurde eine Aufgabe zugeteilt')
            ->text(
                sprintf(
                    "Hallo %s,\n\nDir wurde eine neue Aufgabe zugeteilt:\n\nTitel: %s\nBeschreibung: %s\n\nAufgabe öffnen: %s\n\nViele Grüße\nDein Kimai-System",
                    \$user->getUsername(),
                    \$task->getTitle(),
                    \$task->getDescription(),
                    \$taskUrl
                )
            );

        \$this->mailer->send(\$email);
    }

    private function hasRule(string $ruleName): bool
    {
        try {
            $sql = "SELECT 1 FROM kimai2_meta_field_rules WHERE name = :name LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(['name' => $ruleName]);
            return (bool)$stmt->fetchColumn();
        } catch (\Exception $e) {
            // Tabelle existiert nicht, also behandeln wir es so, als gäbe es keinen Eintrag
            return false;
        }
    }

    private function userWantsMail(int $userId): bool
    {
        try {
            $sql = "SELECT 1 FROM kimai2_user_preferences WHERE user_id = :uid AND name = 'mail_for_user' AND value = 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(['uid' => $userId]);
            return (bool)$stmt->fetchColumn();
        } catch (\Exception $e) {
            // Tabelle existiert nicht, also verhalten wir uns, als gäbe es keinen Eintrag
            return false;
        }
    }

    private function activityWantsMail(int $activityId): bool
    {
        try {
            $sql = "SELECT 1 FROM kimai2_activities_meta WHERE activity_id = :aid AND name = 'mail_for_activity' AND value = 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(['aid' => $activityId]);
            return (bool)$stmt->fetchColumn();
        } catch (\Exception $e) {
            // Tabelle existiert nicht, also behandeln wir es so, als gäbe es keinen Eintrag
            return false;
        }
    }
}
